<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Subscribers\Tags;

use Exception;
use Illuminate\Support\Collection;
use Sendportal\Base\Repositories\Subscribers\SubscriberTenantRepositoryInterface;

class ApiSubscriberTagService
{
    /** @var SubscriberTenantRepositoryInterface */
    private $subscribers;

    public function __construct(SubscriberTenantRepositoryInterface $subscribers)
    {
        $this->subscribers = $subscribers;
    }

    /**
     * Add tags to a subscriber.
     *
     * @param int $workspaceId
     * @param int $subscriberId
     * @param Collection $tagIds
     *
     * @return Collection
     * @throws Exception
     */
    public function store(int $workspaceId, int $subscriberId, Collection $tagIds): Collection
    {
        $subscriber = $this->subscribers->find($workspaceId, $subscriberId);

        /** @var Collection $existingTags */
        $existingTags = $subscriber->tags()->pluck('tag.id')->toBase();

        $tagsToStore = $tagIds->diff($existingTags);

        $subscriber->tags()->attach($tagsToStore);

        return $subscriber->tags->toBase();
    }

    /**
     * Sync the list of tags a subscriber is associated with.
     *
     * @param int $workspaceId
     * @param int $subscriberId
     * @param Collection $tagIds
     *
     * @return Collection
     * @throws Exception
     */
    public function update(int $workspaceId, int $subscriberId, Collection $tagIds): Collection
    {
        $subscriber = $this->subscribers->find($workspaceId, $subscriberId, ['tags']);

        $subscriber->tags()->sync($tagIds);

        $subscriber->load('tags');

        return $subscriber->tags->toBase();
    }

    /**
     * Remove tags from a subscriber.
     *
     * @param int $workspaceId
     * @param int $subscriberId
     * @param Collection $tagIds
     *
     * @return Collection
     * @throws Exception
     */
    public function destroy(int $workspaceId, int $subscriberId, Collection $tagIds): Collection
    {
        $subscriber = $this->subscribers->find($workspaceId, $subscriberId);

        $subscriber->tags()->detach($tagIds);

        return $subscriber->tags;
    }
}
