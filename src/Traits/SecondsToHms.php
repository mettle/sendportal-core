<?php

declare(strict_types=1);

namespace Sendportal\Base\Traits;

trait SecondsToHms
{
    protected function secondsToHms(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor($seconds / 60) % 60;
        $seconds = $seconds % 60;

        return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    }
}
