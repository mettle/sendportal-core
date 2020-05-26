<?php

namespace Sendportal\Base\Http\Controllers;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\View\View;
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
        $subscribers = $this->subscribers->all($workspace->id);
        $subscriberGrowthChart = $this->getSubscriberGrowthChart($subscribers);
        $completedCampaigns = $this->campaigns->completedCampaigns($workspace->id, ['messages', 'opens']);

        return view('sendportal::dashboard', [
            'subscribers' => $subscribers->filter(function ($subscriber) {
                return ! $subscriber->unsubscribed_at;
            }),
            'unsubscribers' => $subscribers->filter(function ($subscriber)
            {
                return $subscriber->unsubscribed_at;
            })->count(),
            'recentSubscribers' => $subscribers->sortByDesc(function ($subscriber)
            {
                return $subscriber->created_at;
            })->take(10),
            'newSubscribers' => $this->getNewSubscribers($subscribers),
            'completedCampaigns' => $completedCampaigns,
            'campaignOpenRate' => $this->getCampaignOpenRate($completedCampaigns),
            'emailsDelivered' => $this->messages->totalDelivered($workspace->id),
            'subscriberGrowthChartLabels' => json_encode($subscriberGrowthChart['labels']),
            'subscriberGrowthChartData' => json_encode($subscriberGrowthChart['data']),
        ]);
    }

    protected function getSubscriberGrowthChart(Collection $subscribers): array {
        $growthChart = [];
        $period = CarbonPeriod::create(now()->subDays(30), now());

        foreach ($period as $date)
        {
            /** @var Carbon $date */
            $formattedDate = $date->format('d-m-Y');

            $growthChart['labels'][] = $formattedDate;
            $growthChart['data'][] = $subscribers->filter(function ($subscriber) use ($formattedDate)
            {
                $parsedDate = Carbon::parse($formattedDate);
                return $subscriber->created_at->startOfDay()->lte($parsedDate)
                    && ( ! $subscriber->unsubscribed_at || $subscriber->unsubscribed_at->gte($parsedDate));
            })->count();
        }

        return $growthChart;
    }

    protected function getNewSubscribers(Collection $subscribers): int {
        return $subscribers->filter(function ($subscriber)
        {
            return $subscriber->created_at->gte(now()->subDays(30)) && $subscriber->created_at->lte(now());
        })->count();
    }

    protected function getCampaignOpenRate(Collection $campaigns): string {
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
            return number_format(($uniqueOpens / $sentMessages) * 100, 2);
        }

        return '0.00';
    }
}
