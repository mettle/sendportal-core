<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Interfaces\QuotaServiceInterface;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\CampaignStatus;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\EmailServiceType;
use Sendportal\Base\Services\QuotaService;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class CampaignDispatchControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_draft_campaign_can_be_dispatched()
    {
        $this->ignoreQuota();

        $emailService = $this->createEmailService();

        $campaign = factory(Campaign::class)->states('draft')->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'email_service_id' => $emailService->id,
        ]);

        $this
            ->postJson(route('sendportal.api.campaigns.send', [
                'id' => $campaign->id
            ]))
            ->assertOk()
            ->assertJson([
                'data' => [
                    'status_id' => CampaignStatus::STATUS_QUEUED,
                ],
            ]);
    }

    /** @test */
    public function a_sent_campaign_cannot_be_dispatched()
    {
        $this->ignoreQuota();

        $emailService = $this->createEmailService();

        $campaign = $this->createCampaign($emailService);

        $this
            ->postJson(route('sendportal.api.campaigns.send', [
                'id' => $campaign->id,
            ]))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'status_id' => 'The campaign must have a status of draft to be dispatched'
            ]);
    }

    /** @test */
    public function a_campaign_cannot_be_dispatched_if_the_number_of_subscribers_exceeds_the_ses_quota()
    {
        $this->instance(QuotaServiceInterface::class, Mockery::mock(QuotaService::class, function ($mock) {
            $mock->shouldReceive('exceedsQuota')->andReturn(true);
        }));

        $emailService = factory(EmailService::class)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'type_id' => EmailServiceType::SES
        ]);

        $campaign = factory(Campaign::class)->states('draft')->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'email_service_id' => $emailService->id,
        ]);

        $this
            ->postJson(route('sendportal.api.campaigns.send', [
                'id' => $campaign->id,
            ]))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'message' => 'The number of subscribers for this campaign exceeds your SES quota'
            ]);
    }

    protected function ignoreQuota()
    {
        $this->instance(QuotaServiceInterface::class, Mockery::mock(QuotaService::class, function ($mock) {
            $mock->shouldReceive('exceedsQuota')->andReturn(false);
        }));
    }
}
