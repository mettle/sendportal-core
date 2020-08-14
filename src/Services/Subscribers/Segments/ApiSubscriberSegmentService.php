<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Subscribers\Segments;

use Exception;
use Illuminate\Support\Collection;
use Sendportal\Base\Repositories\Subscribers\SubscriberTenantRepositoryInterface;

class ApiSubscriberSegmentService
{
    /** @var SubscriberTenantRepositoryInterface */
    private $subscribers;

    public function __construct(SubscriberTenantRepositoryInterface $subscribers)
    {
        $this->subscribers = $subscribers;
    }

    /**
     * Add segments to a subscriber.
     *
     * @param int $workspaceId
     * @param int $subscriberId
     * @param Collection $segmentIds
     *
     * @return Collection
     * @throws Exception
     */
    public function store(int $workspaceId, int $subscriberId, Collection $segmentIds): Collection
    {
        $subscriber = $this->subscribers->find($workspaceId, $subscriberId);

        /** @var Collection $existingSegments */
        $existingSegments = $subscriber->segments()->pluck('segment.id')->toBase();

        $segmentsToStore = $segmentIds->diff($existingSegments);

        $subscriber->segments()->attach($segmentsToStore);

        return $subscriber->segments->toBase();
    }

    /**
     * Sync the list of segments a subscriber is associated with.
     *
     * @param int $workspaceId
     * @param int $subscriberId
     * @param Collection $segmentIds
     *
     * @return Collection
     * @throws Exception
     */
    public function update(int $workspaceId, int $subscriberId, Collection $segmentIds): Collection
    {
        $subscriber = $this->subscribers->find($workspaceId, $subscriberId, ['segments']);

        $subscriber->segments()->sync($segmentIds);

        $subscriber->load('segments');

        return $subscriber->segments->toBase();
    }

    /**
     * Remove segments from a subscriber.
     *
     * @param int $workspaceId
     * @param int $subscriberId
     * @param Collection $segmentIds
     *
     * @return Collection
     * @throws Exception
     */
    public function destroy(int $workspaceId, int $subscriberId, Collection $segmentIds): Collection
    {
        $subscriber = $this->subscribers->find($workspaceId, $subscriberId);

        $subscriber->segments()->detach($segmentIds);

        return $subscriber->segments;
    }
}
