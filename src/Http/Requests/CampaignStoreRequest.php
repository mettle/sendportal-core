<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CampaignStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return $this->getRules();
    }

    /**
     * @return array
     */
    protected function getRules(): array
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
//            'from_email' => [
//                'required',
//                'max:255',
//                'email',
//            ],
            'email_service_id' => [
                'required',
                'integer',
                'exists:sendportal_email_services,id',
            ],
            'template_id' => [
                'nullable',
                'exists:sendportal_templates,id',
            ],
            'content' => [
                Rule::requiredIf($this->template_id === null),
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
            'email_service_id.required' => __('Please select an email service.'),
        ];
    }
}
