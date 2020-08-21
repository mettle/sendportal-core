<?php

namespace Sendportal\Base\Setup;

class Env implements StepInterface
{
    const VIEW = 'sendportal::setup.steps.env';

    public function check(): bool
    {
        if (file_exists(base_path('.env'))) {
            return true;
        }

        return false;
    }

    public function run(?array $input): bool
    {
        copy(base_path('.env.example'), base_path('.env'));

        return true;
    }
}