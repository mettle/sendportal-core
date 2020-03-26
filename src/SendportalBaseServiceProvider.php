<?php

namespace Sendportal\Base;

use Collective\Html\FormFacade;
use Collective\Html\HtmlFacade;
use Collective\Html\HtmlServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Sendportal\Base\Console\Commands\CampaignDispatchCommand;
use Sendportal\Base\Console\Commands\GenerateTestSubscribers;
use Sendportal\Base\Console\Commands\SetupProduction;
use Sendportal\Base\Http\Middleware\OwnsCurrentTeam;
use Sendportal\Base\Http\Middleware\VerifyUserOnTeam;
use Sendportal\Base\Providers\EventServiceProvider;
use Sendportal\Base\Providers\FormServiceProvider;
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
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'sendportal');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');



        Route::group([
            'namespace' => 'Sendportal\Base\Http\Controllers'
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(SendportalAppServiceProvider::class);
        $this->app->register(HtmlServiceProvider::class);
        $this->app->register(FormServiceProvider::class);

        //$this->mergeConfigFrom(__DIR__.'/../config/config.php', 'automations');
    }
}
