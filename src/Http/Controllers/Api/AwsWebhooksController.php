<?php

namespace Sendportal\Base\Http\Controllers\Api;

use Sendportal\Base\Http\Controllers\Controller;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Sendportal\Base\Interfaces\EmailWebhookServiceInterface;

class AwsWebhooksController extends Controller
{

    /**
     * @var EmailWebhookServiceInterface
     */
    protected $emailWebhookService;

    /**
     * @param EmailWebhookServiceInterface $emailWebhookService
     */
    public function __construct(
        EmailWebhookServiceInterface $emailWebhookService
    ) {
        $this->emailWebhookService = $emailWebhookService;
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function handle()
    {
        $content = json_decode(request()->getContent(), true);

        if (array_get($content, 'Type') == 'SubscriptionConfirmation') {
            $subscribeUrl = array_get($content, 'SubscribeURL');

            $httpClient = new Client();
            $httpClient->get($subscribeUrl);

            \Log::info('subscribing', ['url' => $subscribeUrl]);

            return response('OK');
        }

        if (! array_get($content, 'Type') == 'Notification') {
            return response('OK (not processed).');
        }

        if ($event = json_decode(array_get($content, 'Message'), true)) {
            return $this->processEmailEvent($event);
        }

        return response('OK (not processed).');
    }

    /**
     * @param array $event
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|void
     */
    protected function processEmailEvent(array $event)
    {
        $messageId = array_get($event, 'mail.messageId');

        if (! $eventType = array_get($event, 'eventType')) {
            return response('OK (not processed).');
        }

        $method = 'handle' . studly_case(str_slug($eventType, ''));

        // https://docs.aws.amazon.com/ses/latest/DeveloperGuide/event-publishing-retrieving-sns-examples.html#event-publishing-retrieving-sns-open
        // Bounce, Complaint, Message, Send Email, Reject Event, Open Event, Click Event
        if (method_exists($this, $method)) {
            $this->{$method}($messageId, $event);

            return response('OK');
        }

        abort(404);
    }

    /**
     * @param $messageId
     * @param array $event
     */
    public function handleClick($messageId, array $event)
    {
        // https://docs.aws.amazon.com/ses/latest/DeveloperGuide/event-publishing-retrieving-sns-examples.html#event-publishing-retrieving-sns-click
        // https://docs.aws.amazon.com/ses/latest/DeveloperGuide/event-publishing-retrieving-sns-contents.html#event-publishing-retrieving-sns-contents-click-object
        $link = array_get($event, 'click.link');
        $timestamp = Carbon::parse(array_get($event, 'click.timestamp'));

        $this->emailWebhookService->handleClick($messageId, $timestamp, $link);
    }

    /**
     * @param $messageId
     * @param array $event
     */
    public function handleOpen($messageId, array $event)
    {
        // https://docs.aws.amazon.com/ses/latest/DeveloperGuide/event-publishing-retrieving-sns-contents.html#event-publishing-retrieving-sns-contents-open-object
        // https://docs.aws.amazon.com/ses/latest/DeveloperGuide/event-publishing-retrieving-sns-examples.html#event-publishing-retrieving-sns-open
        $ipAddress = array_get($event, 'open.ipAddress');
        $timestamp = Carbon::parse(array_get($event, 'open.timestamp'));

        $this->emailWebhookService->handleOpen($messageId, $timestamp, $ipAddress);
    }

    /**
     * @param $messageId
     * @param array $event
     */
    public function handleReject($messageId, array $event)
    {
        // https://docs.aws.amazon.com/ses/latest/DeveloperGuide/event-publishing-retrieving-sns-contents.html#event-publishing-retrieving-sns-contents-reject-object
        // https://docs.aws.amazon.com/ses/latest/DeveloperGuide/event-publishing-retrieving-sns-examples.html#event-publishing-retrieving-sns-reject
    }

    /**
     * @param $messageId
     * @param array $event
     */
    protected function handleDelivery($messageId, array $event)
    {
        // https://docs.aws.amazon.com/ses/latest/DeveloperGuide/ses/latest/DeveloperGuide/ses/latest/DeveloperGuide/notification-contents.html.html#delivery-object
        // https://docs.aws.amazon.com/ses/latest/DeveloperGuide/event-publishing-retrieving-sns-examples.html#event-publishing-retrieving-sns-delivery
        $timestamp = Carbon::parse(array_get($event, 'delivery.timestamp'));

        $this->emailWebhookService->handleDelivery($messageId, $timestamp);
    }

    /**
     * @param $messageId
     * @param array $event
     */
    protected function handleComplaint($messageId, array $event)
    {
        // https://docs.aws.amazon.com/ses/latest/DeveloperGuide/notification-contents.html#complaint-object
        // $complaint = array_get($event, 'complaint');
        // $feedbackType = array_get($complaint, 'complaintFeedbackType');

        // abuse — Indicates unsolicited email or some other kind of email abuse.
        // auth-failure — Email authentication failure report.
        // fraud — Indicates some kind of fraud or phishing activity.
        // not-spam — Indicates that the entity providing the report does not consider the message to be spam. This may be used to correct a message that was incorrectly tagged or categorized as spam.
        // other — Indicates any other feedback that does not fit into other registered types.
        // virus — Reports that a virus is found in the originating message.
        //
        // https://aws.amazon.com/blogs/messaging-and-targeting/handling-bounces-and-complaints/

        $timestamp = Carbon::parse(array_get($event, 'complaint.timestamp'));

        $this->emailWebhookService->handleComplaint($messageId, $timestamp);
    }

    /**
     * @param $messageId
     * @param array $event
     */
    protected function handleBounce($messageId, array $event)
    {
        // https://docs.aws.amazon.com/ses/latest/DeveloperGuide/notification-contents.html#bounce-object
        $bounceType = array_get($event, 'bounce.bounceType');

        // https://aws.amazon.com/blogs/messaging-and-targeting/handling-bounces-and-complaints/
        if (strtolower($bounceType) == 'permanent') {
            $this->emailWebhookService->handlePermanentBounce($messageId);
        }
    }
}
