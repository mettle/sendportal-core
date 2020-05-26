<?php

namespace Sendportal\Base\Traits;

use Aws\Ses\Exception\SesException;
use Closure;

trait ThrottlesSending
{
    protected function throttleSending(Closure $closure)
    {
        $attempt = 0;

        while ($attempt < 10) {
            try {
                $attempt++;

                return $closure();
            } catch (SesException $e) {
                if ($e->getMessage() == 'Maximum sending rate exceeded.') {
                    $sleepDuration = $this->resolveSleepDuration($attempt);

                    info("Maximum send rate exceeded. Sleeping for {$sleepDuration}");

                    usleep($sleepDuration);
                } else {
                    throw $e;
                }
            }
        }
    }

    protected function resolveSleepDuration(int $attempt = 1, int $minSleepMilli = 10, int $maxSleepMilli = 5000): int
    {
        $sleepDuration = $minSleepMilli * ($attempt ** 2);

        // usleep() uses microseconds
        // rather than milliseconds
        $sleepDuration *= 1000;
        $maxSleepMilli *= 1000;

        return min($sleepDuration, $maxSleepMilli);
    }
}
