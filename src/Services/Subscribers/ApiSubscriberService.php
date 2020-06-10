<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Subscribers;

use Sendportal\Base\Events\SubscriberAddedEvent;
use Sendportal\Base\Models\Subscriber;
use Sendportal\Base\Repositories\Subscribers\SubscriberTenantRepositoryInterface;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ApiSubscriberService
{
    /** @var SubscriberTenantRepositoryInterface */
    protected $subscribers;

    public function __construct(SubscriberTenantRepositoryInterface $subscribers)
    {
        $this->subscribers = $subscribers;
    }

    /**
     * @param int                            $workspaceId
     * @param \Illuminate\Support\Collection $data
     *
     * @return \Sendportal\Base\Models\Subscriber
     * @throws \Exception
     */
    public function store(int $workspaceId, Collection $data): Subscriber
    {
        $subscriber = $this->subscribers->store($workspaceId, $data->except(['segments'])->toArray());

        event(new SubscriberAddedEvent($subscriber));

        $this->handleSegments($data, $subscriber);

        return $subscriber;
    }

    public function delete(int $workspaceId, Subscriber $subscriber): bool
    {
        return DB::transaction(function () use ($workspaceId, $subscriber) {
            $subscriber->segments()->detach();
            return $this->subscribers->destroy($workspaceId, $subscriber->id);
        });
    }

    protected function handleSegments(Collection $data, Subscriber $subscriber): Subscriber
    {
        if (!empty($data['segments'])) {
            $subscriber->segments()->sync($data['segments']);
        }

        return $subscriber;
    }
}
