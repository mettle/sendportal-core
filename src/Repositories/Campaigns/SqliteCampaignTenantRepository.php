<?php

declare(strict_types=1);

namespace Sendportal\Base\Repositories\Campaigns;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Sendportal\Base\Models\Campaign;

class SqliteCampaignTenantRepository extends BaseCampaignTenantRepository
{
    /**
     * @inheritDoc
     */
    public function getAverageTimeToOpen(Campaign $campaign): string
    {
        $average = $campaign->opens()
            ->selectRaw('ROUND(AVG(strftime("%s", opened_at) - strftime("%s", delivered_at))) as average_time_to_open')
            ->value('average_time_to_open');

        return $average ? $this->secondsToHms($average) : 'N/A';
    }

    /**
     * @inheritDoc
     */
    public function getAverageTimeToClick(Campaign $campaign): string
    {
        $average = $campaign->clicks()
            ->selectRaw('ROUND(AVG(strftime("%s", clicked_at) - strftime("%s", delivered_at))) as average_time_to_click')
            ->value('average_time_to_click');

        return $average ? $this->secondsToHms($average) : 'N/A';
    }
}
