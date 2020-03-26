<?php

namespace Sendportal\Base\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Route::group([
            'namespace' => 'Sendportal\Base\Http\Controllers'
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        });

        Route::group([
            'namespace' => 'Sendportal\Base\Http\Controllers',
            'prefix' => 'api'
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
        });
    }
}