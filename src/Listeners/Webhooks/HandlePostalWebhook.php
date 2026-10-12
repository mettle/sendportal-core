<?php

declare(strict_types=1);

namespace Sendportal\Base\Listeners\Webhooks;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Sendportal\Base\Events\Webhooks\PostalWebhookReceived;
use Sendportal\Base\Services\Webhooks\EmailWebhookService;

class HandlePostalWebhook implements ShouldQueue
{
    /** @var string */
    public $queue = 'sendportal-webhook-process';

    /** @var EmailWebhookService */
    private $emailWebhookService;

    public function __construct(EmailWebhookService $emailWebhookService)
    {
        $this->emailWebhookService = $emailWebhookService;
    }

    public function handle(PostalWebhookReceived $event): void
    {
        $messageId = $this->extractMessageId($event->payload);
        $eventName = $this->extractEventName($event->payload);

        Log::info('Processing Postal webhook.', ['type' => $eventName, 'message_id' => $messageId]);

        switch ($eventName) {
            case 'MessageSent':
                $this->handleDelivered($messageId, $event->payload);
                break;

            case 'MessageLoaded':
                $this->handleOpen($messageId, $event->payload);
                break;

            case 'MessageLinkClicked':
                $this->handleClick($messageId, $event->payload);
                break;

            case 'MessageBounced':
                $messageId = $this->extractMessageIdBounced($event->payload);
                $this->handleBounce($messageId, $event->payload);
                break;

            case 'MessageDeliveryFailed':
                $this->handleFailed($messageId, $event->payload);
                break;

            case 'MessageHeld':
                $this->handleHeld($messageId, $event->payload);
                break;

            default:
                throw new RuntimeException("Unknown Postal webhook event type '{$eventName}'.");
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
        $url = Arr::get($content, 'payload.url');
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleClick($messageId, $timestamp, $url);
    }

    private function handleBounce(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestampBounced($content);
        $description = Arr::get($content, 'payload.bounce.subject');

        $this->emailWebhookService->handleFailure($messageId, 'Permanent', $description, $timestamp);

        $this->emailWebhookService->handlePermanentBounce($messageId, $timestamp);
    }

    private function handleFailed(string $messageId, array $content): void
    {
        $severity = Arr::get($content, 'payload.status');
        $description = Arr::get($content, 'payload.output');
        $timestamp = $this->extractTimestampFailed($content);

        $this->emailWebhookService->handleFailure($messageId, $severity, $description, $timestamp);

        if ($severity === 'HardFail') {
            $this->emailWebhookService->handlePermanentBounce($messageId, $timestamp);
        }
    }

    private function handleHeld(string $messageId, array $content): void
    {
        $severity = Arr::get($content, 'payload.status');
        $description = Arr::get($content, 'payload.details');
        $timestamp = $this->extractTimestampFailed($content);

        $this->emailWebhookService->handleFailure($messageId, $severity, $description, $timestamp);

        if ($severity === 'Held') {
            $this->emailWebhookService->handlePermanentBounce($messageId, $timestamp);
        }
    }

    private function extractEventName(array $payload): string
    {
        return Arr::get($payload, 'event');
    }

    private function extractMessageIdBounced(array $payload): string
    {
        $messageId = Arr::get($payload, 'payload.original_message.id');

        return trim((string) $messageId);
    }

    private function extractMessageId(array $payload): string
    {
        $messageId = Arr::get($payload, 'payload.message.id');

        return trim((string) $messageId);
    }

    private function extractTimestampBounced($payload): Carbon
    {
        return Carbon::createFromTimestamp(Arr::get($payload, 'payload.bounce.timestamp'));
    }

    private function extractTimestampFailed($payload): Carbon
    {
        return Carbon::createFromTimestamp(Arr::get($payload, 'payload.timestamp'));
    }

    private function extractTimestamp($payload): Carbon
    {
        return Carbon::createFromTimestamp(Arr::get($payload, 'timestamp'));
    }
}
