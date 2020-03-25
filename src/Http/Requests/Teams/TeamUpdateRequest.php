<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Requests\Teams;

use Illuminate\Foundation\Http\FormRequest;

class TeamUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'workspace_name' => ['required']
        ];
    }
}
