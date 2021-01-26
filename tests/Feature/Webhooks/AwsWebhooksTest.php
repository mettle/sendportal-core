<?php

declare(strict_types=1);

namespace Tests\Feature\Webhooks;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Sendportal\Base\Models\Message;
use Tests\TestCase;

class AwsWebhooksTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @var string */
    protected $route = 'sendportal.api.webhooks.aws';

    /** @test */
    public function it_accepts_delivery_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertNull($message->delivered_at);

        $webhook = $this->resolveWebhook('delivery', $message->message_id);

        // when
        $response = $this->json('POST', route($this->route), $webhook);

        // then
        $response->assertOk();

        self::assertNotNull($message->refresh()->delivered_at);
    }

    /** @test */
    public function it_accepts_click_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertEquals(0, $message->click_count);
        self::assertNull($message->clicked_at);

        $link = ['link' => $this->faker->url];
        $webhook = $this->resolveWebhook('click', $message->message_id, $link);

        // when
        $response = $this->json('POST', route($this->route), $webhook);

        // then
        $response->assertOk();

        self::assertEquals(1, $message->refresh()->click_count);
        self::assertNotNull($message->clicked_at);
    }

    /** @test */
    public function it_accepts_open_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertEquals(0, $message->open_count);
        self::assertNull($message->opened_at);

        $webhook = $this->resolveWebhook('open', $message->message_id);

        // when
        $response = $this->json('POST', route($this->route), $webhook);

        // then
        $response->assertOk();

        self::assertEquals(1, $message->refresh()->open_count);
        self::assertNotNull($message->opened_at);
    }

    /** @test */
    public function it_accepts_complaint_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertNull($message->unsubscribed_at);

        $webhook = $this->resolveWebhook('complaint', $message->message_id);

        // when
        $response = $this->json('POST', route($this->route), $webhook);

        // then
        $response->assertOk();

        self::assertNotNull($message->refresh()->unsubscribed_at);
    }

    /** @test */
    public function it_accepts_bounce_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertNull($message->bounced_at);

        $bounceType = ['bounceType' => 'permanent'];
        $webhook = $this->resolveWebhook('bounce', $message->message_id, $bounceType);

        // when
        $response = $this->json('POST', route($this->route), $webhook);

        // then
        $response->assertOk();

        self::assertNotNull($message->refresh()->bounced_at);
    }

    protected function createMessage(): Message
    {
        return Message::factory()->create(
            [
                'message_id' => Str::random(),
            ]
        );
    }

    protected function resolveWebhook(string $type, string $messageId, array $properties = []): array
    {
        return [
            'Message' => json_encode(
                [
                    $type => [
                            'timestamp' => now()->timestamp,
                        ] + $properties,
                    'eventType' => $type,
                    'mail' => [
                        'messageId' => $messageId,
                    ]
                ]
            ),
            'Type' => 'Notification',
        ];
    }
}
