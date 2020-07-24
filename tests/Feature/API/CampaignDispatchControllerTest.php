<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\ValidationException;
use Sendportal\Base\Interfaces\QuotaServiceInterface;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\CampaignStatus;
use Tests\TestCase;

class CampaignDispatchControllerTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $quotaService = $this->getMockBuilder(QuotaServiceInterface::class)->getMock();

        $quotaService->method('exceedsQuota')->willReturn(false);

        $this->app->instance(QuotaServiceInterface::class, $quotaService);
    }

    /** @test */
    public function a_draft_campaign_can_be_dispatched()
    {
        [$workspace, $emailService] = $this->createUserWithWorkspaceAndEmailService();

        $campaign = factory(Campaign::class)->states('draft')->create(
            [
                'workspace_id' => $workspace->id,
                'email_service_id' => $emailService->id,
            ]
        );

        $route = route('sendportal.api.campaigns.send', [
            'workspaceId' => $workspace->id,
            'id' => $campaign->id,
            'api_token' => $workspace->owner->api_token,
        ]);

        $response = $this->post($route);

        $response->assertStatus(200);

        $expected = [
            'data' => [
                'status_id' => CampaignStatus::STATUS_QUEUED,
            ],
        ];

        $response->assertJson($expected);
    }

    /** @test */
    public function a_sent_campaign_cannot_be_dispatched()
    {
        [$workspace, $emailService] = $this->createUserWithWorkspaceAndEmailService();

        $campaign = $this->createCampaign($workspace, $emailService);

        $route = route('sendportal.api.campaigns.send', [
            'workspaceId' => $workspace->id,
            'id' => $campaign->id,
            'api_token' => $workspace->owner->api_token,
        ]);

        $this->expectException(ValidationException::class);

        $response = $this->post($route);

        $expected = [
            'message' => __('The campaign must have a status of draft to be dispatched'),
        ];

        $response->assertJson($expected);
    }
}
