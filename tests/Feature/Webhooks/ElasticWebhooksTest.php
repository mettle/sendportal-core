<?php

namespace Tests\Feature\Webhooks;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Sendportal\Base\Models\Message;
use Tests\TestCase;

class ElasticWebhooksTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @var string
     */
    protected $route = 'api.webhooks.elastic';

    /**
     * @return void
     */
    public function testDelivery()
    {
        $message = $this->createMessage();

        $this->assertNull($message->delivered_at);

        $webhook = $this->resolveWebhook($message, 'Sent');

        $this->get(route($this->route, $webhook));

        $this->assertNotNull($message->refresh()->delivered_at);
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

    protected function resolveWebhook(Message $message, $status, $category = 'NotDelivered')
    {
        return [
            'date'      => now()->toIso8601String(),
            'messageid' => $message->message_id,
            'target'    => $this->faker->url,
            'status'    => $status,
            'category'  => $category,
        ];
    }

    /**
     * @return void
     */
    public function testOpen()
    {
        $message = $this->createMessage();

        $this->assertEquals(0, $message->open_count);
        $this->assertNull($message->opened_at);

        $webhook = $this->resolveWebhook($message, 'Opened');

        $this->get(route($this->route, $webhook));

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

        $webhook = $this->resolveWebhook($message, 'Clicked');

        $this->get(route($this->route, $webhook));

        $this->assertEquals(1, $message->refresh()->click_count);
        $this->assertNotNull($message->clicked_at);
    }

    /**
     * @return void
     */
    public function testAbuseReport()
    {
        $message = $this->createMessage();

        $this->assertNull($message->unsubscribed_at);

        $webhook = $this->resolveWebhook($message, 'AbuseReport');

        $this->get(route($this->route, $webhook));

        $this->assertNotNull($message->refresh()->unsubscribed_at);
    }

    /**
     * @return void
     */
    public function testError()
    {
        $message = $this->createMessage();

        $webhook = $this->resolveWebhook($message, 'Error');

        $this->get(route($this->route, $webhook));

        $this->assertDatabaseHas(
            'message_failures',
            [
                'message_id' => $message->id,
                'severity'   => 'Temporary',
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

        $webhook = $this->resolveWebhook($message, 'Unsubscribed');

        $this->get(route($this->route, $webhook));

        $this->assertNotNull($message->refresh()->unsubscribed_at);
    }
}
