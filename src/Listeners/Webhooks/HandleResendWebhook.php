<?php

declare(strict_types=1);

namespace Sendportal\Base\Listeners\Webhooks;

use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Sendportal\Base\Events\Webhooks\ResendWebhookReceived;
use Sendportal\Base\Services\Webhooks\EmailWebhookService;

class HandleResendWebhook implements ShouldQueue
{
    /** @var string */
    public $queue = 'sendportal-webhook-process';

    /** @var EmailWebhookService */
    private $emailWebhookService;

    public function __construct(EmailWebhookService $emailWebhookService)
    {
        $this->emailWebhookService = $emailWebhookService;
    }

    /**
     * @throws Exception
     */
    public function handle(ResendWebhookReceived $event): void
    {
        // https://resend.com/docs/dashboard/webhooks/event-types
        if (Str::startsWith($this->extractEventName($event->payload), 'contact.')) {
            $this->handleContact($event->payload, $event->workspaceIds);

            return;
        }

        $messageId = $this->extractMessageId($event->payload);
        $eventName = $this->extractEventName($event->payload);

        Log::info('Processing Resend webhook.', ['type' => $eventName, 'message_id' => $messageId]);

        switch ($eventName) {
            case 'email.delivered':
                $this->handleDelivered($messageId, $event->payload);
                break;

            case 'email.opened':
                $this->handleOpened($messageId, $event->payload);
                break;

            case 'email.clicked':
                $this->handleClicked($messageId, $event->payload);
                break;

            case 'email.complained':
                $this->handleComplained($messageId, $event->payload);
                break;

            case 'email.bounced':
                $this->handleBounced($messageId, $event->payload);
                break;

            case 'email.suppressed':
                $this->handleSuppressed($messageId, $event->payload);
                break;

            case 'email.delivery_delayed':
                $this->handleDeliveryDelayed($messageId, $event->payload);
                break;

            case 'email.failed':
                $this->handleFailed($messageId, $event->payload);
                break;

            case 'email.sent':
            case 'email.scheduled':
            case 'email.received':
                break;

            default:
                throw new RuntimeException("Unknown Resend webhook event type '{$eventName}'.");
        }
    }

    /**
     * Resend records unsubscribes against contacts rather than individual emails.
     */
    private function handleContact(array $content, array $workspaceIds): void
    {
        $email = Arr::get($content, 'data.email');

        Log::info('Processing Resend contact webhook.', ['type' => $this->extractEventName($content)]);

        if (Arr::get($content, 'data.unsubscribed') !== true || ! $email) {
            return;
        }

        $this->emailWebhookService->handleUnsubscribeByEmail($workspaceIds, $email, $this->extractTimestamp($content));
    }

    private function handleDelivered(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleDelivery($messageId, $timestamp);
    }

    /**
     * @throws Exception
     */
    private function handleOpened(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleOpen($messageId, $timestamp, null);
    }

    /**
     * @throws Exception
     */
    private function handleClicked(string $messageId, array $content): void
    {
        $url = Arr::get($content, 'data.click.link');
        $timestamp = $this->extractTimestamp($content, 'data.click.timestamp');

        $this->emailWebhookService->handleClick($messageId, $timestamp, $url);
    }

    private function handleComplained(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleComplaint($messageId, $timestamp);
    }

    private function handleBounced(string $messageId, array $content): void
    {
        $severity = Arr::get($content, 'data.bounce.type');
        $description = Arr::get($content, 'data.bounce.message');
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleFailure($messageId, $severity, $description, $timestamp);

        if ($severity === 'Permanent') {
            $this->emailWebhookService->handlePermanentBounce($messageId, $timestamp);
        }
    }

    /**
     * Resend will not send to an address on its suppression list, so treat it as a permanent bounce.
     */
    private function handleSuppressed(string $messageId, array $content): void
    {
        $description = Arr::get($content, 'data.suppressed.message');
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleFailure($messageId, 'Permanent', $description, $timestamp);

        $this->emailWebhookService->handlePermanentBounce($messageId, $timestamp);
    }

    private function handleDeliveryDelayed(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleFailure($messageId, 'Temporary', 'Delivery delayed', $timestamp);
    }

    private function handleFailed(string $messageId, array $content): void
    {
        $description = Arr::get($content, 'data.failed.reason');
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleFailure($messageId, 'Failed', $description, $timestamp);
    }

    private function extractEventName(array $payload): string
    {
        return Arr::get($payload, 'type');
    }

    private function extractMessageId(array $payload): string
    {
        return trim((string) Arr::get($payload, 'data.email_id'));
    }

    private function extractTimestamp(array $payload, string $key = 'created_at'): Carbon
    {
        return Carbon::parse(Arr::get($payload, $key));
    }
}
