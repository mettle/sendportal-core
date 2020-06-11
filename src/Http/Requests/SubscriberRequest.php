<?php

namespace Sendportal\Base\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $rule = Rule::unique('subscribers', 'email')
            ->ignore($this->subscriber, 'id')
            ->where(function ($query) {
                $query->where('workspace_id', auth()->user()->currentWorkspace()->id);
            });

        return $rule;
    }
}
