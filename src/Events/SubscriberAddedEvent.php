<?php

declare(strict_types=1);

namespace Sendportal\Base\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Sendportal\Base\Models\Subscriber;

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
