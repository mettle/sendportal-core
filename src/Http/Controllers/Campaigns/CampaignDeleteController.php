<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Campaigns;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Repositories\Campaigns\CampaignTenantRepository;

class CampaignDeleteController extends Controller
{
    /** @var CampaignTenantRepository */
    protected $campaigns;

    public function __construct(CampaignTenantRepository $campaigns)
    {
        $this->campaigns = $campaigns;
    }

    /**
     * Show a confirmation view prior to deletion.
     *
     * @return RedirectResponse|View
     * @throws Exception
     */
    public function confirm(int $id)
    {
        $campaign = $this->campaigns->find(auth()->user()->currentWorkspace()->id, $id);

        if (!$campaign->draft) {
            return redirect()->route('sendportal.campaigns.index')
                ->withErrors(__('Unable to delete a campaign that is not in draft status'));
        }

        return view('sendportal::campaigns.delete', compact('campaign'));
    }

    /**
     * Delete a campaign from the database.
     *
     * @throws Exception
     */
    public function destroy(Request $request): RedirectResponse
    {
        $campaign = $this->campaigns->find(auth()->user()->currentWorkspace()->id, $request->get('id'));

        if (!$campaign->draft) {
            return redirect()->route('sendportal.campaigns.index')
                ->withErrors(__('Unable to delete a campaign that is not in draft status'));
        }

        $this->campaigns->destroy(auth()->user()->currentWorkspace()->id, $request->get('id'));

        return redirect()->route('sendportal.campaigns.index')
            ->with('success', __('The Campaign has been successfully deleted'));
    }
}
