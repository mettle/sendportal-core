<?php

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
        return factory(EmailService::class)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);
    }

    protected function createCampaign(EmailService $emailService): Campaign
    {
        return factory(Campaign::class)->states(['withContent', 'sent'])->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'email_service_id' => $emailService->id,
        ]);
    }

    protected function createSegment(): Segment
    {
        return factory(Segment::class)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);
    }

    protected function createSubscriber(): Subscriber
    {
        return factory(Subscriber::class)->create([
            'workspace_id' => Sendportal::currentWorkspaceId(),
        ]);
    }
}
