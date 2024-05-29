<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Campaigns;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\CampaignDispatchRequest;
use Sendportal\Base\Models\CampaignStatus;
use Sendportal\Base\Repositories\Campaigns\CampaignTenantRepositoryInterface;

class CampaignDispatchController extends Controller
{
    /** @var CampaignTenantRepositoryInterface */
    protected $campaigns;

    public function __construct(
        CampaignTenantRepositoryInterface $campaigns
    ) {
        $this->campaigns = $campaigns;
    }

    /**
     * Dispatch the campaign.
     *
     * @throws Exception
     */
    public function send(CampaignDispatchRequest $request, int $id): RedirectResponse
    {
        $campaign = $this->campaigns->find(Sendportal::currentWorkspaceId(), $id, ['email_service', 'messages']);

        if ($campaign->status_id !== CampaignStatus::STATUS_DRAFT) {
            return redirect()->route('sendportal.campaigns.status', $id);
        }

        if (! $campaign->email_service_id) {
            return redirect()->route('sendportal.campaigns.edit', $id)
                ->withErrors(__('Please select an Email Service'));
        }

        $campaign->update([
            'send_to_all' => $request->get('recipients') === 'send_to_all',
        ]);

        $campaign->tags()->sync($request->get('tags'));

        $scheduledAt = $request->get('schedule') === 'scheduled' ? Carbon::parse($request->get('scheduled_at')) : now();

        $campaign->update([
            'scheduled_at' => $scheduledAt,
            'status_id' => CampaignStatus::STATUS_QUEUED,
            'save_as_draft' => $request->get('behaviour') === 'draft',
        ]);

        return redirect()->route('sendportal.campaigns.status', $id);
    }
}
