<?php

namespace Sendportal\Base\Http\Controllers;

use Carbon\CarbonPeriod;
use Exception;
use Illuminate\View\View;
use Sendportal\Base\Models\Workspace;
use Sendportal\Base\Repositories\Campaigns\CampaignTenantRepositoryInterface;
use Sendportal\Base\Repositories\Messages\MessageTenantRepositoryInterface;
use \Sendportal\Base\Repositories\Subscribers\SubscriberTenantRepositoryInterface;
use Sendportal\Base\Services\Campaigns\CampaignStatisticsService;

class DashboardController extends Controller
{
    /**
     * @var SubscriberTenantRepositoryInterface
     */
    protected $subscribers;

    /**
     * @var CampaignTenantRepositoryInterface
     */
    protected $campaigns;

    /**
     * @var MessageTenantRepositoryInterface
     */
    protected $messages;

    /**
     * @var CampaignStatisticsService
     */
    protected $campaignStatisticsService;

    /**
     * DashboardController constructor.
     *
     * @param SubscriberTenantRepository $subscribers
     * @param CampaignTenantRepositoryInterface $campaigns
     * @param MessageTenantRepositoryInterface $messages
     */
    public function __construct(SubscriberTenantRepositoryInterface $subscribers, CampaignTenantRepositoryInterface $campaigns, MessageTenantRepositoryInterface $messages, CampaignStatisticsService $campaignStatisticsService)
    {
        $this->subscribers = $subscribers;
        $this->campaigns = $campaigns;
        $this->messages = $messages;
        $this->campaignStatisticsService = $campaignStatisticsService;
    }

    /**
     * @throws Exception
     */
    public function index(): View
    {
        $workspace = auth()->user()->currentWorkspace();
        $completedCampaigns = $this->campaigns->completedCampaigns($workspace->id, ['status']);
        $subscriberGrowthChart = $this->getSubscriberGrowthChart($workspace);

        return view('sendportal::dashboard.index', [
            'recentSubscribers' => $this->subscribers->getRecentSubscribers($workspace->id),
            'completedCampaigns' => $completedCampaigns,
            'campaignStats' => $this->campaignStatisticsService->getForCollection($completedCampaigns, $workspace),
            'subscriberGrowthChartLabels' => json_encode($subscriberGrowthChart['labels']),
            'subscriberGrowthChartData' => json_encode($subscriberGrowthChart['data']),
        ]);
    }

    protected function getSubscriberGrowthChart(Workspace $workspace): array
    {
        $period = CarbonPeriod::create(now()->subDays(30)->startOfDay(), now()->endOfDay());

        $growthChartData = $this->subscribers->getGrowthChartData($period, $workspace->id);

        $growthChart = [
            'labels' => [],
            'data' => [],
        ];

        $currentTotal = $growthChartData['startingValue'];

        foreach ($period as $date) {
            $formattedDate = $date->format('d-m-Y');

            $periodValue = $growthChartData['runningTotal'][$formattedDate]->total ?? 0;
            $periodUnsubscribe = $growthChartData['unsubscribers'][$formattedDate]->total ?? 0;
            $currentTotal += $periodValue - $periodUnsubscribe;

            $growthChart['labels'][] = $formattedDate;
            $growthChart['data'][] = $currentTotal;
        }

        return $growthChart;
    }
}
