<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Requests\Api;

use Sendportal\Base\Http\Requests\CampaignStoreRequest as BaseCampaignStoreRequest;

class CampaignStoreRequest extends BaseCampaignStoreRequest
{
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
        ];

        return array_merge($this->getRules(), $rules);
    }
}
