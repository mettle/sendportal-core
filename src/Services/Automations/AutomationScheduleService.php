<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Automations;

use Sendportal\Base\Models\AutomationSchedule;
use Exception;

class AutomationScheduleService
{
    /**
     * @throws Exception
     */
    public function deleteAutomationSchedule(AutomationSchedule $schedule): bool
    {
        return $schedule->delete();
    }
}
