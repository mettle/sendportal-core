<?php

namespace Sendportal\Base\Http\Controllers\Campaigns;

use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Interfaces\CampaignTenantInterface;
use Sendportal\Base\Models\CampaignStatus;
use Illuminate\Http\RedirectResponse;

class CampaignDuplicateController extends Controller
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
    public function __construct(CampaignTenantInterface $campaigns)
    {
        $this->campaigns = $campaigns;
    }

    /**
     * Duplicate a campaign
     *
     * @param $campaignId
     * @return RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */
    public function duplicate($campaignId)
    {
        $campaign = $this->campaigns->find(auth()->user()->currentWorkspace()->id, $campaignId);

        return redirect()->route('campaigns.create')->withInput([
                'name' => $campaign->name . ' - Duplicate',
                'status_id' => CampaignStatus::STATUS_DRAFT,
                'template_id' => $campaign->template_id,
                'provider_id' => $campaign->provider_id,
                'subject' => $campaign->subject,
                'content' => $campaign->content,
                'from_name' => $campaign->from_name,
                'from_email' => $campaign->from_email,
            ]);
    }
}
