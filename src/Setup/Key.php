<?php

namespace Sendportal\Base\Setup;

use Illuminate\Support\Facades\Artisan;

class Key implements StepInterface
{
    const VIEW = 'sendportal::setup.steps.key';

    public function check(): bool
    {
        if (config('app.key')) {
            return true;
        }

        return false;
    }

    public function run(?array $input): bool
    {
        return (bool) Artisan::call('key:generate');
    }
}
