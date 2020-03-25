<?php

namespace Sendportal\Base\Http\Middleware;

use Closure;

class OwnsCurrentTeam
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request $request
     * @param Closure $next
     * @return string
     */
    public function handle($request, Closure $next)
    {
        if (! user()->ownsCurrentTeam()) {
            abort(404);
        }

        return $next($request);
    }
}
