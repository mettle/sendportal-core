<?php

namespace Sendportal\Base\Setup;

class Env implements StepInterface
{
    const VIEW = 'sendportal::setup.steps.env';

    /**
     * {@inheritDoc}
     */
    public function check(): bool
    {
        return file_exists(base_path('.env'));
    }

    /**
     * {@inheritDoc}
     */
    public function run(?array $input): bool
    {
        return copy(base_path('.env.example'), base_path('.env'));
    }
}
