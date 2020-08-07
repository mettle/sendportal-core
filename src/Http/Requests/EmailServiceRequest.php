<?php

namespace Sendportal\Base\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Sendportal\Base\Models\EmailServiceType;

class EmailServiceRequest extends FormRequest
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
        $rules = [
            'name' => ['required']
        ];

        if(!$this->route('id')) {
            $rules['type_id'] = ['required', 'integer'];
        }

        return array_merge($rules, self::resolveValidationRules($this->input('type_id')));
    }

    /**
     * Get the validation messages
     *
     * @return array
     */
    public function messages()
    {
        switch ((int) $this->input('type_id')) {
            case EmailServiceType::SES:
                return [
                    'settings.key.required' => __('The AWS Email Service requires you to enter a key'),
                    'settings.secret.required' => __('The AWS Email Service requires you to enter a secret'),
                    'settings.region.required' => __('The AWS Email Service requires you to enter a region'),
                    'settings.configuration_set_name.required' => __('The AWS Email Service requires you to enter a configuration set name'),
                ];

            case EmailServiceType::SENDGRID:
                return [
                    'settings.key.required' => __('The Sendgrid Email Service requires you to enter a key'),
                ];

            case EmailServiceType::POSTMARK:
                return [
                    'settings.key.required' => __('The Postmark Email Service requires you to enter a key'),
                ];

            case EmailServiceType::MAILGUN:
                return [
                    'settings.key.required' =>  __('The Mailgun Email Service requires you to enter a key'),
                    'settings.domain.required' => __('The Mailgun Email Service requires you to enter a domain'),
                    'settings.zone.required' => __('The Mailgun Email Service requires you to enter a zone'),
                ];

            case EmailServiceType::MAILJET:
                return [
                    'settings.key.required' =>  __('The Mailjet Email Service requires you to enter a key'),
                    'settings.secret.required' =>  __('The Mailgun Email Service requires you to enter a secret'),
                    'settings.zone.required' =>  __('The Mailgun Email Service requires you to enter a zone'),
                ];

            default:
                return [];
        }
    }

    public static function resolveValidationRules($typeId): array
    {
        switch ($typeId) {
            case EmailServiceType::SES:
                return [
                    'settings.key' => 'required',
                    'settings.secret' => 'required',
                    'settings.region' => 'required',
                    'settings.configuration_set_name' => 'required'
                ];

            case EmailServiceType::SENDGRID:
            case EmailServiceType::POSTMARK:
                return [
                    'settings.key' => 'required',
                ];

            case EmailServiceType::MAILGUN:
                return [
                    'settings.key' => 'required',
                    'settings.domain' => 'required',
                    'settings.zone' => ['required', 'in:US,EU']
                ];

            case EmailServiceType::MAILJET:
                return [
                    'settings.key' => 'required',
                    'settings.secret' => 'required',
                    'settings.zone' => ['required', 'in:Default,US'],
                ];

            default:
                return [];
        }
    }
}
