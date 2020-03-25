<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Messages;

use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\Provider;
use Sendportal\Base\Services\Content\MergeContent;
use Exception;
use Illuminate\Support\Facades\Log;

class DispatchMessage
{
    /** @var ResolveProvider */
    protected $resolveProvider;

    /** @var RelayMessage */
    protected $relayMessage;

    /** @var MergeContent */
    protected $mergeContent;

    /** @var MarkAsSent */
    protected $markAsSent;

    public function __construct(
        MergeContent $mergeContent,
        ResolveProvider $resolveProvider,
        RelayMessage $relayMessage,
        MarkAsSent $markAsSent
    ) {
        $this->resolveProvider = $resolveProvider;
        $this->relayMessage = $relayMessage;
        $this->mergeContent = $mergeContent;
        $this->markAsSent = $markAsSent;
    }

    /**
     * Returns the message_id from the provider
     *
     * @throws Exception
     */
    public function handle(Message $message): ?string
    {
        if (!$this->isValidMessage($message)) {
            Log::info('Message is not valid, skipping id=' . $message->id);

            return null;
        }

        $mergedContent = $this->getMergedContent($message);

        $provider = $this->getProvider($message);

        $trackingOptions = MessageTrackingOptions::fromMessage($message);

        $messageId = $this->dispatch($message, $provider, $trackingOptions, $mergedContent);

        $this->markSent($message, $messageId);

        return $messageId;
    }

    /**
     * @throws Exception
     */
    protected function getMergedContent(Message $message): string
    {
        return $this->mergeContent->handle($message);
    }

    /**
     * @throws Exception
     */
    protected function dispatch(Message $message, Provider $provider, MessageTrackingOptions $trackingOptions, string $mergedContent): ?string
    {
        $messageOptions = (new MessageOptions)
            ->setTo($message->recipient_email)
            ->setFrom($message->from_email)
            ->setSubject($message->subject)
            ->setTrackingOptions($trackingOptions);

        $messageId = $this->relayMessage->handle($mergedContent, $messageOptions, $provider);

        Log::info('Message has been dispatched.', ['message_id' => $messageId]);

        return $messageId;
    }

    /**
     * @throws Exception
     */
    protected function getProvider(Message $message): Provider
    {
        return $this->resolveProvider->handle($message);
    }

    protected function markSent(Message $message, string $messageId): Message
    {
        return $this->markAsSent->handle($message, $messageId);
    }

    /**
     * Check that the message has not already been sent by getting
     * a fresh db record
     */
    protected function isValidMessage(Message $message): bool
    {
        $message->refresh();

        return !(bool)$message->sent_at;
    }
}
