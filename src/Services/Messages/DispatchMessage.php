<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Messages;

use Exception;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Log;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\CampaignStatus;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Services\Content\MergeContent;

class DispatchMessage
{
    /** @var ResolveEmailService */
    protected $resolveEmailService;

    /** @var RelayMessage */
    protected $relayMessage;

    /** @var MergeContent */
    protected $mergeContent;

    /** @var MarkAsSent */
    protected $markAsSent;

    public function __construct(
        MergeContent $mergeContent,
        ResolveEmailService $resolveEmailService,
        RelayMessage $relayMessage,
        MarkAsSent $markAsSent
    ) {
        $this->resolveEmailService = $resolveEmailService;
        $this->relayMessage = $relayMessage;
        $this->mergeContent = $mergeContent;
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
        return $this->mergeContent->handle($message);
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
            ->setSubject($message->subject)
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
        $data = $message->newQuery()
            ->toBase()
            ->select(['messages.sent_at', 'campaigns.status_id'])
            ->leftJoin('campaigns', static function (JoinClause $join) {
                $join->on('messages.source_id', '=', 'campaigns.id')
                      ->where('messages.source_type', Campaign::class);
            })
            ->first();

        if (! $data) {
            return false;
        }

        return !(bool)$data->sent_at && $data->status_id !== CampaignStatus::STATUS_CANCELLED;
    }
}
