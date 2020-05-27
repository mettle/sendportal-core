<?php

declare(strict_types=1);

namespace Sendportal\Base\Listeners\Webhooks;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Sendportal\Base\Events\Webhooks\PostmarkWebhookEvent;
use Sendportal\Base\Services\Webhooks\EmailWebhookService;

class PostmarkWebhookHandler implements ShouldQueue
{
    /** @var string */
    public $queue = 'sendportal-webhook-process';

    /** @var EmailWebhookService */
    private $emailWebhookService;

    public function __construct(EmailWebhookService $emailWebhookService)
    {
        $this->emailWebhookService = $emailWebhookService;
    }

    public function handle(PostmarkWebhookEvent $event): void
    {
        // https://postmarkapp.com/developer/webhooks/webhooks-overview
        $messageId = $this->extractMessageId($event->payload);
        $eventName = $this->extractEventName($event->payload);

        Log::info('Processing Postmark webhook.', ['type' => $eventName, 'message_id' => $messageId]);

        switch ($eventName) {
            case 'Delivery':
                $this->handleDelivery($messageId, $event->payload);
                break;

            case 'Open':
                $this->handleOpen($messageId, $event->payload);
                break;

            case 'Click':
                $this->handleClick($messageId, $event->payload);
                break;

            case 'SpamComplaint':
                $this->handleSpamComplaint($messageId, $event->payload);
                break;

            case 'Bounce':
                $this->handleBounce($messageId, $event->payload);
                break;

            default:
                throw new RuntimeException("Unknown Postmark webhook event type '{$eventName}'.");
        }
    }

    private function handleDelivery(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content, 'DeliveredAt');

        $this->emailWebhookService->handleDelivery($messageId, $timestamp);
    }

    private function handleOpen(string $messageId, array $content): void
    {
        $ipAddress = Arr::get($content, 'Geo.IP');
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleOpen($messageId, $timestamp, $ipAddress);
    }

    private function handleClick(string $messageId, array $content): void
    {
        $url = Arr::get($content, 'OriginalLink');
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleClick($messageId, $timestamp, $url);
    }

    private function handleSpamComplaint(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content, 'BouncedAt');

        $this->emailWebhookService->handleComplaint($messageId, $timestamp);
    }

    private function handleBounce(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content, 'BouncedAt');
        $description = Arr::get($content, 'Description');

        $permanent = $this->resolveBounceTypePermanent(Arr::get($content, 'Type'));

        $severity = $permanent ? 'Permanent' : 'Temporary';

        $this->emailWebhookService->handleFailure($messageId, $severity, $description, $timestamp);

        if ($permanent) {
            $this->emailWebhookService->handlePermanentBounce($messageId, $timestamp);
        }
    }

    /**
     * Determine if the bounce is permanent
     * https://postmarkapp.com/developer/api/bounce-api#bounce-types
     */
    private function resolveBounceTypePermanent(string $bounceType): bool
    {
        return in_array(
            $bounceType,
            [
                'HardBounce',
                'Unsubscribe',
                'AddressChange',
                'SpamNotification',
                'BadEmailAddress',
                'SpamComplaint',
                'ManuallyDeactivated',
                'Blocked',
                'SMTPApiError',
            ]
        );
    }

    private function extractEventName(array $payload): string
    {
        return Arr::get($payload, 'RecordType');
    }

    private function extractMessageId(array $payload): string
    {
        return trim(Arr::get($payload, 'MessageID'));
    }

    private function extractTimestamp(array $payload, string $field = 'ReceivedAt'): Carbon
    {
        return Carbon::parse(Arr::get($payload, $field));
    }
}
