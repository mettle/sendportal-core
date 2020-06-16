<?php

namespace Sendportal\Base\Providers;

use Sendportal\Base\Events\MessageDispatchEvent;
use Sendportal\Base\Events\SubscriberAddedEvent;
use Sendportal\Base\Events\Webhooks\ElasticWebhookReceived;
use Sendportal\Base\Events\Webhooks\MailgunWebhookReceived;
use Sendportal\Base\Events\Webhooks\PostmarkWebhookReceived;
use Sendportal\Base\Events\Webhooks\SendgridWebhookReceived;
use Sendportal\Base\Events\Webhooks\SesWebhookReceived;
use Sendportal\Base\Listeners\MessageDispatchHandler;
use Sendportal\Base\Listeners\Webhooks\HandleElasticWebhook;
use Sendportal\Base\Listeners\Webhooks\HandleSesWebhook;
use Sendportal\Base\Listeners\Webhooks\HandleMailgunWebhook;
use Sendportal\Base\Listeners\Webhooks\HandlePostmarkWebhook;
use Sendportal\Base\Listeners\Webhooks\HandleSendgridWebhook;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        MailgunWebhookReceived::class => [
            HandleMailgunWebhook::class,
        ],
        MessageDispatchEvent::class => [
            MessageDispatchHandler::class,
        ],
        PostmarkWebhookReceived::class => [
            HandlePostmarkWebhook::class,
        ],
        SendgridWebhookReceived::class => [
            HandleSendgridWebhook::class,
        ],
        SesWebhookReceived::class => [
            HandleSesWebhook::class
        ],
        ElasticWebhookReceived::class => [
            HandleElasticWebhook::class
        ],
        SubscriberAddedEvent::class => [
            // ...
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
