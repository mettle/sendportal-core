<?php

namespace Sendportal\Base\Http\Requests\Workspaces;

use Illuminate\Foundation\Http\FormRequest;

class WorkspaceStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required']
        ];
    }
}
