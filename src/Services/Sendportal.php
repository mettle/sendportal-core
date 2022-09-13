<?php

declare(strict_types=1);

namespace Sendportal\Base\Services;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;

class Sendportal
{
    /** @var Application */
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @throws BindingResolutionException
     */
    public function publicApiRoutes(): void
    {
        $this->app->make('router')->sendportalPublicApiRoutes();
    }

    /**
     * @throws BindingResolutionException
     */
    public function apiRoutes(): void
    {
        $this->app->make('router')->sendportalApiRoutes();
    }

    /**
     * @throws BindingResolutionException
     */
    public function publicWebRoutes(): void
    {
        $this->app->make('router')->sendportalPublicWebRoutes();
    }

    /**
     * @throws BindingResolutionException
     */
    public function webRoutes(): void
    {
        $this->app->make('router')->sendportalWebRoutes();
    }

    /**
     * @throws BindingResolutionException
     */
    public function setCurrentWorkspaceIdResolver(callable $resolver): void
    {
        $this->app->make('sendportal.resolver')->setCurrentWorkspaceIdResolver($resolver);
    }

    /**
     * @throws BindingResolutionException
     */
    public function currentWorkspaceId(): ?int
    {
        return $this->app->make('sendportal.resolver')->resolveCurrentWorkspaceId();
    }

    /**
     * @throws BindingResolutionException
     */
    public function currentWorkspaceName(): ?string
    {
        return $this->app->make('sendportal.resolver')->resolveCurrentWorkspaceName();
    }

    /**
     * @throws BindingResolutionException
     */
    public function setSidebarHtmlContentResolver(callable $resolver): void
    {
        $this->app->make('sendportal.resolver')->setSidebarHtmlContentResolver($resolver);
    }

    /**
     * @throws BindingResolutionException
     */
    public function sidebarHtmlContent(): ?string
    {
        return $this->app->make('sendportal.resolver')->resolveSidebarHtmlContent();
    }

    /**
     * @throws BindingResolutionException
     */
    public function setHeaderHtmlContentResolver(callable $resolver): void
    {
        $this->app->make('sendportal.resolver')->setHeaderHtmlContentResolver($resolver);
    }

    /**
     * @throws BindingResolutionException
     */
    public function headerHtmlContent(): ?string
    {
        return $this->app->make('sendportal.resolver')->resolveHeaderHtmlContent();
    }
}
