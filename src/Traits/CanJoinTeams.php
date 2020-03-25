<?php

namespace Sendportal\Base\Traits;

use Sendportal\Base\Models\Invitation;
use Sendportal\Base\Models\Team;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

trait CanJoinTeams
{
    /** @var Team */
    protected $activeTeam;

    /**
     * Get all of the teams that the user belongs to.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_users')
            ->orderBy('name', 'asc')
            ->withPivot(['role'])
            ->withTimestamps();
    }

    /**
     * Get all of the teams that the user owns.
     */
    public function ownedTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    /**
     * Get all of the pending invitations for the user.
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    /**
     * Determine if the user is a member of any teams.
     */
    public function hasTeams(): bool
    {
        return $this->teams->count() > 0;
    }

    /**
     * Determine if the user is on the given team.
     */
    public function onTeam(Team $team): bool
    {
        return $this->teams->contains($team);
    }

    /**
     * Determine if the given team is owned by the user.
     */
    public function ownsTeam(Team $team): bool
    {
        return $this->id && $team->owner_id && (int)$this->id === (int)$team->owner_id;
    }

    /**
     * Get the user's role on a given team.
     */
    public function roleOn(int $teamId): ?string
    {
        /** @var Team $team */
        $team = $this->teams()->find($teamId);

        return $team->pivot->role;
    }

    /**
     * Get the user's role on the team currently being viewed.
     */
    public function roleOnCurrentTeam(): ?string
    {
        return $this->roleOn($this->activeTeam->id);
    }

    /**
     * Accessor for the currentTeam method.
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getCurrentTeamAttribute()
    {
        return $this->currentTeam();
    }

    /**
     * Get the team that user is currently viewing.
     */
    public function currentTeam(): ?Team
    {
        if ($this->activeTeam !== null) {
            return $this->activeTeam;
        }

        if ($this->current_team_id) {
            $team = Team::find($this->current_team_id);
            $this->switchToTeam($team);
            $this->activeTeam = $team;

            return $this->currentTeam();
        }

        if ($this->activeTeam === null && $this->hasTeams()) {

            $this->switchToTeam($this->teams()->first());

            return $this->currentTeam();
        }

        return null;
    }

    /**
     * Determine if the current team is on a trial.
     */
    public function currentTeamOnTrial(): bool
    {
        return $this->currentTeam() && $this->currentTeam()->has_active_trial;
    }

    /**
     * Determine if the user owns the current team.
     */
    public function ownsCurrentTeam(): bool
    {
        return $this->currentTeam() && (int)$this->currentTeam()->owner_id === (int)$this->id;
    }

    /**
     * Switch the current team for the user.
     */
    public function switchToTeam(Team $team): void
    {
        if (! $this->onTeam($team)) {
            throw new InvalidArgumentException('User does not belong to this team');
        }

        $this->activeTeam = $team;

        $this->current_team_id = $team->id;
        $this->save();
    }
}
