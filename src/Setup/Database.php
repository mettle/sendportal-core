<?php

namespace Sendportal\Base\Setup;

use Exception;
use Illuminate\Support\Facades\DB;

class Database implements StepInterface
{
    const VIEW = 'sendportal::setup.steps.database';

    public function check(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function run(?array $input): bool
    {
        return true;
    }
}