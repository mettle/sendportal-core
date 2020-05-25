<?php

namespace Sendportal\Base\Traits;

use Closure;
use Illuminate\Support\Arr;

trait ThrottlesSending
{
    protected function throttleSending(Closure $closure)
    {
        $throttleCacheKey = $this->resolveThrottleCacheKey();

        if ($this->getSentCount($throttleCacheKey) >= $this->getSendRate()) {
            sleep(1);
        }

        $result = $closure();

        cache()->increment($throttleCacheKey);

        return $result;
    }

    abstract function getSendRate();

    protected function getSentCount(string $cacheKey)
    {
        return cache()->remember($cacheKey, 1, function () {
            return 0;
        });
    }

    protected function resolveThrottleCacheKey(): string
    {
        return sprintf('%s%s%d', 'spThrottleSentCount', Arr::get($this->config, 'key'), time());
    }
}
