<?php

namespace Sendportal\Base\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscriberRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'max:255',
                $this->getUniqueEmailRule(),
            ],
            'first_name' => [
                'max:255',
            ],
            'last_name' => [
                'max:255',
            ],
            'segments' => [
                'nullable',
                'array',
            ],
        ];
    }

    protected function getUniqueEmailRule(): string
    {
        $rule = sprintf(
            'unique:subscribers,email,%d,id,workspace_id,%d',
            $this->subscriber ?? 0,
            auth()->user()->currentWorkspace()->id
        );

        return $rule;
    }
}
