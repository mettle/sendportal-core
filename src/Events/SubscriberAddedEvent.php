<?php

declare(strict_types=1);

namespace Sendportal\Base\Events;

use Sendportal\Base\Models\Subscriber;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriberAddedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var Subscriber */
    public $subscriber;

    public function __construct(Subscriber $subscriber)
    {
        $this->subscriber = $subscriber;
    }
}
