<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Campaigns;

use Exception;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\RedirectResponse;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\CampaignStoreRequest;
use Sendportal\Base\Interfaces\CampaignTenantInterface;
use Sendportal\Base\Repositories\ProviderTenantRepository;
use Sendportal\Base\Repositories\SegmentTenantRepository;
use Sendportal\Base\Repositories\SubscriberTenantRepository;
use Sendportal\Base\Repositories\TemplateTenantRepository;

class CampaignsController extends Controller
{
    /** @var CampaignTenantInterface */
    protected $campaigns;

    /** @var TemplateTenantRepository */
    protected $templates;

    /** @var SegmentTenantRepository */
    protected $segments;

    /** @var ProviderTenantRepository */
    protected $providers;

    /** @var SubscriberTenantRepository */
    protected $subscribers;

    public function __construct(
        CampaignTenantInterface $campaigns,
        TemplateTenantRepository $templates,
        SegmentTenantRepository $segments,
        ProviderTenantRepository $providers,
        SubscriberTenantRepository $subscribers
    ) {
        $this->campaigns = $campaigns;
        $this->templates = $templates;
        $this->segments = $segments;
        $this->providers = $providers;
        $this->subscribers = $subscribers;
    }

    /**
     * @throws Exception
     */
    public function index(): ViewContract
    {
        $campaigns = $this->campaigns->paginate(currentTeamId(), 'created_atDesc', ['status']);
        $providerCount = $this->providers->count(currentTeamId());

        return view('sendportal::campaigns.index', compact('campaigns', 'providerCount'));
    }

    /**
     * @throws Exception
     */
    public function create(): ViewContract
    {
        $templates = [null => '- None -'] + $this->templates->pluck(currentTeamId());
        $providers = $this->providers->all(currentTeamId());

        return view('sendportal::campaigns.create', compact('templates', 'providers'));
    }

    /**
     * @throws Exception
     */
    public function store(CampaignStoreRequest $request): RedirectResponse
    {
        $campaign = $this->campaigns->store(currentTeamId(), $this->handleCheckboxes($request->validated()));

        return redirect()->route('sendportal.campaigns.preview', $campaign->id);
    }

    /**
     * @throws Exception
     */
    public function show(int $id): ViewContract
    {
        $campaign = $this->campaigns->find(currentTeamId(), $id);

        return view('sendportal::campaigns.show', compact('campaign'));
    }

    /**
     * @throws Exception
     */
    public function edit(int $id): ViewContract
    {
        $campaign = $this->campaigns->find(currentTeamId(), $id);
        $providers = $this->providers->all(currentTeamId());
        $templates = [null => '- None -'] + $this->templates->pluck(currentTeamId());

        return view('sendportal::campaigns.edit', compact('campaign', 'providers', 'templates'));
    }

    /**
     * @throws Exception
     */
    public function update(int $campaignId, CampaignStoreRequest $request): RedirectResponse
    {
        $campaign = $this->campaigns->update(currentTeamId(), $campaignId,
            $this->handleCheckboxes($request->validated()));

        return redirect()->route('sendportal.campaigns.preview', $campaign->id);
    }

    /**
     * @return RedirectResponse|ViewContract
     * @throws Exception
     */
    public function preview(int $id)
    {
        $campaign = $this->campaigns->find(currentTeamId(), $id);
        $subscriberCount = $this->subscribers->countActive(currentTeamId());

        if (!$campaign->draft) {
            return redirect()->route('sendportal.campaigns.status', $id);
        }

        $segments = $this->segments->all(currentTeamId(), 'name');

        return view('sendportal::campaigns.preview', compact('campaign', 'segments', 'subscriberCount'));
    }

    /**
     * @return RedirectResponse|ViewContract
     * @throws Exception
     */
    public function status(int $id)
    {
        $campaign = $this->campaigns->find(currentTeamId(), $id, ['status']);

        if ($campaign->sent) {
            return redirect()->route('sendportal.campaigns.reports.index', $id);
        }

        return view('sendportal::campaigns.status', compact('campaign'));
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
