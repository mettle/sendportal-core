<?php

declare(strict_types=1);

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Sendportal\Base\Http\Middleware\OwnsCurrentWorkspace;

// Auth.
// TODO(david): we need a way to turn off auth for the situations where `sendportal/base` is getting included in other packages that already have auth.
Route::middleware('web')->namespace('\Sendportal\Base\Http\Controllers')->group(static function () {
    Auth::routes(['verify' => true, 'register' => true]); // config('auth.enable_register')]);
});

Route::middleware('web')->namespace('\Sendportal\Base\Http\Controllers')->name('sendportal.')->group(static function (Router $router) {

    // App.
    $router->middleware(['auth', 'verified'])->group(static function (Router $appRouter) {
        // Auth.
        $appRouter->namespace('Auth')->group(static function (Router $authRouter) {
            // Logout.
            $authRouter->get('logout', ['LoginController@logout'])->name('sendportal.logout');

            // Profile.
            $authRouter->name('profile.')->prefix('profile')->group(static function (Router $profileRouter) {
                $profileRouter->get('/', 'ProfileController@edit')->name('edit');
                $profileRouter->put('/', 'ProfileController@update')->name('update');
            });
        });

        // Dashboard
        $appRouter->get('dashboard', 'DashboardController@index')->name('dashboard');
        $appRouter->get('/', static function () {
            return redirect()->route('sendportal.campaigns.index');
        });

        // Campaigns
        Route::resource('campaigns', 'Campaigns\CampaignsController')->except(['destroy']);
        Route::prefix('campaigns')->namespace('Campaigns')->group(function () {
            Route::get('{id}/preview', 'CampaignsController@preview')->name('campaigns.preview');
            Route::put('{id}/send', 'CampaignDispatchController@send')->name('campaigns.send');
            Route::get('{id}/status', 'CampaignsController@status')->name('campaigns.status');
            Route::post('{id}/test', 'CampaignTestController@handle')->name('campaigns.test');

            Route::get('{id}/confirm-delete', 'CampaignDeleteController@confirm')->name('campaigns.destroy.confirm');
            Route::delete('', 'CampaignDeleteController@destroy')->name('campaigns.destroy');

            Route::get('{id}/duplicate', 'CampaignDuplicateController@duplicate')->name('campaigns.duplicate');

            Route::get('{id}/report', 'CampaignReportsController@index')->name('campaigns.reports.index');
            Route::get('{id}/report/recipients', 'CampaignReportsController@recipients')
                ->name('campaigns.reports.recipients');
            Route::get('{id}/report/opens', 'CampaignReportsController@opens')->name('campaigns.reports.opens');
            Route::get('{id}/report/clicks', 'CampaignReportsController@clicks')->name('campaigns.reports.clicks');
            Route::get('{id}/report/unsubscribes', 'CampaignReportsController@unsubscribes')
                ->name('campaigns.reports.unsubscribes');
            Route::get('{id}/report/bounces', 'CampaignReportsController@bounces')->name('campaigns.reports.bounces');
        });

        // Messages
        Route::get('messages', ['as' => 'messages.index', 'uses' => 'MessagesController@index']);
        Route::get('messages/draft', ['as' => 'messages.draft', 'uses' => 'MessagesController@draft']);
        Route::get('messages/{id}/show', ['as' => 'messages.show', 'uses' => 'MessagesController@show']);
        Route::post('messages/send', ['as' => 'messages.send', 'uses' => 'MessagesController@send']);

        // Providers
        Route::get('providers', ['as' => 'providers.index', 'uses' => 'ProvidersController@index']);
        Route::get('providers/create', ['as' => 'providers.create', 'uses' => 'ProvidersController@create']);
        Route::get('providers/type/{id}',
            ['as' => 'providers.ajax', 'uses' => 'ProvidersController@providersTypeAjax']);
        Route::post('providers', ['as' => 'providers.store', 'uses' => 'ProvidersController@store']);
        Route::get('providers/{id}/edit', ['as' => 'providers.edit', 'uses' => 'ProvidersController@edit']);
        Route::post('providers/{id}', ['as' => 'providers.update', 'uses' => 'ProvidersController@update']);
        Route::delete('providers/{id}', ['as' => 'providers.delete', 'uses' => 'ProvidersController@delete']);

        // Segments
        Route::resource('segments', 'SegmentsController');

        // Settings.
        $appRouter->prefix('settings')->group(static function (Router $settingsRouter) {
            $settingsRouter->get('', 'SettingsController@index')->name('settings.index');

            // Workspace User Management.
            $settingsRouter->namespace('Workspaces')
                ->middleware(OwnsCurrentWorkspace::class)
                ->name('settings.users.')
                ->prefix('users')
                ->group(static function (Router $workspacesRouter) {
                    $workspacesRouter->get('/', 'WorkspaceUsersController@index')->name('index');
                    $workspacesRouter->delete('{userId}', 'WorkspaceUsersController@destroy')->name('destroy');

                    // Invitations.
                    $workspacesRouter->name('invitations.')->prefix('invitations')
                        ->group(static function (Router $invitationsRouter) {
                            $invitationsRouter->post('/', 'WorkspaceInvitationsController@store')->name('store');
                            $invitationsRouter->delete('{invitation}', 'WorkspaceInvitationsController@destroy')
                                ->name('destroy');
                        });
                });

            $settingsRouter->resource('templates', 'TemplatesController');
        });

        // Subscribers
        Route::get('subscribers/export', ['as' => 'subscribers.export', 'uses' => 'SubscribersController@export']);
        Route::get('subscribers/import', ['as' => 'subscribers.import', 'uses' => 'SubscribersImportController@show']);
        Route::post('subscribers/import',
            ['as' => 'subscribers.import.store', 'uses' => 'SubscribersImportController@store']);
        Route::resource('subscribers', 'SubscribersController');

        // Templates
        Route::resource('templates', 'TemplatesController')->except(['show']);

        // Ajax
        Route::namespace('Ajax')->prefix('ajax')->group(function () {
            Route::post('segments/store', 'SegmentsController@store')->name('ajax.segments.store');
        });
    });

    // Workspace Management.
    Route::namespace('Workspaces')->middleware(['auth', 'verified'])->group(static function (Router $workspaceRouter) {
        $workspaceRouter->resource('workspaces', 'WorkspacesController')->except([
            'create',
            'show',
            'destroy',
        ]);

        // Workspace Switching.
        $workspaceRouter->get('workspaces/{workspace}/switch', 'SwitchWorkspaceController@switch')->name('workspaces.switch');

        // Invitations.
        $workspaceRouter->post('workspaces/invitations/{invitation}/accept', 'PendingInvitationController@accept')
            ->name('workspaces.invitations.accept');
        $workspaceRouter->post('workspaces/invitations/{invitation}/reject', 'PendingInvitationController@reject')
            ->name('workspaces.invitations.reject');
    });

    // Subscriptions
    Route::name('subscriptions.')->namespace('Subscriptions')->prefix('subscriptions')->group(function () {
        Route::get('unsubscribe/{messageHash}', 'SubscriptionsController@unsubscribe')->name('unsubscribe');
        Route::get('subscribe/{messageHash}', 'SubscriptionsController@subscribe')->name('subscribe');
        Route::put('subscriptions/{messageHash}', 'SubscriptionsController@update')->name('update');
    });

    // Webview.
    Route::prefix('webview')->name('webview.')->namespace('Webview')->group(static function (Router $webviewRouter) {
        $webviewRouter->get('{messageHash}', 'WebviewController@show')->name('show');
    });

});
