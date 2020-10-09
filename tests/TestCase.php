<?php

namespace Tests;

use Collective\Html\FormFacade;
use Laravel\Ui\UiServiceProvider;
use Livewire\LivewireServiceProvider;
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
            LivewireServiceProvider::class,
            SendportalBaseServiceProvider::class,
            SendportalTestServiceProvider::class,
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

        $this->artisan('migrate')->run();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'Form' => FormFacade::class,
        ];
    }
}
