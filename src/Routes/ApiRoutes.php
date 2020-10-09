<?php

declare(strict_types=1);

namespace Sendportal\Base\Routes;

use Illuminate\Routing\Router;

class ApiRoutes
{
    public function sendportalApiRoutes(): callable
    {
        return function() {
            $this->name('sendportal.api.')->prefix('api/v1/workspaces/{workspaceId}')->namespace('\Sendportal\Base\Http\Controllers\Api')->group(static function (Router $apiRouter) {
                $apiRouter->apiResource('campaigns', 'CampaignsController');
                $apiRouter->post('campaigns/{id}/send', 'CampaignDispatchController@send')->name('campaigns.send');
                $apiRouter->apiResource('subscribers', 'SubscribersController');
                $apiRouter->apiResource('segments', 'SegmentsController');

                $apiRouter->apiResource('subscribers.segments', 'SubscriberSegmentsController')
                    ->except(['show', 'update', 'destroy']);
                $apiRouter->put('subscribers/{subscriber}/segments', 'SubscriberSegmentsController@update')
                    ->name('subscribers.segments.update');
                $apiRouter->delete('subscribers/{subscriber}/segments', 'SubscriberSegmentsController@destroy')
                    ->name('subscribers.segments.destroy');

                $apiRouter->apiResource('segments.subscribers', 'SegmentSubscribersController')
                    ->except(['show', 'update', 'destroy']);
                $apiRouter->put('segments/{segment}/subscribers', 'SegmentSubscribersController@update')
                    ->name('segments.subscribers.update');
                $apiRouter->delete('segments/{segment}/subscribers', 'SegmentSubscribersController@destroy')
                    ->name('segments.subscribers.destroy');

                $apiRouter->apiResource('templates', 'TemplatesController');
            });
        };
    }

    public function sendportalPublicApiRoutes(): callable
    {
        return function() {
            $this->name('sendportal.api.webhooks.')->prefix('api/v1/webhooks')->namespace('\Sendportal\Base\Http\Controllers\Api\Webhooks')->group(static function (Router $webhookRouter) {
                $webhookRouter->post('aws', 'SesWebhooksController@handle')->name('aws');
                $webhookRouter->post('mailgun', 'MailgunWebhooksController@handle')->name('mailgun');
                $webhookRouter->post('postmark', 'PostmarkWebhooksController@handle')->name('postmark');
                $webhookRouter->post('sendgrid', 'SendgridWebhooksController@handle')->name('sendgrid');
                $webhookRouter->post('mailjet', 'MailjetWebhooksController@handle')->name('mailjet');
            });

            $this->get('api/v1/ping', static function () {
                return 'ok';
            });
        };
    }
}