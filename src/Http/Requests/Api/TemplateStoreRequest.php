<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class TemplateStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['string'],
            'content' => ['string']
        ];
    }
}
