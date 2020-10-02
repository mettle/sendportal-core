<?php

declare(strict_types=1);

namespace Sendportal\Base\Rules;

use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Subscriber;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Collection;

class CanAccessSubscriber implements Rule
{

    public function passes($attribute, $value): bool
    {
        $subscriber = Subscriber::find($value);

        if (!$subscriber) {
            return false;
        }

        return $subscriber->workspace_id === Sendportal::currentWorkspaceId();
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
