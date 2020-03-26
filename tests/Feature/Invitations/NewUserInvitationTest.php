<?php

declare(strict_types=1);

namespace Tests\Feature\Invitations;

use Sendportal\Base\Models\Invitation;
use Sendportal\Base\Models\Workspace;
use Sendportal\Base\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class NewUserInvitationTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    /** @test */
    function a_new_user_can_register_with_an_invitation_to_an_existing_workspace()
    {
        // NOTE(david): if this fails, you probably need to set ENABLE_REGISTER=true in the .env.testing file.

        // given
        $workspace = factory(Workspace::class)->create();
        $invitation = factory(Invitation::class)->create([
            'workspace_id' => $workspace->id
        ]);

        $postData = [
            'name' => $this->faker->name,
            'email' => $invitation->email,
            'password' => $this->faker->password,
            'invitation' => $invitation->token
        ];

        // when
        $this->post(route('register'), $postData);

        // then
        /** @var User $user */
        $user = User::where('email', $postData['email'])->first();

        $this->assertNotNull($user);

        $this->assertEquals($postData['name'], $user->name);

        $this->assertTrue($user->onWorkspace($workspace));

        $this->assertDatabaseMissing('invitations', [
            'token' => $invitation->token
        ]);
    }

    /** @test */
    function a_user_cannot_see_the_register_form_with_an_invalid_invitation()
    {
        // when
        $response = $this->get(route('register') . '?invitation=invalid_invitation');

        // then
        $response->assertRedirect(route('register'));
        $response->assertSessionHas('error', 'The invitation is no longer valid.');
    }

    /** @test */
    function registrations_fail_validation_when_invitation_is_invalid()
    {
        // given
        $postData = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'password' => $this->faker->password,
            'invitation' => 'invalid_invitation'
        ];

        // when
        $response = $this->post(route('register'), $postData);

        // then
        $response->assertRedirect();
        $response->assertSessionHasErrors('invitation', 'The invitation is no longer valid.');

        $user = User::where('email', $postData['email'])->first();

        $this->assertNull($user);
    }
}
