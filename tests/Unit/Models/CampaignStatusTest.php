<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Sendportal\Base\Models\CampaignStatus;
use Tests\TestCase;

class CampaignStatusTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_a_cancelled_status()
    {
        $campaignStatus = CampaignStatus::findOrFail(CampaignStatus::STATUS_CANCELLED);

        static::assertEquals('Cancelled', $campaignStatus->name);
    }
}
