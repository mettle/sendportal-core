<?php

namespace Sendportal\Base\Repositories\Messages;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SqliteMessageTenantRepository extends BaseMessageTenantRepository
{
    /**
     * @inheritDoc
     */
    public function countUniqueOpensPerPeriod(int $workspaceId, string $sourceType, int $sourceId, int $intervalInSeconds): Collection
    {
        $data = DB::table('sendportal_messages')
            ->selectRaw('COUNT(*) as open_count, MIN(opened_at) as opened_at, datetime(MIN(strftime("%s", opened_at) / ' . $intervalInSeconds . ') * ' . $intervalInSeconds . ', "unixepoch") as period_start')
            ->where('workspace_id', $workspaceId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->whereNotNull('opened_at')
            ->groupByRaw('strftime("%s", opened_at) / ' . $intervalInSeconds)
            ->orderBy('opened_at')
            ->get();

        return $data->map(function ($item, $k) {
            $item->open_count = (int) $item->open_count;

            return $item;
        });
    }
}
