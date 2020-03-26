<?php

namespace Sendportal\Base\Providers;

use Illuminate\Support\ServiceProvider;
use Sendportal\Base\Interfaces\EmailWebhookServiceInterface;
use Sendportal\Base\Services\Helper;
use Sendportal\Base\Services\Webhooks\EmailWebhookService;

class SendportalAppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(EmailWebhookServiceInterface::class, EmailWebhookService::class);

        $this->app->singleton('sendportal.helper', function() {
            return new Helper();
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
