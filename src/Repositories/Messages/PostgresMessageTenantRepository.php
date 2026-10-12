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
        return DB::table('sendportal_messages')
            ->selectRaw("COUNT(*) as open_count, MIN(opened_at) as opened_at, to_char(to_timestamp(floor(extract('epoch' from opened_at) / {$intervalInSeconds}) * {$intervalInSeconds}),'YYYY-MM-DD HH24:MI:SS') as period_start")
            ->where('workspace_id', $workspaceId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->whereNotNull('opened_at')
            ->groupByRaw("floor(extract('epoch' from opened_at) / {$intervalInSeconds})")
            ->orderBy('opened_at')
            ->get();
    }
}
