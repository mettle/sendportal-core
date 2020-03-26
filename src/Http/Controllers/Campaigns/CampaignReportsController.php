<?php

namespace Sendportal\Base\Http\Controllers\Campaigns;

use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Interfaces\CampaignTenantInterface;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Presenters\CampaignReportPresenter;
use Sendportal\Base\Repositories\MessageTenantRepository;
use Illuminate\Http\RedirectResponse;

class CampaignReportsController extends Controller
{
    /**
     * @var CampaignTenantInterface
     */
    protected $campaignRepo;
    /**
     * @var MessageTenantRepository
     */
    protected $messageRepo;

    /**
     * CampaignsController constructor.
     *
     * @param CampaignTenantInterface $campaignRepository
     * @param MessageTenantRepository $messageRepo
     */
    public function __construct(
        CampaignTenantInterface $campaignRepository,
        MessageTenantRepository $messageRepo
    ) {
        $this->campaignRepo = $campaignRepository;
        $this->messageRepo = $messageRepo;
    }

    /**
     * Show campaign report view
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\Factory|RedirectResponse|\Illuminate\View\View
     * @throws \Exception
     */
    public function index($id)
    {
        $campaign = $this->campaignRepo->find(currentTeamId(), $id);

        if ($campaign->draft) {
            return redirect()->route('campaigns.edit', $id);
        }

        if ($campaign->queued or $campaign->sending) {
            return redirect()->route('campaigns.status', $id);
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
     * @return \Illuminate\Contracts\View\Factory|RedirectResponse|\Illuminate\View\View
     * @throws \Exception
     */
    public function recipients($id)
    {
        $campaign = $this->campaignRepo->find(currentTeamId(), $id);

        if ($campaign->draft) {
            return redirect()->route('campaigns.edit', $id);
        }

        if ($campaign->queued or $campaign->sending) {
            return redirect()->route('campaigns.status', $id);
        }

        $messages = $this->messageRepo->recipients(currentTeamId(), Campaign::class, $id);

        return view('campaigns.reports.recipients', compact('campaign', 'messages'));
    }

    /**
     * Show campaign opens
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\Factory|RedirectResponse|\Illuminate\View\View
     * @throws \Exception
     */
    public function opens($id)
    {
        $campaign = $this->campaignRepo->find(currentTeamId(), $id);
        $averageTimeToOpen = $this->campaignRepo->getAverageTimeToOpen($campaign);

        if ($campaign->draft) {
            return redirect()->route('campaigns.edit', $id);
        }

        if ($campaign->queued or $campaign->sending) {
            return redirect()->route('campaigns.status', $id);
        }

        $messages = $this->messageRepo->opens(currentTeamId(), Campaign::class, $id);

        return view('sendportal::campaigns.reports.opens', compact('campaign', 'messages', 'averageTimeToOpen'));
    }

    /**
     * Show campaign clicks
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\Factory|RedirectResponse|\Illuminate\View\View
     * @throws \Exception
     */
    public function clicks($id)
    {
        $campaign = $this->campaignRepo->find(currentTeamId(), $id);
        $averageTimeToClick = $this->campaignRepo->getAverageTimeToClick($campaign);

        if ($campaign->draft) {
            return redirect()->route('campaigns.edit', $id);
        }

        if ($campaign->queued or $campaign->sending) {
            return redirect()->route('campaigns.status', $id);
        }

        $messages = $this->messageRepo->clicks(currentTeamId(), Campaign::class, $id);

        return view('sendportal::campaigns.reports.clicks', compact('campaign', 'messages', 'averageTimeToClick'));
    }

    /**
     * Show campaign bounces
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\Factory|RedirectResponse|\Illuminate\View\View
     * @throws \Exception
     */
    public function bounces($id)
    {
        $campaign = $this->campaignRepo->find(currentTeamId(), $id);

        if ($campaign->draft) {
            return redirect()->route('campaigns.edit', $id);
        }

        if ($campaign->queued or $campaign->sending) {
            return redirect()->route('campaigns.status', $id);
        }

        $messages = $this->messageRepo->bounces(currentTeamId(), Campaign::class, $id);

        return view('sendportal::campaigns.reports.bounces', compact('campaign', 'messages'));
    }

    /**
     * Show campaign unsubscribes
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\Factory|RedirectResponse|\Illuminate\View\View
     * @throws \Exception
     */
    public function unsubscribes($id)
    {
        $campaign = $this->campaignRepo->find(currentTeamId(), $id);

        if ($campaign->draft) {
            return redirect()->route('campaigns.edit', $id);
        }

        if ($campaign->queued or $campaign->sending) {
            return redirect()->route('campaigns.status', $id);
        }

        $messages = $this->messageRepo->unsubscribes(currentTeamId(), Campaign::class, $id);

        return view('sendportal::campaigns.reports.unsubscribes', compact('campaign', 'messages'));
    }
}
