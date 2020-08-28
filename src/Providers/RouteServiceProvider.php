<?php

namespace Sendportal\Base\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Sendportal\Base\Routes\ApiRoutes;
use Sendportal\Base\Routes\WebRoutes;

class RouteServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Route::mixin(new ApiRoutes());
        Route::mixin(new WebRoutes());
    }
}
