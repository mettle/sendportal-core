<?php

declare(strict_types=1);

namespace Tests\Feature\Teams;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Sendportal\Base\Models\Team;
use Sendportal\Base\Services\Teams\AddTeamMember;
use Tests\TestCase;

class SwitchWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function a_user_can_switch_between_workspaces()
    {
        // given
        $user = $this->createUserWithTeam();

        $secondTeam = factory(Team::class)->create(['owner_id' => $user->id]);

        (new AddTeamMember())->handle($secondTeam, $user, Team::ROLE_OWNER);

        // when
        $this->loginUser($user);
        $response = $this->get(route('sendportal.workspaces.switch', $secondTeam->id));

        // then
        $response->assertRedirect(route('sendportal.campaigns.index'));

        $this->assertEquals($secondTeam->id, $user->currentTeam()->id);
    }

    /** @test */
    function a_user_cannot_switch_to_a_workspace_they_do_not_belong_to()
    {
        // given
        [$team, $user] = $this->createUserAndTeam();

        $secondTeam = factory(Team::class)->create();

        // when
        $this->loginUser($user);
        $response = $this->get(route('sendportal.workspaces.switch', $secondTeam->id));

        // then
        $response->assertStatus(404);

        $this->assertEquals($team->id, $user->currentTeam()->id);
    }

    /** @test */
    function a_guest_cannot_switch_teams()
    {
        // given
        $user = $this->createUserWithTeam();
        $secondTeam = factory(Team::class)->create(['owner_id' => $user->id]);

        (new AddTeamMember())->handle($secondTeam, $user, Team::ROLE_OWNER);

        // when
        $response = $this->get(route('sendportal.workspaces.switch', $secondTeam->id));

        // then
        $response->assertRedirect(route('login'));
    }


}
