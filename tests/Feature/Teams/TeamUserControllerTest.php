<?php

declare(strict_types=1);

namespace Tests\Feature\Teams;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamUserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @group team_user_test
     */
    function an_unauthenticated_user_cannot_view_the_team_user_index()
    {
        $response = $this->get(route('sendportal.settings.users.index'));

        $this->assertLoginRedirect($response);
    }


    /**
     * @test
     * @group team_user_test
     */
    function users_cannot_view_team_users_for_a_team_they_do_not_own()
    {
        $this->createUserAndLogin(['team-member']);

        $response = $this->get(route('sendportal.settings.users.index'));

        $response->assertStatus(404);
    }

    /**
     * @test
     * @group team_user_test
     */
    function users_can_view_team_users_for_a_team_they_do_own()
    {
        $user = $this->createUserWithTeam();

        $this->actingAs($user);
        $response = $this->get(route('sendportal.settings.users.index'));

        $response->assertOk();
        $response->assertSee($user->name);
    }

    /**
     * @test
     * @group team_user_test
     */
    function team_owners_can_remove_users_from_their_team()
    {
        $user = $this->createUserWithTeam();
        $team = $user->currentTeam();

        $otherUser = $this->createTeamUser($team);

        $this->assertTrue($otherUser->onTeam($team));

        $this->actingAs($user);
        $this->delete(route('sendportal.settings.users.destroy', $otherUser->id));

        $this->assertFalse($otherUser->fresh()->onTeam($team));
    }

    /**
     * @test
     * @group team_user_test
     */
    function team_owners_cannot_remove_themselves_from_their_team()
    {
        [$team, $user] = $this->createUserAndTeam();

        $this->actingAs($user);
        $response = $this->delete(route('sendportal.settings.users.destroy', $user->id));

        $response->assertRedirect();

        $this->assertTrue($user->onTeam($team));
    }

    /**
     * @test
     * @group team_user_test
     */
    function only_team_owners_can_remove_users_from_a_team()
    {
        $user = $this->createUserAndLogin(['team-member']);

        $team = $user->currentTeam();

        $otherUser = $this->createTeamUser($team);

        $response = $this->delete(route('sendportal.settings.users.destroy', $otherUser->id));

        $response->assertStatus(404);
    }

}
