<?php

namespace Sendportal\Base\Pipelines\Campaigns;

use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\CampaignStatus;

class StartCampaign
{
    /**
     * Mark the campaign as started in the database
     *
     * @param Campaign $campaign
     * @return Campaign
     */
    public function handle(Campaign $campaign, $next)
    {
        $this->markCampaignAsSending($campaign);

        return $next($campaign);
    }

    /**
     * Execute the database request
     *
     * @param Campaign $campaign
     * @return Campaign
     */
    protected function markCampaignAsSending(Campaign $campaign): ?Campaign
    {
        return tap($campaign)->update([
            'status_id' => CampaignStatus::STATUS_SENDING,
        ]);
    }
}
