<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Campaigns;

use Exception;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\RedirectResponse;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\CampaignStoreRequest;
use Sendportal\Base\Repositories\Campaigns\CampaignTenantRepositoryInterface;
use Sendportal\Base\Repositories\EmailServiceTenantRepository;
use Sendportal\Base\Repositories\SegmentTenantRepository;
use Sendportal\Base\Repositories\Subscribers\SubscriberTenantRepositoryInterface;
use Sendportal\Base\Repositories\TemplateTenantRepository;
use Sendportal\Base\Services\Campaigns\CampaignStatisticsService;

class CampaignsController extends Controller
{
    /** @var CampaignTenantRepositoryInterface */
    protected $campaigns;

    /** @var TemplateTenantRepository */
    protected $templates;

    /** @var SegmentTenantRepository */
    protected $segments;

    /** @var EmailServiceTenantRepository */
    protected $emailServices;

    /** @var SubscriberTenantRepositoryInterface */
    protected $subscribers;

    /**
     * @var CampaignStatisticsService
     */
    protected $campaignStatisticsService;

    public function __construct(
        CampaignTenantRepositoryInterface $campaigns,
        TemplateTenantRepository $templates,
        SegmentTenantRepository $segments,
        EmailServiceTenantRepository $emailServices,
        SubscriberTenantRepositoryInterface $subscribers,
        CampaignStatisticsService $campaignStatisticsService
    ) {
        $this->campaigns = $campaigns;
        $this->templates = $templates;
        $this->segments = $segments;
        $this->emailServices = $emailServices;
        $this->subscribers = $subscribers;
        $this->campaignStatisticsService = $campaignStatisticsService;
    }

    /**
     * @throws Exception
     */
    public function index(): ViewContract
    {
        $workspace = auth()->user()->currentWorkspace();
        $campaigns = $this->campaigns->paginate($workspace->id, 'created_atDesc', ['status']);

        return view('sendportal::campaigns.index', [
            'campaigns' => $campaigns,
            'campaignStats' => $this->campaignStatisticsService->getForPaginator($campaigns, $workspace),
        ]);
    }

    /**
     * @throws Exception
     */
    public function create(): ViewContract
    {
        $templates = [null => '- None -'] + $this->templates->pluck(auth()->user()->currentWorkspace()->id);
        $emailServices = $this->emailServices->all(auth()->user()->currentWorkspace()->id);

        return view('sendportal::campaigns.create', compact('templates', 'emailServices'));
    }

    /**
     * @throws Exception
     */
    public function store(CampaignStoreRequest $request): RedirectResponse
    {
        $campaign = $this->campaigns->store(auth()->user()->currentWorkspace()->id, $this->handleCheckboxes($request->validated()));

        return redirect()->route('sendportal.campaigns.preview', $campaign->id);
    }

    /**
     * @throws Exception
     */
    public function show(int $id): ViewContract
    {
        $campaign = $this->campaigns->find(auth()->user()->currentWorkspace()->id, $id);

        return view('sendportal::campaigns.show', compact('campaign'));
    }

    /**
     * @throws Exception
     */
    public function edit(int $id): ViewContract
    {
        $campaign = $this->campaigns->find(auth()->user()->currentWorkspace()->id, $id);
        $emailServices = $this->emailServices->all(auth()->user()->currentWorkspace()->id);
        $templates = [null => '- None -'] + $this->templates->pluck(auth()->user()->currentWorkspace()->id);

        return view('sendportal::campaigns.edit', compact('campaign', 'emailServices', 'templates'));
    }

    /**
     * @throws Exception
     */
    public function update(int $campaignId, CampaignStoreRequest $request): RedirectResponse
    {
        $campaign = $this->campaigns->update(
            auth()->user()->currentWorkspace()->id,
            $campaignId,
            $this->handleCheckboxes($request->validated())
        );

        return redirect()->route('sendportal.campaigns.preview', $campaign->id);
    }

    /**
     * @return RedirectResponse|ViewContract
     * @throws Exception
     */
    public function preview(int $id)
    {
        $campaign = $this->campaigns->find(auth()->user()->currentWorkspace()->id, $id, ['segments']);
        $subscriberCount = $this->subscribers->countActive(auth()->user()->currentWorkspace()->id);

        if (!$campaign->draft) {
            return redirect()->route('sendportal.campaigns.status', $id);
        }

        $segments = $this->segments->all(auth()->user()->currentWorkspace()->id, 'name');

        return view('sendportal::campaigns.preview', compact('campaign', 'segments', 'subscriberCount'));
    }

    /**
     * @return RedirectResponse|ViewContract
     * @throws Exception
     */
    public function status(int $id)
    {
        $workspace = auth()->user()->currentWorkspace();
        $campaign = $this->campaigns->find($workspace->id, $id, ['status']);

        if ($campaign->sent) {
            return redirect()->route('sendportal.campaigns.reports.index', $id);
        }

        return view('sendportal::campaigns.status', [
            'campaign' => $campaign,
            'campaignStats' => $this->campaignStatisticsService->getForCampaign($campaign, $workspace),
        ]);
    }

    /**
     * Handle checkbox fields.
     *
     * NOTE(david): this is here because the Campaign model is marked as being unable to use boolean fields.
     */
    private function handleCheckboxes(array $input): array
    {
        $checkboxFields = [
            'is_open_tracking',
            'is_click_tracking'
        ];

        foreach ($checkboxFields as $checkboxField) {
            if (!isset($input[$checkboxField])) {
                $input[$checkboxField] = false;
            }
        }

        return $input;
    }
}
