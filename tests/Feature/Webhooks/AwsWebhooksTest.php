<?php

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

    /**
     * @var string
     */
    protected $route = 'api.webhooks.aws';

    /**
     * @return void
     */
    public function testDelivery()
    {
        $message = $this->createMessage();

        $this->assertNull($message->delivered_at);

        $webhook = $this->resolveWebhook('delivery', $message->message_id);

        $this->json('POST', route($this->route), $webhook)
            ->assertOk();

        $this->assertNotNull($message->refresh()->delivered_at);
    }

    /**
     * @return void
     */
    public function testClick()
    {
        $message = $this->createMessage();

        $this->assertEquals(0, $message->click_count);
        $this->assertNull($message->clicked_at);

        $link = ['link' => $this->faker->url];
        $webhook = $this->resolveWebhook('click', $message->message_id, $link);

        $this->json('POST', route($this->route), $webhook)
            ->assertOk();

        $this->assertEquals(1, $message->refresh()->click_count);
        $this->assertNotNull($message->clicked_at);
    }

    /**
     * @return void
     */
    public function testOpen()
    {
        $message = $this->createMessage();

        $this->assertEquals(0, $message->open_count);
        $this->assertNull($message->opened_at);

        $webhook = $this->resolveWebhook('open', $message->message_id);

        $this->json('POST', route($this->route), $webhook)
            ->assertOk();

        $this->assertEquals(1, $message->refresh()->open_count);
        $this->assertNotNull($message->opened_at);
    }

    /**
     * @return void
     */
    public function testComplaint()
    {
        $message = $this->createMessage();

        $this->assertNull($message->unsubscribed_at);

        $webhook = $this->resolveWebhook('complaint', $message->message_id);

        $this->json('POST', route($this->route), $webhook)
            ->assertOk();

        $this->assertNotNull($message->refresh()->unsubscribed_at);
    }

    /**
     * @return void
     */
    public function testBounce()
    {
        $message = $this->createMessage();

        $this->assertNull($message->bounced_at);

        $bounceType = ['bounceType' => 'permanent'];
        $webhook = $this->resolveWebhook('bounce', $message->message_id, $bounceType);

        $this->json('POST', route($this->route), $webhook)
            ->assertOk();

        $this->assertNotNull($message->refresh()->bounced_at);
    }

    protected function createMessage(): Message
    {
        return factory(Message::class)->create(
            [
                'message_id' => Str::random(),
            ]
        );
    }

    protected function resolveWebhook(string $type, string $messageId, array $properties = []): array
    {
        return [
            'Message' => json_encode([
                $type => [
                    'timestamp' => now()->timestamp,
                ] + $properties,
                'eventType' => $type,
                'mail' => [
                    'messageId' => $messageId,
                ]
            ]),
            'Type' => 'Notification',
        ];
    }
}
