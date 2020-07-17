<?php

declare(strict_types=1);

namespace Sendportal\Base\Repositories\Campaigns;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\CampaignStatus;
use Sendportal\Base\Repositories\BaseTenantRepository;
use Sendportal\Base\Traits\SecondsToHms;

abstract class BaseCampaignTenantRepository extends BaseTenantRepository implements CampaignTenantRepositoryInterface
{
    use SecondsToHms;

    /** @var string */
    protected $modelName = Campaign::class;

    /**
     * {@inheritDoc}
     */
    public function completedCampaigns(int $workspaceId, array $relations = []): EloquentCollection
    {
        return $this->getQueryBuilder($workspaceId)
            ->where('status_id', CampaignStatus::STATUS_SENT)
            ->with($relations)
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getCounts(Collection $campaignIds, int $workspaceId): array
    {
        $counts = DB::table('campaigns')
            ->leftJoin('messages', function ($join) use ($campaignIds, $workspaceId) {
                $join->on('messages.source_id', '=', 'campaigns.id')
                    ->where('messages.source_type', Campaign::class)
                    ->whereIn('messages.source_id', $campaignIds)
                    ->where('messages.workspace_id', $workspaceId);
            })
            ->select('campaigns.id as campaign_id')
            ->selectRaw('count(messages.id) as total')
            ->selectRaw('count(case when messages.opened_at IS NOT NULL then 1 end) as opened')
            ->selectRaw('count(case when messages.clicked_at IS NOT NULL then 1 end) as clicked')
            ->selectRaw('count(case when messages.sent_at IS NOT NULL then 1 end) as sent')
            ->selectRaw('count(case when messages.bounced_at IS NOT NULL then 1 end) as bounced')
            ->groupBy('campaigns.id')
            ->orderBy('campaigns.id')
            ->get();

        return $counts->flatten()->keyBy('campaign_id')->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function cancelCampaign(Campaign $campaign): bool
    {
        return $campaign->update([
            'status_id' => CampaignStatus::STATUS_CANCELLED,
        ]);
    }
}
