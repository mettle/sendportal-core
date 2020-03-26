<?php

namespace Tests\Feature\Webhooks;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Sendportal\Base\Models\Message;
use Tests\TestCase;

class SendgridWebhooksTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @var string
     */
    protected $route = 'api.webhooks.sendgrid';

    /**
     * @return void
     */
    public function testDelivered()
    {
        $message = $this->createMessage();

        $this->assertNull($message->delivered_at);

        $webhook = $this->resolveWebhook('delivered', $message->message_id);

        $this->json('POST', route($this->route), $webhook);

        $this->assertNotNull($message->refresh()->delivered_at);
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

        $this->json('POST', route($this->route), $webhook);

        $this->assertNotNull($message->refresh()->opened_at);
        $this->assertEquals(1, $message->open_count);
    }

    /**
     * @return void
     */
    public function testClick()
    {
        $message = $this->createMessage();

        $this->assertEquals(0, $message->click_count);
        $this->assertNull($message->clicked_at);

        $url = ['url' => $this->faker->url];
        $webhook = $this->resolveWebhook('click', $message->message_id, $url);

        $this->json('POST', route($this->route), $webhook);

        $this->assertNotNull($message->refresh()->clicked_at);
        $this->assertEquals(1, $message->click_count);
    }

    /**
     * @return void
     */
    public function testSpamReport()
    {
        $message = $this->createMessage();

        $this->assertNull($message->unsubscribed_at);

        $webhook = $this->resolveWebhook('spamreport', $message->message_id);

        $this->json('POST', route($this->route), $webhook);

        $this->assertNotNull($message->refresh()->unsubscribed_at);
    }

    /**
     * @return void
     */
    public function testDropped()
    {
        $message = $this->createMessage();

        $this->assertNull($message->bounced_at);

        $webhook = $this->resolveWebhook('dropped', $message->message_id);

        $this->json('POST', route($this->route), $webhook);

        $this->assertNotNull($message->refresh()->bounced_at);

        $this->assertDatabaseHas(
            'message_failures',
            [
                'message_id' => $message->id,
                'severity' => 'Permanent',
            ]
        );
    }

    /**
     * @return void
     */
    public function testDeferred()
    {
        $message = $this->createMessage();

        $webhook = $this->resolveWebhook('deferred', $message->message_id);

        $this->json('POST', route($this->route), $webhook);

        $this->assertDatabaseHas(
            'message_failures',
            [
                'message_id' => $message->id,
                'severity' => 'Temporary',
            ]
        );
    }

    /**
     * @return void
     */
    public function testBounce()
    {
        $message = $this->createMessage();

        $this->assertNull($message->bounced_at);

        $webhook = $this->resolveWebhook('bounce', $message->message_id);

        $this->json('POST', route($this->route), $webhook);

        $this->assertNotNull($message->refresh()->bounced_at);

        $this->assertDatabaseHas(
            'message_failures',
            [
                'message_id' => $message->id,
                'severity' => 'Permanent',
            ]
        );
    }

    /**
     * @return void
     */
    public function testBlocked()
    {
        $message = $this->createMessage();

        $webhook = $this->resolveWebhook('blocked', $message->message_id);

        $this->json('POST', route($this->route), $webhook);

        $this->assertDatabaseHas(
            'message_failures',
            [
                'message_id' => $message->id,
                'severity' => 'Temporary',
            ]
        );
    }

    /**
     * @return void
     */
    public function testUnsubscribe()
    {
        $message = $this->createMessage();

        $this->assertNull($message->unsubscribed_at);

        $webhook = $this->resolveWebhook('unsubscribe', $message->message_id);

        $this->json('POST', route($this->route), $webhook);

        $this->assertNotNull($message->refresh()->unsubscribed_at);
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
        return [[
            'event' => $type,
            'sg_message_id' => $messageId,
            'timestamp' => now()->timestamp,
        ] + $properties];
    }
}
