<?php

namespace Sendportal\Base\Services\Webhooks;

use Sendportal\Base\Interfaces\EmailWebhookServiceInterface;
use Sendportal\Automations\Models\AutomationSchedule;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\MessageFailure;
use Sendportal\Base\Models\MessageUrl;
use Sendportal\Base\Models\UnsubscribeEventType;
use Carbon\Carbon;
use Illuminate\Support\Str;

class EmailWebhookService implements EmailWebhookServiceInterface
{
    /**
     * @inheritDoc
     */
    public function handleDelivery($messageId, Carbon $timestamp)
    {
        \DB::table('messages')->where('message_id', $messageId)->whereNull('delivered_at')->update([
            'delivered_at' => $timestamp
        ]);
    }

    /**
     * @inheritDoc
     */
    public function handleOpen($messageId, Carbon $timestamp, $ipAddress)
    {
        if (! $message = Message::where('message_id', $messageId)->first()) {
            return;
        }

        if (! $message->opened_at) {
            $message->opened_at = $timestamp;
            $message->ip = $ipAddress;
        }

        $message->open_count = $message->open_count + 1;
        $message->save();

        // @todo not sure that this give much value? We can just derive the count
        // from the messages table
        if (\Sendportal\Base\Facades\Helper::isPro() && $message->isAutomation()) {
            $automationStep = $this->resolveAutomationStepFromMessage($message);

            \DB::table('automation_steps')->where('id', $automationStep->id)->increment('open_count');
        }
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleClick($messageId, Carbon $timestamp, $url)
    {
        /* @var Message $message */
        if (! $message = Message::where('message_id', $messageId)->first()) {
            return;
        }

        // don't track unsubscribe clicks
        if (Str::contains($url, '/subscriptions/unsubscribe')) {
            return;
        }

        if (! $message->clicked_at) {
            $message->clicked_at = $timestamp;
        }

        // Since you have to open a campaign to click a link inside it, we'll
        // consider those clicks as opens even if the tracking image didn't load.
        if (! $message->opened_at) {
            $message->open_count = $message->open_count + 1;
            $message->opened_at = $timestamp;
        }

        $message->click_count = $message->click_count + 1;
        $message->save();

        // @todo not sure that this give much value? We can just derive the count
        // from the messages table
        if (\Sendportal\Base\Facades\Helper::isPro() && $message->isAutomation()) {
            $automationStep = $this->resolveAutomationStepFromMessage($message);

            \DB::table('automation_steps')->where('id', $automationStep->id)->increment('click_count');
        }

        $messageUrl = MessageUrl::updateOrCreate([
            'hash' => md5($message->source_type . '_' . $message->source_id . '_' . $url),
        ], [
            'source_type' => $message->source_type,
            'source_id' => $message->source_id,
            'url' => $url
        ]);

        if (!$messageUrl->wasRecentlyCreated) {
            \DB::table('message_urls')->where('id', $messageUrl->id)->increment('click_count');
        }
    }

    /**
     * @inheritDoc
     */
    public function handleComplaint($messageId, $timestamp)
    {
        /* @var Message $message */
        if (! $message = Message::where('message_id', $messageId)->first()) {
            return;
        }

        if (! $message->complained_at) {
            $message->unsubscribed_at = $timestamp;
            $message->save();
        }

        return $this->unsubscribe($messageId, UnsubscribeEventType::COMPLAINT);
    }

    /**
     * @inheritDoc
     */
    public function handlePermanentBounce($messageId, $timestamp)
    {
        /* @var Message $message */
        if (! $message = Message::where('message_id', $messageId)->first()) {
            return;
        }

        if (! $message->bounced_at) {
            $message->bounced_at = $timestamp;
            $message->save();
        }

        return $this->unsubscribe($messageId, UnsubscribeEventType::BOUNCE);
    }

    /**
     * @inheritDoc
     */
    public function handleFailure($messageId, $severity, $description, $timestamp)
    {
        /* @var Message $message */
        if (! $message = Message::where('message_id', $messageId)->first()) {
            return;
        }

        $failure = new MessageFailure([
            'severity' => $severity,
            'description' => $description,
            'failed_at' => $timestamp,
        ]);

        return $message->failures()->save($failure);
    }

    /**
     * Unsubscribe a subscriber
     *
     * @param $messageId
     * @param $typeId
     */
    protected function unsubscribe($messageId, $typeId)
    {
        $subscriberId = \DB::table('messages')->where('message_id', $messageId)->value('subscriber_id');

        if (! $subscriberId) {
            return;
        }

        \DB::table('subscribers')->where('id', $subscriberId)->update([
            'unsubscribed_at' => now(),
            'unsubscribe_event_id' => $typeId,
            'updated_at' => now()
        ]);
    }

    /**
     * Resolve the automation step from a message
     *
     * @param Message $message
     * @return mixed
     * @throws \Exception
     */
    protected function resolveAutomationStepFromMessage(Message $message)
    {
        if (\Sendportal\Base\Facades\Helper::isPro() && $message->source_type != AutomationSchedule::class) {
            throw new \Exception('Unable to resolve source for message id=' . $message->id);
        }

        $automationSchedule = \DB::table('automation_schedules')->where('id', $message->source_id)->first();

        return \DB::table('automation_steps')->where('id', $automationSchedule->automation_step_id)->first();
    }
}
