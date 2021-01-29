<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Sendportal\Base\Rules\CanAccessTag;

class SubscriberTagUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'tags' => ['array', 'required'],
            'tags.*' => ['integer', new CanAccessTag()]
        ];
    }
}
