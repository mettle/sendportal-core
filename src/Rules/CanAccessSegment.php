<?php

declare(strict_types=1);

namespace Sendportal\Base\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Collection;
use Sendportal\Base\Models\Segment;
use Sendportal\Base\Models\User;
use Sendportal\Base\Models\Workspace;

class CanAccessSegment implements Rule
{
    /** @var User */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function passes($attribute, $value): bool
    {
        $segment = Segment::find($value);

        if (!$segment) {
            return false;
        }

        /** @var Collection $userWorkspaces */
        $userWorkspaces = $this->user->workspaces;

        /** @var Workspace $segmentWorkspace */
        $segmentWorkspace = $segment->workspace;

        return $userWorkspaces->contains($segmentWorkspace);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Segment ID :input does not exist.';
    }
}
