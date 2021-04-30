<?php

declare(strict_types=1);

namespace Sendportal\Base\Listeners;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Sendportal\Base\Events\MessageDispatchEvent;
use Sendportal\Base\Exceptions\MessageLimitReachedException;
use Sendportal\Base\Models\EmailService;
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
            $quotaPeriod = Arr::get(
                $event->message->source()->email_service->settings,
                'quota_period',
                EmailService::QUOTA_PERIOD_HOUR
            );

            $delay = ($quotaPeriod === EmailService::QUOTA_PERIOD_HOUR)
                ? (5 * 60) // 5 minutes
                : (60 * 60); // 1 hour

            $this->release($delay);
        }

    }
}
