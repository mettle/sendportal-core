<?php

namespace Sendportal\Base\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Sendportal\Base\Models\EmailServiceType;

class EmailServiceStoreRequest extends FormRequest
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
        return array_merge([
            'name' => ['required'],
            'type_id' => ['required', 'integer'],
        ], EmailServiceType::resolveValidationRules($this->input('type_id')));
    }

    /**
     * Get the validation messages
     *
     * @return array
     */
    public function messages()
    {
        return EmailServiceType::resolveValidationMessages($this->input('type_id'));
    }
}
