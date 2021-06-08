<?php

declare(strict_types=1);

namespace Sendportal\Base\Repositories\Campaigns;

use Sendportal\Base\Models\Campaign;

class MySqlCampaignTenantRepository extends BaseCampaignTenantRepository
{
    /**
     * @inheritDoc
     */
    public function getAverageTimeToOpen(Campaign $campaign): string
    {
        $average = $campaign->opens()
            ->selectRaw('ROUND(AVG(TIMESTAMPDIFF(SECOND, delivered_at, opened_at))) as average_time_to_open')
            ->value('average_time_to_open');

        return $average ? $this->secondsToHms($average) : 'N/A';
    }

    /**
     * @inheritDoc
     */
    public function getAverageTimeToClick(Campaign $campaign): string
    {
        $average = $campaign->clicks()
            ->selectRaw('ROUND(AVG(TIMESTAMPDIFF(SECOND, delivered_at, clicked_at))) as average_time_to_click')
            ->value('average_time_to_click');

        return $average ? $this->secondsToHms($average) : 'N/A';
    }
}
