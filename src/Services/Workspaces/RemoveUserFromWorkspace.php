<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Workspaces;

use Sendportal\Base\Models\User;
use Sendportal\Base\Models\Workspace;

class RemoveUserFromWorkspace
{
    public function handle(User $user, Workspace $workspace): void
    {
        $workspace->users()->detach($user);

        $user->current_workspace_id = null;
        $user->save();
    }
}
