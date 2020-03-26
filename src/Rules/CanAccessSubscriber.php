<?php

declare(strict_types=1);

namespace Sendportal\Base\Rules;

use Sendportal\Base\Models\Subscriber;
use Sendportal\Base\Models\Workspace;
use Sendportal\Base\Models\User;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Collection;

class CanAccessSubscriber implements Rule
{
    /** @var User */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function passes($attribute, $value): bool
    {
        $subscriber = Subscriber::find($value);

        if (!$subscriber) {
            return false;
        }

        /** @var Collection $userWorkspaces */
        $userWorkspaces = $this->user->workspaces;

        /** @var Workspace $subscriberWorkspace */
        $subscriberWorkspace = $subscriber->workspace;

        return $userWorkspaces->contains($subscriberWorkspace);
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
