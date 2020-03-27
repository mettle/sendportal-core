<?php

namespace Sendportal\Base\Repositories\Messages;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MySqlMessageTenantRepository extends BaseMessageTenantRepository
{
    /**
     * @inheritDoc
     */
    public function countUniqueOpensPerPeriod(int $workspaceId, string $sourceType, int $sourceId, int $intervalInSeconds): Collection
    {
        return DB::table('messages')
            ->select(DB::raw('COUNT(*) as open_count, MIN(opened_at) as opened_at, FROM_UNIXTIME(MIN(UNIX_TIMESTAMP(opened_at) DIV ' . $intervalInSeconds . ') * ' . $intervalInSeconds . ') as period_start'))
            ->where('workspace_id', $workspaceId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->whereNotNull('opened_at')
            ->groupBy(DB::raw('UNIX_TIMESTAMP(opened_at) DIV ' . $intervalInSeconds))
            ->orderBy('opened_at')
            ->get();
    }
}
