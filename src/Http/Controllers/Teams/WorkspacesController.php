<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Teams;

use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Middleware\OwnsRequestedTeam;
use Sendportal\Base\Http\Requests\Teams\TeamStoreRequest;
use Sendportal\Base\Http\Requests\Teams\TeamUpdateRequest;
use Sendportal\Base\Models\Team;
use Sendportal\Base\Repositories\TeamsRepository;
use Sendportal\Base\Services\Teams\CreateTeam;
use Exception;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\RedirectResponse;

class WorkspacesController extends Controller
{
    /** @var TeamsRepository */
    protected $teams;

    /** @var CreateTeam */
    protected $createTeam;

    public function __construct(TeamsRepository $teams, CreateTeam $createTeam)
    {
        $this->teams = $teams;
        $this->createTeam = $createTeam;

        $this->middleware(OwnsRequestedTeam::class)->only([
            'edit',
            'update'
        ]);
    }

    public function index(): ViewContract
    {
        $user = user()->load('teams', 'invitations.team');

        return view('sendportal::teams.index', [
            'teams' => $user->teams,
            'invitations' => $user->invitations,
        ]);
    }

    /**
     * @throws Exception
     */
    public function store(TeamStoreRequest $request): RedirectResponse
    {
        $this->createTeam->handle(user(), $request->get('name'), Team::ROLE_OWNER);

        return redirect()->route('workspaces.index');
    }

    public function edit(Team $workspace): ViewContract
    {
        return view('sendportal::teams.edit', [
            'team' => $workspace
        ]);
    }

    /**
     * @throws Exception
     */
    public function update(TeamUpdateRequest $request, Team $workspace)
    {
        $this->teams->update($workspace->id, ['name' => $request->get('workspace_name')]);

        return redirect()->route('workspaces.index');
    }
}
