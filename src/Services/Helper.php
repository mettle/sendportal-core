<?php

namespace Sendportal\Base\Services;

use Carbon\Carbon;
use Sendportal\Base\Models\Workspace;

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
        if (!$date) {
            return null;
        }

        return Carbon::parse($date)->copy()->setTimezone($timezone);
    }

    public function getCurrentWorkspace(): ?Workspace
    {
        return auth()->user()->currentWorkspace();
    }

    public function isPro(): bool
    {
        return class_exists(\Sendportal\Pro\SendportalProServiceProvider::class);
    }
}
