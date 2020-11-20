<?php
declare(strict_types=1);

namespace Sendportal\Base\Repositories\Subscribers;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Sendportal\Base\Models\Subscriber;
use Sendportal\Base\Repositories\BaseTenantRepository;

abstract class BaseSubscriberTenantRepository extends BaseTenantRepository implements SubscriberTenantRepositoryInterface
{
    /** @var string */
    protected $modelName = Subscriber::class;

    /**
     * {@inheritDoc}
     */
    public function store($workspaceId, array $data)
    {
        $this->checkTenantData($data);

        /** @var Subscriber $instance */
        $instance = $this->getNewInstance();

        $subscriber = $this->executeSave($workspaceId, $instance, Arr::except($data, ['segments']));

        // Only sync segments if its actually present. This means that users must
        // pass through an empty segments array if they want to delete all segments.
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
    public function syncSegments(Subscriber $subscriber, array $segments = [])
    {
        return $subscriber->segments()->sync($segments);
    }

    /**
     * {@inheritDoc}
     */
    public function update($workspaceId, $id, array $data)
    {
        $this->checkTenantData($data);

        $instance = $this->find($workspaceId, $id);

        $subscriber = $this->executeSave($workspaceId, $instance, Arr::except($data, ['segments', 'id']));

        // Only sync segments if its actually present. This means that users must
        // pass through an empty segments array if they want to delete all segments.
        if (isset($data['segments'])) {
            $this->syncSegments($instance, Arr::get($data, 'segments', []));
        }

        return $subscriber;
    }

    /**
     * Return the count of active subscribers
     *
     * @param int $workspaceId
     *
     * @return mixed
     * @throws Exception
     */
    public function countActive($workspaceId): int
    {
        return $this->getQueryBuilder($workspaceId)
            ->whereNull('unsubscribed_at')
            ->count();
    }

    public function getRecentSubscribers(int $workspaceId): Collection
    {
        return $this->getQueryBuilder($workspaceId)
            ->orderBy('created_at', 'DESC')
            ->take(10)
            ->get();
    }

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
    protected function applyNameFilter(Builder $instance, array $filters): void
    {
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
    protected function applyStatusFilter(Builder $instance, array $filters): void
    {
        $status = Arr::get($filters, 'status');

        if ($status === 'subscribed') {
            $instance->whereNull('unsubscribed_at');
        } elseif ($status === 'unsubscribed') {
            $instance->whereNotNull('unsubscribed_at');
        }
    }

    /**
     * Filter by segment.
     */
    protected function applySegmentFilter(Builder $instance, array $filters = []): void
    {
        if ($segmentIds = Arr::get($filters, 'segments')) {
            $instance->select('subscribers.*')
                ->leftJoin('segment_subscriber', 'subscribers.id', '=', 'segment_subscriber.subscriber_id')
                ->whereIn('segment_subscriber.segment_id', $segmentIds)
                ->distinct();
        }
    }
}
