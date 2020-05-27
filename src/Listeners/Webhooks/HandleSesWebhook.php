<?php

declare(strict_types=1);

namespace Sendportal\Base\Listeners\Webhooks;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Sendportal\Base\Events\Webhooks\SesWebhookReceived;
use Sendportal\Base\Services\Webhooks\EmailWebhookService;

class HandleSesWebhook implements ShouldQueue
{
    /** @var EmailWebhookService */
    private $emailWebhookService;

    public function __construct(EmailWebhookService $emailWebhookService)
    {
        $this->emailWebhookService = $emailWebhookService;
    }

    /**
     * @throws Exception
     */
    public function handle(SesWebhookReceived $event): void
    {
        if ($event->payloadType === 'SubscriptionConfirmation') {
            $subscribeUrl = Arr::get($event->payload, 'SubscribeURL');

            $httpClient = new Client();
            $httpClient->get($subscribeUrl);

            Log::info('subscribing', ['url' => $subscribeUrl]);
            return;
        }

        $event = json_decode(Arr::get($event->payload, 'Message'), true);

        if (!$event) {
            return;
        }

        $this->processEmailEvent($event);
    }

    /**
     * @throws Exception
     */
    private function processEmailEvent(array $event): void
    {
        /** @var string|null $messageId */
        $messageId = $event['mail']['messageId'] ?? null;

        /** @var string|null $eventType */
        $eventType = $event['eventType'] ?? null;

        if (!$eventType || !$messageId) {
            return;
        }

        // https://docs.aws.amazon.com/ses/latest/DeveloperGuide/event-publishing-retrieving-sns-examples.html#event-publishing-retrieving-sns-open
        // Bounce, Complaint, Message, Send Email, Reject Event, Open Event, Click Event
        switch ($eventType) {
            case 'click':
                $this->handleClick($messageId, $event);
                break;

            case 'open':
                $this->handleOpen($messageId, $event);
                break;

            case 'reject':
                $this->handleReject($messageId, $event);
                break;

            case 'delivery':
                $this->handleDelivery($messageId, $event);
                break;

            case 'complaint':
                $this->handleComplaint($messageId, $event);
                break;

            case 'bounce':
                $this->handleBounce($messageId, $event);
                break;
        }
    }

    /**
     * @throws Exception
     */
    private function handleClick(string $messageId, array $event): void
    {
        // https://docs.aws.amazon.com/ses/latest/DeveloperGuide/event-publishing-retrieving-sns-examples.html#event-publishing-retrieving-sns-click
        // https://docs.aws.amazon.com/ses/latest/DeveloperGuide/event-publishing-retrieving-sns-contents.html#event-publishing-retrieving-sns-contents-click-object
        $link = Arr::get($event, 'click.link');
        $timestamp = Carbon::parse(Arr::get($event, 'click.timestamp'));

        $this->emailWebhookService->handleClick($messageId, $timestamp, $link);
    }

    /**
     * @throws Exception
     */
    private function handleOpen(string $messageId, array $event): void
    {
        // https://docs.aws.amazon.com/ses/latest/DeveloperGuide/event-publishing-retrieving-sns-contents.html#event-publishing-retrieving-sns-contents-open-object
        // https://docs.aws.amazon.com/ses/latest/DeveloperGuide/event-publishing-retrieving-sns-examples.html#event-publishing-retrieving-sns-open
        $ipAddress = Arr::get($event, 'open.ipAddress');
        $timestamp = Carbon::parse(Arr::get($event, 'open.timestamp'));

        $this->emailWebhookService->handleOpen($messageId, $timestamp, $ipAddress);
    }

    private function handleReject(string $messageId, array $event): void
    {
        // https://docs.aws.amazon.com/ses/latest/DeveloperGuide/event-publishing-retrieving-sns-contents.html#event-publishing-retrieving-sns-contents-reject-object
        // https://docs.aws.amazon.com/ses/latest/DeveloperGuide/event-publishing-retrieving-sns-examples.html#event-publishing-retrieving-sns-reject
    }

    private function handleDelivery(string $messageId, array $event): void
    {
        // https://docs.aws.amazon.com/ses/latest/DeveloperGuide/ses/latest/DeveloperGuide/ses/latest/DeveloperGuide/notification-contents.html.html#delivery-object
        // https://docs.aws.amazon.com/ses/latest/DeveloperGuide/event-publishing-retrieving-sns-examples.html#event-publishing-retrieving-sns-delivery
        $timestamp = Carbon::parse(Arr::get($event, 'delivery.timestamp'));

        $this->emailWebhookService->handleDelivery($messageId, $timestamp);
    }

    private function handleComplaint(string $messageId, array $event): void
    {
        // https://docs.aws.amazon.com/ses/latest/DeveloperGuide/notification-contents.html#complaint-object
        // $complaint = \Arr::get($event, 'complaint');
        // $feedbackType = \Arr::get($complaint, 'complaintFeedbackType');

        // abuse — Indicates unsolicited email or some other kind of email abuse.
        // auth-failure — Email authentication failure report.
        // fraud — Indicates some kind of fraud or phishing activity.
        // not-spam — Indicates that the entity providing the report does not consider the message to be spam. This may be used to correct a message that was incorrectly tagged or categorized as spam.
        // other — Indicates any other feedback that does not fit into other registered types.
        // virus — Reports that a virus is found in the originating message.
        //
        // https://aws.amazon.com/blogs/messaging-and-targeting/handling-bounces-and-complaints/

        $timestamp = Carbon::parse(Arr::get($event, 'complaint.timestamp'));

        $this->emailWebhookService->handleComplaint($messageId, $timestamp);
    }

    private function handleBounce(string $messageId, array $event): void
    {
        // https://docs.aws.amazon.com/ses/latest/DeveloperGuide/notification-contents.html#bounce-object
        $bounceType = Arr::get($event, 'bounce.bounceType');
        $timestamp = Carbon::parse(Arr::get($event, 'bounce.timestamp'));

        // https://aws.amazon.com/blogs/messaging-and-targeting/handling-bounces-and-complaints/
        if (strtolower($bounceType) === 'permanent') {
            $this->emailWebhookService->handlePermanentBounce($messageId, $timestamp);
        }
    }
}
