<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Segment;
use Sendportal\Base\Models\Workspace;
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
        $this->assertEquals($campaign->updated_at, $campaign->fresh()->updated_at);
    }

    /** @test */
    public function campaigns_cannot_be_saved_with_segments_belonging_to_another_workspace()
    {
        $workspace = factory(Workspace::class)->create();
        $anotherWorkspace = factory(Workspace::class)->create();

        $campaign = factory(Campaign::class)->make([
            'workspace_id' => $workspace->id,
            'content' => 'foo',
            'send_to_all' => 0,
            'scheduled_at' => now()->addDay(),
        ]);
        $validSegment = factory(Segment::class)->create([
            'workspace_id' => $workspace->id,
        ]);
        $invalidSegment = factory(Segment::class)->create([
            'workspace_id' => $anotherWorkspace->id,
        ]);

        $this
            ->postJson(route('sendportal.api.campaigns.store', [
                'workspaceId' => $workspace->id,
                'api_token' => $workspace->owner->api_token,
            ]), array_merge($campaign->toArray(), ['segments' => [$validSegment->id, $invalidSegment->id]]))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'segments' => ['One or more of the segments is invalid.'],
                ],
            ]);
    }

    /** @test */
    public function campaigns_can_be_saved_with_segments_belonging_to_the_same_workspace()
    {
        $workspace = factory(Workspace::class)->create();

        $campaign = factory(Campaign::class)->make([
            'workspace_id' => $workspace->id,
            'content' => 'foo',
            'send_to_all' => 0,
            'scheduled_at' => now()->addDay(),
        ]);
        $validSegment = factory(Segment::class)->create([
            'workspace_id' => $workspace->id,
        ]);

        $this
            ->postJson(route('sendportal.api.campaigns.store', [
                'workspaceId' => $workspace->id,
                'api_token' => $workspace->owner->api_token,
            ]), array_merge($campaign->toArray(), ['segments' => [$validSegment->id]]))
            ->assertStatus(Response::HTTP_CREATED);
    }

    /** @test */
    public function campaigns_cannot_be_updated_with_segments_belonging_to_another_workspace()
    {
        $workspace = factory(Workspace::class)->create();
        $anotherWorkspace = factory(Workspace::class)->create();

        $campaign = factory(Campaign::class)->create([
            'workspace_id' => $workspace->id,
            'content' => 'foo',
            'send_to_all' => 0,
            'scheduled_at' => now()->addDay(),
        ]);
        $validSegment = factory(Segment::class)->create([
            'workspace_id' => $workspace->id,
        ]);
        $invalidSegment = factory(Segment::class)->create([
            'workspace_id' => $anotherWorkspace->id,
        ]);

        $this
            ->patchJson(route('sendportal.api.campaigns.update', [
                'workspaceId' => $workspace->id,
                'campaign' => $campaign->id,
                'api_token' => $workspace->owner->api_token,
            ]), array_merge($campaign->toArray(), ['segments' => [$validSegment->id, $invalidSegment->id]]))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'segments' => ['One or more of the segments is invalid.'],
                ],
            ]);
    }

    /** @test */
    public function campaigns_can_be_updated_with_segments_belonging_to_the_same_workspace()
    {
        $workspace = factory(Workspace::class)->create();

        $campaign = factory(Campaign::class)->create([
            'workspace_id' => $workspace->id,
            'content' => 'foo',
            'send_to_all' => 0,
            'scheduled_at' => now()->addDay(),
        ]);
        $validSegment = factory(Segment::class)->create([
            'workspace_id' => $workspace->id,
        ]);
        $this
            ->patchJson(route('sendportal.api.campaigns.update', [
                'workspaceId' => $workspace->id,
                'campaign' => $campaign->id,
                'api_token' => $workspace->owner->api_token,
            ]), array_merge($campaign->toArray(), ['segments' => [$validSegment->id]]))
            ->assertStatus(Response::HTTP_OK);
    }
}
