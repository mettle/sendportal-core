<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Teams;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Sendportal\Base\Models\Team;

class SwitchWorkspaceController
{
    public function switch(Request $request, Team $team): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->onTeam($team), 404);

        $user->switchToTeam($team);

        return redirect()->route('sendportal.campaigns.index');
    }
}
