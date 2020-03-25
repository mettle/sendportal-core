<?php

namespace Sendportal\Base\Http\Requests;

use Sendportal\Base\Models\ProviderType;
use Illuminate\Foundation\Http\FormRequest;

class ProviderStoreRequest extends FormRequest
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
            'name' => ['required'],
            'type_id' => ['required', 'integer'],

            'settings.key' => ['required'],
            'settings.secret' => ['required_if:type_id,' . ProviderType::SES],
            'settings.region' => ['required_if:type_id,' . ProviderType::SES],
            'settings.configuration_set_name' => ['required_if:type_id,' . ProviderType::SES],

            'settings.domain' => ['required_if:type_id,' . ProviderType::MAILGUN],
            'settings.zone' => ['required_if:type_id,' . ProviderType::MAILGUN, 'in:US,EU'],
        ];
    }

    /**
     * Get the validation messages
     *
     * @return array
     */
    public function messages()
    {
        return [
            'settings.secret.required_if' => __('The AWS Provider requires you to enter a secret'),
            'settings.region.required_if' => __('The AWS Provider requires you to enter a region'),
            'settings.configuration_set_name.required_if' => __('The AWS Provider requires you to enter a configuration set name'),
            'settings.domain.required_if' => __('The Mailgun provider requires you to enter a domain'),
            'settings.zone.required_if' => __('The Mailgun provider requires you to enter a domain'),
        ];
    }
}
