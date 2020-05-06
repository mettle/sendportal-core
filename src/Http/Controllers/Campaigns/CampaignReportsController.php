<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Campaigns;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Presenters\CampaignReportPresenter;
use Sendportal\Base\Repositories\Campaigns\CampaignTenantRepositoryInterface;
use Sendportal\Base\Repositories\Messages\MessageTenantRepositoryInterface;

class CampaignReportsController extends Controller
{
    /** @var CampaignTenantRepositoryInterface */
    protected $campaignRepo;

    /** @var MessageTenantRepositoryInterface */
    protected $messageRepo;

    public function __construct(
        CampaignTenantRepositoryInterface $campaignRepository,
        MessageTenantRepositoryInterface $messageRepo
    ) {
        $this->campaignRepo = $campaignRepository;
        $this->messageRepo = $messageRepo;
    }

    /**
     * Show campaign report view.
     *
     * @return RedirectResponse|View
     * @throws Exception
     */
    public function index(int $id)
    {
        $campaign = $this->campaignRepo->find(auth()->user()->currentWorkspace()->id, $id);

        if ($campaign->draft) {
            return redirect()->route('sendportal.campaigns.edit', $id);
        }

        if ($campaign->queued || $campaign->sending) {
            return redirect()->route('sendportal.campaigns.status', $id);
        }

        $presenter = new CampaignReportPresenter($campaign, auth()->user()->currentWorkspace());
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
     * Show campaign recipients.
     *
     * @return RedirectResponse|View
     * @throws Exception
     */
    public function recipients(int $id)
    {
        $campaign = $this->campaignRepo->find(auth()->user()->currentWorkspace()->id, $id);

        if ($campaign->draft) {
            return redirect()->route('sendportal.campaigns.edit', $id);
        }

        if ($campaign->queued || $campaign->sending) {
            return redirect()->route('sendportal.campaigns.status', $id);
        }

        $messages = $this->messageRepo->recipients(auth()->user()->currentWorkspace()->id, Campaign::class, $id);

        return view('sendportal::campaigns.reports.recipients', compact('campaign', 'messages'));
    }

    /**
     * Show campaign opens.
     *
     * @return RedirectResponse|View
     * @throws Exception
     */
    public function opens(int $id)
    {
        $campaign = $this->campaignRepo->find(auth()->user()->currentWorkspace()->id, $id);
        $averageTimeToOpen = $this->campaignRepo->getAverageTimeToOpen($campaign);

        if ($campaign->draft) {
            return redirect()->route('sendportal.campaigns.edit', $id);
        }

        if ($campaign->queued || $campaign->sending) {
            return redirect()->route('sendportal.campaigns.status', $id);
        }

        $messages = $this->messageRepo->opens(auth()->user()->currentWorkspace()->id, Campaign::class, $id);

        return view('sendportal::campaigns.reports.opens', compact('campaign', 'messages', 'averageTimeToOpen'));
    }

    /**
     * Show campaign clicks.
     *
     * @return RedirectResponse|View
     * @throws Exception
     */
    public function clicks(int $id)
    {
        $campaign = $this->campaignRepo->find(auth()->user()->currentWorkspace()->id, $id);
        $averageTimeToClick = $this->campaignRepo->getAverageTimeToClick($campaign);

        if ($campaign->draft) {
            return redirect()->route('sendportal.campaigns.edit', $id);
        }

        if ($campaign->queued || $campaign->sending) {
            return redirect()->route('sendportal.campaigns.status', $id);
        }

        $messages = $this->messageRepo->clicks(auth()->user()->currentWorkspace()->id, Campaign::class, $id);

        return view('sendportal::campaigns.reports.clicks', compact('campaign', 'messages', 'averageTimeToClick'));
    }

    /**
     * Show campaign bounces.
     *
     * @return RedirectResponse|View
     * @throws Exception
     */
    public function bounces(int $id)
    {
        $campaign = $this->campaignRepo->find(auth()->user()->currentWorkspace()->id, $id);

        if ($campaign->draft) {
            return redirect()->route('sendportal.campaigns.edit', $id);
        }

        if ($campaign->queued || $campaign->sending) {
            return redirect()->route('sendportal.campaigns.status', $id);
        }

        $messages = $this->messageRepo->bounces(auth()->user()->currentWorkspace()->id, Campaign::class, $id);

        return view('sendportal::campaigns.reports.bounces', compact('campaign', 'messages'));
    }

    /**
     * Show campaign unsubscribes.
     *
     * @return RedirectResponse|View
     * @throws Exception
     */
    public function unsubscribes(int $id)
    {
        $campaign = $this->campaignRepo->find(auth()->user()->currentWorkspace()->id, $id);

        if ($campaign->draft) {
            return redirect()->route('sendportal.campaigns.edit', $id);
        }

        if ($campaign->queued || $campaign->sending) {
            return redirect()->route('sendportal.campaigns.status', $id);
        }

        $messages = $this->messageRepo->unsubscribes(auth()->user()->currentWorkspace()->id, Campaign::class, $id);

        return view('sendportal::campaigns.reports.unsubscribes', compact('campaign', 'messages'));
    }
}
