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
    public function import(int $teamId, array $data): Subscriber
    {
        $subscriber = null;

        if (!empty(array_get($data, 'id'))) {
            $subscriber = $this->subscribers->findBy($teamId, 'id', $data['id'], ['segments']);
        }

        if (!$subscriber) {
            $subscriber = $this->subscribers->findBy($teamId, 'email', array_get($data, 'email'), ['segments']);
        }

        if (!$subscriber) {
            $subscriber = $this->subscribers->store($teamId, array_except($data, ['id', 'segments']));
        }

        $data['segments'] = array_merge($subscriber->segments->pluck('id')->toArray(), array_get($data, 'segments'));

        $this->subscribers->update($teamId, $subscriber->id, $data);

        return $subscriber;
    }
}
