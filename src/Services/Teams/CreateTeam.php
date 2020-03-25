<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Teams;

use Sendportal\Base\Models\Team;
use Sendportal\Base\Models\User;
use Sendportal\Base\Repositories\TeamsRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class CreateTeam
{
    /** @var TeamsRepository */
    protected $teams;

    /** @var AddTeamMember */
    protected $addTeamMember;

    public function __construct(TeamsRepository $teamsRepo, AddTeamMember $addTeamMember)
    {
        $this->teams = $teamsRepo;
        $this->addTeamMember = $addTeamMember;
    }

    /**
     * Create a new team.
     *
     * @param User $user
     * @param string $teamName
     * @param string|null $role
     *
     * @return Team
     * @throws Exception
     */
    public function handle(User $user, string $teamName, ?string $role = null): Team
    {
        return DB::transaction(function () use ($user, $teamName, $role) {
            /** @var Team $team */
            $team = $this->teams->store([
                'name' => $teamName,
                'owner_id' => $user->id,
            ]);

            $this->addTeamMember->handle($team, $user, $role);

            return $team;
        });
    }
}
