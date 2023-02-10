<?php

namespace Sendportal\Base\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\Api\CampaignDispatchRequest;
use Sendportal\Base\Http\Resources\Campaign as CampaignResource;
use Sendportal\Base\Interfaces\QuotaServiceInterface;
use Sendportal\Base\Models\CampaignStatus;
use Sendportal\Base\Repositories\Campaigns\CampaignTenantRepositoryInterface;

class CampaignDispatchController extends Controller
{
    /**
     * @var CampaignTenantRepositoryInterface
     */
    protected $campaigns;

    /**
     * @var QuotaServiceInterface
     */
    protected $quotaService;

    public function __construct(
        CampaignTenantRepositoryInterface $campaigns,
        QuotaServiceInterface $quotaService
    ) {
        $this->campaigns = $campaigns;
        $this->quotaService = $quotaService;
    }

    /**
     * @throws \Exception
     */
    public function send(CampaignDispatchRequest $request, $campaignId)
    {

        $campaign = $request->getCampaign(['email_service', 'messages']);
        $workspaceId = Sendportal::currentWorkspaceId();

        // check if the Authenticated user has units to send the request
        $unitBalance = DB::table('user_units')->where('id', Auth::user()->id)->first()->unit_balance;
        $perUnitPrice = 5;
        if($unitBalance <= 0){
            return response()->json([
               'status' => false,
               'message' => 'You do not have units to send this request'
            ], 400);
        }else{
            $reciepient = $request->recipients;
            $no_of_reciepient = 0;
            $workspace = $request->user()->currentWorkspace();
            if($reciepient == 'send_to_all'){
                $allSubscriber = DB::table('sendportal_subscribers')->where('workspace_id', $workspaceId)->count();

                if($allSubscriber*$perUnitPrice > $unitBalance){
                    return response()->json([
                       'status' => false,
                       'message' => 'You do not have enough units to send this request'
                    ], 400);
                }
            }else{

                // get All tags
                $all_tags = $request->tags;
                $taggedSubscriber = DB::table("sendportal_tag_subscriber")->whereIn('tag_id', $all_tags)->count();
                if($taggedSubscriber*$perUnitPrice > $unitBalance){
                    return response()->json([
                       'status' => false,
                       'message' => 'You do not have enough units to send this request'
                    ], 400);
                }
            }
        }
        

        if ($this->quotaService->exceedsQuota($campaign->email_service, $campaign->unsent_count)) {
            return response([
                'message' => __('The number of subscribers for this campaign exceeds your SES quota')
            ], 422);
        }

        $campaign = $this->campaigns->update($workspaceId, $campaignId, [
            'status_id' => CampaignStatus::STATUS_QUEUED,
        ]);

        return new CampaignResource($campaign);
    }
}
