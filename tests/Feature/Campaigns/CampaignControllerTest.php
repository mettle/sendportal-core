<?php

declare(strict_types=1);

namespace Tests\Feature\Campaigns;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Provider;
use Sendportal\Base\Models\Workspace;
use Sendportal\Base\Models\Template;
use Tests\TestCase;

class CampaignControllerTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    /** @test */
    function the_index_of_campaigns_is_accessible_to_authenticated_users()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();

        factory(Campaign::class, 3)->create(['workspace_id' => $workspace->id]);

        // when
        $response = $this->actingAs($user)->get(route('sendportal.campaigns.index'));

        // then
        $response->assertOk();
    }

    /** @test */
    function the_campaign_creation_form_is_accessible_to_authenticated_users()
    {
        // given
        $user = $this->createUserWithWorkspace();

        // when
        $response = $this->actingAs($user)->get(route('sendportal.campaigns.create'));

        // then
        $response->assertOk();
    }

    /** @test */
    function new_campaigns_can_be_created_by_authenticated_users()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();

        $campaignStoreData = $this->generateCampaignStoreData($workspace);

        // when
        $response = $this->actingAs($user)
            ->post(route('sendportal.campaigns.store'), $campaignStoreData);

        // then
        $response->assertRedirect();
        $this->assertDatabaseHas('campaigns', [
            'name' => $campaignStoreData['name'],
        ]);
    }

    /** @test */
    function the_preview_view_is_accessible_by_authenticated_users()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();
        $campaign = factory(Campaign::class)->create(['workspace_id' => $workspace->id]);

        // when
        $response = $this->actingAs($user)->get(route('sendportal.campaigns.preview', $campaign->id));

        // then
        $response->assertOk();
    }

    /** @test */
    function the_edit_view_is_accessible_by_authenticated_users()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();
        $campaign = factory(Campaign::class)->create(['workspace_id' => $workspace->id]);

        // when
        $response = $this->actingAs($user)->get(route('sendportal.campaigns.edit', $campaign->id));

        // then
        $response->assertOk();
    }

    /** @test */
    function a_campaign_is_updateable_by_authenticated_users()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();
        $campaign = factory(Campaign::class)->create(['workspace_id' => $workspace->id]);

        $campaignUpdateData = [
            'name' => $this->faker->word,
            'subject' => $this->faker->sentence,
            'from_name' => $this->faker->name,
            'from_email' => $this->faker->safeEmail,
            'provider_id' => $campaign->provider_id,
            'template_id' => $campaign->template_id,
            'content' => $this->faker->paragraph
        ];

        // when
        $response = $this->actingAs($user)
            ->put(route('sendportal.campaigns.update', $campaign->id), $campaignUpdateData);

        // then
        $response->assertRedirect();
        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'name' => $campaignUpdateData['name'],
            'subject' => $campaignUpdateData['subject']
        ]);
    }

    /** @test */
    function campaigns_can_be_set_to_not_track_opens()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();

        $campaignStoreData = $this->generateCampaignStoreData($workspace);

        // when
        $response = $this->actingAs($user)
            ->post(route('sendportal.campaigns.store'), $campaignStoreData);

        // then
        $response->assertRedirect();
        $this->assertDatabaseHas('campaigns', [
            'name' => $campaignStoreData['name'],
            'is_open_tracking' => 0
        ]);
    }

    /** @test */
    function campaigns_can_be_set_to_track_opens()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();

        $campaignStoreData = $this->generateCampaignStoreData($workspace) + ['is_open_tracking' => true];

        // when
        $response = $this->actingAs($user)
            ->post(route('sendportal.campaigns.store'), $campaignStoreData);

        // then
        $response->assertRedirect();
        $this->assertDatabaseHas('campaigns', [
            'name' => $campaignStoreData['name'],
            'is_open_tracking' => 1
        ]);
    }

    /** @test */
    function campaigns_can_be_set_to_not_track_clicks()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();

        $campaignStoreData = $this->generateCampaignStoreData($workspace);

        // when
        $response = $this->actingAs($user)
            ->post(route('sendportal.campaigns.store'), $campaignStoreData);

        // then
        $response->assertRedirect();
        $this->assertDatabaseHas('campaigns', [
            'name' => $campaignStoreData['name'],
            'is_click_tracking' => 0
        ]);
    }

    /** @test */
    function campaigns_can_be_set_to_track_clicks()
    {
        // given
        [$workspace, $user] = $this->createUserAndWorkspace();

        $campaignStoreData = $this->generateCampaignStoreData($workspace) + ['is_click_tracking' => true];

        // when
        $response = $this->actingAs($user)
            ->post(route('sendportal.campaigns.store'), $campaignStoreData);

        // then
        $response->assertRedirect();
        $this->assertDatabaseHas('campaigns', [
            'name' => $campaignStoreData['name'],
            'is_click_tracking' => 1
        ]);
    }

    private function generateCampaignStoreData(Workspace $workspace): array
    {
        $provider = factory(Provider::class)->create(['workspace_id' => $workspace->id]);
        $template = factory(Template::class)->create(['workspace_id' => $workspace->id]);

        return [
            'name' => $this->faker->word,
            'subject' => $this->faker->sentence,
            'from_name' => $this->faker->name,
            'from_email' => $this->faker->safeEmail,
            'provider_id' => $provider->id,
            'template_id' => $template->id,
            'content' => $this->faker->paragraph
        ];
    }
}
