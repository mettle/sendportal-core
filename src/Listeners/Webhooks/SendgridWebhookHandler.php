<?php

declare(strict_types=1);

namespace Sendportal\Base\Listeners\Webhooks;

use Sendportal\Base\Events\Webhooks\SendgridWebhookEvent;
use Sendportal\Base\Interfaces\EmailWebhookServiceInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SendgridWebhookHandler implements ShouldQueue
{
    /** @var string */
    public $queue = 'webhook-process';

    /** @var EmailWebhookServiceInterface */
    private $emailWebhookService;

    public function __construct(EmailWebhookServiceInterface $emailWebhookService)
    {
        $this->emailWebhookService = $emailWebhookService;
    }

    public function handle(SendgridWebhookEvent $event): void
    {
        // https://sendgrid.com/docs/for-developers/tracking-events/event/#events
        $messageId = $this->extractMessageId($event->payload);
        $eventName = $this->extractEventName($event->payload);

        $method = 'handle' . Str::studly(Str::slug($eventName, ''));

        if (method_exists($this, $method)) {
            Log::info('SendGrid webhook processing type=' . $eventName . ' message_id=' . $messageId);

            $this->{$method}($messageId, $event->payload);
        }
    }

    /**
     * Handle an email delivery event.
     */
    protected function handleDelivered(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleDelivery($messageId, $timestamp);
    }

    /**
     * Handle an email open event.
     */
    protected function handleOpen(string $messageId, array $content): void
    {
        $ipAddress = Arr::get($content, 'ip');
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleOpen($messageId, $timestamp, $ipAddress);
    }

    /**
     * Handle an email click event.
     */
    protected function handleClick(string $messageId, array $content): void
    {
        $url = Arr::get($content, 'url');
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleClick($messageId, $timestamp, $url);
    }

    /**
     * Handle an email complained event.
     */
    protected function handleSpamreport(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleComplaint($messageId, $timestamp);
    }

    /*
     * Handle dropped event
     */
    protected function handleDropped(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content);
        $description = Arr::get($content, 'reason');

        $this->emailWebhookService->handleFailure($messageId, 'Permanent', $description, $timestamp);

        $this->emailWebhookService->handlePermanentBounce($messageId, $timestamp);
    }

    /*
     * Handle deferred event
     */
    protected function handleDeferred(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content);
        $description = Arr::get($content, 'response');

        $this->emailWebhookService->handleFailure($messageId, 'Temporary', $description, $timestamp);
    }

    /*
     * Handle bounce event
     */
    protected function handleBounce(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content);
        $description = Arr::get($content, 'reason');

        $this->emailWebhookService->handleFailure($messageId, 'Permanent', $description, $timestamp);

        $this->emailWebhookService->handlePermanentBounce($messageId, $timestamp);
    }

    /*
     * Handle blocked event
     */
    protected function handleBlocked(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content);
        $description = Arr::get($content, 'response');

        $this->emailWebhookService->handleFailure($messageId, 'Temporary', $description, $timestamp);
    }

    /*
     * Handle unsubscribe event
     */
    protected function handleUnsubscribe(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleComplaint($messageId, $timestamp);
    }

    /**
     * Extract the event name from the payload.
     */
    protected function extractEventName(array $payload): string
    {
        return Arr::get($payload, 'event');
    }

    /**
     * Extract the message ID from the payload.
     */
    protected function extractMessageId(array $payload): string
    {
        $messageId = Arr::get($payload, 'sg_message_id');

        return trim(Str::before($messageId, '.'));
    }

    /**
     * Resolve the timestamp
     *
     * @param array $payload
     * @return Carbon
     */
    protected function extractTimestamp($payload)
    {
        return Carbon::createFromTimestamp(Arr::get($payload, 'timestamp'));
    }
}
