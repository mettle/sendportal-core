<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Teams;

use Exception;
use Illuminate\Http\RedirectResponse;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Middleware\OwnsCurrentTeam;
use Sendportal\Base\Http\Requests\Teams\TeamInvitationStoreRequest;
use Sendportal\Base\Models\Invitation;
use Sendportal\Base\Services\Teams\SendInvitation;

class TeamInvitationsController extends Controller
{
    /** @var SendInvitation */
    protected $sendInvitation;

    public function __construct(SendInvitation $sendInvitation)
    {
        $this->sendInvitation = $sendInvitation;

        $this->middleware(OwnsCurrentTeam::class)->only(['store']);
    }

    /**
     * @throws Exception
     */
    public function store(TeamInvitationStoreRequest $request): RedirectResponse
    {
        $team = $request->user()->currentTeam();

        $this->sendInvitation->handle($team, $request->email);

        return redirect()->route('sendportal.settings.users.index');
    }

    /**
     * @throws Exception
     */
    public function destroy(Invitation $invitation): RedirectResponse
    {
        $invitation->delete();

        return redirect()->route('sendportal.settings.users.index');
    }
}
