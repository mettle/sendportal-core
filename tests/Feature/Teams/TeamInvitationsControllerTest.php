<?php

declare(strict_types=1);

namespace Tests\Feature\Teams;

use Sendportal\Base\Models\Invitation;
use Sendportal\Base\Models\Team;
use Sendportal\Base\Models\User;
use Sendportal\Base\Services\Teams\AddTeamMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TeamInvitationsControllerTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    /** @test */
    function an_invitation_can_be_sent_to_a_new_user()
    {
        // given
        [$team, $user] = $this->createUserAndTeam();

        $email = $this->faker->safeEmail;

        $postData = [
            'email' => $email
        ];

        // when
        $this->actingAs($user);
        $response = $this->post(route('settings.users.invitations.store', $postData));

        // then
        $response->assertRedirect(route('settings.users.index'));

        $this->assertDatabaseHas('invitations', [
            'team_id' => $team->id,
            'email' => $email,
            'user_id' => null
        ]);
    }

    /** @test */
    function an_invitation_can_be_sent_to_an_existing_user()
    {
        // given
        [$team, $user] = $this->createUserAndTeam();

        $existingInviteUser = factory(User::class)->create();

        $postData = [
            'email' => $existingInviteUser->email
        ];

        // when
        $this->actingAs($user);
        $response = $this->post(route('settings.users.invitations.store', $postData));

        // then
        $response->assertRedirect(route('settings.users.index'));

        $this->assertDatabaseHas('invitations', [
            'team_id' => $team->id,
            'email' => $existingInviteUser->email,
            'user_id' => $existingInviteUser->id
        ]);
    }

    /** @test */
    function non_owners_cannot_invite_new_members()
    {
        // given
        $user = factory(User::class)->create();
        $team = factory(Team::class)->create();

        (new AddTeamMember())->handle($team, $user, Team::ROLE_MEMBER);

        $email = $this->faker->safeEmail;

        $postData = [
            'email' => $email
        ];

        // when
        $this->actingAs($user);
        $response = $this->post(route('settings.users.invitations.store', $postData));

        // then
        $response->assertStatus(404);

        $this->assertDatabaseMissing('invitations', [
            'team_id' => $team->id,
            'email' => $email
        ]);
    }

    /** @test */
    function invitations_can_be_retracted()
    {
        // given
        [$team, $user] = $this->createUserAndTeam();

        $invitation = factory(Invitation::class)->create([
            'team_id' => $team->id
        ]);

        // when
        $this->actingAs($user);
        $response = $this->delete(route('settings.users.invitations.destroy', $invitation));

        // then
        $response->assertRedirect(route('settings.users.index'));

        $this->assertDatabaseMissing('invitations', [
            'id' => $invitation->id
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }


}
