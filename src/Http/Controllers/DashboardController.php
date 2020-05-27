<?php

namespace Sendportal\Base\Http\Controllers;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Sendportal\Base\Models\Workspace;
use Sendportal\Base\Repositories\Campaigns\CampaignTenantRepositoryInterface;
use Sendportal\Base\Repositories\Messages\MessageTenantRepositoryInterface;
use Sendportal\Base\Repositories\SubscriberTenantRepository;

class DashboardController extends Controller
{
    /**
     * @var SubscriberTenantRepository
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
     * DashboardController constructor.
     *
     * @param SubscriberTenantRepository $subscribers
     * @param CampaignTenantRepositoryInterface $campaigns
     * @param MessageTenantRepositoryInterface $messages
     */
    public function __construct(SubscriberTenantRepository $subscribers, CampaignTenantRepositoryInterface $campaigns, MessageTenantRepositoryInterface $messages) {
        $this->subscribers = $subscribers;
        $this->campaigns = $campaigns;
        $this->messages = $messages;
    }

    /**
     * @throws Exception
     */
    public function index(): View {
        $workspace = auth()->user()->currentWorkspace();
        $subscriberGrowthChart = $this->getSubscriberGrowthChart($workspace);

        return view('sendportal::dashboard', [
            'recentSubscribers' => $this->subscribers->getRecentSubscribers($workspace->id),
            'completedCampaigns' => $this->campaigns->completedCampaigns($workspace->id, ['messages', 'opens']),
            'subscriberGrowthChartLabels' => json_encode($subscriberGrowthChart['labels']),
            'subscriberGrowthChartData' => json_encode($subscriberGrowthChart['data']),
        ]);
    }

    protected function getSubscriberGrowthChart(Workspace $workspace): array {

        $period = CarbonPeriod::create(now()->subDays(30), now());
        $growthChartData = $this->subscribers->getGrowthChartData($period, $workspace->id);
        $growthChart = [
            'labels' => [],
            'data' => [],
        ];

        $previousUnsubscribersValue = 0;

        foreach ($period as $date) {
            /** @var Carbon $date */
            $formattedDate = $date->format('d-m-Y');
            $previousValue = (collect($growthChart['data'])->last() ?? $growthChartData['startingValue']) + $previousUnsubscribersValue;

            if($unsubscribers = Arr::get($growthChartData['unsubscribers'], $formattedDate))
            {
                $previousUnsubscribersValue = $unsubscribers->total;
            }

            $growthChart['labels'][] = $formattedDate;
            $growthChart['data'][] = ($growthChartData['runningTotal'][$formattedDate]->total ?? $previousValue) - $previousUnsubscribersValue;
        }

        return $growthChart;
    }
}
