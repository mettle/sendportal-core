<?php

namespace Sendportal\Base;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Sendportal\Base\Console\Commands\CampaignDispatchCommand;
use Sendportal\Base\Console\Commands\GenerateTestSubscribers;
use Sendportal\Base\Console\Commands\SetupProduction;
use Sendportal\Base\Providers\EventServiceProvider;

class SendportalBaseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('sendportal.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/sendportal'),
            ], 'views');

            $this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/sendportal'),
            ], 'lang');

            $this->commands([
                CampaignDispatchCommand::class,
                GenerateTestSubscribers::class,
                SetupProduction::class,
            ]);

            $this->app->booted(function () {
                $schedule = $this->app->make(Schedule::class);
                $schedule->command(CampaignDispatchCommand::class)->everyMinute()->withoutOverlapping();
            });
        }

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'sendportal');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'sendportal');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        $this->registerEvents();
    }

    protected function registerEvents(): void
    {
        $this->register(EventServiceProvider::class);
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        //$this->mergeConfigFrom(__DIR__.'/../config/config.php', 'automations');
    }
}
