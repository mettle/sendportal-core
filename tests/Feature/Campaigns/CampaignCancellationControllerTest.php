<?php

declare(strict_types=1);

namespace Tests\Feature\Campaigns;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\CampaignStatus;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\Subscriber;
use Sendportal\Base\Models\Tag;
use Tests\TestCase;

class CampaignCancellationControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function the_confirm_cancel_endpoint_returns_the_confirm_cancel_view()
    {
        $campaign = Campaign::factory()->queued()->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        $response = $this->get(route('sendportal.campaigns.confirm-cancel', ['id' => $campaign->id]));

        $response->assertViewIs('sendportal::campaigns.cancel');
    }

    /** @test */
    public function the_cancel_endpoint_cancels_a_queued_campaign()
    {
        $campaign = Campaign::factory()->queued()->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        $response = $this->post(route('sendportal.campaigns.cancel', ['id' => $campaign->id]));

        $response->assertRedirect(route('sendportal.campaigns.index'));
        $response->assertSessionHas('success', 'The queued campaign was cancelled successfully.');
        static::assertEquals(CampaignStatus::STATUS_CANCELLED, $campaign->refresh()->status_id);
    }

    /** @test */
    public function the_cancel_endpoint_cancels_a_sending_campaign()
    {
        $campaign = Campaign::factory()->sending()->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        $response = $this->post(route('sendportal.campaigns.cancel', ['id' => $campaign->id]));

        $response->assertRedirect(route('sendportal.campaigns.index'));
        static::assertEquals(CampaignStatus::STATUS_CANCELLED, $campaign->refresh()->status_id);
    }

    /** @test */
    public function the_cancel_endpoint_does_not_allow_a_draft_campaign_to_be_cancelled()
    {
        $campaign = Campaign::factory()->draft()->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        $response = $this->post(route('sendportal.campaigns.cancel', ['id' => $campaign->id]));

        $response->assertRedirect(route('sendportal.campaigns.index'));
        $response->assertSessionHasErrors('campaignStatus', "{$campaign->status->name} campaigns cannot be cancelled.");
        static::assertEquals(CampaignStatus::STATUS_DRAFT, $campaign->refresh()->status_id);
    }

    /** @test */
    public function the_cancel_endpoint_does_not_allow_a_sent_campaign_to_be_cancelled()
    {
        $campaign = Campaign::factory()->sent()->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        $response = $this->post(route('sendportal.campaigns.cancel', ['id' => $campaign->id]));

        $response->assertRedirect(route('sendportal.campaigns.index'));
        $response->assertSessionHasErrors('campaignStatus', "{$campaign->status->name} campaigns cannot be cancelled.");
        static::assertEquals(CampaignStatus::STATUS_SENT, $campaign->refresh()->status_id);
    }

    /** @test */
    public function the_cancel_endpoint_does_not_allow_a_cancelled_campaign_to_be_cancelled()
    {
        $campaign = Campaign::factory()->cancelled()->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        $response = $this->post(route('sendportal.campaigns.cancel', ['id' => $campaign->id]));

        $response->assertRedirect(route('sendportal.campaigns.index'));
        $response->assertSessionHasErrors('campaignStatus', "{$campaign->status->name} campaigns cannot be cancelled.");
        static::assertEquals(CampaignStatus::STATUS_CANCELLED, $campaign->refresh()->status_id);
    }

    /** @test */
    public function when_a_sending_send_to_all_campaign_is_cancelled_the_user_is_told_how_many_messages_were_dispatched()
    {
        $campaign = Campaign::factory()->sending()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'save_as_draft' => 0,
            'send_to_all' => 1,
        ]);

        // Dispatched
        Message::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'subscriber_id' => Subscriber::factory()->create([
                'workspace_id' => Sendportal::currentWorkspaceId(),
            ])->id,
            'source_id' => $campaign->id,
            'sent_at' => now(),
        ]);

        // Not Sent
        Message::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'subscriber_id' => Subscriber::factory()->create([
                'workspace_id' => Sendportal::currentWorkspaceId(),
            ])->id,
            'source_id' => $campaign->id,
            'sent_at' => null,
        ]);

        $response = $this->post(route('sendportal.campaigns.cancel', ['id' => $campaign->id]));

        $response->assertSessionHas('success', "The campaign was cancelled whilst being processed (~1/2 dispatched).");
    }

    /** @test */
    public function when_a_sending_not_send_to_all_campaign_is_cancelled_the_user_is_told_how_many_messages_were_dispatched()
    {
        $tag = Tag::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);
        $campaign = Campaign::factory()->sending()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'save_as_draft' => 0,
            'send_to_all' => 0,
        ]);
        $campaign->tags()->attach($tag->id);

        // Dispatched
        $subscriber = Subscriber::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);
        $subscriber->tags()->attach($tag->id);
        Message::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'subscriber_id' => $subscriber->id,
            'source_id' => $campaign->id,
            'sent_at' => now(),
        ]);

        // Not Sent
        $otherSubscriber = Subscriber::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);
        $otherSubscriber->tags()->attach($tag->id);
        Message::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'subscriber_id' => $otherSubscriber->id,
            'source_id' => $campaign->id,
            'sent_at' => null,
        ]);

        $response = $this->post(route('sendportal.campaigns.cancel', ['id' => $campaign->id]));

        $response->assertSessionHas('success', "The campaign was cancelled whilst being processed (~1/2 dispatched).");
    }

    /** @test */
    public function campaigns_that_save_as_draft_cannot_be_cancelled_until_every_draft_message_has_been_created()
    {
        $campaign = Campaign::factory()->sending()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'send_to_all' => 0,
            'save_as_draft' => 1,
        ]);
        $tag = Tag::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);
        $campaign->tags()->attach($tag->id);
        $subscribers = Subscriber::factory()->count(5)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);
        $tag->subscribers()->attach($subscribers->pluck('id'));

        // Message Drafts
        Message::factory()->count(3)->pending()->create([
            'source_id' => $campaign->id,
        ]);

        $response = $this->post(route('sendportal.campaigns.cancel', ['id' => $campaign->id]));

        $response->assertSessionHasErrors(
            'messagesPendingDraft',
            'Campaigns that save draft messages cannot be cancelled until all drafts have been created.'
        );
    }

    /** @test */
    public function campaigns_that_save_as_draft_can_be_cancelled_if_every_draft_message_has_been_created()
    {
        $campaign = Campaign::factory()->sending()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'send_to_all' => 0,
            'save_as_draft' => 1,
        ]);
        $tag = Tag::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);
        $campaign->tags()->attach($tag->id);
        $subscribers = Subscriber::factory()->count(5)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);
        $tag->subscribers()->attach($subscribers->pluck('id'));

        // Message Drafts
        Message::factory()->count($subscribers->count())->pending()->create([
            'source_id' => $campaign->id,
        ]);

        $response = $this->post(route('sendportal.campaigns.cancel', ['id' => $campaign->id]));

        $response->assertSessionHas(
            'success',
            'The campaign was cancelled and any remaining draft messages were deleted.'
        );
    }
}
