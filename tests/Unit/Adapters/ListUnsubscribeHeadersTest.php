<?php

declare(strict_types=1);

namespace Tests\Unit\Adapters;

use Aws\CommandInterface;
use Aws\MockHandler as AwsMockHandler;
use Aws\Result;
use Aws\Sdk;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Mailgun\HttpClient\HttpClientConfigurator;
use Mailgun\Mailgun;
use Mailjet\Client as MailjetClient;
use PHPUnit\Framework\Attributes\Test;
use Postal\Client as PostalClient;
use Postmark\PostmarkClient;
use Psr\Http\Message\RequestInterface;
use ReflectionProperty;
use SendGrid;
use SendGrid\Mail\Mail;
use SendGrid\Response as SendgridResponse;
use Sendportal\Base\Adapters\MailgunMailAdapter;
use Sendportal\Base\Adapters\MailjetAdapter;
use Sendportal\Base\Adapters\PostalAdapter;
use Sendportal\Base\Adapters\PostmarkMailAdapter;
use Sendportal\Base\Adapters\SendgridMailAdapter;
use Sendportal\Base\Adapters\SesMailAdapter;
use Sendportal\Base\Adapters\SmtpAdapter;
use Sendportal\Base\Interfaces\MailAdapterInterface;
use Sendportal\Base\Services\Messages\MessageTrackingOptions;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\RawMessage;
use Tests\TestCase;

/**
 * Each adapter has to put the List-Unsubscribe headers into its own provider's
 * request format. These tests check the request that actually goes out.
 */
class ListUnsubscribeHeadersTest extends TestCase
{
    private const HEADERS = [
        'List-Unsubscribe' => '<https://sendportal.test/subscriptions/unsubscribe/0b5e4c1a-3f0e-4a7e-9d2b-6c1f8e2a9b47>',
        'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
    ];

    /** @var array<int, array{request: RequestInterface}> */
    private $requests = [];

    #[Test]
    public function ses_sends_the_headers_through_sesv2()
    {
        // given
        $sent = null;
        $this->app->instance('aws', new Sdk([
            'version' => 'latest',
            'handler' => new AwsMockHandler([
                function (CommandInterface $command, RequestInterface $request) use (&$sent) {
                    $sent = $request;

                    return new Result(['MessageId' => 'ses-message-id']);
                },
            ]),
        ]));
        $adapter = new SesMailAdapter(['key' => 'key', 'secret' => 'secret', 'region' => 'eu-west-1']);

        // when
        $messageId = $this->send($adapter);

        // then
        self::assertSame('ses-message-id', $messageId);
        self::assertSame('/v2/email/outbound-emails', $sent->getUri()->getPath());

        $body = json_decode((string) $sent->getBody(), true);
        self::assertSame('Sendportal <from@sendportal.test>', $body['FromEmailAddress']);
        self::assertSame(['ToAddresses' => ['jane@example.com']], $body['Destination']);
        self::assertSame('Hello', $body['Content']['Simple']['Subject']['Data']);
        self::assertSame('<p>Hi</p>', $body['Content']['Simple']['Body']['Html']['Data']);
        self::assertSame([
            ['Name' => 'List-Unsubscribe', 'Value' => self::HEADERS['List-Unsubscribe']],
            ['Name' => 'List-Unsubscribe-Post', 'Value' => 'List-Unsubscribe=One-Click'],
        ], $body['Content']['Simple']['Headers']);
    }

    #[Test]
    public function postmark_sends_the_headers()
    {
        // given
        $client = new PostmarkClient('token');
        $client->setClient($this->fakeHttpClient('{"MessageID":"postmark-message-id","ErrorCode":0,"Message":"OK"}'));
        $adapter = $this->withClient(new PostmarkMailAdapter(), 'client', $client);

        // when
        $this->send($adapter);

        // then
        self::assertSame([
            ['Name' => 'List-Unsubscribe', 'Value' => self::HEADERS['List-Unsubscribe']],
            ['Name' => 'List-Unsubscribe-Post', 'Value' => 'List-Unsubscribe=One-Click'],
        ], $this->sentJson()['Headers']);
    }

    #[Test]
    public function sendgrid_sends_the_headers()
    {
        // given
        $sent = null;
        $client = $this->createStub(SendGrid::class);
        $client->method('send')->willReturnCallback(function (Mail $mail) use (&$sent) {
            $sent = $mail;

            return new SendgridResponse(202, '', ['X-Message-Id: sendgrid-message-id']);
        });
        $adapter = $this->withClient(new SendgridMailAdapter(), 'client', $client);

        // when
        $this->send($adapter);

        // then
        // The Mail object is what the SendGrid client JSON-encodes as the request body.
        self::assertSame(self::HEADERS, json_decode(json_encode($sent), true)['headers']);
    }

