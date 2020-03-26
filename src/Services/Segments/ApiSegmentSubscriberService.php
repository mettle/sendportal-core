<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Segments;

use Sendportal\Base\Repositories\SegmentTenantRepository;
use Exception;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class ApiSegmentSubscriberService
{
    /** @var SegmentTenantRepository */
    private $segments;

    public function __construct(SegmentTenantRepository $segments)
    {
        $this->segments = $segments;
    }

    /**
     * Add new subscribers to a segment.
     *
     * @throws Exception
     */
    public function store(int $workspaceId, int $segmentId, Collection $subscriberIds): Collection
    {
        $segment = $this->segments->find($workspaceId, $segmentId);

        /** @var Collection $existingSubscribers */
        $existingSubscribers = $segment->subscribers()->pluck('subscribers.id')->toBase();

        $subscribersToStore = $subscriberIds->diff($existingSubscribers);

        $segment->subscribers()->attach($subscribersToStore);

        return $segment->subscribers->toBase();
    }

    /**
     * Sync subscribers on a segment.
     *
     * @throws Exception
     */
    public function update(int $workspaceId, int $segmentId, Collection $subscriberIds): EloquentCollection
    {
        $segment = $this->segments->find($workspaceId, $segmentId);

        $segment->subscribers()->sync($subscriberIds);

        $segment->load('subscribers');

        return $segment->subscribers;
    }

    /**
     * Remove subscribers from a segment.
     *
     * @throws Exception
     */
    public function destroy(int $workspaceId, int $segmentId, Collection $subscriberIds): EloquentCollection
    {
        $segment = $this->segments->find($workspaceId, $segmentId);

        $segment->subscribers()->detach($subscriberIds);

        return $segment->subscribers;
    }
}
