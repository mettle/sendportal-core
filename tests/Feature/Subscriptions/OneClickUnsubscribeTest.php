<?php

declare(strict_types=1);

namespace Tests\Feature\Subscriptions;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\Test;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Factories\MailAdapterFactory;
use Sendportal\Base\Interfaces\MailAdapterInterface;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\UnsubscribeEventType;
use Sendportal\Base\Services\Messages\DispatchMessage;
use Sendportal\Base\Services\Messages\DispatchTestMessage;
use Sendportal\Base\Services\Messages\MessageTrackingOptions;
use Sendportal\Base\Services\Messages\RelayMessage;
use Tests\TestCase;

class OneClickUnsubscribeTest extends TestCase
{
    use RefreshDatabase;

    /** @var MailAdapterInterface */
    private $adapter;

    protected function setUp(): void
    {
        parent::setUp();

        // Relay through a real RelayMessage so the headers reach the adapter.
        $this->adapter = new class () implements MailAdapterInterface {
            /** @var array<string, string> */
            public $headers;

            public function send(string $fromEmail, string $fromName, string $toEmail, string $subject, MessageTrackingOptions $trackingOptions, string $content, array $headers = []): string
            {
                $this->headers = $headers;

                return 'message-id';
            }
        };

        $factory = $this->createStub(MailAdapterFactory::class);
        $factory->method('adapter')->willReturn($this->adapter);

        $this->app->instance(RelayMessage::class, new RelayMessage($factory));
    }

    #[Test]
    public function campaign_emails_carry_a_one_click_header_that_unsubscribes_the_subscriber()
    {
        // given
        URL::forceScheme('https');
        $message = $this->createPendingCampaignMessage();

        // when
        app(DispatchMessage::class)->handle($message);

        // then
        $url = 'https://localhost/sendportal/subscriptions/unsubscribe/' . $message->hash;
        self::assertSame([
            'List-Unsubscribe' => '<' . $url . '>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ], $this->adapter->headers);

        // Opening the link (or a link scanner fetching it) only shows the page.
        $this->get($url)->assertOk();
        self::assertNull($message->subscriber->fresh()->unsubscribed_at);

        // The one-click POST unsubscribes straight away.
        $this->post($url, ['List-Unsubscribe' => 'One-Click'])->assertOk();

        $subscriber = $message->subscriber->fresh();
        self::assertNotNull($subscriber->unsubscribed_at);
        self::assertSame(UnsubscribeEventType::MANUAL_BY_SUBSCRIBER, $subscriber->unsubscribe_event_id);
        self::assertNotNull($message->fresh()->unsubscribed_at);
    }

    #[Test]
    public function the_one_click_header_is_left_out_when_the_link_is_not_https()
    {
        // given
        $message = $this->createPendingCampaignMessage();

        // when
        app(DispatchMessage::class)->handle($message);

        // then
        // RFC 8058 only allows one-click over HTTPS; the plain link still works.
        self::assertSame([
            'List-Unsubscribe' => '<http://localhost/sendportal/subscriptions/unsubscribe/' . $message->hash . '>',
        ], $this->adapter->headers);
    }

    #[Test]
    public function test_sends_carry_the_headers_but_cannot_unsubscribe_anyone()
    {
        // given
        URL::forceScheme('https');
        $campaign = $this->createCampaign($this->createEmailService());

        // when
        app(DispatchTestMessage::class)->handle($campaign->workspace_id, $campaign->id, 'admin@sendportal.test');

        // then
        self::assertSame('List-Unsubscribe=One-Click', $this->adapter->headers['List-Unsubscribe-Post']);

        // The test message is never saved, so its link resolves to nothing.
        $url = trim($this->adapter->headers['List-Unsubscribe'], '<>');
        $this->post($url, ['List-Unsubscribe' => 'One-Click'])->assertNotFound();
    }

    #[Test]
    public function one_click_unsubscribe_does_not_need_a_csrf_token()
    {
        // given
        // Serve the public routes behind the CSRF middleware that Laravel's default web
        // group uses, and stop it from skipping its check because tests are running.
        $csrf = class_exists(PreventRequestForgery::class) ? PreventRequestForgery::class : ValidateCsrfToken::class;
        Route::middleware([StartSession::class, $csrf])->prefix('web')->group(function () {
            Sendportal::publicWebRoutes();
        });
        $this->app['env'] = 'production';

        $message = Message::factory()->create();

        // then
        // The unsubscribe page's own form still needs a token...
        $this->put('web/subscriptions/subscriptions/' . $message->hash, ['unsubscribed' => 1])->assertStatus(419);
        self::assertNull($message->subscriber->fresh()->unsubscribed_at);

        // ...but mailbox providers can POST without one.
        $this->post('web/subscriptions/unsubscribe/' . $message->hash, ['List-Unsubscribe' => 'One-Click'])->assertOk();
        self::assertNotNull($message->subscriber->fresh()->unsubscribed_at);
    }

    private function createPendingCampaignMessage(): Message
    {
        $campaign = $this->createCampaign($this->createEmailService());

        return Message::factory()->pending()->create([
            'workspace_id' => $campaign->workspace_id,
            'source_id' => $campaign->id,
        ]);
    }
}
