<?php

namespace Sendportal\Base\Http\Controllers;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Sendportal\Base\Models\Campaign;
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
    private $campaigns;

    /**
     * @var MessageTenantRepositoryInterface
     */
    private $messages;

    /**
     * DashboardController constructor.
     *
     * @param SubscriberTenantRepository $subscribers
     * @param CampaignTenantRepositoryInterface $campaigns
     * @param MessageTenantRepositoryInterface $messages
     */
    public function __construct(SubscriberTenantRepository $subscribers, CampaignTenantRepositoryInterface $campaigns, MessageTenantRepositoryInterface $messages)
    {
        $this->subscribers = $subscribers;
        $this->campaigns = $campaigns;
        $this->messages = $messages;
    }

    /**
     * @throws Exception
     */
    public function index(): View
    {
        $workspace = auth()->user()->currentWorkspace();
        $subscribers = $this->subscribers->all($workspace->id);
        $subscriberGrowthChart = $this->getSubscriberGrowthChart($subscribers);
        $completedCampaigns = $this->campaigns->completedCampaigns($workspace->id, ['messages', 'opens']);

        return view('sendportal::dashboard', [
            'subscribers' => $subscribers,
            'unsubscribers' => $subscribers->filter(function ($subscriber)
            {
                return $subscriber->unsubscribed_at;
            })->count(),
            'recentSubscribers' => $subscribers->sortByDesc(function ($subscriber)
            {
                return $subscriber->created_at;
            })->take(10),
            'subscribersThisMonth' => $this->getSubscribersForMonth($subscribers),
            'completedCampaigns' => $completedCampaigns,
            'campaignOpenRate' => $this->getCampaignOpenRate($completedCampaigns),
            'emailsDelivered' => $this->messages->totalDelivered($workspace->id),
            'subscriberGrowthChartLabels' => json_encode($subscriberGrowthChart['labels']),
            'subscriberGrowthChartData' => json_encode($subscriberGrowthChart['data']),
        ]);
    }

    protected function getSubscriberGrowthChart(Collection $subscribers): array
    {
        $growthChart = [];
        $period = CarbonPeriod::create(now()->subDays(30), now());

        foreach ($period as $date)
        {
            /** @var Carbon $date */
            $formattedDate = $date->format('d-m-Y');

            $growthChart['labels'][] = $formattedDate;
            $growthChart['data'][] = $subscribers->filter(function ($subscriber) use ($formattedDate)
            {
                return $subscriber->created_at->startOfDay()->lte(Carbon::parse($formattedDate));
            })->count();
        }

        return $growthChart;
    }

    protected function getSubscribersForMonth(Collection $subscribers): int
    {
        return $subscribers->filter(function ($subscriber)
        {
            return $subscriber->created_at->isSameMonth(now());
        })->count();
    }

    protected function getCampaignOpenRate(Collection $campaigns): float
    {
        $sentMessages = $campaigns->sum(function ($campaign)
        {
            return $campaign->sent_count;
        });

        $uniqueOpens = $campaigns->sum(function ($campaign)
        {
            return $campaign->unique_open_count;
        });

        if ($sentMessages && $uniqueOpens)
        {
            return $uniqueOpens / $sentMessages;
        }

        return 0.00;
    }
}
