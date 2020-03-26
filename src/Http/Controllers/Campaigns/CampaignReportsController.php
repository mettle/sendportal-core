<?php

namespace Sendportal\Base\Http\Controllers\Campaigns;

use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Presenters\CampaignReportPresenter;
use Sendportal\Base\Repositories\CampaignTenantRepository;
use Sendportal\Base\Repositories\MessageTenantRepository;

class CampaignReportsController extends Controller
{
    /**
     * @var CampaignTenantRepository
     */
    protected $campaignRepo;
    /**
     * @var MessageTenantRepository
     */
    protected $messageRepo;

    /**
     * CampaignsController constructor.
     *
     * @param CampaignTenantRepository $campaignRepository
     * @param MessageTenantRepository $messageRepo
     */
    public function __construct(
        CampaignTenantRepository $campaignRepository,
        MessageTenantRepository $messageRepo
    ) {
        $this->campaignRepo = $campaignRepository;
        $this->messageRepo = $messageRepo;
    }

    /**
     * Show campaign report view
     *
     * @param int $id
     * @return Factory|RedirectResponse|View
     * @throws Exception
     */
    public function index($id)
    {
        $campaign = $this->campaignRepo->find(auth()->user()->currentWorkspace()->id, $id);

        if ($campaign->draft) {
            return redirect()->route('sendportal.campaigns.edit', $id);
        }

        if ($campaign->queued or $campaign->sending) {
            return redirect()->route('sendportal.campaigns.status', $id);
        }

        $presenter = new CampaignReportPresenter($campaign);
        $presenterData = $presenter->generate();

        $data = [
            'campaign' => $campaign,
            'campaignUrls' => $presenterData['campaignUrls'],
            'chartLabels' => json_encode($presenterData['chartData']['labels']),
            'chartData' => json_encode($presenterData['chartData']['data']),
        ];

        return view('sendportal::campaigns.reports.index', $data);
    }

    /**
     * Show campaign recipients
     *
     * @param int $id
     * @return Factory|RedirectResponse|View
     * @throws Exception
     */
    public function recipients($id)
    {
        $campaign = $this->campaignRepo->find(auth()->user()->currentWorkspace()->id, $id);

        if ($campaign->draft) {
            return redirect()->route('sendportal.campaigns.edit', $id);
        }

        if ($campaign->queued or $campaign->sending) {
            return redirect()->route('sendportal.campaigns.status', $id);
        }

        $messages = $this->messageRepo->recipients(auth()->user()->currentWorkspace()->id, Campaign::class, $id);

        return view('sendportal::campaigns.reports.recipients', compact('campaign', 'messages'));
    }

    /**
     * Show campaign opens
     *
     * @param int $id
     * @return Factory|RedirectResponse|View
     * @throws Exception
     */
    public function opens($id)
    {
        $campaign = $this->campaignRepo->find(auth()->user()->currentWorkspace()->id, $id);
        $averageTimeToOpen = $this->campaignRepo->getAverageTimeToOpen($campaign);

        if ($campaign->draft) {
            return redirect()->route('sendportal.campaigns.edit', $id);
        }

        if ($campaign->queued or $campaign->sending) {
            return redirect()->route('sendportal.campaigns.status', $id);
        }

        $messages = $this->messageRepo->opens(auth()->user()->currentWorkspace()->id, Campaign::class, $id);

        return view('sendportal::campaigns.reports.opens', compact('campaign', 'messages', 'averageTimeToOpen'));
    }

    /**
     * Show campaign clicks
     *
     * @param int $id
     * @return Factory|RedirectResponse|View
     * @throws Exception
     */
    public function clicks($id)
    {
        $campaign = $this->campaignRepo->find(auth()->user()->currentWorkspace()->id, $id);
        $averageTimeToClick = $this->campaignRepo->getAverageTimeToClick($campaign);

        if ($campaign->draft) {
            return redirect()->route('sendportal.campaigns.edit', $id);
        }

        if ($campaign->queued or $campaign->sending) {
            return redirect()->route('sendportal.campaigns.status', $id);
        }

        $messages = $this->messageRepo->clicks(auth()->user()->currentWorkspace()->id, Campaign::class, $id);

        return view('sendportal::campaigns.reports.clicks', compact('campaign', 'messages', 'averageTimeToClick'));
    }

    /**
     * Show campaign bounces
     *
     * @param int $id
     * @return Factory|RedirectResponse|View
     * @throws Exception
     */
    public function bounces($id)
    {
        $campaign = $this->campaignRepo->find(auth()->user()->currentWorkspace()->id, $id);

        if ($campaign->draft) {
            return redirect()->route('sendportal.campaigns.edit', $id);
        }

        if ($campaign->queued or $campaign->sending) {
            return redirect()->route('sendportal.campaigns.status', $id);
        }

        $messages = $this->messageRepo->bounces(auth()->user()->currentWorkspace()->id, Campaign::class, $id);

        return view('sendportal::campaigns.reports.bounces', compact('campaign', 'messages'));
    }

    /**
     * Show campaign unsubscribes
     *
     * @param int $id
     * @return Factory|RedirectResponse|View
     * @throws Exception
     */
    public function unsubscribes($id)
    {
        $campaign = $this->campaignRepo->find(auth()->user()->currentWorkspace()->id, $id);

        if ($campaign->draft) {
            return redirect()->route('sendportal.campaigns.edit', $id);
        }

        if ($campaign->queued or $campaign->sending) {
            return redirect()->route('sendportal.campaigns.status', $id);
        }

        $messages = $this->messageRepo->unsubscribes(auth()->user()->currentWorkspace()->id, Campaign::class, $id);

        return view('sendportal::campaigns.reports.unsubscribes', compact('campaign', 'messages'));
    }
}
