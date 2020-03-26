<?php

namespace Sendportal\Base\Http\Controllers\Campaigns;

use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Interfaces\CampaignTenantInterface;

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
     * @return Factory|RedirectResponse|View
     * @throws Exception
     */
    public function confirm($id)
    {
        $campaign = $this->campaigns->find(auth()->user()->currentTeam()->id, $id);

        if (! $campaign->draft) {
            return redirect()->route('sendportal.campaigns.index')
                ->withErrors(__('Unable to delete a campaign that is not in draft status'));
        }

        return view('sendportal::campaigns.delete', compact('campaign'));
    }

    /**
     * Delete a campaign from the database
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws Exception
     */
    public function destroy(Request $request)
    {
        $campaign = $this->campaigns->find(auth()->user()->currentTeam()->id, $request->get('id'));

        if (! $campaign->draft) {
            return redirect()->route('sendportal.campaigns.index')
                ->withErrors(__('Unable to delete a campaign that is not in draft status'));
        }

        $this->campaigns->destroy(auth()->user()->currentTeam()->id, $request->get('id'));

        return redirect()->route('sendportal.campaigns.index')
            ->with('success', __('The Campaign has been successfully deleted'));
    }
}
