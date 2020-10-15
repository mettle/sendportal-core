<?php

namespace Tests;

use Collective\Html\FormFacade;
use Illuminate\Support\Str;
use Laravel\Ui\UiServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Sendportal\Base\Models\User;
use Sendportal\Base\SendportalBaseServiceProvider;
use Sendportal\Base\Services\Messages\RelayMessage;

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
            LivewireServiceProvider::class,
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
        $this->mockRelayMessageService();

        $this->artisan('migrate')->run();
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

    /**
     * @return void
     */
    protected function mockRelayMessageService()
    {
        $service = $this->getMockBuilder(RelayMessage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $service->method('handle')->willReturn(Str::random());

        app()->instance(RelayMessage::class, $service);
    }
}
