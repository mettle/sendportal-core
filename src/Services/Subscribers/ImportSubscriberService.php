<?php

namespace Sendportal\Base\Services\Subscribers;

use Exception;
use Illuminate\Support\Arr;
use Sendportal\Base\Models\Subscriber;
use Sendportal\Base\Repositories\Subscribers\SubscriberTenantRepositoryInterface;

class ImportSubscriberService
{
    /** @var SubscriberTenantRepositoryInterface */
    protected $subscribers;

    public function __construct(SubscriberTenantRepositoryInterface $subscribers)
    {
        $this->subscribers = $subscribers;
    }

    /**
     * @throws Exception
     */
    public function import(int $workspaceId, array $data): Subscriber
    {
        $subscriber = null;

        if (! empty(Arr::get($data, 'id'))) {
            $subscriber = $this->subscribers->findBy($workspaceId, 'id', $data['id'], ['tags']);
        }

        if (! $subscriber) {
            $subscriber = $this->subscribers->findBy($workspaceId, 'email', Arr::get($data, 'email'), ['tags']);
        }

        if (! $subscriber) {
            $subscriber = $this->subscribers->store($workspaceId, Arr::except($data, ['id', 'tags']));
        }

        $data['tags'] = array_merge($subscriber->tags->pluck('id')->toArray(), Arr::get($data, 'tags'));

        $this->subscribers->update($workspaceId, $subscriber->id, $data);

        return $subscriber;
    }
}
