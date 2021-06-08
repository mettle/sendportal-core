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
                'required_unless:recipients,send_to_all',
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
            'tags.required_unless' => __('At least one tag must be selected')
        ];
    }
}
