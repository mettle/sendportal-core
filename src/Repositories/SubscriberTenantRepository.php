<?php

declare(strict_types=1);

namespace Sendportal\Base\Repositories;

use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Sendportal\Base\Models\Subscriber;

class SubscriberTenantRepository extends BaseTenantRepository
{
    /** @var string */
    protected $modelName = Subscriber::class;

    /**
     * @inheritDoc
     */
    protected function applyFilters(Builder $instance, array $filters = []): void
    {
        $this->applyNameFilter($instance, $filters);
        $this->applyStatusFilter($instance, $filters);
        $this->applySegmentFilter($instance, $filters);
    }

    /**
     * Filter by name or email.
     */
    protected function applyNameFilter(Builder $instance, array $filters): void {
        if ($name = Arr::get($filters, 'name')) {
            $filterString = '%' . $name . '%';

            $instance->where(static function (Builder $instance) use ($filterString) {
                $instance->where('subscribers.first_name', 'like', $filterString)
                    ->orWhere('subscribers.last_name', 'like', $filterString)
                    ->orWhere('subscribers.email', 'like', $filterString);
            });
        }
    }

    /**
     * Filter by subscription status.
     */
    protected function applyStatusFilter(Builder $instance, array $filters): void {
        $status = Arr::get($filters, 'status');

        if ($status === 'subscribed') {
            $instance->whereNull('unsubscribed_at');
        }
        elseif ($status === 'unsubscribed') {
            $instance->whereNotNull('unsubscribed_at');
        }
    }

    /**
     * Filter by segment.
     */
    protected function applySegmentFilter(Builder $instance, array $filters = []): void {
        if ($segmentId = Arr::get($filters, 'segment_id')) {
            $instance->select('subscribers.*')
                ->leftJoin('segment_subscriber', 'subscribers.id', '=', 'segment_subscriber.subscriber_id')
                ->whereIn('segment_subscriber.segment_id', $segmentId);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function store($workspaceId, array $data) {
        $this->checkTenantData($data);

        /** @var Subscriber $instance */
        $instance = $this->getNewInstance();

        $subscriber = $this->executeSave($workspaceId, $instance, Arr::except($data, ['segments']));

        // only sync segments if its actually present. This means that users's must
        // pass through an empty segments array if they want to delete all segments
        if (isset($data['segments'])) {
            $this->syncSegments($instance, Arr::get($data, 'segments', []));
        }

        return $subscriber;
    }

    /**
     * {@inheritDoc}
     */
    public function update($workspaceId, $id, array $data) {
        $this->checkTenantData($data);

        $instance = $this->find($workspaceId, $id);

        $subscriber = $this->executeSave($workspaceId, $instance, Arr::except($data, ['segments']));

        // only sync segments if its actually present. This means that users's must
        // pass through an empty segments array if they want to delete all segments
        if (isset($data['segments'])) {
            $this->syncSegments($instance, Arr::get($data, 'segments', []));
        }

        return $subscriber;
    }

    /**
     * Sync Segments to a Subscriber.
     *
     * @param Subscriber $subscriber
     * @param array $segments
     *
     * @return mixed
     */
    public function syncSegments(Subscriber $subscriber, array $segments = []) {
        return $subscriber->segments()->sync($segments);
    }

    /**
     * Return the count of active subscribers
     *
     * @param int $workspaceId
     *
     * @return mixed
     * @throws Exception
     */
    public function countActive($workspaceId): int {
        return $this->getQueryBuilder($workspaceId)
            ->whereNull('unsubscribed_at')
            ->count();
    }

    public function getGrowthChartData(CarbonPeriod $period, int $workspaceId): array {
        $startingValue = DB::table('subscribers')
            ->where('workspace_id', $workspaceId)
            ->whereDate('created_at', '<=', $period->first())
            ->count();

        $runningTotal = DB::table('subscribers as s1')
            ->select(DB::raw("date_format(created_at, '%d-%m-%Y') AS date"), DB::raw("(select count(s2.id) from subscribers s2 where s2.created_at <= s1.created_at) as total"))
            ->where('workspace_id', $workspaceId)
            ->whereDate('created_at', '>=', $period->first())
            ->whereDate('created_at', '<=', $period->last())
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $unsubscribers = DB::table('subscribers as s1')
            ->select(DB::raw("date_format(unsubscribed_at, '%d-%m-%Y') AS date"), DB::raw("(select count(s2.id) from subscribers s2 where s2.unsubscribed_at <= s1.unsubscribed_at) as total"))
            ->whereDate('unsubscribed_at', '>=', $period->first())
            ->whereDate('unsubscribed_at', '<=', $period->last())
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'startingValue' => $startingValue,
            'runningTotal' => $runningTotal->flatten()->keyBy('date'),
            'unsubscribers' => $unsubscribers->flatten()->keyBy('date'),
        ];
    }
}
