<?php

declare(strict_types=1);

namespace Sendportal\Base\Providers;

use Illuminate\Support\ServiceProvider;
use Sendportal\Base\Services\ResolverService;

class ResolverProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('sendportal.resolver', function () {
            return new ResolverService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
