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
    public function a_list_of_a_workspaces_campaigns_can_be_retreived()
    {
        $user = $this->createUserWithWorkspace();

        $campaign = $this->createCampaign($user);

        $route = route('sendportal.api.campaigns.index', [
            'workspaceId' => $user->currentWorkspace()->id,
            'api_token' => $user->api_token,
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
    public function a_single_campaign_can_be_retreived()
    {
        $user = $this->createUserWithWorkspace();

        $campaign = $this->createCampaign($user);

        $route = route('sendportal.api.campaigns.show', [
            'workspaceId' => $user->currentWorkspace()->id,
            'campaign' => $campaign->id,
            'api_token' => $user->api_token,
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
        $user = $this->createUserWithWorkspace();

        $route = route('sendportal.api.campaigns.store', $user->currentWorkspace()->id);

        $request = [
            'name' => $this->faker->colorName,
        ];

        $response = $this->post($route, array_merge($request, ['api_token' => $user->api_token]));

        $response->assertStatus(201);
        $this->assertDatabaseHas('campaigns', $request);
        $response->assertJson(['data' => $request]);
    }
}
