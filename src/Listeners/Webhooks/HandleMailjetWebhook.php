<?php

declare(strict_types=1);

namespace Sendportal\Base\Listeners\Webhooks;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Sendportal\Base\Events\Webhooks\MailjetWebhookReceived;
use Sendportal\Base\Services\Webhooks\EmailWebhookService;

class HandleMailjetWebhook implements ShouldQueue
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
     * @param MailjetWebhookReceived $event
     * @see https://dev.mailjet.com/email/guides/webhooks/#overview
     */
    public function handle(MailjetWebhookReceived $event): void
    {
        if (array_key_exists('event', $event->payload)) {
            $this->handleEvent($event->payload);
        } else {
            foreach ($event->payload as $item) {
                $this->handleEvent($item);
            }
        }
    }

    private function handleEvent(array $payload): void
    {
        // open, click, bounce, spam, blocked, unsub and sent.
        $messageId = $this->extractMessageId($payload);
        $eventName = $this->extractEventName($payload);

        Log::info('Processing Mailjet webhook.', ['type' => $eventName, 'message_id' => $messageId]);

        switch ($eventName) {
            case 'sent':
                $this->handleDelivery($messageId, $payload);
                break;

            case 'open':
                $this->handleOpen($messageId, $payload);
                break;

            case 'click':
                $this->handleClick($messageId, $payload);
                break;

            case 'bounce':
                $this->handleBounce($messageId, $payload);
                break;

            case 'blocked':
                $this->handleBlocked($messageId, $payload);
                break;

            case 'spam':
                $this->handleSpamComplaint($messageId, $payload);
                break;

            case 'unsub':
                $this->handleUnsub($messageId, $payload);
                break;

            default:
                throw new RuntimeException("Unknown Mailjet webhook event type '{$eventName}'.");
        }
    }

    private function handleDelivery(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content, 'time');

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

    private function handleSpamComplaint(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleComplaint($messageId, $timestamp);
    }

    private function handleBounce(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content, 'time');
        $description = Arr::get($content, 'comment');

        $permanent = (bool) Arr::get($content, 'hard_bounce');

        $severity = $permanent ? 'Permanent' : 'Temporary';

        $this->emailWebhookService->handleFailure($messageId, $severity, $description, $timestamp);

        if ($permanent) {
            $this->emailWebhookService->handlePermanentBounce($messageId, $timestamp);
        }
    }

    private function handleBlocked(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content);

        $permanent = $this->resolveBounceTypePermanent(Arr::get($content, 'error_related_to'));

        $severity = $permanent ? 'Permanent' : 'Temporary';

        $error = Arr::get($content, 'error');

        $this->emailWebhookService->handleFailure($messageId, $severity, $error, $timestamp);

        if ($permanent) {
            $this->emailWebhookService->handlePermanentBounce($messageId, $timestamp);
        }
    }

    private function handleUnsub(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleComplaint($messageId, $timestamp);
    }

    /**
     * Determine if the bounce is permanent
     * @see https://dev.mailjet.com/email/guides/webhooks/#possible-values-for-errors
     */
    private function resolveBounceTypePermanent(string $bounceType): bool
    {
        return in_array(
            $bounceType,
            [
                'blacklisted',
                'spam reporter',
                'domain',
                'relay/access denied',
                'typofix',
                'content',
                'error in template language',
                'spam',
                'content blocked',
                'policy issue',
                'mailjet',
                'duplicate in campaign',
            ]
        );
    }

    private function extractEventName(array $payload): string
    {
        return Arr::get($payload, 'event');
    }

    private function extractMessageId(array $payload): string
    {
        return (string) Arr::get($payload, 'MessageID');
    }

    private function extractTimestamp(array $payload): Carbon
    {
        return Carbon::parse(Arr::get($payload, 'time'));
    }
}
