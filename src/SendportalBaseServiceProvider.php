<?php

namespace Sendportal\Base;

use Collective\Html\HtmlServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Sendportal\Base\Console\Commands\CampaignDispatchCommand;
use Sendportal\Base\Console\Commands\GenerateTestSubscribers;
use Sendportal\Base\Console\Commands\SetupProduction;
use Sendportal\Base\Providers\EventServiceProvider;
use Sendportal\Base\Providers\FormServiceProvider;
use Sendportal\Base\Providers\ResolverProvider;
use Sendportal\Base\Providers\RouteServiceProvider;
use Sendportal\Base\Providers\SendportalAppServiceProvider;

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
            ], 'sendportal-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/sendportal'),
            ], 'sendportal-views');

            $this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/sendportal'),
            ], 'sendportal-lang');

            $this->publishes([
                __DIR__.'/../public' => public_path('vendor/sendportal'),
            ], 'sendportal-assets');

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
        $this->loadJsonTranslationsFrom(resource_path('lang/vendor/sendportal'));
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'sendportal');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');


    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->register(SendportalAppServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
        $this->app->register(HtmlServiceProvider::class);
        $this->app->register(FormServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(ResolverProvider::class);

        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'sendportal');
    }
}
