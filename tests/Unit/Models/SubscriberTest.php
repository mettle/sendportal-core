<?php

namespace Tests\Unit\Models;

use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\Subscriber;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriberTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function it_has_many_messages()
    {
        $subscriber = factory(Subscriber::class)->create();
        [$campaignOne, $messageOne] = $this->createCampaignAndMessage($subscriber);
        [$campaignTwo, $messageTwo] = $this->createCampaignAndMessage($subscriber);

        $messages = $subscriber->messages;

        static::assertInstanceOf(HasMany::class, $subscriber->messages());
        static::assertCount(2, $messages);

        static::assertTrue($messages->contains($messageOne));
        static::assertEquals(Campaign::class, $messageOne->source_type);
        static::assertEquals($campaignOne->id, $messageOne->source_id);

        static::assertTrue($messages->contains($messageTwo));
        static::assertEquals(Campaign::class, $messageTwo->source_type);
        static::assertEquals($campaignTwo->id, $messageTwo->source_id);
    }

    /**
     * @param Subscriber $subscriber
     *
     * @return array
     */
    protected function createCampaignAndMessage(Subscriber $subscriber)
    {
        $campaign = factory(Campaign::class)->state('sent')->create();
        $message = factory(Message::class)->create([
            'subscriber_id' => $subscriber->id,
            'source_id' => $campaign->id,
        ]);

        return [$campaign, $message];
    }
}
