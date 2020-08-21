<?php

namespace Sendportal\Base\Setup;

use Illuminate\Support\Facades\Artisan;

class Migrations implements StepInterface
{
    const VIEW = 'sendportal::setup.steps.migrations';

    public function check(): bool
    {
        $migrator = app('migrator');

        $files = $migrator->getMigrationFiles($migrator->paths());

        return count(array_diff(array_keys($files), $this->getPastMigrations($migrator))) === 0;
    }

    protected function getPastMigrations($migrator): array
    {
        if (!$migrator->repositoryExists()) {
            return [];
        }

        return $migrator->getRepository()->getRan();
    }

    public function run(?array $input): bool
    {
        Artisan::call('migrate');

        return true;
    }
}