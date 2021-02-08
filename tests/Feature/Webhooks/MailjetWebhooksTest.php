<?php

declare(strict_types=1);

namespace Tests\Feature\Webhooks;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\Subscriber;
use Sendportal\Base\Models\UnsubscribeEventType;
use Tests\TestCase;

class MailjetWebhooksTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @var string */
    protected $route = 'sendportal.api.webhooks.mailjet';

    /** @test */
    public function it_accepts_delivery_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertNull($message->delivered_at);

        $webhook = [
            'event' => 'sent',
            'time' => 1433333949, // 2015-06-03 12:19:09
            'MessageID' => $message->message_id,
            'Message_GUID' => '1ab23cd4-e567-8901-2345-6789f0gh1i2j',
            'email' => 'api@mailjet.com',
            'smtp_reply' => 'sent (250 2.0.0 OK 1433333948 fa5si855896wjc.199 - gsmtp)',
            'Payload' => ''
        ];

        // when
        $this->json('POST', route($this->route), $webhook);

        $message = $message->refresh();

        // then
        self::assertEquals('2015-06-03 12:19:09', $message->delivered_at->toDateTimeString());
    }

    /** @test */
    public function it_accepts_open_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertEquals(0, $message->open_count);
        self::assertNull($message->opened_at);

        $webhook = [
            'event' =>  'open',
            'time' =>  1433103519, // 2015-05-31 20:18:39
            'MessageID' =>  $message->message_id,
            'Message_GUID' =>  '1ab23cd4-e567-8901-2345-6789f0gh1i2j',
            'email' =>  'api@mailjet.com',
            'Payload' =>  '',
            'ip' =>  '127.0.0.1',
            'geo' =>  'US',
            'agent' =>  'Mozilla/5.0 (Windows NT 5.1; rv:11.0) Gecko Firefox/11.0'
        ];

        // when
        $this->json('POST', route($this->route), $webhook);

        // then
        self::assertEquals(1, $message->refresh()->open_count);
        self::assertEquals('2015-05-31 20:18:39', $message->opened_at->toDateTimeString());
    }

    /** @test */
    public function it_accepts_click_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertEquals(0, $message->click_count);
        self::assertNull($message->clicked_at);

        $webhook = [
            'event' => 'click',
            'time' => 1433334653, // 2015-06-03 12:30:53
            'MessageID' => $message->message_id,
            'Message_GUID' => '1ab23cd4-e567-8901-2345-6789f0gh1i2j',
            'email' => 'api@mailjet.com',
            'url' => $this->faker->url,
            'ip' => '127.0.0.1',
            'geo' => 'FR',
            'agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_0) AppleWebKit/537.36'
        ];

        // when
        $this->json('POST', route($this->route), $webhook);

        // then
        self::assertEquals(1, $message->refresh()->click_count);
        self::assertEquals('2015-06-03 12:30:53', $message->clicked_at->toDateTimeString());
    }

    /** @test */
    public function it_accepts_spam_complaint_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertNull($message->unsubscribed_at);

        $webhook = [
            'event' => 'spam',
            'time' => 1430812195, // 2015-05-05 07:49:55
            'MessageID' => $message->message_id,
            'Message_GUID' => '1ab23cd4-e567-8901-2345-6789f0gh1i2j',
            'email' => 'bounce@mailjet.com',
            'source' => 'JMRPP'
        ];

        // when
        $this->json('POST', route($this->route), $webhook);

        // then
        self::assertEquals('2015-05-05 07:49:55', $message->refresh()->unsubscribed_at->toDateTimeString());
    }

    /** @test */
    public function it_accepts_temporary_bounce_webhooks()
    {
        // given
        $message = $this->createMessage();

        $webhook = [
            'event' => 'bounce',
            'time' => 1430812195,
            'MessageID' => $message->message_id,
            'Message_GUID' => '1ab23cd4-e567-8901-2345-6789f0gh1i2j',
            'email' => 'bounce@mailjet.com',
            'blocked' => false,
            'hard_bounce' => false,
            'error_related_to' => 'recipient',
            'error' => 'user unknown',
            'comment' => 'Host or domain name not found. Name service error for name=lbjsnrftlsiuvbsren.com type=A: Host not found'
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
    public function it_accepts_permanent_bounce_webhooks()
    {
        // given
        $message = $this->createMessage();

        self::assertNull($message->bounced_at);

        $webhook = [
            'event' => 'bounce',
            'time' => 1430812195,
            'MessageID' => $message->message_id,
            'Message_GUID' => '1ab23cd4-e567-8901-2345-6789f0gh1i2j',
            'email' => 'bounce@mailjet.com',
            'blocked' => false,
            'hard_bounce' => true,
            'error_related_to' => 'recipient',
            'error' => 'user unknown',
            'comment' => 'Host or domain name not found. Name service error for name=lbjsnrftlsiuvbsren.com type=A: Host not found'
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

        self::assertEquals('2015-05-05 07:49:55', $message->refresh()->bounced_at->toDateTimeString());
    }

    /** @test */
    public function it_accepts_blocked_webhooks()
    {
        // given
        $message = $this->createMessage();

        $webhook = [
            'event' => 'blocked',
            'time' => 1430812195,
            'MessageID' => $message->message_id,
            'Message_GUID' => '1ab23cd4-e567-8901-2345-6789f0gh1i2j',
            'email' => 'bounce@mailjet.com',
            'error_related_to' => 'recipient',
            'error' => 'user unknown'
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
    public function it_accepts_unsubscribe_webhooks()
    {
        // given
        $subscriber = Subscriber::factory()->create();

        $message = Message::factory()->create([
            'message_id' => Str::random(),
            'subscriber_id' => $subscriber->id
        ]);

        $webhook = [
            'event' => 'unsub',
            'time' => 1433334941,
            'MessageID' => $message->message_id,
            'Message_GUID' => '1ab23cd4-e567-8901-2345-6789f0gh1i2j',
            'email' => 'api@mailjet.com',
            'ip' => '127.0.0.1',
            'geo' => 'FR',
            'agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36'
        ];

        // when
        $this->json('POST', route($this->route), $webhook);

        $subscriber = $subscriber->refresh();

        // then
        self::assertEquals('2015-06-03 12:35:41', $message->refresh()->unsubscribed_at);

        self::assertNotNull($subscriber->unsubscribed_at);
        self::assertEquals(UnsubscribeEventType::COMPLAINT, $subscriber->unsubscribe_event_id);
    }

    /** @test */
    public function it_accepts_grouped_event_webhooks()
    {
        // given
        $campaign = Campaign::factory()->create();

        $messageA = Message::factory()->create([
            'workspace_id' => $campaign->workspace_id,
            'source_id' => $campaign->id,
            'message_id' => Str::random(),
        ]);

        $messageB = Message::factory()->create([
            'workspace_id' => $campaign->workspace_id,
            'source_id' => $campaign->id,
            'message_id' => Str::random(),
        ]);

        $webhook = [
            [
                'event' => 'sent',
                'time' => 1433333949, // 2015-06-03 12:19:09
                'MessageID' => $messageA->message_id
            ],
            [
                'event' => 'sent',
                'time' => 1433333950, // 2015-06-03 12:19:10
                'MessageID' => $messageB->message_id
            ]
        ];

        // when
        $this->json('POST', route($this->route), $webhook);

        // then
        self::assertEquals('2015-06-03 12:19:09', $messageA->refresh()->delivered_at->toDateTimeString());
        self::assertEquals('2015-06-03 12:19:10', $messageB->refresh()->delivered_at->toDateTimeString());
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
