<?php

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

    /**
     * @var string
     */
    protected $route = 'api.webhooks.mailgun';

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->apiKey = Str::random();
    }

    /**
     * @return void
     */
    public function testDelivery()
    {
        $message = $this->createMessage();

        $this->assertNull($message->delivered_at);

        $webhook = $this->resolveWebhook('delivered', $message->message_id);

        $this->json('POST', route($this->route), $webhook)
            ->assertOk();

        $this->assertNotNull($message->refresh()->delivered_at);
    }

    /**
     * @return void
     */
    public function testOpened()
    {
        $message = $this->createMessage();

        $this->assertEquals(0, $message->open_count);
        $this->assertNull($message->opened_at);

        $webhook = $this->resolveWebhook('opened', $message->message_id);

        $this->json('POST', route($this->route), $webhook)
            ->assertOk();

        $this->assertEquals(1, $message->refresh()->open_count);
        $this->assertNotNull($message->opened_at);
    }

    /**
     * @return void
     */
    public function testClicked()
    {
        $message = $this->createMessage();

        $this->assertEquals(0, $message->click_count);
        $this->assertNull($message->clicked_at);

        $webhook = $this->resolveWebhook('clicked', $message->message_id);

        $webhook['event-data']['url'] = $this->faker->url;

        $this->json('POST', route($this->route), $webhook)
            ->assertOk();

        $this->assertEquals(1, $message->refresh()->click_count);
        $this->assertNotNull($message->clicked_at);
    }

    /**
     * @return void
     */
    public function testComplained()
    {
        $message = $this->createMessage();

        $this->assertNull($message->unsubscribed_at);

        $webhook = $this->resolveWebhook('complained', $message->message_id);

        $this->json('POST', route($this->route), $webhook)
            ->assertOk();

        $this->assertNotNull($message->refresh()->unsubscribed_at);
    }

    /**
     * @return void
     */
    public function testPermanentFailure()
    {
        $message = $this->createMessage();

        $this->assertNull($message->bounced_at);

        $webhook = $this->resolveWebhook('failed', $message->message_id);

        $webhook['event-data']['severity'] = 'permanent';

        $this->json('POST', route($this->route), $webhook)
            ->assertOk();

        $this->assertNotNull($message->refresh()->bounced_at);

        $this->assertDatabaseHas(
            'message_failures',
            [
                'message_id' => $message->id,
                'severity' => 'permanent',
            ]
        );
    }

    /**
     * @return void
     */
    public function testTemporaryFailure()
    {
        $message = $this->createMessage();

        $webhook = $this->resolveWebhook('failed', $message->message_id);

        $webhook['event-data']['severity'] = 'temporary';

        $this->json('POST', route($this->route), $webhook)
            ->assertOk();

        $this->assertDatabaseHas(
            'message_failures',
            [
                'message_id' => $message->id,
                'severity' => 'temporary',
            ]
        );
    }

    protected function createMessage(): Message
    {
        $emailService = factory(EmailService::class)->create([
            'type_id' => EmailServiceType::MAILGUN,
            'settings' => [
                'key' => $this->apiKey,
            ],
        ]);

        $campaign = factory(Campaign::class)->create([
            'email_service_id' => $emailService->id,
        ]);

        return factory(Message::class)->create([
            'message_id' => '<' . Str::random() . '>',
            'source_id' => $campaign->id,
        ]);
    }

    protected function resolveWebhook(string $type, string $messageId): array
    {
        $timestamp = now()->timestamp;

        $token = Str::random();

        $signature = hash_hmac('sha256', $timestamp . $token, $this->apiKey);

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
