<?php

declare(strict_types=1);

namespace Tests\Feature\Webhooks;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\EmailServiceType;
use Sendportal\Base\Models\Message;
use Tests\TestCase;

class MailgunWebhooksTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @var string */
    protected $route = 'sendportal.api.webhooks.mailgun';

    /** @var string */
    protected $webHookKey;

    public function setUp(): void
    {
        parent::setUp();

        $this->webHookKey = Str::random();
    }

    /** @test */
    public function it_accepts_delivery_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertNull($message->delivered_at);

        $webhook = $this->resolveWebhook('delivered', $message->message_id);

        // when
        $response = $this->json('POST', route($this->route), $webhook);

        // then
        $response->assertOk();

        self::assertNotNull($message->refresh()->delivered_at);
    }

    /** @test */
    public function it_accepts_opened_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertEquals(0, $message->open_count);
        self::assertNull($message->opened_at);

        $webhook = $this->resolveWebhook('opened', $message->message_id);

        // when
        $response = $this->json('POST', route($this->route), $webhook);

        // then
        $response->assertOk();

        self::assertEquals(1, $message->refresh()->open_count);
        self::assertNotNull($message->opened_at);
    }

    /** @test */
    public function it_accepts_clicked_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertEquals(0, $message->click_count);
        self::assertNull($message->clicked_at);

        $webhook = $this->resolveWebhook('clicked', $message->message_id);

        $webhook['event-data']['url'] = $this->faker->url;

        // when
        $response = $this->json('POST', route($this->route), $webhook);

        // then
        $response->assertOk();

        self::assertEquals(1, $message->refresh()->click_count);
        self::assertNotNull($message->clicked_at);
    }

    /** @test */
    public function it_accepts_complained_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertNull($message->unsubscribed_at);

        $webhook = $this->resolveWebhook('complained', $message->message_id);

        // when
        $response = $this->json('POST', route($this->route), $webhook);

        // then
        $response->assertOk();

        self::assertNotNull($message->refresh()->unsubscribed_at);
    }

    /** @test */
    public function it_accepts_permanent_failure_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertNull($message->bounced_at);

        $webhook = $this->resolveWebhook('failed', $message->message_id);

        $webhook['event-data']['severity'] = 'permanent';

        // when
        $response = $this->json('POST', route($this->route), $webhook);

        // then
        $response->assertOk();

        self::assertNotNull($message->refresh()->bounced_at);

        $this->assertDatabaseHas(
            'sendportal_message_failures',
            [
                'message_id' => $message->id,
                'severity' => 'permanent',
            ]
        );
    }

    /** @test */
    public function it_accepts_temporary_failure_webhooks()
    {
        // given
        $message = $this->createMessage();

        $webhook = $this->resolveWebhook('failed', $message->message_id);

        $webhook['event-data']['severity'] = 'temporary';

        // when
        $response = $this->json('POST', route($this->route), $webhook);

        // then
        $response->assertOk();

        $this->assertDatabaseHas(
            'sendportal_message_failures',
            [
                'message_id' => $message->id,
                'severity' => 'temporary',
            ]
        );
    }

    protected function createMessage(): Message
    {
        $emailService = EmailService::factory()->create([
            'type_id' => EmailServiceType::MAILGUN,
            'settings' => [
                'webhook_key' => $this->webHookKey,
            ],
        ]);

        $campaign = Campaign::factory()->create([
            'email_service_id' => $emailService->id,
        ]);

        return Message::factory()->create([
            'message_id' => '<' . Str::random() . '>',
            'source_id' => $campaign->id,
        ]);
    }

    protected function resolveWebhook(string $type, string $messageId): array
    {
        $timestamp = now()->timestamp;

        $token = Str::random();

        $signature = hash_hmac('sha256', $timestamp . $token, $this->webHookKey);

        return [
            'event-data' => [
                'event' => $type,
                'message' => [
                    'headers' => [
                        'message-id' => $messageId,
                    ],
                ],
                'timestamp' => $timestamp,
            ],
            'signature' => [
                'token' => $token,
                'timestamp' => $timestamp,
                'signature' => $signature,
            ],
        ];
    }
}
