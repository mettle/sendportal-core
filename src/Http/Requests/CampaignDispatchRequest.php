<?php

namespace Sendportal\Base\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Repositories\TagTenantRepository;

class CampaignDispatchRequest extends FormRequest
{
    public function rules(): array
    {
        /** @var TagTenantRepository $tags */
        $tags = app(TagTenantRepository::class)->pluck(
            Sendportal::currentWorkspaceId(),
            'id'
        );

        return [
            'tags' => [
                'required_unless:recipients,send_to_all',
                'array',
                Rule::in($tags),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'tags.required_unless' => __('At least one tag must be selected'),
            'tags.in' => __('One or more of the tags is invalid.'),
        ];
    }
}
