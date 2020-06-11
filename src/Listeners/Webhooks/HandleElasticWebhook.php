<?php

declare(strict_types=1);

namespace Sendportal\Base\Listeners\Webhooks;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Sendportal\Base\Events\Webhooks\ElasticWebhookReceived;
use Sendportal\Base\Events\Webhooks\SendgridWebhookReceived;
use Sendportal\Base\Services\Webhooks\EmailWebhookService;

class HandleElasticWebhook implements ShouldQueue
{
    /** @var string */
    public $queue = 'sendportal-webhook-process';

    /** @var EmailWebhookService */
    private $emailWebhookService;

    public function __construct(EmailWebhookService $emailWebhookService)
    {
        $this->emailWebhookService = $emailWebhookService;
    }

    public function handle(ElasticWebhookReceived $event): void
    {
        // https://help.elasticemail.com/en/articles/2376855-how-to-manage-http-web-notifications-webhooks
        $messageId = $this->extractMessageId($event->payload);
        $eventName = $this->extractEventName($event->payload);

        Log::info('Processing ElasticEmail webhook.', ['type' => $eventName, 'message_id' => $messageId]);

        switch ($eventName) {
            case 'Sent':
                $this->handleSent($messageId, $event->payload);
                break;

            case 'Opened':
                $this->handleOpen($messageId, $event->payload);
                break;

            case 'Clicked':
                $this->handleClick($messageId, $event->payload);
                break;

            case 'AbuseReport':
                $this->handleAbuseReport($messageId, $event->payload);
                break;

            case 'Error':
                $this->handleError($messageId, $event->payload);
                break;

            case 'Unsubscribed':
                $this->handleUnsubscribe($messageId, $event->payload);
                break;

            default:
                throw new RuntimeException("Unknown ElasticEmail webhook event type '{$eventName}'.");
        }
    }

    private function extractMessageId(array $payload): string
    {
        return Arr::get($payload, 'messageid');
    }

    private function extractEventName(array $payload): string
    {
        return Arr::get($payload, 'status');
    }

    private function handleSent(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleDelivery($messageId, $timestamp);
    }

    private function extractTimestamp($payload): Carbon
    {
        return Carbon::createFromDate(Arr::get($payload, 'date'));
    }

    private function handleOpen(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleOpen($messageId, $timestamp, null);
    }

    private function handleClick(string $messageId, array $content): void
    {
        $url = Arr::get($content, 'target');
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleClick($messageId, $timestamp, $url);
    }

    private function handleAbuseReport(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleComplaint($messageId, $timestamp);
    }

    private function handleError(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content);
        $description = Arr::get($content, 'category');

        //TODO Create method to determine the severity of the failure:
        // Ignore|Spam|BlackListed|NoMailbox|GreyListed|Throttled|Timeout|ConnectionProblem|SPFProblem|AccountProblem|DNSProblem|WhitelistingProblem|CodeError|ManualCancel|ConnectionTerminated|ContentFilter|NotDelivered|Unknown

        $this->emailWebhookService->handleFailure($messageId, 'Temporary', $description, $timestamp);
    }

    private function handleUnsubscribe(string $messageId, array $content): void
    {
        $timestamp = $this->extractTimestamp($content);

        $this->emailWebhookService->handleComplaint($messageId, $timestamp);
    }
}
