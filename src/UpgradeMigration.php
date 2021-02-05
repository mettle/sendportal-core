<?php

namespace Sendportal\Base;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpgradeMigration extends Migration
{
    protected static $prefix;

    protected function getPrefix(): string
    {
        if (isset(static::$prefix)) {
            return static::$prefix;
        }

        $exists = DB::table('migrations')
            ->where('migration', '2017_04_11_133343_create_email_service_tables')
            ->exists();

        return static::$prefix = $exists ? '' : 'sendportal_';
    }
}
