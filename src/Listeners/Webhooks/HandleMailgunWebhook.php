<?php

declare(strict_types=1);

namespace Sendportal\Base\Listeners\Webhooks;

use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Sendportal\Base\Events\Webhooks\MailgunWebhookReceived;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Services\Webhooks\EmailWebhookService;
use Sendportal\Base\Services\Webhooks\Mailgun\WebhookVerifier;

class HandleMailgunWebhook implements ShouldQueue
{
    /** @var string */
    public $queue = 'sendportal-webhook-process';

    /** @var EmailWebhookService */
    private $emailWebhookService;

    /** @var WebhookVerifier */
    private $verifier;

    public function __construct(
        EmailWebhookService $emailWebhookService,
        WebhookVerifier $verifier
    ) {
        $this->emailWebhookService = $emailWebhookService;
        $this->verifier = $verifier;
    }

    /**
     * @throws Exception
     */
    public function handle(MailgunWebhookReceived $event): void
    {
        // https://documentation.mailgun.com/en/latest/user_manual.html#events
        $messageId = $this->extractMessageId($event->payload);
        $eventName = $this->extractEventName($event->payload);

        if (!$this->checkWebhookValidity($messageId, $event->payload)) {
            Log::error('Mailgun webhook failed verification check.', ['payload' => $event->payload]);
            return;
        }

        Log::info('Processing Mailgun webhook.', ['type' => $eventName, 'message_id' => $messageId]);

        switch ($eventName) {
            case 'delivered':
                $this->handleDelivered($messageId, $event->payload);
                break;

            case 'opened':
                $this->handleOpened($messageId, $event->payload);
                break;

            case 'clicked':
                $this->handleClicked($messageId, $event->payload);
                break;

            case 'complained':
                $this->handleComplained($messageId, $event->payload);
                break;

            case 'failed':
                $this->handleFailed($messageId, $event->payload);
                break;

            default:
                throw new RuntimeException("Unknown Mailgun webhook event type '{$eventName}'.");
        }
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
        $ipAddress = Arr::get($content, 'event-data.ip');
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleOpen($messageId, $timestamp, $ipAddress);
    }

    /**
     * @throws Exception
     */
    private function handleClicked(string $messageId, array $content): void
    {
        $url = Arr::get($content, 'event-data.url');
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleClick($messageId, $timestamp, $url);
    }

    private function handleComplained(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleComplaint($messageId, $timestamp);
    }

    private function handleFailed(string $messageId, array $content): void
    {
        $severity = Arr::get($content, 'event-data.severity');
        $description = $this->extractFailureDescription($content);
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleFailure($messageId, $severity, $description, $timestamp);

        if ($severity === 'permanent') {
            $this->emailWebhookService->handlePermanentBounce($messageId, $timestamp);
        }
    }

    private function extractEventName(array $payload): string
    {
        return Arr::get($payload, 'event-data.event');
    }

    private function extractMessageId(array $payload): string
    {
        return $this->formatMessageId(Arr::get($payload, 'event-data.message.headers.message-id'));
    }

    /**
     * Prepend/append crocodiles to message ID.
     */
    private function formatMessageId(string $messageId): string
    {
        $messageId = strpos($messageId, '<') === 0
            ? $messageId
            : $messageId = '<' . $messageId . '>';

        return trim($messageId);
    }

    private function extractTimestamp($payload): Carbon
    {
        return Carbon::createFromTimestamp(Arr::get($payload, 'event-data.timestamp'));
    }

    private function extractFailureDescription(array $payload): string
    {
        if ($description = Arr::get($payload, 'event-data.delivery-status.description')) {
            return $description;
        }

        if ($message = Arr::get($payload, 'event-data.delivery-status.message')) {
            return $message;
        }

        return '';
    }

    /**
     * Validate that the webhook came from Mailgun.
     */
    private function checkWebhookValidity(string $messageId, array $payload): bool
    {
        $message = Message::with('source.email_service')->where('message_id', $messageId)->first();

        /** @var EmailService|null $emailservice */
        $emailservice = $message->source->email_service ?? null;

        if (!$emailservice) {
            return false;
        }

        /** @var string|null $signingKey */
        $signingKey = $emailservice->settings['key'] ?? null;

        if (!$signingKey) {
            return false;
        }

        $signature = $payload['signature'];

        return $this->verifier->verify(
            $signingKey,
            $signature['token'],
            (int)$signature['timestamp'],
            $signature['signature']
        );
    }
}
