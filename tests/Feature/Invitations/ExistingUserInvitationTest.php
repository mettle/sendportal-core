<?php

declare(strict_types=1);

namespace Tests\Feature\Invitations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Sendportal\Base\Models\Team;
use Sendportal\Base\Models\User;
use Tests\TestCase;

class ExistingUserInvitationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function a_user_can_see_their_invitations()
    {
        // given
        $user = $this->createUserWithTeam();

        $newTeam = factory(Team::class)->create();

        $newTeam->invitations()->create([
            'id' => Uuid::uuid4(),
            'user_id' => $user->id,
            'role' => Team::ROLE_MEMBER,
            'email' => $user->email,
            'token' => Str::random(40),
        ]);

        // when
        $response = $this->actingAs($user)
            ->get(route('sendportal.workspaces.index'));

        // then
        $response->assertSee($newTeam->name);
        $response->assertSee('Accept');
        $response->assertSee('Reject');
    }

    /** @test */
    function a_user_cannot_see_another_users_invitations()
    {
        // given
        $user = $this->createUserWithTeam();

        $secondUser = factory(User::class)->create();
        $newTeam = factory(Team::class)->create();

        $newTeam->invitations()->create([
            'id' => Uuid::uuid4(),
            'user_id' => $secondUser->id,
            'role' => Team::ROLE_MEMBER,
            'email' => $secondUser->email,
            'token' => Str::random(40),
        ]);

        // when
        $response = $this->actingAs($user)
            ->get(route('sendportal.workspaces.index'));

        // then
        $response->assertDontSee($newTeam->name);
    }

    /** @test */
    function a_user_can_accept_valid_invitations()
    {
        // given
        $user = $this->createUserWithTeam();

        $newTeam = factory(Team::class)->create();

        $invitation = $newTeam->invitations()->create([
            'id' => Uuid::uuid4(),
            'user_id' => $user->id,
            'role' => Team::ROLE_MEMBER,
            'email' => $user->email,
            'token' => Str::random(40),
        ]);

        // when
        $response = $this->actingAs($user)
            ->post(route('sendportal.teams.invitations.accept', $invitation));

        // then
        $response->assertRedirect(route('sendportal.workspaces.index'));

        $this->assertTrue($user->fresh()->onTeam($newTeam));
    }

    /** @test */
    function a_user_can_reject_invitations()
    {
        // given
        $user = $this->createUserWithTeam();

        $newTeam = factory(Team::class)->create();

        $invitation = $newTeam->invitations()->create([
            'id' => Uuid::uuid4(),
            'user_id' => $user->id,
            'role' => Team::ROLE_MEMBER,
            'email' => $user->email,
            'token' => Str::random(40),
        ]);

        // when
        $response = $this->actingAs($user)
            ->post(route('sendportal.teams.invitations.reject', $invitation));

        // then
        $response->assertRedirect(route('sendportal.workspaces.index'));

        $this->assertFalse($user->fresh()->onTeam($newTeam));

        $this->assertDatabaseMissing('invitations', [
            'id' => $invitation->id
        ]);
    }

    /** @test */
    function a_user_cannot_accept_an_expired_invitation()
    {
        // given
        $user = $this->createUserWithTeam();

        $newTeam = factory(Team::class)->create();

        $invitation = $newTeam->invitations()->create([
            'id' => Uuid::uuid4(),
            'user_id' => $user->id,
            'role' => Team::ROLE_MEMBER,
            'email' => $user->email,
            'token' => Str::random(40),
        ]);

        $invitation->created_at = $invitation->created_at->subWeeks(2);
        $invitation->save();

        // when
        $this->actingAs($user)
            ->post(route('sendportal.teams.invitations.accept', $invitation));

        // then
        $this->assertFalse($user->fresh()->onTeam($newTeam));
    }
}
