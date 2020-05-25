<?php

declare(strict_types=1);

namespace Sendportal\Base\Repositories\Campaigns;

use Illuminate\Database\Eloquent\Collection;
use Sendportal\Base\Interfaces\BaseTenantInterface;
use Sendportal\Base\Models\Campaign;

interface CampaignTenantRepositoryInterface extends BaseTenantInterface
{
    /**
     * Get the average time it takes for a message to be opened once it has been delivered for the campaign.
     */
    public function getAverageTimeToOpen(Campaign $campaign): string;

    /**
     * Get the average time it takes for a link to be clicked for the campaign.
     */
    public function getAverageTimeToClick(Campaign $campaign): string;

    /**
     * Campaigns that have been completed (have a SENT status).
     */
    public function completedCampaigns(int $workspaceId): Collection;
}
