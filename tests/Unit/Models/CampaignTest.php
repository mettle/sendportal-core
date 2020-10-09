<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\Segment;
use Sendportal\Base\Models\Subscriber;
use Sendportal\Base\Models\Workspace;
use Tests\TestCase;

class CampaignTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_many_opens()
    {
        [$workspace, $emailService] = $this->createUserWithWorkspaceAndEmailService();
        $campaign = $this->createCampaign($workspace, $emailService);

        $openedMessages = $this->createOpenedMessage($workspace, $campaign, 3);
        $this->createUnopenedMessage($workspace, $campaign, 2);

        $opens = $campaign->opens;

        $opens->each(function ($open) use ($openedMessages) {
            $validMessages = $openedMessages->pluck('id')->toArray();

            static::assertTrue(in_array($open->id, $validMessages));
        });
        static::assertEquals(3, $opens->count());
    }

    /** @test */
    public function the_unique_open_count_attribute_returns_the_number_of_unique_opens_for_a_campaign()
    {
        [$workspace, $emailService] = $this->createUserWithWorkspaceAndEmailService();

        $campaign = factory(Campaign::class)->states(['withContent', 'sent'])->create([
            'workspace_id' => $workspace->id,
            'email_service_id' => $emailService->id,
        ]);
        $this->createOpenedMessage($workspace, $campaign, 3);

        static::assertEquals(3, $campaign->unique_open_count);
    }

    /** @test */
    public function the_total_open_count_attribute_returns_the_total_number_of_opens_for_a_campaign()
    {
        [$workspace, $emailService] = $this->createUserWithWorkspaceAndEmailService();

        $campaign = $this->createCampaign($workspace, $emailService);
        $this->createOpenedMessage($workspace, $campaign, 3, [
            'open_count' => 5
        ]);

        static::assertEquals(15, $campaign->total_open_count);
    }

    /** @test */
    public function it_has_many_clicks()
    {
        [$workspace, $emailService] = $this->createUserWithWorkspaceAndEmailService();

        $campaign = $this->createCampaign($workspace, $emailService);
        $clickedMessages = $this->createClickedMessage($workspace, $campaign, 3);
        $this->createUnclickedMessage($workspace, $campaign, 2);

        $clicks = $campaign->clicks;

        $clicks->each(function ($click) use ($clickedMessages) {
            $validMessages = $clickedMessages->pluck('id')->toArray();

            static::assertTrue(in_array($click->id, $validMessages));
        });
        static::assertEquals(3, $clicks->count());
    }

    /** @test */
    public function the_unique_click_count_attribute_returns_the_number_of_unique_clicks_for_a_campaign()
    {
        [$workspace, $emailService] = $this->createUserWithWorkspaceAndEmailService();

        $campaign = $this->createCampaign($workspace, $emailService);
        $this->createClickedMessage($workspace, $campaign, 3);

        static::assertEquals(3, $campaign->unique_click_count);
    }

    /** @test */
    public function the_total_click_count_attribute_returns_the_total_number_of_clicks_for_a_campaign()
    {
        [$workspace, $emailService] = $this->createUserWithWorkspaceAndEmailService();

        $campaign = $this->createCampaign($workspace, $emailService);
        $this->createClickedMessage($workspace, $campaign, 3, [
            'click_count' => 5,
        ]);

        static::assertEquals(15, $campaign->total_click_count);
    }

    /** @test */
    public function the_cancelled_attribute_returns_true_if_the_campaign_is_cancelled()
    {
        $campaign = factory(Campaign::class)->state('cancelled')->create();

        static::assertTrue($campaign->cancelled);
    }

    /** @test */
    public function the_can_be_cancelled_method_returns_true_if_the_campaign_is_queued()
    {
        /** @var Campaign $campaign */
        $campaign = factory(Campaign::class)->state('queued')->create();

        static::assertTrue($campaign->canBeCancelled());
    }

    /** @test */
    public function the_can_be_cancelled_method_returns_true_if_the_campaign_is_sending()
    {
        /** @var Campaign $campaign */
        $campaign = factory(Campaign::class)->state('sending')->create();

        static::assertTrue($campaign->canBeCancelled());
    }

    /** @test */
    public function the_can_be_cancelled_method_returns_true_if_the_campaign_is_sent_and_saves_as_draft_and_not_all_drafts_have_been_sent()
    {
        $campaign = factory(Campaign::class)->state('sent')->create([
            'save_as_draft' => 1,
            'send_to_all' => 1,
        ]);

        // Subscribers
        factory(Subscriber::class, 5)->create([
            'workspace_id' => $campaign->workspace_id,
        ]);

        // Draft Messages
        factory(Message::class, 3)->state('pending')->create([
            'workspace_id' => $campaign->workspace_id,
            'source_id' => $campaign->id,
        ]);

        // Sent Messages
        factory(Message::class, 2)->state('dispatched')->create([
            'workspace_id' => $campaign->workspace_id,
            'source_id' => $campaign->id,
        ]);

        static::assertTrue($campaign->canBeCancelled());
    }

    /** @test */
    public function the_can_be_cancelled_method_returns_false_if_the_campaign_is_sent_and_saves_as_draft_and_all_drafts_have_been_sent()
    {
        $campaign = factory(Campaign::class)->state('sent')->create([
            'save_as_draft' => 1,
            'send_to_all' => 1,
        ]);

        $subscribers = factory(Subscriber::class, 5)->create([
            'workspace_id' => $campaign->workspace_id,
        ]);

        // Sent Messages
        factory(Message::class, $subscribers->count())->state('dispatched')->create([
            'workspace_id' => $campaign->workspace_id,
            'source_id' => $campaign->id,
        ]);

        static::assertFalse($campaign->canBeCancelled());
    }


    /** @test */
    public function the_all_drafts_created_method_returns_true_if_all_drafts_have_been_created()
    {
        $workspace = factory(Workspace::class)->create();
        $campaign = factory(Campaign::class)->state('sending')->create([
            'workspace_id' => $workspace->id,
            'save_as_draft' => 1,
        ]);
        $segment = factory(Segment::class)->create([
            'workspace_id' => $workspace->id,
        ]);
        $campaign->segments()->attach($segment->id);
        $subscribers = factory(Subscriber::class, 5)->create([
            'workspace_id' => $workspace->id,
        ]);
        $segment->subscribers()->attach($subscribers->pluck('id'));

        // Message Drafts
        factory(Message::class, $subscribers->count())->state('pending')->create([
            'source_id' => $campaign->id,
        ]);

        static::assertTrue($campaign->allDraftsCreated());
    }

    /** @test */
    public function the_all_drafts_created_method_returns_false_if_all_drafts_have_not_been_created()
    {
        $workspace = factory(Workspace::class)->create();
        $campaign = factory(Campaign::class)->state('sending')->create([
            'workspace_id' => $workspace->id,
            'save_as_draft' => 1,
        ]);
        $segment = factory(Segment::class)->create([
            'workspace_id' => $workspace->id,
        ]);
        $campaign->segments()->attach($segment->id);
        $subscribers = factory(Subscriber::class, 5)->create([
            'workspace_id' => $workspace->id,
        ]);
        $segment->subscribers()->attach($subscribers->pluck('id'));

        // Message Drafts
        factory(Message::class, 3)->state('pending')->create([
            'source_id' => $campaign->id,
        ]);

        static::assertFalse($campaign->allDraftsCreated());
    }

    /** @test */
    public function the_all_drafts_created_method_returns_true_if_the_campaign_does_not_save_as_draft()
    {
        $workspace = factory(Workspace::class)->create();
        $campaign = factory(Campaign::class)->state('sending')->create([
            'workspace_id' => $workspace->id,
            'save_as_draft' => 0,
            'send_to_all' => 1,
        ]);

        factory(Subscriber::class, 5)->create([
            'workspace_id' => $workspace->id,
        ]);

        static::assertTrue($campaign->allDraftsCreated());
    }

    /**
     * @param Workspace $workspace
     * @param Campaign $campaign
     * @param int $quantity
     * @param array $overrides
     * @return mixed
     */
    protected function createOpenedMessage(Workspace $workspace, Campaign $campaign, int $quantity = 1, array $overrides = [])
    {
        $data = array_merge([
            'workspace_id' => $workspace->id,
            'subscriber_id' => factory(Subscriber::class)->create([
                'workspace_id' => $workspace->id,
            ]),
            'source_type' => Campaign::class,
            'source_id' => $campaign->id,
            'open_count' => 1,
            'sent_at' => now(),
            'delivered_at' => now(),
            'opened_at' => now(),
        ], $overrides);

        return factory(Message::class, $quantity)->create($data);
    }

    /**
     * @param Workspace $workspace
     * @param Campaign $campaign
     * @param int $count
     *
     * @return mixed
     */
    protected function createUnopenedMessage(Workspace $workspace, Campaign $campaign, int $count)
    {
        return factory(Message::class, $count)->create([
            'workspace_id' => $workspace->id,
            'subscriber_id' => factory(Subscriber::class)->create([
                'workspace_id' => $workspace->id,
            ]),
            'source_type' => Campaign::class,
            'source_id' => $campaign->id,
            'open_count' => 0,
            'sent_at' => now(),
            'delivered_at' => now(),
        ]);
    }

    /**
     * @param Workspace $workspace
     * @param Campaign $campaign
     * @param int $quantity
     * @param array $overrides
     * @return mixed
     */
    protected function createClickedMessage(Workspace $workspace, Campaign $campaign, int $quantity = 1, array $overrides = [])
    {
        $data = array_merge([
            'workspace_id' => $workspace->id,
            'subscriber_id' => factory(Subscriber::class)->create([
                'workspace_id' => $workspace->id,
            ]),
            'source_type' => Campaign::class,
            'source_id' => $campaign->id,
            'click_count' => 1,
            'sent_at' => now(),
            'delivered_at' => now(),
            'clicked_at' => now(),
        ], $overrides);

        return factory(Message::class, $quantity)->create($data);
    }

    /**
     * @param Workspace $workspace
     * @param Campaign $campaign
     * @param int $count
     *
     * @return mixed
     */
    protected function createUnclickedMessage(Workspace $workspace, Campaign $campaign, int $count)
    {
        return factory(Message::class, $count)->create([
            'workspace_id' => $workspace->id,
            'subscriber_id' => factory(Subscriber::class)->create([
                'workspace_id' => $workspace->id,
            ]),
            'source_type' => Campaign::class,
            'source_id' => $campaign->id,
            'click_count' => 0,
            'sent_at' => now(),
            'delivered_at' => now(),
        ]);
    }
}
