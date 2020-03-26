<?php

declare(strict_types=1);

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// TODO(david): look into whether we have a cleaner way of setting up the namespaces and middlewares for the routes.
Route::middleware('web')->namespace('\Sendportal\Base\Http\Controllers')->group(static function (Router $router) {

    // TODO(david): we may want a way to disable the auth routes in sendportal, to allow for auth from other places?
    //  E.g. if the `sendportal/base` package is included in another project that already has its own auth setup.
    //  Though, that in itself opens up issues around setting up teams, etc, that we currently rely on.

    // Auth.
    Auth::routes(['verify' => true, 'register' => true]); // config('auth.enable_register')]);

    // App.
    $router->middleware(['auth', 'verified'])->group(static function (Router $appRouter) {
        // Auth.
        $appRouter->namespace('Auth')->group(static function (Router $authRouter) {
            // Logout.
            $authRouter->get('logout', ['LoginController@logout'])->name('logout');

            // Profile.
            $authRouter->name('profile.')->prefix('profile')->group(static function (Router $profileRouter) {
                $profileRouter->get('/', 'ProfileController@edit')->name('edit');
                $profileRouter->put('/', 'ProfileController@update')->name('update');
            });
        });

        // Dashboard
        $appRouter->get('dashboard', 'DashboardController@index')->name('dashboard');
        $appRouter->get('/', static function () {
            return redirect()->route('campaigns.index');
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

            // Team User Management.
            $settingsRouter->namespace('Teams')
                ->middleware('ownsCurrentTeam')
                ->name('settings.users.')
                ->prefix('users')
                ->group(static function (Router $teamsRouter) {
                    $teamsRouter->get('/', 'TeamUsersController@index')->name('index');
                    $teamsRouter->delete('{userId}', 'TeamUsersController@destroy')->name('destroy');

                    // Invitations.
                    $teamsRouter->name('invitations.')->prefix('invitations')
                        ->group(static function (Router $invitationsRouter) {
                            $invitationsRouter->post('/', 'TeamInvitationsController@store')->name('store');
                            $invitationsRouter->delete('{invitation}', 'TeamInvitationsController@destroy')
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

    // Team Management.
    Route::namespace('Teams')->middleware(['auth', 'verified'])->group(static function (Router $teamRouter) {
        $teamRouter->resource('workspaces', 'WorkspacesController')->except([
            'create',
            'show',
            'destroy',
        ]);

        // Team Switching.
        $teamRouter->get('workspaces/{team}/switch', 'SwitchWorkspaceController@switch')->name('workspaces.switch');

        // Invitations.
        $teamRouter->post('teams/invitations/{invitation}/accept', 'PendingInvitationController@accept')
            ->name('teams.invitations.accept');
        $teamRouter->post('teams/invitations/{invitation}/reject', 'PendingInvitationController@reject')
            ->name('teams.invitations.reject');
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
