<?php

declare(strict_types=1);

namespace Sendportal\Base\Routes;

use Illuminate\Routing\Router;

class WebRoutes
{
    public function sendportalPublicWebRoutes(): callable
    {
        return function () {
            $this->name('sendportal.')->namespace('\Sendportal\Base\Http\Controllers')->group(static function (
                Router $appRouter
            ) {
                // Subscriptions
                $appRouter->name('subscriptions.')->namespace('Subscriptions')->prefix('subscriptions')->group(static function (
                    Router $subscriptionController
                ) {
                    $subscriptionController->get('unsubscribe/{messageHash}', 'SubscriptionsController@unsubscribe')
                        ->name('unsubscribe');
                    $subscriptionController->get(
                        'subscribe/{messageHash}',
                        'SubscriptionsController@subscribe'
                    )->name('subscribe');
                    $subscriptionController->put(
                        'subscriptions/{messageHash}',
                        'SubscriptionsController@update'
                    )->name('update');
                });

                // Webview.
                $appRouter->name('webview.')->prefix('webview')->namespace('Webview')->group(static function (
                    Router $webviewRouter
                ) {
                    $webviewRouter->get('{messageHash}', 'WebviewController@show')->name('show');
                });
            });
        };
    }

    public function sendportalWebRoutes(): callable
    {
        return function () {
            $this->name('sendportal.')->namespace('\Sendportal\Base\Http\Controllers')->group(static function (
                Router $appRouter
            ) {
                // Dashboard.
                $appRouter->get('/', 'DashboardController@index')->name('dashboard');

                // Campaigns.
                $appRouter->resource('campaigns', 'Campaigns\CampaignsController')->except(['show', 'destroy']);
                $appRouter->name('campaigns.')->prefix('campaigns')->namespace('Campaigns')->group(static function (
                    Router $campaignRouter
                ) {
                    $campaignRouter->get('sent', 'CampaignsController@sent')->name('sent');
                    $campaignRouter->get('{id}', 'CampaignsController@show')->name('show');
                    $campaignRouter->get('{id}/preview', 'CampaignsController@preview')->name('preview');
                    $campaignRouter->put('{id}/send', 'CampaignDispatchController@send')->name('send');
                    $campaignRouter->get('{id}/status', 'CampaignsController@status')->name('status');
                    $campaignRouter->post('{id}/test', 'CampaignTestController@handle')->name('test');

                    $campaignRouter->get(
                        '{id}/confirm-delete',
                        'CampaignDeleteController@confirm'
                    )->name('destroy.confirm');
                    $campaignRouter->delete('', 'CampaignDeleteController@destroy')->name('destroy');

                    $campaignRouter->get('{id}/duplicate', 'CampaignDuplicateController@duplicate')->name('duplicate');

                    $campaignRouter->get('{id}/confirm-cancel', 'CampaignCancellationController@confirm')->name('confirm-cancel');
                    $campaignRouter->post('{id}/cancel', 'CampaignCancellationController@cancel')->name('cancel');

                    $campaignRouter->get('{id}/report', 'CampaignReportsController@index')->name('reports.index');
                    $campaignRouter->get('{id}/report/recipients', 'CampaignReportsController@recipients')
                        ->name('reports.recipients');
                    $campaignRouter->get('{id}/report/opens', 'CampaignReportsController@opens')->name('reports.opens');
                    $campaignRouter->get(
                        '{id}/report/clicks',
                        'CampaignReportsController@clicks'
                    )->name('reports.clicks');
                    $campaignRouter->get('{id}/report/unsubscribes', 'CampaignReportsController@unsubscribes')
                        ->name('reports.unsubscribes');
                    $campaignRouter->get(
                        '{id}/report/bounces',
                        'CampaignReportsController@bounces'
                    )->name('reports.bounces');
                });

                // Messages.
                $appRouter->name('messages.')->prefix('messages')->group(static function (Router $messageRouter) {
                    $messageRouter->get('/', 'MessagesController@index')->name('index');
                    $messageRouter->get('draft', 'MessagesController@draft')->name('draft');
                    $messageRouter->get('{id}/show', 'MessagesController@show')->name('show');
                    $messageRouter->post('send', 'MessagesController@send')->name('send');
                    $messageRouter->delete('{id}/delete', 'MessagesController@delete')->name('delete');
                    $messageRouter->post('send-selected', 'MessagesController@sendSelected')->name('send-selected');
                });

                // Email Services.
                $appRouter->name('email_services.')->prefix('email-services')->namespace('EmailServices')->group(static function (
                    Router $servicesRouter
                ) {
                    $servicesRouter->get('/', 'EmailServicesController@index')->name('index');
                    $servicesRouter->get('create', 'EmailServicesController@create')->name('create');
                    $servicesRouter->get('type/{id}', 'EmailServicesController@emailServicesTypeAjax')->name('ajax');
                    $servicesRouter->post('/', 'EmailServicesController@store')->name('store');
                    $servicesRouter->get('{id}/edit', 'EmailServicesController@edit')->name('edit');
                    $servicesRouter->put('{id}', 'EmailServicesController@update')->name('update');
                    $servicesRouter->delete('{id}', 'EmailServicesController@delete')->name('delete');

                    $servicesRouter->get('{id}/test', 'TestEmailServiceController@create')->name('test.create');
                    $servicesRouter->post('{id}/test', 'TestEmailServiceController@store')->name('test.store');
                });

                // Tags.
                $appRouter->resource('tags', 'Tags\TagsController')->except(['show']);
                $appRouter->resource('templates', 'TemplatesController');

                // Subscribers.
                $appRouter->name('subscribers.')->prefix('subscribers')->namespace('Subscribers')->group(static function (
                    Router $subscriberRouter
                ) {
                    $subscriberRouter->get('export', 'SubscribersController@export')->name('export');
                    $subscriberRouter->get('import', 'SubscribersImportController@show')->name('import');
                    $subscriberRouter->post('import', 'SubscribersImportController@store')->name('import.store');
                });
                $appRouter->resource('subscribers', 'Subscribers\SubscribersController');

                // Templates.
                $appRouter->resource('templates', 'TemplatesController')->except(['show']);
            });
        };
    }
}
