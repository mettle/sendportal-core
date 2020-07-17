<?php

namespace Tests\Unit\Models;

use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\CampaignStatus;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\Subscriber;
use Sendportal\Base\Models\Workspace;
use Sendportal\Base\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignStatusTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function it_has_a_cancelled_status()
    {
        $campaignStatus = CampaignStatus::findOrFail(CampaignStatus::STATUS_CANCELLED);

        static::assertEquals('Cancelled', $campaignStatus->name);
    }
}
