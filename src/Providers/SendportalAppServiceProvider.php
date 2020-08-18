<?php

declare(strict_types=1);

namespace Sendportal\Base\Providers;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Sendportal\Base\Interfaces\QuotaServiceInterface;
use Sendportal\Base\Repositories\Campaigns\CampaignTenantRepositoryInterface;
use Sendportal\Base\Repositories\Campaigns\MySqlCampaignTenantRepository;
use Sendportal\Base\Repositories\Campaigns\PostgresCampaignTenantRepository;
use Sendportal\Base\Repositories\Campaigns\SqliteCampaignTenantRepository;
use Sendportal\Base\Repositories\Messages\MessageTenantRepositoryInterface;
use Sendportal\Base\Repositories\Messages\MySqlMessageTenantRepository;
use Sendportal\Base\Repositories\Messages\PostgresMessageTenantRepository;
use Sendportal\Base\Repositories\Messages\SqliteMessageTenantRepository;
use Sendportal\Base\Repositories\Subscribers\MySqlSubscriberTenantRepository;
use Sendportal\Base\Repositories\Subscribers\PostgresSubscriberTenantRepository;
use Sendportal\Base\Repositories\Subscribers\SqliteSubscriberTenantRepository;
use Sendportal\Base\Repositories\Subscribers\SubscriberTenantRepositoryInterface;
use Sendportal\Base\Services\Helper;
use Sendportal\Base\Services\QuotaService;
use Sendportal\Base\Traits\ResolvesDatabaseDriver;

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
        // Campaign repository.
        $this->app->bind(CampaignTenantRepositoryInterface::class, function (Application $app) {
            if ($this->usingPostgres()) {
                return $app->make(PostgresCampaignTenantRepository::class);
            }

            if ($this->usingMySQL()) {
                return $app->make(MySqlCampaignTenantRepository::class);
            }

            return $app->make(SqliteCampaignTenantRepository::class);
        });

        // Message repository.
        $this->app->bind(MessageTenantRepositoryInterface::class, function (Application $app) {
            if ($this->usingPostgres()) {
                return $app->make(PostgresMessageTenantRepository::class);
            }

            if ($this->usingMySQL()) {
                return $app->make(MySqlMessageTenantRepository::class);
            }

            return $app->make(SqliteMessageTenantRepository::class);
        });

        // Subscriber repository.
        $this->app->bind(SubscriberTenantRepositoryInterface::class, function (Application $app) {
            if ($this->usingPostgres()) {
                return $app->make(PostgresSubscriberTenantRepository::class);
            }

            if ($this->usingMySQL()) {
                return $app->make(MySqlSubscriberTenantRepository::class);
            }

            return $app->make(SqliteSubscriberTenantRepository::class);
        });

        $this->app->bind(QuotaServiceInterface::class, QuotaService::class);

        $this->app->singleton('sendportal.helper', function () {
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
