<?php

declare(strict_types=1);

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

    /** @var string */
    protected $route = 'sendportal.api.webhooks.sendgrid';

    /** @test */
    function it_accepts_delivered_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertNull($message->delivered_at);

        $webhook = $this->resolveWebhook('delivered', $message->message_id);

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

        $webhook = $this->resolveWebhook('open', $message->message_id);

        // when
        $this->json('POST', route($this->route), $webhook);

        // then
        self::assertNotNull($message->refresh()->opened_at);
        self::assertEquals(1, $message->open_count);
    }

    /** @test */
    function it_accepts_click_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertEquals(0, $message->click_count);
        self::assertNull($message->clicked_at);

        $url = ['url' => $this->faker->url];
        $webhook = $this->resolveWebhook('click', $message->message_id, $url);

        // when
        $this->json('POST', route($this->route), $webhook);

        // then
        self::assertNotNull($message->refresh()->clicked_at);
        self::assertEquals(1, $message->click_count);
    }

    /** @test */
    function it_accepts_spam_report_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertNull($message->unsubscribed_at);

        $webhook = $this->resolveWebhook('spamreport', $message->message_id);

        // when
        $this->json('POST', route($this->route), $webhook);

        // then
        self::assertNotNull($message->refresh()->unsubscribed_at);
    }

    /** @test */
    function it_accepts_dropped_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertNull($message->bounced_at);

        $webhook = $this->resolveWebhook('dropped', $message->message_id);

        // when
        $this->json('POST', route($this->route), $webhook);

        // then
        self::assertNotNull($message->refresh()->bounced_at);

        $this->assertDatabaseHas(
            'sendportal_message_failures',
            [
                'message_id' => $message->id,
                'severity' => 'Permanent',
            ]
        );
    }

    /** @test */
    function it_accepts_deferred_webhooks()
    {
        // given
        $message = $this->createMessage();

        $webhook = $this->resolveWebhook('deferred', $message->message_id);

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
    function it_accepts_bounce_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertNull($message->bounced_at);

        $webhook = $this->resolveWebhook('bounce', $message->message_id);

        // when
        $this->json('POST', route($this->route), $webhook);

        // then
        self::assertNotNull($message->refresh()->bounced_at);

        $this->assertDatabaseHas(
            'sendportal_message_failures',
            [
                'message_id' => $message->id,
                'severity' => 'Permanent',
            ]
        );
    }

    /** @test */
    function it_accepts_blocked_webhooks()
    {
        // given
        $message = $this->createMessage();

        $webhook = $this->resolveWebhook('blocked', $message->message_id);

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
    function it_accepts_unsubscribed_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertNull($message->unsubscribed_at);

        $webhook = $this->resolveWebhook('unsubscribe', $message->message_id);

        // when
        $this->json('POST', route($this->route), $webhook);

        // then
        self::assertNotNull($message->refresh()->unsubscribed_at);
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
            [
                'event' => $type,
                'sg_message_id' => $messageId,
                'timestamp' => now()->timestamp,
            ] + $properties
        ];
    }
}
