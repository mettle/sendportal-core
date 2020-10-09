<?php

namespace Sendportal\Base\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Sendportal\Base\Repositories\SegmentTenantRepository;

class CampaignDispatchRequest extends FormRequest
{
    public function rules(): array
    {
        /** @var SegmentTenantRepository $segments */
        $segments = app(SegmentTenantRepository::class)->pluck(
            auth()->user()->currentWorkspace()->id,
            'id'
        );

        return [
            'segments' => [
                'required_unless:recipients,send_to_all',
                'array',
                Rule::in($segments),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'segments.required_unless' => __('At least one segment must be selected'),
            'segments.in' => __('One or more of the segments is invalid.'),
        ];
    }
}
