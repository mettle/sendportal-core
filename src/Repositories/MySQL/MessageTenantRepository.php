<?php

namespace Sendportal\Base\Repositories\MySQL;

use Illuminate\Support\Facades\DB;
use Sendportal\Base\Repositories\BaseMessageTenantRepository;

class MessageTenantRepository extends BaseMessageTenantRepository
{
    /**
     * Count the number of unique open per period for a campaign or automation schedule
     *
     * @param int $workspaceId
     * @param string $sourceType
     * @param int $sourceId
     * @param int $intervalInSeconds
     * @return mixed
     * @throws \Exception
     */
    public function countUniqueOpensPerPeriod($workspaceId, $sourceType, $sourceId, $intervalInSeconds)
    {
        $intervalInSeconds = (int)$intervalInSeconds;

        return DB::table('messages')
            ->select(DB::raw('COUNT(*) as open_count, MIN(opened_at) as opened_at, FROM_UNIXTIME(MIN(UNIX_TIMESTAMP(opened_at) DIV '.$intervalInSeconds.') * '.$intervalInSeconds.') as period_start'))
            ->where('workspace_id', $workspaceId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->whereNotNull('opened_at')
            ->groupBy(DB::raw("UNIX_TIMESTAMP(opened_at) DIV " . $intervalInSeconds))
            ->orderBy('opened_at')
            ->get();
    }
}
