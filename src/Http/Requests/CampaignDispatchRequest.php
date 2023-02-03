<?php

namespace Sendportal\Base\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Segment;
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

        $segments = Segment::where('owner', request()->user->sc_user_id ?? 0)->pluck('id');


        return [
            'tags' => [
                'required_if:recipients,send_to_tags',
                'array',
                Rule::in($tags),
            ],
            'segment_tags' => [
                'required_if:recipients,send_to_segments',
                'array',
                Rule::in($segments),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'tags.required_if' => __('At least one tag must be selected'),
            'tag_segments.required_if' => __('At least one segment must be selected'),
            'tags.in' => __('One or more of the tags is invalid.'),
            'segment_tags.in' => __('One or more of the tags segments invalid.'),
        ];
    }
}
