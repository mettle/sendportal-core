<?php

namespace Sendportal\Base\Services;

use Carbon\Carbon;

class Helper
{
    /**
     * Display a given date in the active user's timezone.
     *
     * @param mixed $date
     * @param string $timezone
     * @return mixed
     */
    public function displayDate($date, string $timezone = null)
    {
        if (! $date) {
            return null;
        }

        return Carbon::parse($date)->copy()->tz($timezone);
    }

    public function isPro(): bool
    {
        return class_exists(\Sendportal\Pro\SendportalProServiceProvider::class);
    }
}
