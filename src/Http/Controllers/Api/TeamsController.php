<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Api;

use Illuminate\Http\Request;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Resources\Team as TeamResource;
use Sendportal\Base\Repositories\TeamsRepository;
use Exception;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TeamsController extends Controller
{
    /** @var TeamsRepository */
    private $teams;

    public function __construct(TeamsRepository $teams)
    {
        $this->teams = $teams;
    }

    /**
     * @throws Exception
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $teams = $this->teams->teamsForUser($request->user());

        return TeamResource::collection($teams);
    }
}
