<?php

declare(strict_types=1);

namespace Tests\Feature\Campaigns;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\CampaignStatus;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\Template;
use Tests\TestCase;

class CampaignControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function the_index_of_campaigns_is_accessible_to_authenticated_users()
    {
        // given
        Campaign::factory()->count(3)->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        // when
        $response = $this->get(route('sendportal.campaigns.index'));

        // then
        $response->assertOk();
    }

    /** @test */
    public function draft_campaigns_appear_on_the_draft_index()
    {
        $statuses = [
            CampaignStatus::STATUS_DRAFT,
            CampaignStatus::STATUS_QUEUED,
            CampaignStatus::STATUS_SENDING,
        ];

        foreach ($statuses as $status) {
            $campaign = Campaign::factory()->create(
                [
                    'workspace_id' => Sendportal::currentWorkspaceId(),
                    'status_id' => $status,
                ]
            );

            $this
                ->get(route('sendportal.campaigns.index'))
                ->assertSee($campaign->name);
        }
    }

    /** @test */
    public function sent_campaigns_dont_appear_on_the_draft_index()
    {
        $statuses = [
            CampaignStatus::STATUS_SENT,
            CampaignStatus::STATUS_CANCELLED,
        ];

        foreach ($statuses as $status) {
            $campaign = Campaign::factory()->create(
                [
                    'workspace_id' => Sendportal::currentWorkspaceId(),
                    'status_id' => $status,
                ]
            );

            $this
                ->get(route('sendportal.campaigns.index'))
                ->assertDontSee($campaign->name);
        }
    }

    /** @test */
    public function the_sent_index_of_campaigns_is_accessible_to_authenticated_users()
    {
        // given
        Campaign::factory()->count(3)->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        // when
        $response = $this->get(route('sendportal.campaigns.sent'));

        // then
        $response->assertOk();
    }

    /** @test */
    public function sent_campaigns_appear_on_the_sent_index()
    {
        $statuses = [
            CampaignStatus::STATUS_SENT,
            CampaignStatus::STATUS_CANCELLED,
        ];

        foreach ($statuses as $status) {
            $campaign = Campaign::factory()->create(
                [
                    'workspace_id' => Sendportal::currentWorkspaceId(),
                    'status_id' => $status,
                ]
            );

            $this
                ->get(route('sendportal.campaigns.sent'))
                ->assertSee($campaign->name);
        }
    }

    /** @test */
    public function draft_campaigns_dont_appear_on_the_sent_index()
    {
        $statuses = [
            CampaignStatus::STATUS_DRAFT,
            CampaignStatus::STATUS_QUEUED,
            CampaignStatus::STATUS_SENDING,
        ];

        foreach ($statuses as $status) {
            $campaign = Campaign::factory()->create(
                [
                    'workspace_id' => Sendportal::currentWorkspaceId(),
                    'status_id' => $status,
                ]
            );

            $this
                ->get(route('sendportal.campaigns.sent'))
                ->assertDontSee($campaign->name);
        }
    }

    /** @test */
    public function the_campaign_creation_form_is_accessible_to_authenticated_users()
    {
        // when
        $response = $this->get(route('sendportal.campaigns.create'));

        // then
        $response->assertOk();
    }

    /** @test */
    public function new_campaigns_can_be_created_by_authenticated_users()
    {
        $campaignStoreData = $this->generateCampaignStoreData();

        // when
        $response = $this->post(route('sendportal.campaigns.store'), $campaignStoreData);

        // then
        $response->assertRedirect();
        $this->assertDatabaseHas('sendportal_campaigns', [
            'name' => $campaignStoreData['name'],
        ]);
    }

    /** @test */
    public function the_preview_view_is_accessible_by_authenticated_users()
    {
        // given
        $campaign = Campaign::factory()->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        // when
        $response = $this->get(route('sendportal.campaigns.preview', $campaign->id));

        // then
        $response->assertOk();
    }

    /** @test */
    public function the_edit_view_is_accessible_by_authenticated_users()
    {
        // given
        $campaign = Campaign::factory()->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        // when
        $response = $this->get(route('sendportal.campaigns.edit', $campaign->id));

        // then
        $response->assertOk();
    }

    /** @test */
    public function a_campaign_is_updateable_by_authenticated_users()
    {
        // given
        $campaign = Campaign::factory()->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        $campaignUpdateData = [
            'name' => $this->faker->word(),
            'subject' => $this->faker->sentence(),
            'from_name' => $this->faker->name(),
            'from_email' => $this->faker->safeEmail(),
            'email_service_id' => $campaign->email_service_id,
            'template_id' => $campaign->template_id,
            'content' => $this->faker->paragraph()
        ];

        // when
        $response = $this->put(route('sendportal.campaigns.update', $campaign->id), $campaignUpdateData);

        // then
        $response->assertRedirect();
        $this->assertDatabaseHas('sendportal_campaigns', [
            'id' => $campaign->id,
            'name' => $campaignUpdateData['name'],
            'subject' => $campaignUpdateData['subject']
        ]);
    }

    /** @test */
    public function campaigns_can_be_set_to_not_track_opens()
    {
        // given
        $campaignStoreData = $this->generateCampaignStoreData();

        // when
        $response = $this->post(route('sendportal.campaigns.store'), $campaignStoreData);

        // then
        $response->assertRedirect();
        $this->assertDatabaseHas('sendportal_campaigns', [
            'name' => $campaignStoreData['name'],
            'is_open_tracking' => 0
        ]);
    }

    /** @test */
    public function campaigns_can_be_set_to_track_opens()
    {
        // given
        $campaignStoreData = $this->generateCampaignStoreData() + ['is_open_tracking' => true];

        // when
        $response = $this->post(route('sendportal.campaigns.store'), $campaignStoreData);

        // then
        $response->assertRedirect();
        $this->assertDatabaseHas('sendportal_campaigns', [
            'name' => $campaignStoreData['name'],
            'is_open_tracking' => 1
        ]);
    }

    /** @test */
    public function campaigns_can_be_set_to_not_track_clicks()
    {
        // given
        $campaignStoreData = $this->generateCampaignStoreData();

        // when
        $response = $this->post(route('sendportal.campaigns.store'), $campaignStoreData);

        // then
        $response->assertRedirect();
        $this->assertDatabaseHas('sendportal_campaigns', [
            'name' => $campaignStoreData['name'],
            'is_click_tracking' => 0
        ]);
    }

    /** @test */
    public function campaigns_can_be_set_to_track_clicks()
    {
        // given
        $campaignStoreData = $this->generateCampaignStoreData() + ['is_click_tracking' => true];

        // when
        $response = $this->post(route('sendportal.campaigns.store'), $campaignStoreData);

        // then
        $response->assertRedirect();
        $this->assertDatabaseHas('sendportal_campaigns', [
            'name' => $campaignStoreData['name'],
            'is_click_tracking' => 1
        ]);
    }

    /** @test */
    public function campaign_content_is_required_if_no_template_is_selected()
    {
        // given
        $campaignStoreData = $this->generateCampaignStoreData([
            'template_id' => null,
            'content' => null,
        ]);

        // when
        $response = $this->post(route('sendportal.campaigns.store'), $campaignStoreData);

        // then
        $response->assertSessionHasErrors('content');
    }

    /** @test */
    public function campaign_content_is_not_required_if_a_template_is_selected()
    {
        // given
        $campaignStoreData = $this->generateCampaignStoreData([
            'content' => null,
        ]);

        // when
        $response = $this->post(route('sendportal.campaigns.store'), $campaignStoreData);

        // then
        $response->assertSessionHasNoErrors();
    }

    private function generateCampaignStoreData(array $overrides = []): array
    {
        $emailService = EmailService::factory()->create(['workspace_id' => Sendportal::currentWorkspaceId()]);
        $template = Template::factory()->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        return array_merge([
            'name' => $this->faker->word(),
            'subject' => $this->faker->sentence(),
            'from_name' => $this->faker->name(),
            'from_email' => $this->faker->safeEmail(),
            'email_service_id' => $emailService->id,
            'template_id' => $template->id,
            'content' => $this->faker->paragraph()
        ], $overrides);
    }
}
