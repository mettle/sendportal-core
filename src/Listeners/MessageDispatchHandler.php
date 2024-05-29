<?php

declare(strict_types=1);

namespace Sendportal\Base\Listeners;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Sendportal\Base\Events\MessageDispatchEvent;
use Sendportal\Base\Exceptions\MessageLimitReachedException;
use Sendportal\Base\Services\Messages\DispatchMessage;

class MessageDispatchHandler implements ShouldQueue
{
    use InteractsWithQueue;

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
        try {
            $this->dispatchMessage->handle($event->message);
        } catch (MessageLimitReachedException $e) {
            $this->release();
        }
    }
}
