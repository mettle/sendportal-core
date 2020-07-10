<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
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

        $route = route('sendportal.api.campaigns.index', [
            'workspaceId' => $workspace->id,
            'api_token' => $workspace->owner->api_token,
        ]);

        $response = $this->get($route);

        $response->assertStatus(200);

        $expected = [
            'data' => [
                Arr::only($campaign->toArray(), ['name'])
            ],
        ];

        $response->assertJson($expected);
    }

    /** @test */
    public function a_single_campaign_can_be_retrieved()
    {
        [$workspace, $emailService] = $this->createUserWithWorkspaceAndEmailService();

        $campaign = $this->createCampaign($workspace, $emailService);

        $route = route('sendportal.api.campaigns.show', [
            'workspaceId' => $workspace->id,
            'campaign' => $campaign->id,
            'api_token' => $workspace->owner->api_token,
        ]);

        $response = $this->get($route);

        $response->assertStatus(200);

        $expected = [
            'data' => Arr::only($campaign->toArray(), ['name']),
        ];

        $response->assertJson($expected);
    }

    /** @test */
    public function a_new_campaign_can_be_added()
    {
        [$workspace, $emailService] = $this->createUserWithWorkspaceAndEmailService();

        $route = route('sendportal.api.campaigns.store', $workspace->id);

        $request = [
            'name' => $this->faker->colorName,
            'subject' => $this->faker->word,
            'from_name' => $this->faker->word,
            'from_email' => $this->faker->safeEmail,
            'email_service_id' => $emailService->id,
            'content' => $this->faker->sentence,
        ];

        $response = $this->post($route, array_merge($request, ['api_token' => $workspace->owner->api_token]));

        $response->assertStatus(201);
        $this->assertDatabaseHas('campaigns', $request);
        $response->assertJson(['data' => $request]);
    }
}
