<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Sendportal\Base\Facades\Sendportal;

class TagUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'max:255',
                Rule::unique('sendportal_segments')
                    ->where('workspace_id', Sendportal::currentWorkspaceId())
                    ->ignore($this->segment),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => __('The segment name must be unique.'),
        ];
    }
}
