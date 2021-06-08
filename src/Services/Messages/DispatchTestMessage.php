<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Messages;

use Exception;
use Illuminate\Support\Facades\Log;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Repositories\Campaigns\CampaignTenantRepositoryInterface;
use Sendportal\Base\Services\Content\MergeContentService;

class DispatchTestMessage
{
    /** @var ResolveEmailService */
    protected $resolveEmailService;

    /** @var RelayMessage */
    protected $relayMessage;

    /** @var MergeContentService */
    protected $mergeContent;

    /** @var CampaignTenantRepositoryInterface */
    protected $campaignTenant;

    public function __construct(
        CampaignTenantRepositoryInterface $campaignTenant,
        MergeContentService $mergeContent,
        ResolveEmailService $resolveEmailService,
        RelayMessage $relayMessage
    ) {
        $this->resolveEmailService = $resolveEmailService;
        $this->relayMessage = $relayMessage;
        $this->mergeContent = $mergeContent;
        $this->campaignTenant = $campaignTenant;
    }

    /**
     * @throws Exception
     */
    public function handle(int $workspaceId, int $campaignId, string $recipientEmail): ?string
    {
        $campaign = $this->resolveCampaign($workspaceId, $campaignId);

        if (!$campaign) {
            Log::error(
                'Unable to get campaign to send test message.',
                ['workspace_id' => $workspaceId, 'campaign_id' => $campaignId]
            );
            return null;
        }

        $message = $this->createTestMessage($campaign, $recipientEmail);

        $mergedContent = $this->getMergedContent($message);

        $emailService = $this->getEmailService($message);

        $trackingOptions = MessageTrackingOptions::fromCampaign($campaign);

        return $this->dispatch($message, $emailService, $trackingOptions, $mergedContent);
    }

    /**
     * @throws Exception
     */
    public function testService(int $workspaceId, EmailService $emailService, MessageOptions $options): ?string
    {
        $message = new Message([
            'workspace_id' => $workspaceId,
            'recipient_email' => $options->getTo(),
            'subject' => $options->getSubject(),
            'from_name' => 'Sendportal',
            'from_email' => $options->getFromEmail(),
            'hash' => 'abc123',
        ]);

        $trackingOptions = (new MessageTrackingOptions())->disable();

        return $this->dispatch($message, $emailService, $trackingOptions, $options->getBody());
    }

    /**
     * @throws Exception
     */
    protected function resolveCampaign(int $workspaceId, int $campaignId): ?Campaign
    {
        return $this->campaignTenant->find($workspaceId, $campaignId);
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

    protected function createTestMessage(Campaign $campaign, string $recipientEmail): Message
    {
        return new Message([
            'workspace_id' => $campaign->workspace_id,
            'source_type' => Campaign::class,
            'source_id' => $campaign->id,
            'recipient_email' => $recipientEmail,
            'subject' => '[Test] ' . $campaign->subject,
            'from_name' => $campaign->from_name,
            'from_email' => $campaign->from_email,
            'hash' => 'abc123',
        ]);
    }
}
