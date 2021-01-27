<?php

declare(strict_types=1);

namespace Sendportal\Base\Facades;

use Illuminate\Support\Facades\Facade;

class Sendportal extends Facade
{
    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function publicApiRoutes(): void
    {
        static::$app->make('router')->sendportalPublicApiRoutes();
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function apiRoutes(): void
    {
        static::$app->make('router')->sendportalApiRoutes();
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function publicWebRoutes(): void
    {
        static::$app->make('router')->sendportalPublicWebRoutes();
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function webRoutes(): void
    {
        static::$app->make('router')->sendportalWebRoutes();
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function currentWorkspaceIdResolver(callable $resolver): void
    {
        static::$app->make('sendportal.resolver')->setCurrentWorkspaceIdResolver($resolver);
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function currentWorkspaceId(): ?int
    {
        return static::$app->make('sendportal.resolver')->resolveCurrentWorkspaceId();
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function sidebarHtmlContentResolver(callable $resolver): void
    {
        static::$app->make('sendportal.resolver')->setSidebarHtmlContentResolver($resolver);
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function sidebarHtmlContent(): ?string
    {
        return static::$app->make('sendportal.resolver')->resolveSidebarHtmlContent();
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function headerHtmlContentResolver(callable $resolver): void
    {
        static::$app->make('sendportal.resolver')->setHeaderHtmlContentResolver($resolver);
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function headerHtmlContent(): ?string
    {
        return static::$app->make('sendportal.resolver')->resolveHeaderHtmlContent();
    }
}
