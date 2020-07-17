<?php

declare(strict_types=1);

namespace Tests\Feature\Campaigns;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\CampaignStatus;
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
        $response->assertSessionHas('success', "The campaign was cancelled whilst being processed.");
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
}
