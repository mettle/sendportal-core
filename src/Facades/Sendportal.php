<?php

namespace Sendportal\Base\Facades;

use Illuminate\Support\Facades\Facade;

class Sendportal extends Facade
{

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function publicApiRoutes()
    {
        static::$app->make('router')->sendportalPublicApiRoutes();
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function apiRoutes()
    {
        static::$app->make('router')->sendportalPublicApiRoutes();
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function publicWebRoutes()
    {
        static::$app->make('router')->sendportalPublicWebRoutes();
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function webRoutes()
    {
        static::$app->make('router')->sendportalWebRoutes();
    }

    /**
     * @param callable $resolver
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function currentWorkspaceIdResolver(callable $resolver)
    {
        static::$app->make('sendportal.resolver')->setCurrentWorkspaceIdResolver($resolver);
    }

    /**
     * @return int|null
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function currentWorkspaceId():?int
    {
        return static::$app->make('sendportal.resolver')->resolveCurrentWorkspaceId();
    }

    /**
     * @param callable $resolver
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function siderbarHtmlContentResolver(callable $resolver)
    {
        static::$app->make('sendportal.resolver')->setSiderbarHtmlContentResolver($resolver);
    }

    /**
     * @return string|null
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function siderbarHtmlContent():?string
    {
        return static::$app->make('sendportal.resolver')->resolveSiderbarHtmlContent();
    }

    /**
     * @param callable $resolver
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function headerHtmlContentResolver(callable $resolver)
    {
        static::$app->make('sendportal.resolver')->setHeaderHtmlContentResolver($resolver);
    }

    /**
     * @return string|null
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function headerHtmlContent():?string
    {
        return static::$app->make('sendportal.resolver')->resolveHeaderHtmlContent();
    }
}
