<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Teams;

use Sendportal\Base\Models\Team;
use Sendportal\Base\Models\User;

class RemoveUserFromTeam
{
    public function handle(User $user, Team $team): void
    {
        $team->users()->detach($user);

        $user->current_team_id = null;
        $user->save();
    }
}
