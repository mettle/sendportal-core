<?php

namespace Sendportal\Base\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CampaignDeleteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'tags' => [
                'required_if:recipients,send_to_tags',
                'array',
            ],
            'segment_tags' => [
                'required_if:recipients,send_to_segments',
                'array',
            ],
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
//            'tags.if' => __('At least one tag must be selected')
        ];
    }
}
