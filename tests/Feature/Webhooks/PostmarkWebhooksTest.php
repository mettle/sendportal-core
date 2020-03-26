<?php

namespace Tests\Feature\Webhooks;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Sendportal\Base\Models\Message;
use Tests\TestCase;

class PostmarkWebhooksTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var string
     */
    protected $route = 'api.webhooks.postmark';

    /**
     * @return void
     */
    public function testDelivery()
    {
        $message = $this->createMessage();

        $this->assertNull($message->delivered_at);

        $webhook = [
            'MessageID' => $message->message_id,
            'DeliveredAt' => now()->toIso8601String(),
            'RecordType' => 'Delivery',
        ];

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

        $webhook = [
            'MessageID' => $message->message_id,
            'ReceivedAt' => now()->toIso8601String(),
            'RecordType' => 'Open',
        ];

        $this->json('POST', route($this->route), $webhook);

        $this->assertEquals(1, $message->refresh()->open_count);
        $this->assertNotNull($message->opened_at);
    }

    /**
     * @return void
     */
    public function testClick()
    {
        $message = $this->createMessage();

        $this->assertEquals(0, $message->click_count);
        $this->assertNull($message->clicked_at);

        $webhook = [
            'MessageID' => $message->message_id,
            'ReceivedAt' => now()->toIso8601String(),
            'RecordType' => 'Click',
        ];

        $this->json('POST', route($this->route), $webhook);

        $this->assertEquals(1, $message->refresh()->click_count);
        $this->assertNotNull($message->clicked_at);
    }

    /**
     * @return void
     */
    public function testSpamComplaint()
    {
        $message = $this->createMessage();

        $this->assertNull($message->unsubscribed_at);

        $webhook = [
            'MessageID' => $message->message_id,
            'BouncedAt' => now()->toIso8601String(),
            'RecordType' => 'SpamComplaint',
        ];

        $this->json('POST', route($this->route), $webhook);

        $this->assertNotNull($message->refresh()->unsubscribed_at);
    }

    /**
     * @return void
     */
    public function testTemporaryBounce()
    {
        $message = $this->createMessage();

        $webhook = [
            'MessageID' => $message->message_id,
            'BouncedAt' => now()->toIso8601String(),
            'RecordType' => 'Bounce',
            'Type' => 'Transient',
        ];

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
    public function testPermanentBounce()
    {
        $message = $this->createMessage();

        $this->assertNull($message->bounced_at);

        $webhook = [
            'MessageID' => $message->message_id,
            'BouncedAt' => now()->toIso8601String(),
            'RecordType' => 'Bounce',
            'Type' => 'HardBounce',
        ];

        $this->json('POST', route($this->route), $webhook);

        $this->assertDatabaseHas(
            'message_failures',
            [
                'message_id' => $message->id,
                'severity' => 'Permanent',
            ]
        );

        $this->assertNotNull($message->refresh()->bounced_at);
    }

    /**
     * Create Message
     */
    protected function createMessage(): Message
    {
        return factory(Message::class)->create([
            'message_id' => Str::random(),
        ]);
    }
}
