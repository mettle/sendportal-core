<?php

namespace Sendportal\Base\Http\Controllers\Campaigns;

use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\CampaignDispatchRequest;
use Sendportal\Base\Interfaces\CampaignTenantInterface;
use Sendportal\Base\Models\CampaignStatus;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;

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
     */
    public function __construct(
        CampaignTenantInterface $campaigns
    ) {
        $this->campaigns = $campaigns;
    }

    /**
     * Dispatch the campaign
     *
     * @param CampaignDispatchRequest $request
     * @param $id
     * @return RedirectResponse
     * @throws \Exception
     */
    public function send(CampaignDispatchRequest $request, $id)
    {
        $campaign = $this->campaigns->find(currentTeamId(), $id);

        if ($campaign->status_id > CampaignStatus::STATUS_DRAFT) {
            return redirect()->route('campaigns.status', $id);
        }

        if (! $campaign->provider_id) {
            return redirect()->route('campaigns.edit', $id)
                ->withErrors(__('Please select a Provider'));
        }

        $scheduledAt = $request->get('schedule') == 'scheduled' ? Carbon::parse($request->get('scheduled_at')) : now();

        $campaign->update([
            'send_to_all' => $request->get('recipients') == 'send_to_all',
            'scheduled_at' => $scheduledAt,
            'status_id' => CampaignStatus::STATUS_QUEUED,
            'save_as_draft' => $request->get('behaviour') == 'draft',
        ]);

        $campaign->segments()->sync($request->get('segments'));

        return redirect()->route('campaigns.status', $id);
    }
}
