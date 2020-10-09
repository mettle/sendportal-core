<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Sendportal\Base\Rules\CanAccessSubscriber;

class SegmentSubscriberUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'subscribers' => ['array', 'required'],
            'subscribers.*' => ['integer', new CanAccessSubscriber()]
        ];
    }
}
