<?php

namespace Sendportal\Base\Http\Middleware;

use Sendportal\Base\Models\Team;
use Closure;

class VerifyUserOnTeam
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $team = Team::find($request->route()->parameter('teamId'));

        if (! $team) {
            abort(403, 'Unauthorized');
        }

        abort_unless(user()->onTeam($team), 403, 'Unauthorized');

        config()->set('current_team_id', $team->id);

        return $next($request);
    }
}
