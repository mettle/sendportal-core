<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\MessageFailure;
use Sendportal\Base\Models\Subscriber;
use Tests\TestCase;

class SubscriberTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_many_messages()
    {
        // given
        $subscriber = Subscriber::factory()->create();

        [$campaignOne, $messageOne] = $this->createCampaignAndMessage($subscriber);
        [$campaignTwo, $messageTwo] = $this->createCampaignAndMessage($subscriber);

        $messages = $subscriber->messages;

        // then
        static::assertInstanceOf(HasMany::class, $subscriber->messages());
        static::assertCount(2, $messages);

        static::assertTrue($messages->contains($messageOne));
        static::assertEquals(Campaign::class, $messageOne->source_type);
        static::assertEquals($campaignOne->id, $messageOne->source_id);

        static::assertTrue($messages->contains($messageTwo));
        static::assertEquals(Campaign::class, $messageTwo->source_type);
        static::assertEquals($campaignTwo->id, $messageTwo->source_id);
    }

    /** @test */
    public function deleting_a_subscriber_also_deletes_its_messages_and_any_failures_associated_to_them()
    {
        // given
        $subscriber = Subscriber::factory()->create();
        $message = Message::factory()->create([
            'subscriber_id' => $subscriber->id,
        ]);
        $message->failures()->create();

        // when
        $subscriber->delete();

        // then
        static::assertCount(0, Subscriber::all());
        static::assertCount(0, Message::all());
        static::assertCount(0, MessageFailure::all());
    }

    /**
     * @param Subscriber $subscriber
     *
     * @return array
     */
    protected function createCampaignAndMessage(Subscriber $subscriber)
    {
        $campaign = Campaign::factory()->sent()->create();
        $message = Message::factory()->create([
            'subscriber_id' => $subscriber->id,
            'source_id' => $campaign->id,
        ]);

        return [$campaign, $message];
    }
}
