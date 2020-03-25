<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Requests\Api;

use Sendportal\Base\Rules\CanAccessSegment;
use Illuminate\Foundation\Http\FormRequest;

class SubscriberSegmentDestroyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'segments' => ['array', 'required'],
            'segments.*' => ['integer', new CanAccessSegment(user())]
        ];
    }
}
