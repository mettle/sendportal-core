<?php

namespace Tests;

use Collective\Html\FormFacade;
use Laravel\Ui\UiServiceProvider;
use Sendportal\Base\Models\Segment;
use Sendportal\Base\Models\Subscriber;
use Sendportal\Base\Models\Team;
use Sendportal\Base\Models\User;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;
use Sendportal\Base\SendportalBaseServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            UiServiceProvider::class,
            SendportalBaseServiceProvider::class
        ];
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMix();
        $this->withExceptionHandling();
        $this->withFactories(__DIR__ . '/../database/factories');

        $this->artisan('migrate', ['--database' => 'mysql'])->run();
        $this->artisan('migrate', ['--database' => 'pgsql'])->run();
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('auth.providers.users.model', User::class);
    }


    protected function getPackageAliases($app)
    {
        return [
            'Form' => FormFacade::class
        ];
    }

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
