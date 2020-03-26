<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Teams;

use Illuminate\Http\Request;
use Sendportal\Base\Models\Team;
use Illuminate\Http\RedirectResponse;

class SwitchWorkspaceController
{
    public function switch(Request $request, Team $team): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->onTeam($team), 404);

        $user->switchToTeam($team);

        return redirect()->route('campaigns.index');
    }
}
