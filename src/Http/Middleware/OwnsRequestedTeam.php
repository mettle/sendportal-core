<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OwnsRequestedTeam
{
    public function handle(Request $request, Closure $next)
    {
        if (!$user = user()) {
            abort(404);
        }

        // NOTE(david): we need to come to a decision about the naming of teams, as we currently call it
        // both "workspace" and "team" in various places. Workspace seems to be the term used in the UI
        // but Team is the most common in the code.
        $team = $request->workspace ?? $request->team;

        if (!$team) {
            abort(404);
        }

        if (!$user->ownsTeam($team)) {
            abort(404);
        }

        return $next($request);
    }
}
