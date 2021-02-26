<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Messages;

use Exception;
use Illuminate\Support\Facades\Log;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\CampaignStatus;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Services\Content\MergeContentService;
use Sendportal\Base\Services\Content\MergeSubjectService;

class DispatchMessage
{
    /** @var ResolveEmailService */
    protected $resolveEmailService;

    /** @var RelayMessage */
    protected $relayMessage;

    /** @var MergeContentService */
    protected $mergeContentService;

    /** @var MergeSubjectService */
    protected $mergeSubjectService;

    /** @var MarkAsSent */
    protected $markAsSent;

    public function __construct(
        MergeContentService $mergeContentService,
        MergeSubjectService $mergeSubjectService,
        ResolveEmailService $resolveEmailService,
        RelayMessage $relayMessage,
        MarkAsSent $markAsSent
    ) {
        $this->mergeContentService = $mergeContentService;
        $this->mergeContentService = $mergeSubjectService;
        $this->resolveEmailService = $resolveEmailService;
        $this->relayMessage = $relayMessage;
        $this->markAsSent = $markAsSent;
    }

    /**
     * @throws Exception
     */
    public function handle(Message $message): ?string
    {
        if (!$this->isValidMessage($message)) {
            Log::info('Message is not valid, skipping id=' . $message->id);

            return null;
        }

        $mergedContent = $this->getMergedContent($message);

        $emailService = $this->getEmailService($message);

        $trackingOptions = MessageTrackingOptions::fromMessage($message);

        $messageId = $this->dispatch($message, $emailService, $trackingOptions, $mergedContent);

        $this->markSent($message, $messageId);

        return $messageId;
    }

    /**
     * @throws Exception
     */
    protected function getMergedContent(Message $message): string
    {
        return $this->mergeContentService->handle($message);
    }

    /**
     * @throws Exception
     */
    protected function dispatch(Message $message, EmailService $emailService, MessageTrackingOptions $trackingOptions, string $mergedContent): ?string
    {
        $messageOptions = (new MessageOptions)
            ->setTo($message->recipient_email)
            ->setFromEmail($message->from_email)
            ->setFromName($message->from_name)
            ->setSubject($this->mergeSubjectService->handle($message))
            ->setTrackingOptions($trackingOptions);

        $messageId = $this->relayMessage->handle($mergedContent, $messageOptions, $emailService);

        Log::info('Message has been dispatched.', ['message_id' => $messageId]);

        return $messageId;
    }

    /**
     * @throws Exception
     */
    protected function getEmailService(Message $message): EmailService
    {
        return $this->resolveEmailService->handle($message);
    }

    protected function markSent(Message $message, string $messageId): Message
    {
        return $this->markAsSent->handle($message, $messageId);
    }

    protected function isValidMessage(Message $message): bool
    {
        if ($message->sent_at) {
            return false;
        }

        if (!$message->isCampaign()) {
            return true;
        }

        $campaign = Campaign::find($message->source_id);

        if (!$campaign) {
            return false;
        }

        return $campaign->status_id !== CampaignStatus::STATUS_CANCELLED;
    }
}
