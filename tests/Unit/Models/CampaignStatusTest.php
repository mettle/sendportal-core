<?php

declare(strict_types=1);

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
        // given
        $campaignStatus = CampaignStatus::findOrFail(CampaignStatus::STATUS_CANCELLED);

        // then
        static::assertEquals('Cancelled', $campaignStatus->name);
    }
}
