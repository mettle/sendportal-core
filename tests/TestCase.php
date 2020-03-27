<?php

namespace Tests;

use Collective\Html\FormFacade;
use Laravel\Ui\UiServiceProvider;
use Sendportal\Base\Models\User;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Sendportal\Base\SendportalBaseServiceProvider;

abstract class TestCase extends BaseTestCase
{
    use SendportalTestSupportTrait;

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
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

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'Form' => FormFacade::class
        ];
    }
}
