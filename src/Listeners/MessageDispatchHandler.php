<?php

namespace Sendportal\Base\Listeners;

use Sendportal\Base\Events\MessageDispatchEvent;
use Sendportal\Base\Services\Messages\DispatchMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

class MessageDispatchHandler implements ShouldQueue
{
    /**
     * @var string
     */
    public $queue = 'message-dispatch';

    /**
     * @var DispatchMessage
     */
    protected $dispatchMessage;

    public function __construct(DispatchMessage $dispatchMessage)
    {
        $this->dispatchMessage = $dispatchMessage;
    }

    /**
     * Handle the event.
     *
     * @param MessageDispatchEvent $event
     * @throws \Exception
     */
    public function handle(MessageDispatchEvent $event)
    {
        $this->dispatchMessage->handle($event->message);
    }
}
