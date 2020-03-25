<?php

namespace Tests;

use Sendportal\Base\Models\Segment;
use Sendportal\Base\Models\Subscriber;
use Sendportal\Base\Models\Team;
use Sendportal\Base\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Create a user with attached team.
     */
    protected function createUserWithTeam(): User
    {
        return factory(Team::class)->create()->owner;
    }

    /**
     * Create a user with attached team, returning both team and user.
     */
    protected function createUserAndTeam(): array
    {
        $team = factory(Team::class)->create();

        return [$team, $team->owner];
    }

    /**
     * Log in the given user.
     */
    protected function loginUser(User $user): void
    {
        auth()->login($user);
    }

    protected function createSegment(User $user): Segment
    {
        return factory(Segment::class)->create([
            'team_id' => $user->currentTeam()->id,
        ]);
    }

    protected function createSubscriber(User $user): Subscriber
    {
        return factory(Subscriber::class)->create([
            'team_id' => $user->currentTeam()->id,
        ]);
    }

    public function assertLoginRedirect(TestResponse $response): void
    {
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function createUserAndLogin(array $states = [], array $overrides = []): User
    {
        $user = factory(User::class)->states($states)->create($overrides);
        $this->actingAs($user);

        return $user;
    }

    public function createTeamUser(Team $team, array $overrides = []): User
    {
        $user = factory(User::class)->create($overrides);
        $team->users()->attach($user, ['role' => Team::ROLE_MEMBER]);

        return $user;
    }
}
