<?php

namespace Sendportal\Base\Services\Campaigns;

use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Workspace;
use Sendportal\Base\Repositories\Campaigns\CampaignTenantRepositoryInterface;

class CampaignStatistics
{
    /**
     * @var Collection
     */
    protected $campaigns;

    /**
     * @var CampaignTenantRepositoryInterface
     */
    protected $campaignRepository;

    /**
     * @var Workspace|null
     */
    protected $workspace;

    public function __construct(Workspace $workspace)
    {
        $this->workspace = $workspace;
        $this->campaigns = collect();
        $this->campaignRepository = app(CampaignTenantRepositoryInterface::class);
    }

    /**
     * Gather statistics for the specified campaigns.
     *
     * @return Collection
     * @throws Exception
     */
    public function get(): Collection
    {
        $countData = $this->campaignRepository->getCounts($this->campaigns->pluck('id'), $this->workspace->id);

        return collect($this->campaigns)->map(function ($campaign) use ($countData)
        {
            /** @var Campaign $campaign */
            return [
                'campaign_id' => $campaign->id,
                'counts' => [
                    'total' => $countData[$campaign->id]->total,
                    'open' => $countData[$campaign->id]->opened,
                    'click' => $countData[$campaign->id]->clicked,
                    'sent' => $campaign->formatCount($countData[$campaign->id]->sent),
                ],
                'ratios' => [
                    'open' => $campaign->getActionRatio($countData[$campaign->id]->opened, $countData[$campaign->id]->sent),
                    'click' => $campaign->getActionRatio($countData[$campaign->id]->clicked, $countData[$campaign->id]->sent),
                ],
            ];
        })->keyBy('campaign_id');
    }

    /**
     * Specify a single campaign for which statistics should be gathered.
     *
     * @param Campaign $campaign
     * @return CampaignStatistics
     */
    public function forCampaign(Campaign $campaign): self
    {
        $this->campaigns = collect([$campaign]);

        return $this;
    }

    /**
     * Specify a collection of campaigns for which statistics should be gathered.
     *
     * @param Collection $campaigns
     * @return CampaignStatistics
     */
    public function forCampaigns(Collection $campaigns): self
    {
        $this->campaigns = $campaigns;

        return $this;
    }

    /**
     * Specify paginated campaigns for which statistics should be gathered.
     *
     * @param LengthAwarePaginator $paginator
     * @return CampaignStatistics
     */
    public function forPaginatedCampaigns(LengthAwarePaginator $paginator): self
    {
        $this->campaigns = collect($paginator->items());

        return $this;
    }
}
