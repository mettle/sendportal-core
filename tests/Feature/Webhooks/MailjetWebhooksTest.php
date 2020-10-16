<?php

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

    /**
     * @var string
     */
    protected $route = 'sendportal.api.webhooks.mailjet';

    /**
     * @return void
     */
    public function testDelivery()
    {
        $message = $this->createMessage();

        $this->assertNull($message->delivered_at);

        $webhook = [
            'event' => 'sent',
            'time' => 1433333949, // 2015-06-03 12:19:09
            'MessageID' => $message->message_id,
            'Message_GUID' => '1ab23cd4-e567-8901-2345-6789f0gh1i2j',
            'email' => 'api@mailjet.com',
            'smtp_reply' => 'sent (250 2.0.0 OK 1433333948 fa5si855896wjc.199 - gsmtp)',
            'Payload' => ''
        ];

        $this->json('POST', route($this->route), $webhook);

        $message = $message->refresh();

        $this->assertEquals('2015-06-03 12:19:09', $message->delivered_at->toDateTimeString());
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

        $this->json('POST', route($this->route), $webhook);

        $this->assertEquals(1, $message->refresh()->open_count);
        $this->assertEquals('2015-05-31 20:18:39', $message->opened_at->toDateTimeString());
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

        $this->json('POST', route($this->route), $webhook);

        $this->assertEquals(1, $message->refresh()->click_count);
        $this->assertEquals('2015-06-03 12:30:53', $message->clicked_at->toDateTimeString());
    }

    /**
     * @return void
     */
    public function testSpamComplaint()
    {
        $message = $this->createMessage();

        $this->assertNull($message->unsubscribed_at);

        $webhook = [
            'event' => 'spam',
            'time' => 1430812195, // 2015-05-05 07:49:55
            'MessageID' => $message->message_id,
            'Message_GUID' => '1ab23cd4-e567-8901-2345-6789f0gh1i2j',
            'email' => 'bounce@mailjet.com',
            'source' => 'JMRPP'
        ];

        $this->json('POST', route($this->route), $webhook);

        $this->assertEquals('2015-05-05 07:49:55', $message->refresh()->unsubscribed_at->toDateTimeString());
    }

    /**
     * @return void
     */
    public function testTemporaryBounce()
    {
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

        $this->json('POST', route($this->route), $webhook);

        $this->assertDatabaseHas(
            'sendportal_message_failures',
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

        $this->json('POST', route($this->route), $webhook);

        $this->assertDatabaseHas(
            'sendportal_message_failures',
            [
                'message_id' => $message->id,
                'severity' => 'Permanent',
            ]
        );

        $this->assertEquals('2015-05-05 07:49:55', $message->refresh()->bounced_at->toDateTimeString());
    }

    /**
     * @return void
     */
    public function testBlocked()
    {
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

        $this->json('POST', route($this->route), $webhook);

        $this->assertDatabaseHas(
            'sendportal_message_failures',
            [
                'message_id' => $message->id,
                'severity' => 'Temporary',
            ]
        );
    }

    /**
     * @return void
     */
    public function testUnsub()
    {
        $subscriber = factory(Subscriber::class)->create();

        $message = factory(Message::class)->create([
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

        $this->json('POST', route($this->route), $webhook);

        $this->assertEquals('2015-06-03 12:35:41', $message->refresh()->unsubscribed_at);

        $subscriber = $subscriber->refresh();

        $this->assertNotNull($subscriber->unsubscribed_at);
        $this->assertEquals(UnsubscribeEventType::COMPLAINT, $subscriber->unsubscribe_event_id);
    }

    /** @test */
    public function testGroupedEvents()
    {
        $campaign = factory(Campaign::class)->create();

        $messageA = factory(Message::class)->create([
            'workspace_id' => $campaign->workspace_id,
            'source_id' => $campaign->id,
            'message_id' => Str::random(),
        ]);
        $messageB = factory(Message::class)->create([
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

        $this->json('POST', route($this->route), $webhook);

        $this->assertEquals('2015-06-03 12:19:09', $messageA->refresh()->delivered_at->toDateTimeString());
        $this->assertEquals('2015-06-03 12:19:10', $messageB->refresh()->delivered_at->toDateTimeString());
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
