<?php

namespace Sendportal\Base\Repositories;

use Sendportal\Base\Models\Subscriber;

class SubscriberTenantRepository extends BaseTenantRepository
{
    /**
     * @var string
     */
    protected $modelName = Subscriber::class;

    /**
     * Apply parameters, which can be extended in child classes for filtering
     *
     * @param object $instance
     * @param array $filters
     * @return mixed
     */
    protected function applyFilters($instance, array $filters = [])
    {
        $this->applyNameFilter($instance, $filters);
        $this->applyStatusFilter($instance, $filters);
        $this->applySegmentFilter($instance, $filters);
    }

    /**
     * Filter by name or email
     *
     * @param object $instance
     * @param array $filters
     */
    protected function applyNameFilter(object $instance, array $filters)
    {
        if ($name = array_get($filters, 'name')) {
            $name = '%' . $name . '%';

            $instance->where(function ($instance) use ($name) {
                $instance->where('subscribers.first_name', 'like', $name)
                    ->orWhere('subscribers.last_name', 'like', $name)
                    ->orWhere('subscribers.email', 'like', $name);
            });
        }
    }

    /**
     * Filter by subscription status
     *
     * @param object $instance
     * @param array $filters
     */
    protected function applyStatusFilter(object $instance, array $filters)
    {
        $status = array_get($filters, 'status');

        if ($status == 'subscribed') {
            $instance->whereNull('unsubscribed_at');
        } elseif ($status == 'unsubscribed') {
            $instance->whereNotNull('unsubscribed_at');
        }
    }

    /**
     * Filter by segment
     *
     * @param $instance
     * @param array $filters
     */
    protected function applySegmentFilter($instance, $filters = [])
    {
        if ($segmentId = array_get($filters, 'segment_id')) {
            $instance->select('subscribers.*')
                ->leftJoin('segment_subscriber', 'subscribers.id', '=', 'segment_subscriber.subscriber_id')
                ->whereIn('segment_subscriber.segment_id', $segmentId);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function store($teamId, array $data)
    {
        $this->checkTenantData($data);

        $instance = $this->getNewInstance();

        $subscriber = $this->executeSave($teamId, $instance, array_except($data, ['segments']));

        // only sync segments if its actually present. This means that users's must
        // pass through an empty segments array if they want to delete all segments
        if (isset($data['segments'])) {
            $this->syncSegments($instance, array_get($data, 'segments', []));
        }

        return $subscriber;
    }

    /**
     * {@inheritDoc}
     */
    public function update($teamId, $id, array $data)
    {
        $this->checkTenantData($data);

        $instance = $this->find($teamId, $id);

        $subscriber = $this->executeSave($teamId, $instance, array_except($data, ['segments']));

        // only sync segments if its actually present. This means that users's must
        // pass through an empty segments array if they want to delete all segments
        if (isset($data['segments'])) {
            $this->syncSegments($instance, array_get($data, 'segments', []));
        }

        return $subscriber;
    }

    /**
     * Sync Segments to a Subscriber.
     *
     * @param Subscriber $subscriber
     * @param array $segments
     * @return mixed
     */
    public function syncSegments(Subscriber $subscriber, array $segments = [])
    {
        return $subscriber->segments()->sync($segments);
    }

    /**
     * Return the count of active subscribers
     *
     * @param int $teamId
     * @return mixed
     * @throws \Exception
     */
    public function countActive($teamId): int
    {
        return $this->getQueryBuilder($teamId)
            ->whereNull('unsubscribed_at')
            ->count();
    }
}
