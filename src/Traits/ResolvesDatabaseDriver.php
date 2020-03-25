<?php

namespace Sendportal\Base\Traits;

trait ResolvesDatabaseDriver
{
    /**
     * Determine whether the application is using the MySQL database driver.
     *
     * @return bool
     */
    public function usingMySQL(): bool
    {
        return $this->getDatabaseDriver() === 'mysql';
    }

    /**
     * Determine whether the application is using the Postgres database driver.
     *
     * @return bool
     */
    public function usingPostgres(): bool
    {
        return $this->getDatabaseDriver() === 'pgsql';
    }

    /**
     * Get the database driver.
     *
     * @return string
     */
    protected function getDatabaseDriver(): string
    {
        $connection = config('database.default');

        return config('database.connections.'.$connection.'.driver');
    }
}
