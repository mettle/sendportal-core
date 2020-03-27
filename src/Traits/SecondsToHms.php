<?php

declare(strict_types=1);

namespace Sendportal\Base\Traits;

trait SecondsToHms
{
    /**
     * @param int|string $seconds
     * @return string
     */
    protected function secondsToHms($seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor($seconds / 60) % 60;
        $seconds = $seconds % 60;

        return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    }
}
