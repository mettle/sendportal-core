<?php

namespace Sendportal\Base\Interfaces;

use Sendportal\Base\Models\Campaign;

interface CampaignTenantInterface extends BaseTenantInterface
{
    /**
     * Get the average time it takes for a message to be opened once it has been delivered for the campaign.
     *
     * @param Campaign $campaign
     * @return string
     */
    public function getAverageTimeToOpen(Campaign $campaign): string;

    /**
     * Get the average time it takes for a link to be clicked for the campaign.
     *
     * @param Campaign $campaign
     * @return string
     */
    public function getAverageTimeToClick(Campaign $campaign): string;
}
