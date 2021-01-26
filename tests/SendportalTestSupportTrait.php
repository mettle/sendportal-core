<?php

declare(strict_types=1);

namespace Tests;

use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\Segment;
use Sendportal\Base\Models\Subscriber;

trait SendportalTestSupportTrait
{
    protected function createEmailService(): EmailService
    {
        return EmailService::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);
    }

    protected function createCampaign(EmailService $emailService): Campaign
    {
        return Campaign::factory()
            ->withContent()
            ->sent()
            ->create([
                'workspace_id' => Sendportal::currentWorkspaceId(),
                'email_service_id' => $emailService->id,
            ]);
    }

    protected function createSegment(): Segment
    {
        return Segment::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);
    }

    protected function createSubscriber(): Subscriber
    {
        return Subscriber::factory()->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);
    }
}
