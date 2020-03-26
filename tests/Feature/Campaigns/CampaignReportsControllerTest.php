<?php

declare(strict_types=1);

namespace Tests\Feature\Campaigns;

use Sendportal\Base\Models\Campaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignReportsControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function a_sent_campaign_report_is_accessible_by_authenticated_users()
    {
        // given
        [$campaign, $user] = $this->getCampaignAndUser();

        // when
        $response = $this->actingAs($user)->get(route('campaigns.reports.index', $campaign->id));

        // then
        $response->assertOk();
    }

    private function getCampaignAndUser(): array
    {
        [$workspace, $user] = $this->createUserAndWorkspace();
        $campaign = factory(Campaign::class)
            ->state('sent')
            ->create(['workspace_id' => $workspace->id]);

        return [$campaign, $user];
    }

    /** @test */
    function sent_campaign_recipients_are_accessible_by_authenticated_users()
    {
        // given
        [$campaign, $user] = $this->getCampaignAndUser();

        // when
        $response = $this->actingAs($user)->get(route('campaigns.reports.recipients', $campaign->id));

        // then
        $response->assertOk();
    }

    /** @test */
    function sent_campaign_opens_are_accessible_by_authenticated_users()
    {
        // given
        [$campaign, $user] = $this->getCampaignAndUser();

        // when
        $response = $this->actingAs($user)->get(route('campaigns.reports.opens', $campaign->id));

        // then
        $response->assertOk();
    }

    /** @test */
    function sent_campaign_clicks_are_accessible_by_authenticated_users()
    {
        // given
        [$campaign, $user] = $this->getCampaignAndUser();

        // when
        $response = $this->actingAs($user)->get(route('campaigns.reports.clicks', $campaign->id));

        // then
        $response->assertOk();
    }

    /** @test */
    function sent_campaign_bounces_are_accessible_by_authenticated_users()
    {
        // given
        [$campaign, $user] = $this->getCampaignAndUser();

        // when
        $response = $this->actingAs($user)->get(route('campaigns.reports.bounces', $campaign->id));

        // then
        $response->assertOk();
    }

    /** @test */
    function sent_campaign_unsubscribes_are_accessible_by_authenticated_users()
    {
        // given
        [$campaign, $user] = $this->getCampaignAndUser();

        // when
        $response = $this->actingAs($user)->get(route('campaigns.reports.unsubscribes', $campaign->id));

        // then
        $response->assertOk();
    }
}
