<?php

declare(strict_types=1);

namespace Sendportal\Base\Listeners;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Sendportal\Base\Events\MessageDispatchEvent;
use Sendportal\Base\Services\Messages\DispatchMessage;

class MessageDispatchHandler implements ShouldQueue
{
    /** @var string */
    public $queue = 'sendportal-message-dispatch';

    /** @var DispatchMessage */
    protected $dispatchMessage;

    public function __construct(DispatchMessage $dispatchMessage)
    {
        $this->dispatchMessage = $dispatchMessage;
    }

    /**
     * @throws Exception
     */
    public function handle(MessageDispatchEvent $event): void
    {
        $this->dispatchMessage->handle($event->message);
    }
}
