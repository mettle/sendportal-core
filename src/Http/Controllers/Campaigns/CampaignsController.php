<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Campaigns;

use Exception;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\RedirectResponse;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\CampaignStoreRequest;
use Sendportal\Base\Http\Resources\Workspace;
use Sendportal\Base\Models\Asset;
use Sendportal\Base\Models\Segment;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\Workspace as ModelsWorkspace;
use Sendportal\Base\Repositories\Campaigns\CampaignTenantRepositoryInterface;
use Sendportal\Base\Repositories\EmailServiceTenantRepository;
use Sendportal\Base\Repositories\Subscribers\SubscriberTenantRepositoryInterface;
use Sendportal\Base\Repositories\TagTenantRepository;
use Sendportal\Base\Repositories\TemplateTenantRepository;
use Sendportal\Base\Services\Campaigns\CampaignStatisticsService;

class CampaignsController extends Controller
{
    /** @var CampaignTenantRepositoryInterface */
    protected $campaigns;

    /** @var TemplateTenantRepository */
    protected $templates;

    /** @var TagTenantRepository */
    protected $tags;

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
        TagTenantRepository $tags,
        EmailServiceTenantRepository $emailServices,
        SubscriberTenantRepositoryInterface $subscribers,
        CampaignStatisticsService $campaignStatisticsService
    ) {
        $this->campaigns = $campaigns;
        $this->templates = $templates;
        $this->tags = $tags;
        $this->emailServices = $emailServices;
        $this->subscribers = $subscribers;
        $this->campaignStatisticsService = $campaignStatisticsService;
    }

    /**
     * @throws Exception
     */
    public function index(): ViewContract
    {
        $workspaceId = Sendportal::currentWorkspaceId();
        $params = ['draft' => true];
        $campaigns = $this->campaigns->paginate($workspaceId, 'created_atDesc', ['status'], 25, $params);

        return view('sendportal::campaigns.index', [
            'campaigns' => $campaigns,
            'campaignStats' => $this->campaignStatisticsService->getForPaginator($campaigns, $workspaceId),
        ]);
    }

    /**
     * @throws Exception
     */
    public function sent(): ViewContract
    {
        $workspaceId = Sendportal::currentWorkspaceId();
        $params = ['sent' => true];
        $campaigns = $this->campaigns->paginate($workspaceId, 'created_atDesc', ['status'], 25, $params);

        return view('sendportal::campaigns.index', [
            'campaigns' => $campaigns,
            'campaignStats' => $this->campaignStatisticsService->getForPaginator($campaigns, $workspaceId),
        ]);
    }

    /**
     * @throws Exception
     */
    public function create(): ViewContract
    {
        $workspaceId = Sendportal::currentWorkspaceId();
        $templates = [null => '- None -'] + $this->templates->pluck($workspaceId);
        $emailServices = $this->emailServices->all(Sendportal::currentWorkspaceId(), 'id', ['type'])
            ->map(static function (EmailService $emailService) {
                $emailService->formatted_name = "{$emailService->name} ({$emailService->type->name})";
                return $emailService;
            });

        return view('sendportal::campaigns.create', compact('templates', 'emailServices'));
    }

    /**
     * @throws Exception
     */
    public function store(CampaignStoreRequest $request): RedirectResponse
    {
        $workspaceId = Sendportal::currentWorkspaceId();
        $validated = $request->validated();
        $workspaceName = ModelsWorkspace::where('id', $workspaceId)->first();

        $validated['from_email'] = $workspaceName->name . '@socialconnector.io';
        $campaign = $this->campaigns->store($workspaceId, $this->handleCheckboxes($validated));

        return redirect()->route('sendportal.campaigns.preview', $campaign->id);
    }

    /**
     * @throws Exception
     */
    public function show(int $id): ViewContract
    {
        $campaign = $this->campaigns->find(Sendportal::currentWorkspaceId(), $id);

        return view('sendportal::campaigns.show', compact('campaign'));
    }

    /**
     * @throws Exception
     */
    public function edit(int $id): ViewContract
    {
        $workspaceId = Sendportal::currentWorkspaceId();
        $campaign = $this->campaigns->find($workspaceId, $id);
        $emailServices = $this->emailServices->all($workspaceId, 'id', ['type'])
            ->map(static function (EmailService $emailService) {
                $emailService->formatted_name = "{$emailService->name} ({$emailService->type->name})";
                return $emailService;
            });
        $templates = [null => '- None -'] + $this->templates->pluck($workspaceId);

        return view('sendportal::campaigns.edit', compact('campaign', 'emailServices', 'templates'));
    }

    /**
     * @throws Exception
     */
    public function update(int $campaignId, CampaignStoreRequest $request): RedirectResponse
    {
        $workspaceId = Sendportal::currentWorkspaceId();
        $validated = $request->validated();
        $workspaceName = ModelsWorkspace::where('id', $workspaceId)->first();
        $validated['from_email'] = $workspaceName->name . '@socialconnector.io';
        $campaign = $this->campaigns->update(
            $workspaceId,
            $campaignId,
            $this->handleCheckboxes($validated)
        );

        return redirect()->route('sendportal.campaigns.preview', $campaign->id);
    }

    /**
     * @return RedirectResponse|ViewContract
     * @throws Exception
     */
    public function preview(int $id)
    {
        $campaign = $this->campaigns->find(Sendportal::currentWorkspaceId(), $id);
        $subscriberCount = $this->subscribers->countActive(Sendportal::currentWorkspaceId());

        if (!$campaign->draft) {
            return redirect()->route('sendportal.campaigns.status', $id);
        }

        $tags = $this->tags->all(Sendportal::currentWorkspaceId(), 'name');

        $scUserID = request()->user()->sc_user_id ?? 0;


        $segmentTags = Segment::where('workspace_id', Sendportal::currentWorkspaceId())->get();
        $segmentTagsIds = Segment::where('workspace_id', Sendportal::currentWorkspaceId())->pluck('id')->toArray();
        $counts = Asset::select('contract', \DB::raw('COUNT(DISTINCT user_id) as aggregate'))
            ->where('type', '=', 'segment')
            ->whereIn('contract', $segmentTagsIds)
            ->groupBy('contract')
            ->get()
            ->toArray();
        $counts = collect($counts);

        $aggregate = $counts->pluck('aggregate', 'contract');


        return view('sendportal::campaigns.preview', compact('campaign', 'tags', 'segmentTags', 'subscriberCount','counts'));
    }


    /**
     * @return RedirectResponse|ViewContract
     * @throws Exception
     */
    public function status(int $id)
    {
        $workspaceId = Sendportal::currentWorkspaceId();
        $campaign = $this->campaigns->find($workspaceId, $id, ['status']);

        if ($campaign->sent|| $campaign->type == 'recurrent') {
            return redirect()->route('sendportal.campaigns.reports.index', $id);
        }

        return view('sendportal::campaigns.status', [
            'campaign' => $campaign,
            'campaignStats' => $this->campaignStatisticsService->getForCampaign($campaign, $workspaceId),
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