    #[Test]
    public function mailgun_sends_the_headers()
    {
        // given
        $client = new Mailgun(
            (new HttpClientConfigurator())
                ->setApiKey('key')
                ->setHttpClient($this->fakeHttpClient('{"id":"<mailgun-message-id>","message":"Queued. Thank you."}'))
        );
        $adapter = $this->withClient(new MailgunMailAdapter(['domain' => 'mg.sendportal.test']), 'client', $client);

        // when
        $this->send($adapter);

        // then
        $fields = $this->sentMultipartFields();
        self::assertSame(self::HEADERS['List-Unsubscribe'], $fields['h:List-Unsubscribe']);
        self::assertSame('List-Unsubscribe=One-Click', $fields['h:List-Unsubscribe-Post']);
    }

    #[Test]
    public function mailjet_sends_the_headers()
    {
        // given
        $client = new MailjetClient(
            'key',
            'secret',
            true,
            ['version' => 'v3.1'],
            $this->fakeHttpClient('{"Messages":[{"Status":"success","To":[{"Email":"jane@example.com","MessageID":288230376151711744}]}]}')
        );
        $adapter = $this->withClient(new MailjetAdapter(), 'client', $client);

        // when
        $this->send($adapter);

        // then
        self::assertSame(self::HEADERS, $this->sentJson()['Messages'][0]['Headers']);
    }

    #[Test]
    public function postal_sends_the_headers()
    {
        // given
        $client = new PostalClient(
            'https://postal.sendportal.test',
            'key',
            $this->fakeHttpClient('{"status":"success","data":{"message_id":"postal-message-id","messages":{"jane@example.com":{"id":42,"token":"abc"}}}}')
        );
        $adapter = $this->withClient(new PostalAdapter(), 'client', $client);

        // when
        $messageId = $this->send($adapter);

        // then
        self::assertSame('42', $messageId);
        self::assertSame(self::HEADERS, $this->sentJson()['headers']);
    }

    #[Test]
    public function smtp_sends_the_headers()
    {
        // given
        $sent = null;
        $transport = $this->createStub(EsmtpTransport::class);
        $transport->method('send')->willReturnCallback(function (RawMessage $message) use (&$sent) {
            $sent = $message;

            return new SentMessage($message, Envelope::create($message));
        });
        $adapter = $this->withClient(new SmtpAdapter(), 'transport', $transport);

        // when
        $this->send($adapter);

        // then
        $raw = $sent->toString();
        self::assertStringContainsString("\r\nList-Unsubscribe: " . self::HEADERS['List-Unsubscribe'] . "\r\n", $raw);
        self::assertStringContainsString("\r\nList-Unsubscribe-Post: List-Unsubscribe=One-Click\r\n", $raw);
    }

    private function send(MailAdapterInterface $adapter): string
    {
        return $adapter->send(
            'from@sendportal.test',
            'Sendportal',
            'jane@example.com',
            'Hello',
            new MessageTrackingOptions(),
            '<p>Hi</p>',
            self::HEADERS
        );
    }

    private function withClient(MailAdapterInterface $adapter, string $property, object $client): MailAdapterInterface
    {
        (new ReflectionProperty($adapter, $property))->setValue($adapter, $client);

        return $adapter;
    }

    /**
     * A Guzzle client (which is also a PSR-18 client) that records requests and
     * answers each one with the given JSON.
     */
    private function fakeHttpClient(string $responseJson): GuzzleClient
    {
        $stack = HandlerStack::create(new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], $responseJson),
        ]));
        $stack->push(Middleware::history($this->requests));

        return new GuzzleClient(['handler' => $stack]);
    }

    private function sentJson(): array
    {
        self::assertCount(1, $this->requests);

        return json_decode((string) $this->requests[0]['request']->getBody(), true);
    }

    /**
     * @return array<string, string>
     */
    private function sentMultipartFields(): array
    {
        self::assertCount(1, $this->requests);

        preg_match_all(
            '/Content-Disposition: form-data; name="([^"]+)"\r\n(?:[^\r\n]+\r\n)*\r\n(.*?)\r\n--/s',
            (string) $this->requests[0]['request']->getBody(),
            $matches
        );

        return array_combine($matches[1], $matches[2]);
    }
}
