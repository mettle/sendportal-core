<?php

declare(strict_types=1);

namespace Sendportal\Base\Repositories;

use Sendportal\Base\Models\Team;
use Sendportal\Base\Models\User;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TeamsRepository extends BaseEloquentRepository
{
    /** @var string */
    protected $modelName = Team::class;

    /**
     * Get a paginated list of all the teams a user is a part of.
     *
     * @throws Exception
     */
    public function teamsForUser(User $user): LengthAwarePaginator
    {
        return $this->getQueryBuilder()
            ->select('teams.*')
            ->leftJoin('team_users', 'team_users.team_id', '=', 'teams.id')
            ->where('team_users.user_id', $user->id)
            ->paginate(25);
    }
}
