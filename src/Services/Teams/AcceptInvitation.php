<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Teams;

use Sendportal\Base\Models\Invitation;
use Sendportal\Base\Models\Team;
use Sendportal\Base\Models\User;
use Exception;
use RuntimeException;

class AcceptInvitation
{
    /** @var AddTeamMember */
    protected $addTeamMember;

    public function __construct(AddTeamMember $addTeamMember)
    {
        $this->addTeamMember = $addTeamMember;
    }

    /**
     * Accept user invitation.
     *
     * @param User $user
     * @param Invitation $invitation
     *
     * @return bool
     * @throws Exception
     */
    public function handle(User $user, Invitation $invitation): bool
    {
        $team = $this->resolveTeam($invitation->team_id);

        if (!$team) {
            throw new RuntimeException("Invalid team ID encountered: {$invitation->team_id}");
        }

        $this->addTeamMember->handle($team, $user, Team::ROLE_MEMBER);

        $invitation->delete();

        return true;
    }

    protected function resolveTeam(int $teamId): ?Team
    {
        return Team::where('id', $teamId)->first();
    }
}
