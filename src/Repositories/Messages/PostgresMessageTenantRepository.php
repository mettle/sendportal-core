<?php

declare(strict_types=1);

namespace Sendportal\Base\Repositories\Messages;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PostgresMessageTenantRepository extends BaseMessageTenantRepository
{
    /**
     * @inheritDoc
     */
    public function countUniqueOpensPerPeriod(int $workspaceId, string $sourceType, int $sourceId, int $intervalInSeconds): Collection
    {
        return DB::table('messages')
            ->select(DB::raw("COUNT(*) as open_count, MIN(opened_at) as opened_at, round(extract('epoch' from opened_at) / $intervalInSeconds) * $intervalInSeconds as period_start"))
            ->where('workspace_id', $workspaceId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->whereNotNull('opened_at')
            ->groupBy(DB::raw("round(extract('epoch' from opened_at) / " . $intervalInSeconds . ")"))
            ->orderBy('opened_at')
            ->get();
    }
}
