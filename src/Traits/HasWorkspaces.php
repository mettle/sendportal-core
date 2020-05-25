<?php

declare(strict_types=1);

namespace Sendportal\Base\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;
use Sendportal\Base\Models\Invitation;
use Sendportal\Base\Models\Workspace;

trait HasWorkspaces
{
    /** @var Workspace */
    protected $activeWorkspace;

    /**
     * Get all of the workspaces that the user belongs to.
     */
    public function workspaces(): BelongsToMany
    {
        return $this->belongsToMany(Workspace::class, 'workspace_users')
            ->orderBy('name', 'asc')
            ->withPivot(['role'])
            ->withTimestamps();
    }

    /**
     * Get all of the pending invitations for the user.
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    /**
     * Determine if the user is a member of any workspaces.
     */
    public function hasWorkspaces(): bool
    {
        return $this->workspaces->count() > 0;
    }

    /**
     * Determine if the user is on the given workspace.
     */
    public function onWorkspace(Workspace $workspace): bool
    {
        return $this->workspaces->contains($workspace);
    }

    /**
     * Determine if the given workspace is owned by the user.
     */
    public function ownsWorkspace(Workspace $workspace): bool
    {
        return $this->id && $workspace->owner_id && (int)$this->id === (int)$workspace->owner_id;
    }

    /**
     * Get the workspace that user is currently viewing.
     */
    public function currentWorkspace(): ?Workspace
    {
        if ($this->activeWorkspace !== null) {
            return $this->activeWorkspace;
        }

        if ($this->current_workspace_id) {
            $this->switchToWorkspace(Workspace::find($this->current_workspace_id));
            return $this->activeWorkspace;
        }

        if ($this->activeWorkspace === null && $this->hasWorkspaces()) {
            $this->switchToWorkspace($this->workspaces()->first());
            return $this->activeWorkspace;
        }

        return null;
    }

    /**
     * Determine if the user owns the current workspace.
     */
    public function ownsCurrentWorkspace(): bool
    {
        return $this->currentWorkspace() && (int)$this->currentWorkspace()->owner_id === (int)$this->id;
    }

    /**
     * Switch the current workspace for the user.
     */
    public function switchToWorkspace(Workspace $workspace): void
    {
        if (!$this->onWorkspace($workspace)) {
            throw new InvalidArgumentException('User does not belong to this workspace');
        }

        $this->activeWorkspace = $workspace;

        $this->current_workspace_id = $workspace->id;
        $this->save();
    }
}
