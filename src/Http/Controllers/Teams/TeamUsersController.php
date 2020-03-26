<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Teams;

use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Models\User;
use Sendportal\Base\Services\Teams\RemoveUserFromTeam;

class TeamUsersController extends Controller
{
    /** @var RemoveUserFromTeam */
    private $removeUserFromTeam;

    public function __construct(RemoveUserFromTeam $removeUserFromTeam)
    {
        $this->removeUserFromTeam = $removeUserFromTeam;
    }

    public function index(Request $request): ViewContract
    {
        return view('sendportal::settings.users.index', [
            'users' => $request->user()->currentTeam->users,
            'invitations' => $request->user()->currentTeam->invitations,
        ]);
    }

    /**
     * Remove a user from the current team.
     *
     * @param int $userId
     *
     * @return RedirectResponse
     */
    public function destroy(Request $request, int $userId): RedirectResponse
    {
        /* @var $requestUser \Sendportal\Base\Models\User */
        $requestUser = $request->user();

        if ($userId === $requestUser->id) {
            return redirect()
                ->back()
                ->with('error', __('You cannot remove yourself from your own team.'));
        }

        $team = $requestUser->currentTeam();

        $user = User::find($userId);

        $this->removeUserFromTeam->handle($user, $team);

        return redirect()
            ->route('sendportal.settings.users.index')
            ->with('success', __(':user was removed from :team.', ['user' => $user->name, 'team' => $team->name]));
    }
}
