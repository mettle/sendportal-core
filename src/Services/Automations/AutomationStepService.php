<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Automations;

use Sendportal\Base\Models\AutomationSchedule;
use Sendportal\Base\Models\AutomationStep;
use Illuminate\Support\Facades\DB;

class AutomationStepService
{
    /** @var AutomationScheduleService */
    private $automationScheduleService;

    public function __construct(AutomationScheduleService $automationScheduleService)
    {
        $this->automationScheduleService = $automationScheduleService;
    }

    public function deleteAutomationStep(AutomationStep $automationStep): bool
    {
        return DB::transaction(function () use ($automationStep) {
            /** @var AutomationSchedule $schedule */
            foreach ($automationStep->schedules as $schedule) {
                $this->automationScheduleService->deleteAutomationSchedule($schedule);
            }

            return $automationStep->delete();
        });
    }
}
