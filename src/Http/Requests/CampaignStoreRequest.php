<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CampaignStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'max:255'
            ],
            'subject' => [
                'required',
                'max:255'
            ],
            'from_name' => [
                'required',
                'max:255',
            ],
            'from_email' => [
                'required',
                'max:255',
                'email',
            ],
            'provider_id' => [
                'required',
                'integer',
                'exists:providers,id',
            ],
            'template_id' => [
                'nullable',
                'exists:templates,id',
            ],
            'content' => [
                'nullable',
            ],

            'is_open_tracking' => [
                'boolean',
                'nullable'
            ],

            'is_click_tracking' => [
                'boolean',
                'nullable'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'provider_id.required' => __('Please select a provider.'),
        ];
    }
}
