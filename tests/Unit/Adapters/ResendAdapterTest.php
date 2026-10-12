<?php

declare(strict_types=1);

namespace Tests\Unit\Adapters;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use Resend\Client;
use Resend\Transporters\HttpTransporter;
use Resend\ValueObjects\ApiKey;
use Resend\ValueObjects\Transporter\BaseUri;
use Resend\ValueObjects\Transporter\Headers;
use Sendportal\Base\Adapters\ResendAdapter;
use Sendportal\Base\Services\Messages\MessageTrackingOptions;
use Tests\TestCase;

class ResendAdapterTest extends TestCase
{
    #[Test]
    public function it_sends_an_email_and_returns_the_resend_email_id()
    {
        // given
        $history = [];

        $handler = HandlerStack::create(new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode(['id' => '49a3999c-0ce1-4ea6-ab68-afcd6dc2e794'])),
        ]));
        $handler->push(Middleware::history($history));

        $adapter = new class (['key' => 're_test']) extends ResendAdapter {
            public function setClient(Client $client): void
            {
                $this->client = $client;
            }
        };

        $adapter->setClient(new Client(new HttpTransporter(
            new GuzzleClient(['handler' => $handler]),
            BaseUri::from('api.resend.com'),
            Headers::withAuthorization(ApiKey::from('re_test'))
        )));

        // when
        $messageId = $adapter->send('from@example.com', 'From Name', 'to@example.com', 'Subject', new MessageTrackingOptions(), '<p>Hello</p>');

        // then
        self::assertEquals('49a3999c-0ce1-4ea6-ab68-afcd6dc2e794', $messageId);

        self::assertCount(1, $history);

        $request = $history[0]['request'];

        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('https://api.resend.com/emails', (string) $request->getUri());
        self::assertEquals('Bearer re_test', $request->getHeaderLine('Authorization'));
        self::assertEquals([
            'from' => 'From Name <from@example.com>',
            'to' => ['to@example.com'],
            'subject' => 'Subject',
            'html' => '<p>Hello</p>',
        ], json_decode((string) $request->getBody(), true));
    }
}
