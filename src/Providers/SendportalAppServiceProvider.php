<?php

namespace Sendportal\Base\Providers;

use Sendportal\Base\Interfaces\CampaignTenantInterface;
use Sendportal\Base\Interfaces\EmailWebhookServiceInterface;
use Sendportal\Base\Interfaces\QuotaServiceInterface;
use Sendportal\Base\Repositories\MySQL\CampaignTenantRepository as MySQLCampaignTenantRepository;
use Sendportal\Base\Repositories\Postgres\CampaignTenantRepository as PostgresCampaignTenantRepository;
use Sendportal\Base\Services\Helper;
use Sendportal\Base\Services\QuotaService;
use Sendportal\Base\Services\Webhooks\EmailWebhookService;
use Sendportal\Base\Traits\ResolvesDatabaseDriver;
use Illuminate\Support\ServiceProvider;

class SendportalAppServiceProvider extends ServiceProvider
{
    use ResolvesDatabaseDriver;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(CampaignTenantInterface::class, function ($app) {
            if ($this->usingPostgres()) {
                return $app->make(PostgresCampaignTenantRepository::class);
            }

            return $app->make(MySQLCampaignTenantRepository::class);
        });

        $this->app->bind(EmailWebhookServiceInterface::class, EmailWebhookService::class);
        $this->app->bind(QuotaServiceInterface::class, QuotaService::class);

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
