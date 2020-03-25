<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Teams;

use Sendportal\Base\Models\Team;
use Sendportal\Base\Models\User;

class AddTeamMember
{
    /**
     * Attach a user to a team.
     *
     * @param Team $team
     * @param User $user
     * @param string|null $role
     */
    public function handle(Team $team, User $user, ?string $role = null): void
    {
        if (!$user->onTeam($team)) {
            $team->users()->attach($user, ['role' => $role ?: Team::ROLE_MEMBER]);
        }
    }
}
