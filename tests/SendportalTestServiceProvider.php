<?php

namespace Tests;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Sendportal\Base\Facades\Sendportal;

class SendportalTestServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Sendportal::currentWorkspaceIdResolver(function() {
            return 1;
        });

        Route::group(['prefix' => 'sendportal'], function() {
            Sendportal::webRoutes();
            Sendportal::publicWebRoutes();
            Sendportal::apiRoutes();
            Sendportal::publicApiRoutes();
        });
    }
}