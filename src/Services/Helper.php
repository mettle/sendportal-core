<?php

namespace Sendportal\Base\Services;

use Carbon\Carbon;

class Helper
{
    /**
     * Display a given date in the active user's timezone.
     *
     * @param mixed $date
     * @param string|null $timezone
     * @return mixed
     */
    public function displayDate($date, ?string $timezone = null)
    {
        if (! $date) {
            return null;
        }

        if (! $timezone) {
            $timezone = config('app.timezone');
        }

        return Carbon::parse($date)->copy()->tz($timezone);
    }

    public function isPro(): bool
    {
        return class_exists(\Sendportal\Pro\SendportalProServiceProvider::class);
    }
}
