<?php

namespace Sendportal\Base\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property-read string $subscriber
 */
class SubscriberRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('subscribers', 'email')
                    ->ignore($this->subscriber, 'id')
                    ->where(static function (Builder $query) {
                        $query->where('workspace_id', auth()->user()->currentWorkspace()->id);
                    })
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
}
