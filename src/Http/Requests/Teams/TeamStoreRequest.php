<?php

namespace Sendportal\Base\Http\Requests\Teams;

use Illuminate\Foundation\Http\FormRequest;

class TeamStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required']
        ];
    }
}
