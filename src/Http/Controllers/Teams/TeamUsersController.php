<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Teams;

use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Models\User;
use Sendportal\Base\Services\Teams\RemoveUserFromTeam;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\RedirectResponse;

class TeamUsersController extends Controller
{
    /** @var RemoveUserFromTeam */
    private $removeUserFromTeam;

    public function __construct(RemoveUserFromTeam $removeUserFromTeam)
    {
        $this->removeUserFromTeam = $removeUserFromTeam;
    }

    /**
     * List of users and invitations for the current team.
     *
     * @return ViewContract
     */
    public function index(): ViewContract
    {
        return view('sendportal::settings.users.index', [
            'users' => user()->currentTeam->users,
            'invitations' => user()->currentTeam->invitations,
        ]);
    }

    /**
     * Remove a user from the current team.
     *
     * @param int $userId
     *
     * @return RedirectResponse
     */
    public function destroy(int $userId): RedirectResponse
    {
        if ($userId === user()->id) {
            return redirect()
                ->back()
                ->with('error', __('You cannot remove yourself from your own team.'));
        }

        $team = currentTeam();

        $user = User::find($userId);

        $this->removeUserFromTeam->handle($user, $team);

        return redirect()
            ->route('settings.users.index')
            ->with('success', __(':user was removed from :team.', ['user' => $user->name, 'team' => $team->name]));
    }
}
