<?php

declare(strict_types=1);

namespace Tests\Feature\Campaigns;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\CampaignStatus;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\Segment;
use Sendportal\Base\Models\Subscriber;
use Tests\TestCase;

class CampaignCancellationControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function the_confirm_cancel_endpoint_returns_the_confirm_cancel_view()
    {
        [$workspace, $user] = $this->createUserAndWorkspace();
        $campaign = factory(Campaign::class)->state('queued')->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)->get(route('sendportal.campaigns.confirm-cancel', ['id' => $campaign->id]));

        $response->assertViewIs('sendportal::campaigns.cancel');
    }

    /** @test */
    function the_cancel_endpoint_cancels_a_queued_campaign()
    {
        [$workspace, $user] = $this->createUserAndWorkspace();
        $campaign = factory(Campaign::class)->state('queued')->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)->post(route('sendportal.campaigns.cancel', ['id' => $campaign->id]));

        $response->assertRedirect(route('sendportal.campaigns.index'));
        $response->assertSessionHas('success', 'The queued campaign was cancelled successfully.');
        static::assertEquals(CampaignStatus::STATUS_CANCELLED, $campaign->refresh()->status_id);
    }

    /** @test */
    function the_cancel_endpoint_a_sending_campaign()
    {
        [$workspace, $user] = $this->createUserAndWorkspace();
        $campaign = factory(Campaign::class)->state('sending')->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)->post(route('sendportal.campaigns.cancel', ['id' => $campaign->id]));

        $response->assertRedirect(route('sendportal.campaigns.index'));
        static::assertEquals(CampaignStatus::STATUS_CANCELLED, $campaign->refresh()->status_id);
    }

    /** @test */
    function the_cancel_endpoint_does_not_allow_a_draft_campaign_to_be_cancelled()
    {
        [$workspace, $user] = $this->createUserAndWorkspace();
        $campaign = factory(Campaign::class)->state('draft')->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)->post(route('sendportal.campaigns.cancel', ['id' => $campaign->id]));

        $response->assertRedirect(route('sendportal.campaigns.index'));
        $response->assertSessionHasErrors('campaignStatus', "{$campaign->status->name} campaigns cannot be cancelled.");
        static::assertEquals(CampaignStatus::STATUS_DRAFT, $campaign->refresh()->status_id);
    }

    /** @test */
    function the_cancel_endpoint_does_not_allow_a_sent_campaign_to_be_cancelled()
    {
        [$workspace, $user] = $this->createUserAndWorkspace();
        $campaign = factory(Campaign::class)->state('sent')->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)->post(route('sendportal.campaigns.cancel', ['id' => $campaign->id]));

        $response->assertRedirect(route('sendportal.campaigns.index'));
        $response->assertSessionHasErrors('campaignStatus', "{$campaign->status->name} campaigns cannot be cancelled.");
        static::assertEquals(CampaignStatus::STATUS_SENT, $campaign->refresh()->status_id);
    }

    /** @test */
    function the_cancel_endpoint_does_not_allow_a_cancelled_campaign_to_be_cancelled()
    {
        [$workspace, $user] = $this->createUserAndWorkspace();
        $campaign = factory(Campaign::class)->state('cancelled')->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)->post(route('sendportal.campaigns.cancel', ['id' => $campaign->id]));

        $response->assertRedirect(route('sendportal.campaigns.index'));
        $response->assertSessionHasErrors('campaignStatus', "{$campaign->status->name} campaigns cannot be cancelled.");
        static::assertEquals(CampaignStatus::STATUS_CANCELLED, $campaign->refresh()->status_id);
    }

    /** @test */
    function when_a_sending_send_to_all_campaign_is_cancelled_the_user_is_told_how_many_messages_were_dispatched()
    {
        [$workspace, $user] = $this->createUserAndWorkspace();
        $campaign = factory(Campaign::class)->state('sending')->create([
            'workspace_id' => $workspace->id,
            'save_as_draft' => 0,
            'send_to_all' => 1,
        ]);

        // Dispatched
        factory(Message::class)->create([
            'workspace_id' => $workspace->id,
            'subscriber_id' => factory(Subscriber::class)->create([
                'workspace_id' => $workspace->id,
            ])->id,
            'source_id' => $campaign->id,
            'sent_at' => now(),
        ]);

        // Not Sent
        factory(Message::class)->create([
            'workspace_id' => $workspace->id,
            'subscriber_id' => factory(Subscriber::class)->create([
                'workspace_id' => $workspace->id,
            ])->id,
            'source_id' => $campaign->id,
            'sent_at' => null,
        ]);

        $response = $this->actingAs($user)->post(route('sendportal.campaigns.cancel', ['id' => $campaign->id]));

        $response->assertSessionHas('success', "The campaign was cancelled whilst being processed (~1/2 dispatched).");
    }

    /** @test */
    function when_a_sending_not_send_to_all_campaign_is_cancelled_the_user_is_told_how_many_messages_were_dispatched()
    {
        [$workspace, $user] = $this->createUserAndWorkspace();


        $segment = factory(Segment::class)->create([
            'workspace_id' => $workspace->id,
        ]);
        $campaign = factory(Campaign::class)->state('sending')->create([
            'workspace_id' => $workspace->id,
            'save_as_draft' => 0,
            'send_to_all' => 0,
        ]);
        $campaign->segments()->attach($segment->id);

        // Dispatched
        $subscriber = factory(Subscriber::class)->create([
            'workspace_id' => $workspace->id,
        ]);
        $subscriber->segments()->attach($segment->id);
        factory(Message::class)->create([
            'workspace_id' => $workspace->id,
            'subscriber_id' => $subscriber->id,
            'source_id' => $campaign->id,
            'sent_at' => now(),
        ]);

        // Not Sent
        $otherSubscriber = factory(Subscriber::class)->create([
            'workspace_id' => $workspace->id,
        ]);
        $otherSubscriber->segments()->attach($segment->id);
        factory(Message::class)->create([
            'workspace_id' => $workspace->id,
            'subscriber_id' => $otherSubscriber->id,
            'source_id' => $campaign->id,
            'sent_at' => null,
        ]);

        $response = $this->actingAs($user)->post(route('sendportal.campaigns.cancel', ['id' => $campaign->id]));

        $response->assertSessionHas('success', "The campaign was cancelled whilst being processed (~1/2 dispatched).");
    }

    /** @test */
    function campaigns_that_save_as_draft_cannot_be_cancelled_until_every_draft_message_has_been_created()
    {
        [$workspace, $user] = $this->createUserAndWorkspace();
        $campaign = factory(Campaign::class)->state('sending')->create([
            'workspace_id' => $workspace->id,
            'send_to_all' => 0,
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

        $response = $this->actingAs($user)->post(route('sendportal.campaigns.cancel', ['id' => $campaign->id]));

        $response->assertSessionHasErrors('messagesPendingDraft',
            'Campaigns that save draft messages cannot be cancelled until all drafts have been created.');
    }

    /** @test */
    function campaigns_that_save_as_draft_can_be_cancelled_if_every_draft_message_has_been_created()
    {
        [$workspace, $user] = $this->createUserAndWorkspace();
        $campaign = factory(Campaign::class)->state('sending')->create([
            'workspace_id' => $workspace->id,
            'send_to_all' => 0,
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

        $response = $this->actingAs($user)->post(route('sendportal.campaigns.cancel', ['id' => $campaign->id]));

        $response->assertSessionHas('success',
            'The campaign was cancelled and any remaining draft messages were deleted.');
    }
}
