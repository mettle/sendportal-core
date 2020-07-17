<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Requests\Api;

use Illuminate\Validation\Rule;
use Sendportal\Base\Http\Requests\CampaignStoreRequest as BaseCampaignStoreRequest;

class CampaignStoreRequest extends BaseCampaignStoreRequest
{
    public function rules(): array
    {
        $rules = [
            'recipients' => [
                'required',
                Rule::in(['send_to_all', 'send_to_segments']),
            ],
            'segments' => [
                'required_unless:recipients,send_to_all',
                'array',
            ],
            'segments.*' => [
                'integer',
                'exists:segments,id'
            ],
            'scheduled_at' => [
                'nullable',
                'datetime',
            ],
            'save_as_draft' => [
                'nullable',
                'boolean',
            ],
        ];

        return array_merge($this->getRules(), $rules);
    }
}
