<?php

declare(strict_types=1);

namespace Sendportal\Base\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void publicApiRoutes
 * @method static void apiRoutes
 * @method static void publicWebRoutes
 * @method static void webRoutes
 * @method static void setCurrentWorkspaceIdResolver(callable $resolver)
 * @method static int|null currentWorkspaceId
 * @method static void setSidebarHtmlContentResolver(callable $resolver)
 * @method static string|null sidebarHtmlContent
 * @method static void setHeaderHtmlContentResolver(callable $resolver)
 * @method static string|null headerHtmlContent
 */
class Sendportal extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'sendportal';
    }
}
