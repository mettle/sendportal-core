<?php

declare(strict_types=1);

namespace Sendportal\Base\Listeners\Webhooks;

use Illuminate\Support\Str;
use Sendportal\Base\Events\Webhooks\PostmarkWebhookEvent;
use Sendportal\Base\Interfaces\EmailWebhookServiceInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class PostmarkWebhookHandler implements ShouldQueue
{
    /** @var string */
    public $queue = 'webhook-process';

    /** @var EmailWebhookServiceInterface */
    private $emailWebhookService;

    public function __construct(EmailWebhookServiceInterface $emailWebhookService)
    {
        $this->emailWebhookService = $emailWebhookService;
    }

    public function handle(PostmarkWebhookEvent $event): void
    {
        // https://sendgrid.com/docs/for-developers/tracking-events/event/#events
        $messageId = $this->extractMessageId($event->payload);
        $eventName = $this->extractEventName($event->payload);

        $method = 'handle' . Str::studly(Str::slug($eventName, ''));

        if (method_exists($this, $method)) {
            Log::info('postmark webhook processing type=' . $eventName . ' message_id=' . $messageId);

            $this->{$method}($messageId, $event->payload);
        }
    }

    /**
     * Handle an email delivery event.
     */
    protected function handleDelivery(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content, 'DeliveredAt');

        $this->emailWebhookService->handleDelivery($messageId, $timestamp);
    }

    /**
     * Handle an email open event.
     */
    protected function handleOpen(string $messageId, array $content): void
    {
        $ipAddress = Arr::get($content, 'Geo.IP');
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleOpen($messageId, $timestamp, $ipAddress);
    }

    /**
     * Handle an email click event.
     */
    protected function handleClick(string $messageId, array $content): void
    {
        $url = Arr::get($content, 'OriginalLink');
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleClick($messageId, $timestamp, $url);
    }

    /**
     * Handle an email complained event.
     */
    protected function handleSpamComplaint(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content, 'BouncedAt');

        $this->emailWebhookService->handleComplaint($messageId, $timestamp);
    }

    /*
     * Handle bounce event
     */
    protected function handleBounce(string $messageId, array $content): void
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
    protected function resolveBounceTypePermanent(string $bounceType): bool
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

    /**
     * Extract the event name from the payload.
     */
    protected function extractEventName(array $payload): string
    {
        return Arr::get($payload, 'RecordType');
    }

    /**
     * Extract the message ID from the payload.
     */
    protected function extractMessageId(array $payload): string
    {
        return trim(Arr::get($payload, 'MessageID'));
    }

    /**
     * Resolve the timestamp
     */
    protected function extractTimestamp(array $payload, string $field = 'ReceivedAt'): Carbon
    {
        return Carbon::parse(Arr::get($payload, $field));
    }
}
