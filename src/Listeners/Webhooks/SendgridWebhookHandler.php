<?php

declare(strict_types=1);

namespace Sendportal\Base\Listeners\Webhooks;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Sendportal\Base\Events\Webhooks\SendgridWebhookEvent;
use Sendportal\Base\Services\Webhooks\EmailWebhookService;

class SendgridWebhookHandler implements ShouldQueue
{
    /** @var string */
    public $queue = 'sendportal-webhook-process';

    /** @var EmailWebhookService */
    private $emailWebhookService;

    public function __construct(EmailWebhookService $emailWebhookService)
    {
        $this->emailWebhookService = $emailWebhookService;
    }

    public function handle(SendgridWebhookEvent $event): void
    {
        // https://sendgrid.com/docs/for-developers/tracking-events/event/#events
        $messageId = $this->extractMessageId($event->payload);
        $eventName = $this->extractEventName($event->payload);

        Log::info('Processing SendGrid webhook.', ['type' => $eventName, 'message_id' => $messageId]);

        switch ($eventName) {
            case 'delivered':
                $this->handleDelivered($messageId, $event->payload);
                break;

            case 'open':
                $this->handleOpen($messageId, $event->payload);
                break;

            case 'click':
                $this->handleClick($messageId, $event->payload);
                break;

            case 'spamreport':
                $this->handleSpamreport($messageId, $event->payload);
                break;

            case 'dropped':
                $this->handleDropped($messageId, $event->payload);
                break;

            case 'deferred':
                $this->handleDeferred($messageId, $event->payload);
                break;

            case 'bounce':
                $this->handleBounce($messageId, $event->payload);
                break;

            case 'blocked':
                $this->handleBlocked($messageId, $event->payload);
                break;

            case 'unsubscribe':
                $this->handleUnsubscribe($messageId, $event->payload);
                break;

            default:
                throw new RuntimeException("Unknown Sendgrid webhook event type '{$eventName}'.");
        }
    }

    private function handleDelivered(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleDelivery($messageId, $timestamp);
    }

    private function handleOpen(string $messageId, array $content): void
    {
        $ipAddress = Arr::get($content, 'ip');
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleOpen($messageId, $timestamp, $ipAddress);
    }

    private function handleClick(string $messageId, array $content): void
    {
        $url = Arr::get($content, 'url');
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleClick($messageId, $timestamp, $url);
    }

    private function handleSpamreport(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleComplaint($messageId, $timestamp);
    }

    private function handleDropped(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content);
        $description = Arr::get($content, 'reason');

        $this->emailWebhookService->handleFailure($messageId, 'Permanent', $description, $timestamp);

        $this->emailWebhookService->handlePermanentBounce($messageId, $timestamp);
    }

    private function handleDeferred(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content);
        $description = Arr::get($content, 'response');

        $this->emailWebhookService->handleFailure($messageId, 'Temporary', $description, $timestamp);
    }

    private function handleBounce(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content);
        $description = Arr::get($content, 'reason');

        $this->emailWebhookService->handleFailure($messageId, 'Permanent', $description, $timestamp);

        $this->emailWebhookService->handlePermanentBounce($messageId, $timestamp);
    }

    private function handleBlocked(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content);
        $description = Arr::get($content, 'response');

        $this->emailWebhookService->handleFailure($messageId, 'Temporary', $description, $timestamp);
    }

    private function handleUnsubscribe(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleComplaint($messageId, $timestamp);
    }

    private function extractEventName(array $payload): string
    {
        return Arr::get($payload, 'event');
    }

    private function extractMessageId(array $payload): string
    {
        $messageId = Arr::get($payload, 'sg_message_id');

        return trim(Str::before($messageId, '.'));
    }

    private function extractTimestamp($payload): Carbon
    {
        return Carbon::createFromTimestamp(Arr::get($payload, 'timestamp'));
    }
}
