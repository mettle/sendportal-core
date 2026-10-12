<?php

declare(strict_types=1);

namespace Tests\Feature\Webhooks;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\EmailServiceType;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\Subscriber;
use Sendportal\Base\Models\UnsubscribeEventType;
use Tests\TestCase;

class ResendWebhooksTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @var string */
    protected $route = 'sendportal.api.webhooks.resend';

    /** @var string */
    protected $webhookSecret;

    public function setUp(): void
    {
        parent::setUp();

        $this->webhookSecret = 'whsec_' . base64_encode(Str::random(32));
    }

    #[Test]
    public function it_accepts_delivery_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertNull($message->delivered_at);

        $webhook = $this->resolveWebhook('email.delivered', $message->message_id);

        // when
        $response = $this->sendWebhook($webhook);

        // then
        $response->assertOk();

        self::assertNotNull($message->refresh()->delivered_at);
    }

    #[Test]
    public function it_accepts_opened_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertEquals(0, $message->open_count);
        self::assertNull($message->opened_at);

        $webhook = $this->resolveWebhook('email.opened', $message->message_id);

        // when
        $response = $this->sendWebhook($webhook);

        // then
        $response->assertOk();

        self::assertEquals(1, $message->refresh()->open_count);
        self::assertNotNull($message->opened_at);
    }

    #[Test]
    public function it_accepts_clicked_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertEquals(0, $message->click_count);
        self::assertNull($message->clicked_at);

        $url = $this->faker->url();

        $webhook = $this->resolveWebhook('email.clicked', $message->message_id);

        $webhook['data']['click'] = [
            'ipAddress' => $this->faker->ipv4(),
            'link' => $url,
            'timestamp' => now()->toIso8601String(),
            'userAgent' => $this->faker->userAgent(),
        ];

        // when
        $response = $this->sendWebhook($webhook);

        // then
        $response->assertOk();

        self::assertEquals(1, $message->refresh()->click_count);
        self::assertNotNull($message->clicked_at);

        $this->assertDatabaseHas('sendportal_message_urls', [
            'source_id' => $message->source_id,
            'url' => $url,
            'click_count' => 1,
        ]);
    }

    #[Test]
    public function it_accepts_complained_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertNull($message->unsubscribed_at);

        $webhook = $this->resolveWebhook('email.complained', $message->message_id);

        // when
        $response = $this->sendWebhook($webhook);

        // then
        $response->assertOk();

        self::assertNotNull($message->refresh()->unsubscribed_at);
    }

    #[Test]
    public function it_accepts_permanent_bounce_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertNull($message->bounced_at);

        $webhook = $this->resolveWebhook('email.bounced', $message->message_id);

        $webhook['data']['bounce'] = [
            'message' => 'The recipient address does not exist.',
            'subType' => 'General',
            'type' => 'Permanent',
        ];

        // when
        $response = $this->sendWebhook($webhook);

        // then
        $response->assertOk();

        self::assertNotNull($message->refresh()->bounced_at);

        $this->assertDatabaseHas('sendportal_message_failures', [
            'message_id' => $message->id,
            'severity' => 'Permanent',
            'description' => 'The recipient address does not exist.',
        ]);
    }

    #[Test]
    public function it_accepts_transient_bounce_webhooks()
    {
        // given
        $message = $this->createMessage();

        $webhook = $this->resolveWebhook('email.bounced', $message->message_id);

        $webhook['data']['bounce'] = [
            'message' => 'The recipient mailbox is full.',
            'subType' => 'MailboxFull',
            'type' => 'Transient',
        ];

        // when
        $response = $this->sendWebhook($webhook);

        // then
        $response->assertOk();

        self::assertNull($message->refresh()->bounced_at);

        $this->assertDatabaseHas('sendportal_message_failures', [
            'message_id' => $message->id,
            'severity' => 'Transient',
        ]);
    }

    #[Test]
    public function it_accepts_suppressed_webhooks()
    {
        // given
        $message = $this->createMessage();

        $webhook = $this->resolveWebhook('email.suppressed', $message->message_id);

        $webhook['data']['suppressed'] = [
            'message' => 'The address is on the account-level suppression list.',
            'type' => 'OnAccountSuppressionList',
        ];

        // when
        $response = $this->sendWebhook($webhook);

        // then
        $response->assertOk();

        self::assertNotNull($message->refresh()->bounced_at);

        $this->assertDatabaseHas('sendportal_message_failures', [
            'message_id' => $message->id,
            'severity' => 'Permanent',
        ]);
    }

    #[Test]
    public function it_accepts_failed_webhooks()
    {
        // given
        $message = $this->createMessage();

        $webhook = $this->resolveWebhook('email.failed', $message->message_id);

        $webhook['data']['failed'] = [
            'reason' => 'reached_daily_quota',
        ];

        // when
        $response = $this->sendWebhook($webhook);

        // then
        $response->assertOk();

        self::assertNull($message->refresh()->bounced_at);

        $this->assertDatabaseHas('sendportal_message_failures', [
            'message_id' => $message->id,
            'severity' => 'Failed',
            'description' => 'reached_daily_quota',
        ]);
    }

    #[Test]
    public function it_rejects_webhooks_with_an_invalid_signature()
    {
        // given
        $message = $this->createMessage();

        $webhook = $this->resolveWebhook('email.delivered', $message->message_id);

        // when
        $response = $this->sendWebhook($webhook, 'whsec_' . base64_encode(Str::random(32)));

        // then
        $response->assertUnauthorized();

        self::assertNull($message->refresh()->delivered_at);
    }

    #[Test]
    public function it_rejects_webhooks_with_an_expired_timestamp()
    {
        // given
        $message = $this->createMessage();

        $webhook = $this->resolveWebhook('email.delivered', $message->message_id);

        // when
        $response = $this->sendWebhook($webhook, null, now()->subMinutes(10)->timestamp);

        // then
        $response->assertUnauthorized();

        self::assertNull($message->refresh()->delivered_at);
    }

    #[Test]
    public function it_rejects_webhooks_when_no_webhook_secret_is_configured()
    {
        // given
        $message = $this->createMessage([]);

        $webhook = $this->resolveWebhook('email.delivered', $message->message_id);

        // when
        $response = $this->sendWebhook($webhook);

        // then
        $response->assertUnauthorized();

        self::assertNull($message->refresh()->delivered_at);
    }

    #[Test]
    public function it_ignores_webhooks_for_unknown_messages()
    {
        // given
        $webhook = $this->resolveWebhook('email.delivered', (string) Str::uuid());

        // when
        $response = $this->sendWebhook($webhook);

        // then
        $response->assertOk();
        $response->assertSee('not processed');
    }

    #[Test]
    public function it_ignores_non_email_webhooks()
    {
        // given
        $webhook = [
            'type' => 'domain.created',
            'created_at' => now()->toIso8601String(),
            'data' => [
                'id' => (string) Str::uuid(),
                'name' => 'example.com',
            ],
        ];

        // when
        $response = $this->sendWebhook($webhook);

        // then
        $response->assertOk();
        $response->assertSee('not processed');
    }

    #[Test]
    public function it_unsubscribes_subscribers_from_contact_updated_webhooks()
    {
        // given
        $this->createResendEmailService(1);
        $subscriber = Subscriber::factory()->create(['workspace_id' => 1]);

        $webhook = $this->resolveContactWebhook('contact.updated', $subscriber->email, true);

        // when
        $response = $this->sendWebhook($webhook);

        // then
        $response->assertOk();

        $subscriber->refresh();

        self::assertNotNull($subscriber->unsubscribed_at);
        self::assertEquals(UnsubscribeEventType::EMAIL_SERVICE, $subscriber->unsubscribe_event_id);
    }

    #[Test]
    public function it_unsubscribes_subscribers_from_contact_created_webhooks()
    {
        // given
        $this->createResendEmailService(1);
        $subscriber = Subscriber::factory()->create(['workspace_id' => 1]);

        $webhook = $this->resolveContactWebhook('contact.created', $subscriber->email, true);

        // when
        $response = $this->sendWebhook($webhook);

        // then
        $response->assertOk();

        self::assertNotNull($subscriber->refresh()->unsubscribed_at);
    }

    #[Test]
    public function it_does_not_change_subscribers_from_contact_webhooks_that_are_still_subscribed()
    {
        // given
        $this->createResendEmailService(1);
        $subscriber = Subscriber::factory()->create(['workspace_id' => 1]);

        $webhook = $this->resolveContactWebhook('contact.updated', $subscriber->email, false);

        // when
        $response = $this->sendWebhook($webhook);

        // then
        $response->assertOk();

        self::assertNull($subscriber->refresh()->unsubscribed_at);
    }

    #[Test]
    public function it_does_not_overwrite_an_existing_unsubscribe_from_contact_webhooks()
    {
        // given
        $this->createResendEmailService(1);
        $subscriber = Subscriber::factory()->create([
            'workspace_id' => 1,
            'unsubscribed_at' => now()->subDay(),
            'unsubscribe_event_id' => UnsubscribeEventType::BOUNCE,
        ]);

        $webhook = $this->resolveContactWebhook('contact.updated', $subscriber->email, true);

        // when
        $response = $this->sendWebhook($webhook);

        // then
        $response->assertOk();

        self::assertEquals(UnsubscribeEventType::BOUNCE, $subscriber->refresh()->unsubscribe_event_id);
    }

    #[Test]
    public function it_only_unsubscribes_subscribers_in_workspaces_with_a_matching_webhook_secret()
    {
        // given
        $this->createResendEmailService(1);
        $this->createResendEmailService(2, 'whsec_' . base64_encode(Str::random(32)));

        $email = $this->faker->safeEmail();
        $matching = Subscriber::factory()->create(['workspace_id' => 1, 'email' => $email]);
        $other = Subscriber::factory()->create(['workspace_id' => 2, 'email' => $email]);

        $webhook = $this->resolveContactWebhook('contact.updated', $email, true);

        // when
        $response = $this->sendWebhook($webhook);

        // then
        $response->assertOk();

        self::assertNotNull($matching->refresh()->unsubscribed_at);
        self::assertNull($other->refresh()->unsubscribed_at);
    }

    #[Test]
    public function it_rejects_contact_webhooks_with_an_invalid_signature()
    {
        // given
        $this->createResendEmailService(1);
        $subscriber = Subscriber::factory()->create(['workspace_id' => 1]);

        $webhook = $this->resolveContactWebhook('contact.updated', $subscriber->email, true);

        // when
        $response = $this->sendWebhook($webhook, 'whsec_' . base64_encode(Str::random(32)));

        // then
        $response->assertUnauthorized();

        self::assertNull($subscriber->refresh()->unsubscribed_at);
    }

    protected function createResendEmailService(int $workspaceId, ?string $webhookSecret = null): EmailService
    {
        $emailService = EmailService::factory()->make([
            'type_id' => EmailServiceType::RESEND,
            'settings' => [
                'key' => 're_' . Str::random(),
                'webhook_secret' => $webhookSecret ?? $this->webhookSecret,
            ],
        ]);

        $emailService->workspace_id = $workspaceId;
        $emailService->save();

        return $emailService;
    }

    protected function resolveContactWebhook(string $type, string $email, bool $unsubscribed): array
    {
        return [
            'type' => $type,
            'created_at' => now()->toIso8601String(),
            'data' => [
                'id' => (string) Str::uuid(),
                'audience_id' => (string) Str::uuid(),
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
                'email' => $email,
                'first_name' => $this->faker->firstName(),
                'last_name' => $this->faker->lastName(),
                'unsubscribed' => $unsubscribed,
            ],
        ];
    }

    protected function createMessage(?array $settings = null): Message
    {
        $emailService = EmailService::factory()->create([
            'type_id' => EmailServiceType::RESEND,
            'settings' => $settings ?? [
                'key' => 're_' . Str::random(),
                'webhook_secret' => $this->webhookSecret,
            ],
        ]);

        $campaign = Campaign::factory()->create([
            'email_service_id' => $emailService->id,
        ]);

        return Message::factory()->create([
            'message_id' => (string) Str::uuid(),
            'source_id' => $campaign->id,
        ]);
    }

    protected function resolveWebhook(string $type, string $messageId): array
    {
        return [
            'type' => $type,
            'created_at' => now()->toIso8601String(),
            'data' => [
                'created_at' => now()->toIso8601String(),
                'email_id' => $messageId,
                'from' => 'Acme <onboarding@resend.dev>',
                'to' => [$this->faker->safeEmail()],
                'subject' => 'Sending this example',
            ],
        ];
    }

    /**
     * Send a webhook signed in the same way as Resend (via Svix).
     */
    protected function sendWebhook(array $webhook, ?string $secret = null, ?int $timestamp = null)
    {
        $body = json_encode($webhook);
        $id = 'msg_' . Str::random(24);
        $timestamp = $timestamp ?? now()->timestamp;
        $key = base64_decode(Str::after($secret ?? $this->webhookSecret, 'whsec_'));
        $signature = base64_encode(hash_hmac('sha256', "{$id}.{$timestamp}.{$body}", $key, true));

        return $this->call('POST', route($this->route), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_SVIX_ID' => $id,
            'HTTP_SVIX_TIMESTAMP' => (string) $timestamp,
            'HTTP_SVIX_SIGNATURE' => "v1,{$signature}",
        ], $body);
    }
}
