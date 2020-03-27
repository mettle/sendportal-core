<?php

declare(strict_types=1);

namespace Sendportal\Base\Repositories\Campaigns;

use Sendportal\Base\Models\Campaign;

class PostgresCampaignTenantRepository extends BaseCampaignTenantRepository
{
    /**
     * @inheritDoc
     */
    public function getAverageTimeToOpen(Campaign $campaign): string
    {
        $average = $campaign->opens()
            ->selectRaw('ROUND(AVG(EXTRACT(EPOCH FROM (opened_at - delivered_at)))) as average_time_to_open')
            ->value('average_time_to_open');

        return $average ? $this->secondsToHms($average) : 'N/A';
    }

    /**
     * @inheritDoc
     */
    public function getAverageTimeToClick(Campaign $campaign): string
    {
        $average = $campaign->clicks()
            ->selectRaw('ROUND(AVG(EXTRACT(EPOCH FROM (clicked_at - delivered_at)))) as average_time_to_click')
            ->value('average_time_to_click');

        return $average ? $this->secondsToHms($average) : 'N/A';
    }
}
