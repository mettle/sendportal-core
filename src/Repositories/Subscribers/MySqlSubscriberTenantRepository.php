<?php

namespace Sendportal\Base\Repositories\Subscribers;

use Carbon\CarbonPeriod;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class MySqlSubscriberTenantRepository extends BaseSubscriberTenantRepository
{
    /**
     * @inheritDoc
     */
    public function getGrowthChartData(CarbonPeriod $period, int $workspaceId): array
    {
        $startingValue = DB::table('subscribers')
            ->where('workspace_id', $workspaceId)
            ->where(function (Builder $q) use ($period) {
                $q->where('unsubscribed_at', '>=', $period->getStartDate())
                    ->orWhereNull('unsubscribed_at');
            })
            ->where('created_at', '<', $period->getStartDate())
            ->count();

        $runningTotal = DB::table('subscribers')
            ->selectRaw("date_format(created_at, '%d-%m-%Y') AS date, count(*) as total")
            ->where('workspace_id', $workspaceId)
            ->where('created_at', '>=', $period->getStartDate())
            ->where('created_at', '<=', $period->getEndDate())
            ->groupBy('date')
            ->get();

        $unsubscribers = DB::table('subscribers')
            ->selectRaw("date_format(unsubscribed_at, '%d-%m-%Y') AS date, count(*) as total")
            ->where('workspace_id', $workspaceId)
            ->where('unsubscribed_at', '>=', $period->getStartDate())
            ->where('unsubscribed_at', '<=', $period->getEndDate())
            ->groupBy('date')
            ->get();

        return [
            'startingValue' => $startingValue,
            'runningTotal' => $runningTotal->flatten()->keyBy('date'),
            'unsubscribers' => $unsubscribers->flatten()->keyBy('date'),
        ];
    }
}
