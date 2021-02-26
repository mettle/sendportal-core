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
        $counts = DB::table('sendportal_campaigns')
            ->leftJoin('sendportal_messages', function ($join) use ($campaignIds, $workspaceId) {
                $join->on('sendportal_messages.source_id', '=', 'sendportal_campaigns.id')
                    ->where('sendportal_messages.source_type', Campaign::class)
                    ->whereIn('sendportal_messages.source_id', $campaignIds)
                    ->where('sendportal_messages.workspace_id', $workspaceId);
            })
            ->select('sendportal_campaigns.id as campaign_id')
            ->selectRaw(sprintf('count(%ssendportal_messages.id) as total', DB::getTablePrefix()))
            ->selectRaw(sprintf('count(case when %ssendportal_messages.opened_at IS NOT NULL then 1 end) as opened', DB::getTablePrefix()))
            ->selectRaw(sprintf('count(case when %ssendportal_messages.clicked_at IS NOT NULL then 1 end) as clicked', DB::getTablePrefix()))
            ->selectRaw(sprintf('count(case when %ssendportal_messages.sent_at IS NOT NULL then 1 end) as sent', DB::getTablePrefix()))
            ->selectRaw(sprintf('count(case when %ssendportal_messages.bounced_at IS NOT NULL then 1 end) as bounced', DB::getTablePrefix()))
            ->selectRaw(sprintf('count(case when %ssendportal_messages.sent_at IS NULL then 1 end) as pending', DB::getTablePrefix()))
            ->groupBy('sendportal_campaigns.id')
            ->orderBy('sendportal_campaigns.id')
            ->get();

        return $counts->flatten()->keyBy('campaign_id')->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function cancelCampaign(Campaign $campaign): bool
    {
        $this->deleteDraftMessages($campaign);

        return $campaign->update([
            'status_id' => CampaignStatus::STATUS_CANCELLED,
        ]);
    }

    private function deleteDraftMessages(Campaign $campaign): void
    {
        if (! $campaign->save_as_draft) {
            return;
        }

        $campaign->messages()->whereNull('sent_at')->delete();
    }
}
