<?php

declare(strict_types=1);

namespace Tests\Feature\Teams;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Sendportal\Base\Models\Team;
use Sendportal\Base\Models\User;
use Sendportal\Base\Services\Teams\AddTeamMember;
use Tests\TestCase;

class WorkspacesControllerTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    /** @test */
    function a_user_can_see_an_index_of_their_workspaces()
    {
        // given
        $user = factory(User::class)->create();

        $teams = factory(Team::class, 2)->create(['owner_id' => $user->id]);

        foreach ($teams as $team) {
            (new AddTeamMember())->handle($team, $user, Team::ROLE_OWNER);
        }

        // when
        $this->loginUser($user);
        $response = $this->get(route('sendportal.workspaces.index'));

        // then
        $response->assertOk();
        $response->assertSee(e($teams[0]->name));
        $response->assertSee(e($teams[1]->name));
    }

    /** @test */
    function a_user_can_create_a_new_workspace()
    {
        // given
        $user = $this->createUserWithTeam();

        $newWorkspaceName = $this->faker->company;

        // when
        $this->loginUser($user);
        $response = $this->post(route('sendportal.workspaces.store'), [
            'name' => $newWorkspaceName
        ]);

        // then
        $response->assertRedirect(route('sendportal.workspaces.index'));

        $this->assertDatabaseHas('teams', [
            'name' => $newWorkspaceName,
            'owner_id' => $user->id
        ]);

        $newTeam = Team::where('name', $newWorkspaceName)->first();

        $this->assertDatabaseHas('team_users', [
            'team_id' => $newTeam->id,
            'user_id' => $user->id,
            'role' => Team::ROLE_OWNER
        ]);
    }
}
