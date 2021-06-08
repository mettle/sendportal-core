<?php

namespace Sendportal\Base\Traits;

use Illuminate\Database\MySqlConnection;
use Illuminate\Database\PostgresConnection;
use Illuminate\Support\Facades\DB;

trait ResolvesDatabaseDriver
{
    /**
     * Determine whether the application is using the MySQL database driver.
     *
     * @return bool
     */
    public function usingMySQL(): bool
    {
        return DB::connection() instanceof MySqlConnection;
    }

    /**
     * Determine whether the application is using the Postgres database driver.
     *
     * @return bool
     */
    public function usingPostgres(): bool
    {
        return DB::connection() instanceof PostgresConnection;
    }
}
