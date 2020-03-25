<?php

namespace Sendportal\Base\Console\Commands;

use Sendportal\Base\Interfaces\CampaignTenantInterface;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\CampaignStatus;
use Sendportal\Base\Services\Campaigns\CampaignDispatchService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class CampaignDispatchCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'sp:campaigns:dispatch';

    /**
     * @var string
     */
    protected $description = 'Dispatch all campaigns waiting in the queue';

    /**
     * @var CampaignTenantInterface
     */
    protected $campaignRepo;

    /**
     * @var CampaignDispatchService
     */
    protected $campaignService;

    /**
     * Execute the console command.
     */
    public function handle(
        CampaignTenantInterface $campaignRepo,
        CampaignDispatchService $campaignService
    ): void
    {
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
            \Log::info($message);
            $count++;

            $this->campaignService->handle($campaign);
        }

        $message = 'Finished dispatching campaigns';
        $this->info($message);
        \Log::info($message);
    }

    /**
     * Get all queued campaigns
     *
     * @return EloquentCollection
     */
    protected function getQueuedCampaigns(): EloquentCollection
    {
        return Campaign::where('status_id', CampaignStatus::STATUS_QUEUED)
            ->get();
    }
}
