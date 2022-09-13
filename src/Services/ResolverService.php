<?php

declare(strict_types=1);

namespace Sendportal\Base\Services;

use Illuminate\Support\Arr;

class ResolverService
{
    /** @var array */
    private $resolvers = [];

    public function setHeaderHtmlContentResolver(callable $callable): void
    {
        $this->setResolver('header', $callable);
    }

    public function resolveHeaderHtmlContent(): ?string
    {
        if ($resolver = $this->getResolver('header')) {
            return $resolver();
        }

        return null;
    }

    public function setSidebarHtmlContentResolver(callable $callable): void
    {
        $this->setResolver('sidebar', $callable);
    }

    public function resolveSidebarHtmlContent(): ?string
    {
        if ($resolver = $this->getResolver('sidebar')) {
            return $resolver();
        }

        return null;
    }

    public function setCurrentWorkspaceIdResolver(callable $callable): void
    {
        $this->setResolver('workspace', $callable);
    }

    public function resolveCurrentWorkspaceId(): ?int
    {
        $resolver = $this->getResolver('workspace');

        return $resolver();
    }

    public function resolveCurrentWorkspaceName()
    {
        return 'julipels';
    }

    private function getResolver(string $resolverName): ?callable
    {
        return Arr::get($this->resolvers, $resolverName);
    }

    private function setResolver(string $resolverName, callable $callable): void
    {
        $this->resolvers[$resolverName] = $callable;
    }
}
