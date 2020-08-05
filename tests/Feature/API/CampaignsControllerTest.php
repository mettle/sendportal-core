<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Sendportal\Base\Models\Campaign;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class CampaignsControllerTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    /** @test */
    public function a_list_of_a_workspaces_campaigns_can_be_retrieved()
    {
        [$workspace, $emailService] = $this->createUserWithWorkspaceAndEmailService();

        $campaign = $this->createCampaign($workspace, $emailService);

        $this
            ->getJson(route('sendportal.api.campaigns.index', [
                'workspaceId' => $workspace->id,
                'api_token' => $workspace->owner->api_token,
            ]))
            ->assertOk()
            ->assertJson([
                'data' => [
                    Arr::only($campaign->toArray(), ['name'])
                ]
            ]);
    }

    /** @test */
    public function a_single_campaign_can_be_retrieved()
    {
        [$workspace, $emailService] = $this->createUserWithWorkspaceAndEmailService();

        $campaign = $this->createCampaign($workspace, $emailService);

        $this
            ->getJson(route('sendportal.api.campaigns.show', [
                'workspaceId' => $workspace->id,
                'campaign' => $campaign->id,
                'api_token' => $workspace->owner->api_token,
            ]))
            ->assertOk()
            ->assertJson([
                'data' => Arr::only($campaign->toArray(), ['name']),
            ]);
    }

    /** @test */
    public function a_new_campaign_can_be_added()
    {
        [$workspace, $emailService] = $this->createUserWithWorkspaceAndEmailService();

        $request = [
            'name' => $this->faker->colorName,
            'subject' => $this->faker->word,
            'from_name' => $this->faker->word,
            'from_email' => $this->faker->safeEmail,
            'email_service_id' => $emailService->id,
            'content' => $this->faker->sentence,
            'send_to_all' => 1,
            'scheduled_at' => now(),
        ];

        $this
            ->postJson(
                route('sendportal.api.campaigns.store', $workspace->id),
                array_merge($request, ['api_token' => $workspace->owner->api_token])
            )
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson(['data' => $request]);

        $this->assertDatabaseHas('campaigns', $request);
    }

    /** @test */
    public function a_campaign_can_be_updated()
    {
        [$workspace, $emailService] = $this->createUserWithWorkspaceAndEmailService();

        $campaign = factory(Campaign::class)->states('draft')->create([
            'workspace_id' => $workspace->id,
            'email_service_id' => $emailService->id,
        ]);

        $request = [
            'name' => $this->faker->word,
            'subject' => $this->faker->word,
            'from_name' => $this->faker->word,
            'from_email' => $this->faker->safeEmail,
            'email_service_id' => $emailService->id,
            'content' => $this->faker->sentence,
            'send_to_all' => 1,
            'scheduled_at' => now(),
        ];

        $this
            ->putJson(route('sendportal.api.campaigns.update', [
                'workspaceId' => $workspace->id,
                'campaign' => $campaign->id,
                'api_token' => $workspace->owner->api_token,
            ]), $request)
            ->assertOk()
            ->assertJson(['data' => $request]);

        $this->assertDatabaseMissing('campaigns', $campaign->toArray());
        $this->assertDatabaseHas('campaigns', $request);
    }

    /** @test */
    public function a_sent_campaign_cannot_be_updated()
    {
        [$workspace, $emailService] = $this->createUserWithWorkspaceAndEmailService();

        $campaign = $this->createCampaign($workspace, $emailService);

        $request = [
            'name' => $this->faker->word,
            'subject' => $this->faker->word,
            'from_name' => $this->faker->word,
            'from_email' => $this->faker->safeEmail,
            'email_service_id' => $emailService->id,
            'content' => $this->faker->sentence,
            'send_to_all' => 1,
            'scheduled_at' => now(),
        ];

        $this
            ->putJson(route('sendportal.api.campaigns.update', [
                'workspaceId' => $workspace->id,
                'campaign' => $campaign->id,
                'api_token' => $workspace->owner->api_token,
            ]), $request)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'status_id' => 'A campaign cannot be updated if its status is not draft'
            ]);

        $this->assertDatabaseMissing('campaigns', $request);
        $this->assertDatabaseHas('campaigns', $campaign->toArray());
    }
}
