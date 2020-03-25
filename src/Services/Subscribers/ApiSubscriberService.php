<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Subscribers;

use Sendportal\Base\Events\SubscriberAddedEvent;
use Sendportal\Base\Models\Subscriber;
use Sendportal\Base\Repositories\SubscriberTenantRepository;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ApiSubscriberService
{
    /** @var SubscriberTenantRepository */
    protected $subscribers;

    public function __construct(SubscriberTenantRepository $subscribers)
    {
        $this->subscribers = $subscribers;
    }

    /**
     * @throws Exception
     */
    public function store(int $teamId, Collection $data): Subscriber
    {
        $subscriber = $this->subscribers->store($teamId, $data->except(['segments'])->toArray());

        event(new SubscriberAddedEvent($subscriber));

        $this->handleSegments($data, $subscriber);

        return $subscriber;
    }

    public function delete(int $teamId, Subscriber $subscriber): bool
    {
        return DB::transaction(function () use ($teamId, $subscriber) {
            $subscriber->segments()->detach();
            return $this->subscribers->destroy($teamId, $subscriber->id);
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
