<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Requests\Api;

use Illuminate\Support\Facades\Validator;
use Sendportal\Base\Http\Requests\CampaignStoreRequest as BaseCampaignStoreRequest;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\CampaignStatus;
use Sendportal\Base\Repositories\Campaigns\CampaignTenantRepositoryInterface;

class CampaignStoreRequest extends BaseCampaignStoreRequest
{
    /**
     * @var CampaignTenantRepositoryInterface
     */
    protected $campaigns;

    public function __construct(CampaignTenantRepositoryInterface $campaigns)
    {
        parent::__construct();

        $this->campaigns = $campaigns;

        Validator::extendImplicit('valid_status', function ($attribute, $value, $parameters, $validator) {
            return $this->campaign
                ? $this->getCampaign()->status_id === CampaignStatus::STATUS_DRAFT
                : true;
        });
    }

    /**
     * @throws \Exception
     */
    public function getCampaign(): Campaign
    {
        return $this->campaign = $this->campaigns->find($this->workspaceId, $this->campaign);
    }

    public function rules(): array
    {
        $rules = [
            'send_to_all' => [
                'required',
                'boolean',
            ],
            'segments' => [
                'required_unless:send_to_all,1',
                'array',
            ],
            'segments.*' => [
                'integer',
                'exists:segments,id'
            ],
            'scheduled_at' => [
                'required',
                'date',
            ],
            'save_as_draft' => [
                'nullable',
                'boolean',
            ],
            'status_id' => 'valid_status',
        ];

        return array_merge($this->getRules(), $rules);
    }

    public function messages(): array
    {
        return [
            'valid_status' => __('A campaign cannot be updated if its status is not draft'),
        ];
    }
}
