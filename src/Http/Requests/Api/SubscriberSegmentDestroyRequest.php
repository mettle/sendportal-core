<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Sendportal\Base\Rules\CanAccessSegment;

class SubscriberSegmentDestroyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'segments' => ['array', 'required'],
            'segments.*' => ['integer', new CanAccessSegment($this->user())]
        ];
    }
}
