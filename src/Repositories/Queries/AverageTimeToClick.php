<?php

namespace Sendportal\Base\Repositories\Queries;

use Illuminate\Database\MySqlConnection;
use Illuminate\Database\PostgresConnection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AverageTimeToClick
{
    public static function compile(string $alias)
    {
        if (DB::connection() instanceof MySqlConnection) {
            return "ROUND(AVG(TIMESTAMPDIFF(SECOND, delivered_at, clicked_at))) as $alias";
        }

        if (DB::connection() instanceof PostgresConnection) {
            return "ROUND(AVG(EXTRACT(EPOCH FROM (clicked_at - delivered_at)))) as $alias";
        }

        throw new RuntimeException(get_class(DB::connection()) . ' not supported');
    }
}