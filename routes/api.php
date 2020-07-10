<?php

declare(strict_types=1);

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Sendportal\Base\Http\Middleware\VerifyUserOnWorkspace;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware([
    'auth:api',
    config('sendportal.throttle_middleware'),
])->name('sendportal.api.')->namespace('Api')->group(static function (Router $router) {
    $router->apiResource('workspaces', 'WorkspacesController')->only('index');
});

Route::middleware([
    'auth:api',
    VerifyUserOnWorkspace::class,
    config('sendportal.throttle_middleware'),
])->name('sendportal.api.')->namespace('Api')->prefix('workspaces/{workspaceId}')->group(static function (Router $apiRouter) {
    $apiRouter->apiResource('campaigns', 'CampaignsController');
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
});

Route::name('api.webhooks.')->prefix('webhooks')->namespace('Api\Webhooks')->group(static function (Router $webhookRouter) {
    $webhookRouter->post('aws', 'SesWebhooksController@handle')->name('aws');
    $webhookRouter->post('mailgun', 'MailgunWebhooksController@handle')->name('mailgun');
    $webhookRouter->post('postmark', 'PostmarkWebhooksController@handle')->name('postmark');
    $webhookRouter->post('sendgrid', 'SendgridWebhooksController@handle')->name('sendgrid');
});

Route::get('ping', static function () {
    return 'ok';
});
