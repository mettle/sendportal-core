<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Sendportal\Base\Facades\Sendportal;
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
        $emailService = $this->createEmailService();

        $campaign = $this->createCampaign($emailService);

        $this
            ->getJson(route('sendportal.api.campaigns.index'))
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
        $emailService = $this->createEmailService();

        $campaign = $this->createCampaign($emailService);

        $this
            ->getJson(route('sendportal.api.campaigns.show', [
                'campaign' => $campaign->id,
            ]))
            ->assertOk()
            ->assertJson([
                'data' => Arr::only($campaign->toArray(), ['name']),
            ]);
    }

    /** @test */
    public function a_new_campaign_can_be_added()
    {
        $emailService = $this->createEmailService();

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
                route('sendportal.api.campaigns.store'),
                $request
            )
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson(['data' => $request]);

        $this->assertDatabaseHas('sendportal_campaigns', $request);
    }

    /** @test */
    public function a_campaign_can_be_updated()
    {
        $emailService = $this->createEmailService();

        $campaign = Campaign::factory()->draft()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
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
                'campaign' => $campaign->id,
            ]), $request)
            ->assertOk()
            ->assertJson(['data' => $request]);

        $this->assertDatabaseMissing('sendportal_campaigns', $campaign->toArray());
        $this->assertDatabaseHas('sendportal_campaigns', $request);
    }

    /** @test */
    public function a_sent_campaign_cannot_be_updated()
    {
        $emailService = $this->createEmailService();

        $campaign = $this->createCampaign($emailService);

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
                'campaign' => $campaign->id,
            ]), $request)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'status_id' => 'A campaign cannot be updated if its status is not draft'
            ]);

        $this->assertDatabaseMissing('sendportal_campaigns', $request);
        self::assertEquals($campaign->updated_at, $campaign->fresh()->updated_at);
    }
}
