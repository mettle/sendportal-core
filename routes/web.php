<?php

declare(strict_types=1);

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Sendportal\Base\Http\Middleware\OwnsCurrentWorkspace;

Route::middleware('web')->namespace('\Sendportal\Base\Http\Controllers')->group(static function () {
    Auth::routes([
        'verify' => config('sendportal.auth.register', false),
        'register' => config('sendportal.auth.register', false),
        'reset' => config('sendportal.auth.password_reset'),
    ]);
});

Route::middleware('web')->namespace('\Sendportal\Base\Http\Controllers')->name('sendportal.')->group(static function (Router $router) {

    // Auth.
    $router->middleware('auth')->namespace('Auth')->group(static function (Router $authRouter) {
        // Logout.
        $authRouter->get('logout', 'LoginController@logout')->name('sendportal.logout');

        // Profile.
        $authRouter->middleware('verified')->name('profile.')->prefix('profile')->group(static function (
            Router $profileRouter
        ) {
            $profileRouter->get('/', 'ProfileController@show')->name('show');
            $profileRouter->get('/edit', 'ProfileController@edit')->name('edit');
            $profileRouter->put('/', 'ProfileController@update')->name('update');
        });
    });

    // App.
    $router->middleware(['auth', 'verified'])->group(static function (Router $appRouter) {

        // Dashboard.
        $appRouter->get('/', 'DashboardController@index')->name('dashboard');

        // Campaigns.
        $appRouter->resource('campaigns', 'Campaigns\CampaignsController')->except(['destroy']);
        $appRouter->name('campaigns.')->prefix('campaigns')->namespace('Campaigns')->group(static function (Router $campaignRouter) {
            $campaignRouter->get('{id}/preview', 'CampaignsController@preview')->name('preview');
            $campaignRouter->put('{id}/send', 'CampaignDispatchController@send')->name('send');
            $campaignRouter->get('{id}/status', 'CampaignsController@status')->name('status');
            $campaignRouter->post('{id}/test', 'CampaignTestController@handle')->name('test');

            $campaignRouter->get('{id}/confirm-delete', 'CampaignDeleteController@confirm')->name('destroy.confirm');
            $campaignRouter->delete('', 'CampaignDeleteController@destroy')->name('destroy');

            $campaignRouter->get('{id}/duplicate', 'CampaignDuplicateController@duplicate')->name('duplicate');

            $campaignRouter->get('{id}/report', 'CampaignReportsController@index')->name('reports.index');
            $campaignRouter->get('{id}/report/recipients', 'CampaignReportsController@recipients')
                ->name('reports.recipients');
            $campaignRouter->get('{id}/report/opens', 'CampaignReportsController@opens')->name('reports.opens');
            $campaignRouter->get('{id}/report/clicks', 'CampaignReportsController@clicks')->name('reports.clicks');
            $campaignRouter->get('{id}/report/unsubscribes', 'CampaignReportsController@unsubscribes')
                ->name('reports.unsubscribes');
            $campaignRouter->get('{id}/report/bounces', 'CampaignReportsController@bounces')->name('reports.bounces');
        });

        // Messages.
        $appRouter->name('messages.')->prefix('messages')->group(static function (Router $messageRouter) {
            $messageRouter->get('/', 'MessagesController@index')->name('index');
            $messageRouter->get('draft', 'MessagesController@draft')->name('draft');
            $messageRouter->get('{id}/show', 'MessagesController@show')->name('show');
            $messageRouter->post('send', 'MessagesController@send')->name('send');
            $messageRouter->post('send-selected', 'MessagesController@sendSelected')->name('send-selected');
        });

        // Email Services.
        $appRouter->name('email_services.')->prefix('email-services')->namespace('EmailServices')->group(static function (Router $servicesRouter) {
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

        // Segments.
        $appRouter->resource('segments', 'Segments\SegmentsController')->except(['show']);

        // Workspace User Management.
        $appRouter->namespace('Workspaces')
            ->middleware(OwnsCurrentWorkspace::class)
            ->name('users.')
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

        // Ajax.
        $appRouter->name('ajax.')->prefix('ajax')->namespace('Ajax')->group(static function (Router $ajaxRouter) {
            $ajaxRouter->post('segments/store', 'SegmentsController@store')->name('segments.store');

            $ajaxRouter->post('templates/file', 'FileController@store')->name('file.store');
        });

        // Workspace Management.
        $appRouter->namespace('Workspaces')->middleware([
            'auth',
            'verified'
        ])->group(static function (Router $workspaceRouter) {
            $workspaceRouter->resource('workspaces', 'WorkspacesController')->except([
                'create',
                'show',
                'destroy',
            ]);

            // Workspace Switching.
            $workspaceRouter->get('workspaces/{workspace}/switch', 'SwitchWorkspaceController@switch')
                ->name('workspaces.switch');

            // Invitations.
            $workspaceRouter->post('workspaces/invitations/{invitation}/accept', 'PendingInvitationController@accept')
                ->name('workspaces.invitations.accept');
            $workspaceRouter->post('workspaces/invitations/{invitation}/reject', 'PendingInvitationController@reject')
                ->name('workspaces.invitations.reject');
        });
    });

    // Subscriptions
    $router->name('subscriptions.')->namespace('Subscriptions')->prefix('subscriptions')->group(static function (Router $subscriptionController) {
        $subscriptionController->get('unsubscribe/{messageHash}', 'SubscriptionsController@unsubscribe')
            ->name('unsubscribe');
        $subscriptionController->get('subscribe/{messageHash}', 'SubscriptionsController@subscribe')->name('subscribe');
        $subscriptionController->put('subscriptions/{messageHash}', 'SubscriptionsController@update')->name('update');
    });

    // Webview.
    $router->name('webview.')->prefix('webview')->namespace('Webview')->group(static function (Router $webviewRouter) {
        $webviewRouter->get('{messageHash}', 'WebviewController@show')->name('show');
    });
});
