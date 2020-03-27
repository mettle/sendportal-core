<?php

namespace Sendportal\Base\Repositories\Postgres;

use Sendportal\Base\Interfaces\CampaignTenantInterface;
use Sendportal\Base\Repositories\BaseTenantRepository;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Traits\SecondsToHms;

class CampaignTenantRepository extends BaseTenantRepository implements CampaignTenantInterface
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
            ->selectRaw('ROUND(AVG(EXTRACT(EPOCH FROM (opened_at - delivered_at)))) as average_time_to_open')
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
            ->selectRaw('ROUND(AVG(EXTRACT(EPOCH FROM (clicked_at - delivered_at)))) as average_time_to_click')
            ->value('average_time_to_click');

        return $average ? $this->secondsToHms($average) : 'N/A';
    }
}
