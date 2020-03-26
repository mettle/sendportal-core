<?php

declare(strict_types=1);

namespace Sendportal\Base\Listeners\Webhooks;

use Illuminate\Support\Str;
use Sendportal\Base\Events\Webhooks\MailgunWebhookEvent;
use Sendportal\Base\Interfaces\EmailWebhookServiceInterface;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\Provider;
use Sendportal\Base\Services\Webhooks\Mailgun\WebhookVerifier;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class MailgunWebhookHandler implements ShouldQueue
{
    /** @var string */
    public $queue = 'webhook-process';

    /** @var EmailWebhookServiceInterface */
    private $emailWebhookService;

    /** @var WebhookVerifier */
    private $verifier;

    public function __construct(
        EmailWebhookServiceInterface $emailWebhookService,
        WebhookVerifier $verifier
    ) {
        $this->emailWebhookService = $emailWebhookService;
        $this->verifier = $verifier;
    }

    public function handle(MailgunWebhookEvent $event): void
    {
        $messageId = $this->extractMessageId($event->payload);
        $eventName = $this->extractEventName($event->payload);

        if (!$this->checkWebhookValidity($messageId, $event->payload)) {
            Log::error('Mailgun webhook failed verification check.', ['payload' => $event->payload]);
            return;
        }

        $method = 'handle' . Str::studly(Str::slug($eventName, ''));

        if (method_exists($this, $method)) {
            Log::info('Mailgun webhook processing type=' . $eventName . ' message_id=' . $messageId);

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
    protected function handleOpened(string $messageId, array $content): void
    {
        $ipAddress = \Arr::get($content, 'event-data.ip');
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleOpen($messageId, $timestamp, $ipAddress);
    }

    /**
     * Handle an email click event.
     */
    protected function handleClicked(string $messageId, array $content): void
    {
        $url = \Arr::get($content, 'event-data.url');
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleClick($messageId, $timestamp, $url);
    }

    /**
     * Handle an email complained event.
     */
    protected function handleComplained(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleComplaint($messageId, $timestamp);
    }

    /**
     * Handle an email failed event.
     */
    protected function handleFailed(string $messageId, array $content): void
    {
        $severity = \Arr::get($content, 'event-data.severity');
        $description = $this->extractFailureDescription($content);
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleFailure($messageId, $severity, $description, $timestamp);

        if ($severity === 'permanent') {
            $this->emailWebhookService->handlePermanentBounce($messageId, $timestamp);
        }
    }

    /**
     * Extract the event name from the payload.
     */
    protected function extractEventName(array $payload): string
    {
        return \Arr::get($payload, 'event-data.event');
    }

    /**
     * Extract the message ID from the payload.
     */
    protected function extractMessageId(array $payload): string
    {
        return $this->formatMessageId(\Arr::get($payload, 'event-data.message.headers.message-id'));
    }

    /**
     * Prepend/append crocodiles to message ID.
     */
    protected function formatMessageId(string $messageId): string
    {
        $messageId = strpos($messageId, '<') === 0
            ? $messageId
            : $messageId = '<' . $messageId . '>';

        return trim($messageId);
    }

    /**
     * Resolve the timestamp
     *
     * @param array $payload
     * @return Carbon
     */
    protected function extractTimestamp($payload)
    {
        return Carbon::createFromTimestamp(\Arr::get($payload, 'event-data.timestamp'));
    }

    /**
     * Resolve the failure description/message.
     */
    protected function extractFailureDescription(array $payload): string
    {
        if ($description = \Arr::get($payload, 'event-data.delivery-status.description')) {
            return $description;
        }

        if ($message = \Arr::get($payload, 'event-data.delivery-status.message')) {
            return $message;
        }

        return '';
    }

    /**
     * Check that the webhook is valid and that we should process it.
     */
    private function checkWebhookValidity(string $messageId, array $payload): bool
    {
        $message = Message::with('source.provider')->where('message_id', $messageId)->first();

        /** @var Provider|null $provider */
        $provider = $message->source->provider ?? null;

        if (!$provider) {
            return false;
        }

        /** @var string|null $signingKey */
        $signingKey = $provider->settings['key'] ?? null;

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
