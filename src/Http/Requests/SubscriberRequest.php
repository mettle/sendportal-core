<?php

namespace Sendportal\Base\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscriberRequest extends FormRequest
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
        $uniqueRule = sprintf(
            'unique:subscribers,email,%d,id,workspace_id,%d',
            $this->subscriber ?? 0,
            auth()->user()->currentWorkspace()->id
        );

        return [
            'email' => [
                'required',
                'email',
                'max:255',
                $uniqueRule,
            ],
            'first_name' => [
                'max:255',
            ],
            'last_name' => [
                'max:255',
            ],
            'segments' => [
                'nullable',
                'array',
            ],
        ];
    }
}
