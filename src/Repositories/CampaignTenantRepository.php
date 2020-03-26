<?php

namespace Sendportal\Base\Repositories;

use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Repositories\Queries\AverageTimeToClick;
use Sendportal\Base\Repositories\Queries\AverageTimeToOpen;
use Sendportal\Base\Traits\SecondsToHms;

class CampaignTenantRepository extends BaseTenantRepository
{
    use SecondsToHms;

    protected $modelName = Campaign::class;

    /**
     * Get the average time it takes for a message to be opened once it has been delivered for the campaign.
     *
     * @param Campaign $campaign
     *
     * @return string
     */
    public function getAverageTimeToOpen(Campaign $campaign): string
    {
        $average = $campaign->opens()
            ->selectRaw(AverageTimeToOpen::compile('average_time_to_open'))
            ->value('average_time_to_open');

        return $average ? $this->secondsToHms($average) : 'N/A';
    }

    /**
     * Get the average time it takes for a link to be clicked for the campaign.
     *
     * @param Campaign $campaign
     * @return string
     */
    public function getAverageTimeToClick(Campaign $campaign): string
    {
        $average = $campaign->clicks()
            ->selectRaw(AverageTimeToClick::compile('average_time_to_click'))
            ->value('average_time_to_click');

        return $average ? $this->secondsToHms($average) : 'N/A';
    }
}
