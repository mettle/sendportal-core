<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Workspaces;

use Sendportal\Base\Models\User;
use Sendportal\Base\Models\Workspace;

class AddWorkspaceMember
{
    /**
     * Attach a user to a workspace.
     *
     * @param Workspace $workspace
     * @param User $user
     * @param string|null $role
     */
    public function handle(Workspace $workspace, User $user, ?string $role = null): void
    {
        if (!$user->onWorkspace($workspace)) {
            $workspace->users()->attach($user, ['role' => $role ?: Workspace::ROLE_MEMBER]);
        }
    }
}
