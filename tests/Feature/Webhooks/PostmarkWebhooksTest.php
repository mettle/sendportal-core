<?php

declare(strict_types=1);

namespace Tests\Feature\Webhooks;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\Subscriber;
use Sendportal\Base\Models\UnsubscribeEventType;
use Tests\TestCase;

class PostmarkWebhooksTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @var string */
    protected $route = 'sendportal.api.webhooks.postmark';

    #[Test]
    public function it_accepts_delivery_webhooks()
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

    #[Test]
    public function it_accepts_open_webhooks()
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

    #[Test]
    public function it_accepts_click_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertEquals(0, $message->click_count);
        self::assertNull($message->clicked_at);

        $webhook = [
            'MessageID' => $message->message_id,
            'ReceivedAt' => now()->toIso8601String(),
            'RecordType' => 'Click',
            'OriginalLink' => $this->faker->url(),
        ];

        // when
        $this->json('POST', route($this->route), $webhook);

        // then
        self::assertEquals(1, $message->refresh()->click_count);
        self::assertNotNull($message->clicked_at);
    }

    #[Test]
    public function it_accepts_spam_complaint_webhooks()
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

    #[Test]
    public function it_accepts_temporary_bounce_webhooks()
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

    #[Test]
    public function it_accepts_permanent_bounce_webhooks()
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

    #[Test]
    public function it_unsubscribes_the_subscriber_on_a_subscription_change_unsubscribe()
    {
        // given
        $message = $this->createMessageWithSubscriber();

        $webhook = $this->subscriptionChange($message, 'ManualSuppression');

        // when
        $response = $this->json('POST', route($this->route), $webhook);

        // then
        $response->assertOk();

        self::assertEquals('2026-10-12 09:30:00', $message->refresh()->unsubscribed_at);
        self::assertNotNull($message->subscriber->unsubscribed_at);
        self::assertEquals(UnsubscribeEventType::EMAIL_SERVICE, $message->subscriber->unsubscribe_event_id);
    }

    #[Test]
    public function it_records_a_bounce_on_a_subscription_change_hard_bounce()
    {
        // given
        $message = $this->createMessageWithSubscriber();

        $webhook = $this->subscriptionChange($message, 'HardBounce');

        // when
        $response = $this->json('POST', route($this->route), $webhook);

        // then
        $response->assertOk();

        self::assertNotNull($message->refresh()->bounced_at);
        self::assertNull($message->unsubscribed_at);
        self::assertEquals(UnsubscribeEventType::BOUNCE, $message->subscriber->unsubscribe_event_id);
    }

    #[Test]
    public function it_records_a_complaint_on_a_subscription_change_spam_complaint()
    {
        // given
        $message = $this->createMessageWithSubscriber();

        $webhook = $this->subscriptionChange($message, 'SpamComplaint');

        // when
        $response = $this->json('POST', route($this->route), $webhook);

        // then
        $response->assertOk();

        self::assertNotNull($message->subscriber->refresh()->unsubscribed_at);
        self::assertEquals(UnsubscribeEventType::COMPLAINT, $message->subscriber->unsubscribe_event_id);
    }

    #[Test]
    public function a_subscription_change_reactivation_does_not_resubscribe_the_subscriber()
    {
        // given
        $message = $this->createMessageWithSubscriber();
        $message->subscriber->update([
            'unsubscribed_at' => now(),
            'unsubscribe_event_id' => UnsubscribeEventType::MANUAL_BY_SUBSCRIBER,
        ]);

        // Postmark sends no MessageID or SuppressionReason for reactivations.
        $webhook = [
            'RecordType' => 'SubscriptionChange',
            'MessageID' => null,
            'ChangedAt' => '2026-10-12T09:30:00Z',
            'Recipient' => $message->recipient_email,
            'Origin' => 'Customer',
            'SuppressSending' => false,
            'SuppressionReason' => null,
        ];

        // when
        $response = $this->json('POST', route($this->route), $webhook);

        // then
        $response->assertOk();

        self::assertNotNull($message->subscriber->refresh()->unsubscribed_at);
        self::assertEquals(UnsubscribeEventType::MANUAL_BY_SUBSCRIBER, $message->subscriber->unsubscribe_event_id);
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

    protected function createMessageWithSubscriber(): Message
    {
        return Message::factory()->create([
            'message_id' => Str::random(),
            'subscriber_id' => Subscriber::factory()->create()->id,
        ]);
    }

    /**
     * https://postmarkapp.com/developer/webhooks/subscription-change-webhook
     */
    protected function subscriptionChange(Message $message, string $reason): array
    {
        return [
            'RecordType' => 'SubscriptionChange',
            'MessageID' => $message->message_id,
            'ServerID' => 23,
            'MessageStream' => 'outbound',
            'ChangedAt' => '2026-10-12T09:30:00Z',
            'Recipient' => $message->recipient_email,
            'Origin' => 'Recipient',
            'SuppressSending' => true,
            'SuppressionReason' => $reason,
            'Tag' => null,
            'Metadata' => [],
        ];
    }
}
