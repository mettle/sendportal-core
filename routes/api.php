<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Sendportal\Base\Http\Middleware\VerifyUserOnTeam;

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

Route::middleware(['auth:api'])->name('api.')->namespace('Api')->group(static function () {
    Route::apiResource('teams', 'TeamsController')->only('index');
});

Route::middleware([
    'auth:api',
    VerifyUserOnTeam::class
])->name('api.')->namespace('Api')->prefix('workspaces/{teamId}')->group(function () {
    Route::apiResource('subscribers', 'SubscribersController');
    Route::apiResource('segments', 'SegmentsController');

    Route::apiResource('subscribers.segments', 'SubscriberSegmentsController')
        ->except(['show', 'update', 'destroy']);
    Route::put('subscribers/{subscriber}/segments', 'SubscriberSegmentsController@update')
        ->name('subscribers.segments.update');
    Route::delete('subscribers/{subscriber}/segments', 'SubscriberSegmentsController@destroy')
        ->name('subscribers.segments.destroy');

    Route::apiResource('segments.subscribers', 'SegmentSubscribersController')
        ->except(['show', 'update', 'destroy']);
    Route::put('segments/{segment}/subscribers', 'SegmentSubscribersController@update')
        ->name('segments.subscribers.update');
    Route::delete('segments/{segment}/subscribers', 'SegmentSubscribersController@destroy')
        ->name('segments.subscribers.destroy');
});

Route::name('api.')->namespace('Api')->group(function()
{
    Route::post('webhooks/aws', 'AwsWebhooksController@handle')->name('webhooks.aws');
    Route::post('webhooks/mailgun', 'MailgunWebhooksController@handle')->name('webhooks.mailgun');
    Route::post('webhooks/postmark', 'PostmarkWebhooksController@handle')->name('webhooks.postmark');
    Route::post('webhooks/sendgrid', 'SendgridWebhooksController@handle')->name('webhooks.sendgrid');
});

Route::get('ping', function() {
    return 'ok';
});

