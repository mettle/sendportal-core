<?php

declare(strict_types=1);

namespace Tests\Feature\Webhooks;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Sendportal\Base\Models\Message;
use Tests\TestCase;

class PostmarkWebhooksTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @var string */
    protected $route = 'sendportal.api.webhooks.postmark';

    /** @test */
    function it_accepts_delivery_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertNull($message->delivered_at);

        $webhook = [
            'MessageID' => $message->message_id,
            'DeliveredAt' => now()->toIso8601String(),
            'RecordType' => 'Delivery',
        ];

        // when
        $this->json('POST', route($this->route), $webhook);

        // then
        self::assertNotNull($message->refresh()->delivered_at);
    }

    /** @test */
    function it_accepts_open_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertEquals(0, $message->open_count);
        self::assertNull($message->opened_at);

        $webhook = [
            'MessageID' => $message->message_id,
            'ReceivedAt' => now()->toIso8601String(),
            'RecordType' => 'Open',
        ];

        // when
        $this->json('POST', route($this->route), $webhook);

        // then
        self::assertEquals(1, $message->refresh()->open_count);
        self::assertNotNull($message->opened_at);
    }

    /** @test */
    function it_accepts_click_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertEquals(0, $message->click_count);
        self::assertNull($message->clicked_at);

        $webhook = [
            'MessageID' => $message->message_id,
            'ReceivedAt' => now()->toIso8601String(),
            'RecordType' => 'Click',
            'OriginalLink' => $this->faker->url,
        ];

        // when
        $this->json('POST', route($this->route), $webhook);

        // then
        self::assertEquals(1, $message->refresh()->click_count);
        self::assertNotNull($message->clicked_at);
    }

    /** @test */
    function it_accepts_spam_complaint_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertNull($message->unsubscribed_at);

        $webhook = [
            'MessageID' => $message->message_id,
            'BouncedAt' => now()->toIso8601String(),
            'RecordType' => 'SpamComplaint',
        ];

        // when
        $this->json('POST', route($this->route), $webhook);

        // then
        self::assertNotNull($message->refresh()->unsubscribed_at);
    }

    /** @test */
    function it_accepts_temporary_bounce_webhooks()
    {
        // given
        $message = $this->createMessage();

        $webhook = [
            'MessageID' => $message->message_id,
            'BouncedAt' => now()->toIso8601String(),
            'RecordType' => 'Bounce',
            'Type' => 'Transient',
        ];

        // when
        $this->json('POST', route($this->route), $webhook);

        // then
        $this->assertDatabaseHas(
            'sendportal_message_failures',
            [
                'message_id' => $message->id,
                'severity' => 'Temporary',
            ]
        );
    }

    /** @test */
    function it_accepts_permanent_bounce_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertNull($message->bounced_at);

        $webhook = [
            'MessageID' => $message->message_id,
            'BouncedAt' => now()->toIso8601String(),
            'RecordType' => 'Bounce',
            'Type' => 'HardBounce',
        ];

        // when
        $this->json('POST', route($this->route), $webhook);

        // then
        $this->assertDatabaseHas(
            'sendportal_message_failures',
            [
                'message_id' => $message->id,
                'severity' => 'Permanent',
            ]
        );

        self::assertNotNull($message->refresh()->bounced_at);
    }

    /**
     * Create Message
     */
    protected function createMessage(): Message
    {
        return Message::factory()->create([
            'message_id' => Str::random(),
        ]);
    }
}
