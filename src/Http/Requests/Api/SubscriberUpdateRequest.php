<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SubscriberUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => ['nullable'],
            'last_name' => ['nullable'],
            'email' => ['required', 'email'],
            'segments' => ['nullable', 'array'],
        ];
    }
}
