<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Automations;

use Sendportal\Base\Models\Automation;
use Sendportal\Base\Models\AutomationStep;
use Illuminate\Support\Facades\DB;

class AutomationService
{
    /** @var AutomationStepService */
    private $automationStepService;

    public function __construct(AutomationStepService $automationStepService)
    {
        $this->automationStepService = $automationStepService;
    }

    public function deleteAutomation(Automation $automation): bool
    {
        return DB::transaction(function () use ($automation) {
            /** @var AutomationStep $step */
            foreach ($automation->automation_steps as $step) {
                $this->automationStepService->deleteAutomationStep($step);
            }

            return $automation->delete();
        });
    }
}
