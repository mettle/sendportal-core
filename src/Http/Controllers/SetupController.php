<?php

namespace Sendportal\Base\Http\Controllers;

use Illuminate\Database\Migrations\Migrator;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SetupController extends Controller
{
    /**
     * @return View|RedirectResponse
     */
    public function index()
    {
        $migrator = app('migrator');
        $files = $migrator->getMigrationFiles($migrator->paths());

        $pendingMigrations = (bool)collect(array_diff(
            array_keys($files),
            $this->getPastMigrations($migrator)
        ))->count();

        if (! $pendingMigrations) {
            return redirect()->to('/');
        }

        return view('sendportal::setup.index');
    }

    /**
     * Get all migrations that have previously been run
     *
     * @param Migrator $migrator
     * @return array
     */
    protected function getPastMigrations(Migrator $migrator): array
    {
        if (!$migrator->repositoryExists()) {
            return [];
        }

        return $migrator->getRepository()->getRan();
    }
}
