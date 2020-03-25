<?php

namespace Sendportal\Base\Providers;

use Sendportal\Base\Events\MessageDispatchEvent;
use Sendportal\Base\Events\SubscriberAddedEvent;
use Sendportal\Base\Events\Webhooks\MailgunWebhookEvent;
use Sendportal\Base\Listeners\MessageDispatchHandler;
use Sendportal\Base\Listeners\Webhooks\MailgunWebhookHandler;
use Sendportal\Base\Listeners\Webhooks\PostmarkWebhookHandler;
use Sendportal\Base\Listeners\Webhooks\SendgridWebhookHandler;
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
        MailgunWebhookEvent::class => [
            MailgunWebhookHandler::class,
        ],
        MessageDispatchEvent::class => [
            MessageDispatchHandler::class,
        ],
        PostmarkWebhookEvent::class => [
            PostmarkWebhookHandler::class,
        ],
        SendgridWebhookEvent::class => [
            SendgridWebhookHandler::class,
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
