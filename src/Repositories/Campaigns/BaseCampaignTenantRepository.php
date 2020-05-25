<?php

declare(strict_types=1);

namespace Sendportal\Base\Repositories\Campaigns;

use Illuminate\Database\Eloquent\Collection;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\CampaignStatus;
use Sendportal\Base\Repositories\BaseTenantRepository;
use Sendportal\Base\Traits\SecondsToHms;

abstract class BaseCampaignTenantRepository extends BaseTenantRepository implements CampaignTenantRepositoryInterface
{
    use SecondsToHms;

    /** @var string */
    protected $modelName = Campaign::class;

    public function completedCampaigns(int $workspaceId, array $relations = []): Collection
    {
        return $this->getQueryBuilder($workspaceId)
            ->where('status_id', CampaignStatus::STATUS_SENT)
            ->with($relations)
            ->get();
    }
}
