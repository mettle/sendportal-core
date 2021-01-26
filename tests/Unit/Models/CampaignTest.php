<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\Segment;
use Sendportal\Base\Models\Subscriber;
use Tests\TestCase;

class CampaignTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function it_has_many_opens()
    {
        // given
        $emailService = $this->createEmailService();
        $campaign = $this->createCampaign($emailService);

        $openedMessages = $this->createOpenedMessage($campaign, 3);
        $this->createUnopenedMessage($campaign, 2);

        $opens = $campaign->opens;

        // then
        $opens->each(function ($open) use ($openedMessages) {
            $validMessages = $openedMessages->pluck('id')->toArray();

            static::assertContains($open->id, $validMessages);
        });

        static::assertEquals(3, $opens->count());
    }

    /** @test */
    function the_unique_open_count_attribute_returns_the_number_of_unique_opens_for_a_campaign()
    {
        // given
        $emailService = $this->createEmailService();

        $campaign = Campaign::factory()->withContent()->sent()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'email_service_id' => $emailService->id,
        ]);

        $this->createOpenedMessage($campaign, 3);

        // then
        static::assertEquals(3, $campaign->unique_open_count);
    }

    /** @test */
    function the_total_open_count_attribute_returns_the_total_number_of_opens_for_a_campaign()
    {
        // given
        $emailService = $this->createEmailService();

        $campaign = $this->createCampaign($emailService);

        $this->createOpenedMessage($campaign, 3, [
            'open_count' => 5
        ]);

        // then
        static::assertEquals(15, $campaign->total_open_count);
    }

    /** @test */
    function it_has_many_clicks()
    {
        // given
        $emailService = $this->createEmailService();

        $campaign = $this->createCampaign($emailService);
        $clickedMessages = $this->createClickedMessage($campaign, 3);
        $this->createUnclickedMessage($campaign, 2);

        $clicks = $campaign->clicks;

        // then
        $clicks->each(function ($click) use ($clickedMessages) {
            $validMessages = $clickedMessages->pluck('id')->toArray();

            static::assertContains($click->id, $validMessages);
        });

        static::assertEquals(3, $clicks->count());
    }

    /** @test */
    function the_unique_click_count_attribute_returns_the_number_of_unique_clicks_for_a_campaign()
    {
        // given
        $emailService = $this->createEmailService();

        $campaign = $this->createCampaign($emailService);

        $this->createClickedMessage($campaign, 3);

        // then
        static::assertEquals(3, $campaign->unique_click_count);
    }

    /** @test */
    function the_total_click_count_attribute_returns_the_total_number_of_clicks_for_a_campaign()
    {
        // given
        $emailService = $this->createEmailService();

        $campaign = $this->createCampaign($emailService);

        $this->createClickedMessage($campaign, 3, [
            'click_count' => 5,
        ]);

        // then
        static::assertEquals(15, $campaign->total_click_count);
    }

    /** @test */
    function the_cancelled_attribute_returns_true_if_the_campaign_is_cancelled()
    {
        // given
        $campaign = Campaign::factory()->cancelled()->create();

        // then
        static::assertTrue($campaign->cancelled);
    }

    /** @test */
    function the_can_be_cancelled_method_returns_true_if_the_campaign_is_queued()
    {
        // given
        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->queued()->create();

        // then
        static::assertTrue($campaign->canBeCancelled());
    }

    /** @test */
    function the_can_be_cancelled_method_returns_true_if_the_campaign_is_sending()
    {
        // given
        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->sending()->create();

        // then
        static::assertTrue($campaign->canBeCancelled());
    }

    /** @test */
    function the_can_be_cancelled_method_returns_true_if_the_campaign_is_sent_and_saves_as_draft_and_not_all_drafts_have_been_sent()
    {
        // given
        $campaign = Campaign::factory()->sent()->create([
            'save_as_draft' => 1,
            'send_to_all' => 1,
        ]);

        // Subscribers
        Subscriber::factory()->count(5)->create([
            'workspace_id' => $campaign->workspace_id,
        ]);

        // Draft Messages
        Message::factory()->count(3)->pending()->create([
            'workspace_id' => $campaign->workspace_id,
            'source_id' => $campaign->id,
        ]);

        // Sent Messages
        Message::factory()->count(2)->dispatched()->create([
            'workspace_id' => $campaign->workspace_id,
            'source_id' => $campaign->id,
        ]);

        // then
        static::assertTrue($campaign->canBeCancelled());
    }

    /** @test */
    function the_can_be_cancelled_method_returns_false_if_the_campaign_is_sent_and_saves_as_draft_and_all_drafts_have_been_sent()
    {
        // given
        $campaign = Campaign::factory()->sent()->create([
            'save_as_draft' => 1,
            'send_to_all' => 1,
        ]);

        $subscribers = Subscriber::factory()->count(5)->create([
            'workspace_id' => $campaign->workspace_id,
        ]);

        // Sent Messages
        Message::factory()->count($subscribers->count())->dispatched()->create([
            'workspace_id' => $campaign->workspace_id,
            'source_id' => $campaign->id,
        ]);

        // then
        static::assertFalse($campaign->canBeCancelled());
    }


    /** @test */
    function the_all_drafts_created_method_returns_true_if_all_drafts_have_been_created()
    {
        // given
        $campaign = Campaign::factory()->sending()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'save_as_draft' => 1,
        ]);

        $segment = Segment::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        $campaign->segments()->attach($segment->id);

        $subscribers = Subscriber::factory()->count(5)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        $segment->subscribers()->attach($subscribers->pluck('id'));

        // Message Drafts
        Message::factory()->count($subscribers->count())->pending()->create([
            'source_id' => $campaign->id,
        ]);

        // then
        static::assertTrue($campaign->allDraftsCreated());
    }

    /** @test */
    function the_all_drafts_created_method_returns_false_if_all_drafts_have_not_been_created()
    {
        // given
        $campaign = Campaign::factory()->sending()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'save_as_draft' => 1,
        ]);

        $segment = Segment::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        $campaign->segments()->attach($segment->id);

        $subscribers = Subscriber::factory()->count(5)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        $segment->subscribers()->attach($subscribers->pluck('id'));

        // Message Drafts
        Message::factory()->count(3)->pending()->create([
            'source_id' => $campaign->id,
        ]);

        // then
        static::assertFalse($campaign->allDraftsCreated());
    }

    /** @test */
    function the_all_drafts_created_method_returns_true_if_the_campaign_does_not_save_as_draft()
    {
        // given
        $campaign = Campaign::factory()->sending()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'save_as_draft' => 0,
            'send_to_all' => 1,
        ]);

        Subscriber::factory()->count(5)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);

        // then
        static::assertTrue($campaign->allDraftsCreated());
    }

    protected function createOpenedMessage(Campaign $campaign, int $quantity = 1, array $overrides = [])
    {
        $data = array_merge([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'subscriber_id' => Subscriber::factory()->create([
                'workspace_id' => Sendportal::currentWorkspaceId(),
            ]),
            'source_type' => Campaign::class,
            'source_id' => $campaign->id,
            'open_count' => 1,
            'sent_at' => now(),
            'delivered_at' => now(),
            'opened_at' => now(),
        ], $overrides);

        return Message::factory()->count($quantity)->create($data);
    }

    protected function createUnopenedMessage(Campaign $campaign, int $count)
    {
        return Message::factory()->count($count)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'subscriber_id' => Subscriber::factory()->create([
                'workspace_id' => Sendportal::currentWorkspaceId(),
            ]),
            'source_type' => Campaign::class,
            'source_id' => $campaign->id,
            'open_count' => 0,
            'sent_at' => now(),
            'delivered_at' => now(),
        ]);
    }

    protected function createClickedMessage(Campaign $campaign, int $quantity = 1, array $overrides = [])
    {
        $data = array_merge([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'subscriber_id' => Subscriber::factory()->create([
                'workspace_id' => Sendportal::currentWorkspaceId(),
            ]),
            'source_type' => Campaign::class,
            'source_id' => $campaign->id,
            'click_count' => 1,
            'sent_at' => now(),
            'delivered_at' => now(),
            'clicked_at' => now(),
        ], $overrides);

        return Message::factory()->count($quantity)->create($data);
    }

    protected function createUnclickedMessage(Campaign $campaign, int $count)
    {
        return Message::factory()->count($count)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'subscriber_id' => Subscriber::factory()->create([
                'workspace_id' => Sendportal::currentWorkspaceId(),
            ]),
            'source_type' => Campaign::class,
            'source_id' => $campaign->id,
            'click_count' => 0,
            'sent_at' => now(),
            'delivered_at' => now(),
        ]);
    }
}
