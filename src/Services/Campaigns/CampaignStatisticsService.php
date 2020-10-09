<?php

namespace Sendportal\Base\Services\Campaigns;

use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Workspace;
use Sendportal\Base\Repositories\Campaigns\CampaignTenantRepositoryInterface;

class CampaignStatisticsService
{
    /**
     * @var CampaignTenantRepositoryInterface
     */
    protected $campaigns;

    public function __construct(CampaignTenantRepositoryInterface $campaigns)
    {
        $this->campaigns = $campaigns;
    }

    /**
     * @throws Exception
     */
    public function getForCampaign(Campaign $campaign, Workspace $workspace): Collection
    {
        return $this->get(collect([$campaign]), $workspace);
    }

    /**
     * @throws Exception
     */
    public function getForCollection(Collection $campaigns, Workspace $workspace): Collection
    {
        return $this->get($campaigns, $workspace);
    }

    /**
     * @throws Exception
     */
    public function getForPaginator(LengthAwarePaginator $paginator, Workspace $workspace): Collection
    {
        return $this->get(collect($paginator->items()), $workspace);
    }

    /**
     * @throws Exception
     */
    protected function get(Collection $campaigns, Workspace $workspace): Collection
    {
        $countData = $this->campaigns->getCounts($campaigns->pluck('id'), $workspace->id);

        return $campaigns->map(function (Campaign $campaign) use ($countData) {
            return [
                'campaign_id' => $campaign->id,
                'counts' => [
                    'total' => $countData[$campaign->id]->total,
                    'open' => $countData[$campaign->id]->opened,
                    'click' => $countData[$campaign->id]->clicked,
                    'sent' => $countData[$campaign->id]->sent,
                ],
                'ratios' => [
                    'open' => $campaign->getActionRatio($countData[$campaign->id]->opened, $countData[$campaign->id]->sent),
                    'click' => $campaign->getActionRatio($countData[$campaign->id]->clicked, $countData[$campaign->id]->sent),
                ],
            ];
        })->keyBy('campaign_id');
    }
}
