<?php

namespace Sendportal\Base\Services\Subscribers;

use Sendportal\Base\Models\Subscriber;
use Sendportal\Base\Repositories\SubscriberTenantRepository;
use Exception;

class ImportSubscriberService
{
    /** @var SubscriberTenantRepository */
    protected $subscribers;

    public function __construct(SubscriberTenantRepository $subscribers)
    {
        $this->subscribers = $subscribers;
    }

    /**
     * Create or update a subscriber.
     *
     * @param array $data
     *
     * @return Subscriber
     * @throws Exception
     */
    public function import(int $workspaceId, array $data): Subscriber
    {
        $subscriber = null;

        if (!empty(\Arr::get($data, 'id'))) {
            $subscriber = $this->subscribers->findBy($workspaceId, 'id', $data['id'], ['segments']);
        }

        if (!$subscriber) {
            $subscriber = $this->subscribers->findBy($workspaceId, 'email', \Arr::get($data, 'email'), ['segments']);
        }

        if (!$subscriber) {
            $subscriber = $this->subscribers->store($workspaceId, array_except($data, ['id', 'segments']));
        }

        $data['segments'] = array_merge($subscriber->segments->pluck('id')->toArray(), \Arr::get($data, 'segments'));

        $this->subscribers->update($workspaceId, $subscriber->id, $data);

        return $subscriber;
    }
}
