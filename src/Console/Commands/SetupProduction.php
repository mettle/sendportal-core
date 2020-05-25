<?php

declare(strict_types=1);

namespace Sendportal\Base\Console\Commands;

use Exception;
use Illuminate\Database\Console\Migrations\BaseCommand;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Sendportal\Base\Models\User;
use Sendportal\Base\Models\Workspace;
use Sendportal\Base\SendportalBaseServiceProvider;

class SetupProduction extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sp:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up the application for a production environment.';

    /** @var Migrator */
    protected $migrator;

    public function handle(): void
    {
        $this->migrator = app('migrator');

        $this->intro();
        $this->line('');
        $this->checkEnvironment();
        $this->checkApplicationKey();
        $this->checkDatabaseConnection();
        $this->checkMigrations();
        $this->checkAdminUserAccount();
        $this->checkVendorAssets();

        $this->line('');
        $this->info('Your application is ready!');
        $this->line('');
    }

    /**
     * Check that the environment file exists. If it doesn't, then prompt the user
     * to create it.
     */
    protected function checkEnvironment(): void
    {
        if (file_exists(base_path('.env'))) {
            $this->line('✅ .env file already exists');

            return;
        }

        $createFile = $this->confirm('The .env file does not yet exist. Would you like to create it now?', true);

        if ($createFile) {
            if (copy(base_path('.env.example'), base_path('.env'))) {
                $this->line('✅ .env file has been created');
                $this->call('key:generate');

                return;
            }
        }

        $this->error('The .env file must be created before you can continue.');

        exit;
    }

    /**
     * Check that the application key exists. If it doesn't then we'll
     * create it automatically
     */
    protected function checkApplicationKey(): void
    {
        if (! config('app.key')) {
            $this->call('key:generate');
        }

        $this->line('✅ Application key has been set');
    }

    /**
     * Check to see if the app can make a database connection
     */
    protected function checkDatabaseConnection(): void
    {
        try {
            DB::connection()->getPdo();
        } catch (Exception $e) {
            $this->error('A database connection could not be established. Please update your configuration and try again.');
            $this->printDatabaseConfig();
            exit();
        }

        $this->line('✅ Database connection successful');
    }

    /**
     * Check if migrations need to be run
     */
    protected function checkMigrations(): void
    {
        if (! $this->pendingMigrations()) {
            $this->line('✅ Database migrations are up to date');
            return;
        }

        if (! $this->runMigrations()) {
            $this->error("Database migrations must be run before setup can be completed.");

            exit;
        }
    }

    /**
     * Run the database migrations
     *
     * @return bool
     */
    protected function runMigrations(): bool
    {
        $runMigrations = $this->confirm("There are pending database migrations. Would you like to run migrations now?", true);

        if (! $runMigrations) {
            return false;
        }

        $this->call('migrate');
        $this->line('✅ Database migrations successful');

        return true;
    }

    /**
     * Check to see if the first admin user account has been created
     */
    protected function checkAdminUserAccount(): void
    {
        if (User::count()) {
            $this->line('✅ Admin user account exists');

            return;
        }

        $companyName = $this->getCompanyName();
        $this->createAdminUserAccount($companyName);

        $this->line('✅ Admin user account has been created');
    }

    /**
     * Prompt the user for their company/workspace name
     */
    protected function getCompanyName(): string
    {
        $this->line('');
        $this->info("Creating first admin user account and company/workspace");
        $companyName = $this->ask("Company/Workspace name");

        if (! $companyName) {
            return $this->getCompanyName();
        }

        return $companyName;
    }

    /**
     * Create the first admin user account and associate it with the company/workspace
     *
     * @param string $companyName
     * @return User
     */
    protected function createAdminUserAccount(string $companyName): User
    {
        $this->line('');
        $this->info("Create the administrator user account");

        $name = $this->getUserParam('name');
        $email = $this->getUserParam('email');
        $password = $this->getUserParam('password');

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'email_verified_at' => now(),
            'password' => Hash::make($password),
            'api_token' => Str::random(80),
        ]);

        $this->storeWorkspace($user, $companyName);

        return $user;
    }

    /**
     * Validate user input
     *
     * @param $param
     * @return string
     */
    protected function getUserParam($param): string
    {
        $validationRules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ];

        $value = $this->ask(ucfirst($param));

        $validator = Validator::make([$param => $value], [
            $param => $validationRules[$param],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->getMessages() as $error) {
                $this->line("{$error[0]}");
            }

            return $this->getUserParam($param);
        }

        return $value;
    }

    /**
     * Store the workspace
     *
     * @param User $user
     * @param string $companyName
     * @return Workspace
     */
    protected function storeWorkspace(User $user, string $companyName): Workspace
    {
        $workspace = Workspace::create([
            'name' => $companyName,
            'owner_id' => $user->id,
        ]);

        $user->workspaces()->attach($workspace->id, [
            'role' => Workspace::ROLE_OWNER,
        ]);

        return $workspace;
    }

    /**
     * Publish frontend assets
     */
    protected function checkVendorAssets(): void
    {
        $this->call('vendor:publish', [
            '--provider' => SendportalBaseServiceProvider::class,
            '--tag' => 'sendportal-assets',
            '--force' => true
        ]);

        $this->info('Published frontend assets');
    }

    /**
     * Print the database config to the console
     */
    protected function printDatabaseConfig(): void
    {
        $connection = config('database.default');

        $this->line('');
        $this->info("Database Configuration:");
        $this->line("- Connection: {$connection}");
        $this->line("- Host: " . config("database.connections.{$connection}.host"));
        $this->line("- Port: " . config("database.connections.{$connection}.port"));
        $this->line("- Database: " . config("database.connections.{$connection}.database"));
        $this->line("- Username: " . config("database.connections.{$connection}.username"));
        $this->line("- Password: " . config("database.connections.{$connection}.password"));
    }

    /**
     * Checks to see if there are any pending migrations
     *
     * @return bool
     */
    protected function pendingMigrations(): bool
    {
        $files = $this->migrator->getMigrationFiles($this->getMigrationPaths());

        return (bool)collect(array_diff(
            array_keys($files),
            $this->getPastMigrations()
        ))->count();
    }

    /**
     * Get all migrations that have previously been run
     *
     * @return array
     */
    protected function getPastMigrations(): array
    {
        if (! $this->migrator->repositoryExists()) {
            return [];
        }

        return $this->migrator->getRepository()->getRan();
    }

    /**
     * Print awesomeness
     */
    protected function intro(): void
    {
        $this->line('');
        $this->line(' ____                 _ ____            _        _ ');
        $this->line('/ ___|  ___ _ __   __| |  _ \ ___  _ __| |_ __ _| |');
        $this->line('\___ \ / _ \ \'_ \ / _` | |_) / _ \| \'__| __/ _` | |');
        $this->line(' ___) |  __/ | | | (_| |  __/ (_) | |  | || (_| | |');
        $this->line('|____/ \___|_| |_|\__,_|_|   \___/|_|   \__\__,_|_|');
    }
}
