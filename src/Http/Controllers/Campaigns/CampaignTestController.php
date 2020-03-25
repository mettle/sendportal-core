<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Campaigns;

use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\CampaignTestRequest;
use Sendportal\Base\Services\Messages\DispatchTestMessage;
use Illuminate\Http\RedirectResponse;
use Exception;

class CampaignTestController extends Controller
{
    /** @var DispatchTestMessage */
    protected $dispatchTestMessage;

    public function __construct(DispatchTestMessage $dispatchTestMessage)
    {
        $this->dispatchTestMessage = $dispatchTestMessage;
    }

    /**
     * @throws Exception
     */
    public function handle(CampaignTestRequest $request, int $campaignId): RedirectResponse
    {
        $messageId = $this->dispatchTestMessage->handle(currentTeamId(), $campaignId, $request->get('recipient_email'));

        if (!$messageId) {
            return redirect()->route('campaigns.preview', $campaignId)
                ->withInput()
                ->with(['error', __('Failed to dispatch test email.')]);
        }

        return redirect()->route('campaigns.preview', $campaignId)
            ->withInput()
            ->with(['success' => __('The test email has been dispatched.')]);
    }
}
