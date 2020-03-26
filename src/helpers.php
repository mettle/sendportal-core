<?php

use Sendportal\Base\Models\User;
use Carbon\Carbon;
use Sendportal\Automations\AutomationsServiceProvider;

/**
 * @return bool
 */
function automationsEnable(): bool
{
    return class_exists(Sendportal\Automations\AutomationsServiceProvider::class);
}

/**
 * Display a given date in the active user's timezone.
 *
 * @param mixed $date
 * @param string $timezone
 * @return mixed
 */
function displayDate($date, string $timezone = null)
{
    if (!$date) {
        return null;
    }

    if (!$timezone) {
        $timezone = auth()->user()->timezone;
    }

    return Carbon::parse($date)->copy()->setTimezone($timezone);
}
