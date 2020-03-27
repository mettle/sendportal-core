<?php

namespace Sendportal\Base\Http\Controllers\Campaigns;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\CampaignDispatchRequest;
use Sendportal\Base\Interfaces\CampaignTenantInterface;
use Sendportal\Base\Interfaces\QuotaServiceInterface;
use Sendportal\Base\Models\CampaignStatus;

class CampaignDispatchController extends Controller
{
    /**
     * @var CampaignTenantInterface
     */
    protected $campaigns;

    /**
     * CampaignsController constructor
     *
     * @param CampaignTenantInterface $campaigns
     * @param QuotaServiceInterface $quotaService
     */
    public function __construct(
        CampaignTenantInterface $campaigns,
        QuotaServiceInterface $quotaService
    ) {
        $this->campaigns = $campaigns;
        $this->quotaService = $quotaService;
    }

    /**
     * Dispatch the campaign
     *
     * @param CampaignDispatchRequest $request
     * @param $id
     * @return RedirectResponse
     * @throws Exception
     */
    public function send(CampaignDispatchRequest $request, $id)
    {
        $campaign = $this->campaigns->find(auth()->user()->currentWorkspace()->id, $id, ['provider']);

        if ($campaign->status_id > CampaignStatus::STATUS_DRAFT) {
            return redirect()->route('sendportal.campaigns.status', $id);
        }

        if (! $campaign->provider_id) {
            return redirect()->route('sendportal.campaigns.edit', $id)
                ->withErrors(__('Please select a Provider'));
        }

        if (! $this->quotaService->campaignCanBeSent($campaign)) {

            return redirect()->route('sendportal.campaigns.edit', $id)
                ->withErrors(__('The number of subscribers for this campaign exceeds your SES quota'));
        }

        $scheduledAt = $request->get('schedule') == 'scheduled' ? Carbon::parse($request->get('scheduled_at')) : now();

        $campaign->update([
            'send_to_all' => $request->get('recipients') == 'send_to_all',
            'scheduled_at' => $scheduledAt,
            'status_id' => CampaignStatus::STATUS_QUEUED,
            'save_as_draft' => $request->get('behaviour') == 'draft',
        ]);

        $campaign->segments()->sync($request->get('segments'));

        return redirect()->route('sendportal.campaigns.status', $id);
    }
}
