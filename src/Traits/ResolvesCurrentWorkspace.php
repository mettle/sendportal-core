<?php

namespace Sendportal\Base\Traits;

use Sendportal\Base\Models\User;
use Sendportal\Base\Models\Workspace;

trait ResolvesCurrentWorkspace
{
    /**
     * Resolve the current workspace.
     */
    public function currentWorkspace(): ?Workspace
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        return $user->currentWorkspace();
    }
}
