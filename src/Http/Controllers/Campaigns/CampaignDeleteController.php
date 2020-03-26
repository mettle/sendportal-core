<?php

namespace Sendportal\Base\Http\Controllers\Campaigns;

use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Interfaces\CampaignTenantInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CampaignDeleteController extends Controller
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
     * Show a confirmation view prior to deletion
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|RedirectResponse|\Illuminate\View\View
     * @throws \Exception
     */
    public function confirm($id)
    {
        $campaign = $this->campaigns->find(currentTeamId(), $id);

        if (! $campaign->draft) {
            return redirect()->route('campaigns.index')
                ->withErrors(__('Unable to delete a campaign that is not in draft status'));
        }

        return view('sendportal::campaigns.delete', compact('campaign'));
    }

    /**
     * Delete a campaign from the database
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws \Exception
     */
    public function destroy(Request $request)
    {
        $campaign = $this->campaigns->find(currentTeamId(), $request->get('id'));

        if (! $campaign->draft) {
            return redirect()->route('campaigns.index')
                ->withErrors(__('Unable to delete a campaign that is not in draft status'));
        }

        $this->campaigns->destroy(currentTeamId(), $request->get('id'));

        return redirect()->route('campaigns.index')
            ->with('success', __('The Campaign has been successfully deleted'));
    }
}
