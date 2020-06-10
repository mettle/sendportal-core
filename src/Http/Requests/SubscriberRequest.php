<?php

namespace Sendportal\Base\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        return [
            'email'      => [
                'required',
                'email',
                'max:255',
                Rule::unique('subscribers', 'email')
                    ->ignore($this->subscriber, 'id')
                    ->where(function ($query) {
                        $query->where('workspace_id', auth()->user()->currentWorkspace()->id);
                    }),
            ],
            'first_name' => [
                'max:255',
            ],
            'last_name'  => [
                'max:255',
            ],
            'segments'   => [
                'nullable',
                'array',
            ],
        ];
    }
}
