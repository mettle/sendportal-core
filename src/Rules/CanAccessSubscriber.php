<?php

declare(strict_types=1);

namespace Sendportal\Base\Rules;

use Illuminate\Contracts\Validation\Rule;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Subscriber;

class CanAccessSubscriber implements Rule
{
    public function passes($attribute, $value): bool
    {
        $subscriber = Subscriber::find($value);

        if (! $subscriber) {
            return false;
        }

        return $subscriber->workspace_id == Sendportal::currentWorkspaceId();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The validation error message.';
    }
}
