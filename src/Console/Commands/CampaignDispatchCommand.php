<?php

declare(strict_types=1);

namespace Sendportal\Base\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Log;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\CampaignStatus;
use Sendportal\Base\Repositories\Campaigns\CampaignTenantRepositoryInterface;
use Sendportal\Base\Services\Campaigns\CampaignDispatchService;

class CampaignDispatchCommand extends Command
{
    /** @var string */
    protected $signature = 'sp:campaigns:dispatch';

    /** @var string */
    protected $description = 'Dispatch all campaigns waiting in the queue';

    /** @var CampaignTenantRepositoryInterface */
    protected $campaignRepo;

    /** @var CampaignDispatchService */
    protected $campaignService;

    public function handle(
        CampaignTenantRepositoryInterface $campaignRepo,
        CampaignDispatchService $campaignService
    ): void {
        $this->campaignRepo = $campaignRepo;
        $this->campaignService = $campaignService;

        $campaigns = $this->getQueuedCampaigns();
        $count = count($campaigns);

        if (! $count) {
            return;
        }

        $this->info('Dispatching campaigns count=' . $count);

        foreach ($campaigns as $campaign) {
            $message = 'Dispatching campaign id=' . $campaign->id;

            $this->info($message);
            Log::info($message);
            $count++;

            $this->campaignService->handle($campaign);
        }

        $message = 'Finished dispatching campaigns';
        $this->info($message);
        Log::info($message);
    }

    /**
     * Get all queued campaigns.
     */
    protected function getQueuedCampaigns(): EloquentCollection
    {
        return Campaign::where('status_id', CampaignStatus::STATUS_QUEUED)->get();
    }
}
