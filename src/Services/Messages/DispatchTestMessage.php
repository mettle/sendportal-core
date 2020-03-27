<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Messages;

use Exception;
use Illuminate\Support\Facades\Log;
use Sendportal\Base\Interfaces\CampaignTenantRepository;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\Provider;
use Sendportal\Base\Services\Content\MergeContent;

class DispatchTestMessage
{
    /** @var ResolveProvider */
    protected $resolveProvider;

    /** @var RelayMessage */
    protected $relayMessage;

    /** @var MergeContent */
    protected $mergeContent;

    /** @var CampaignTenantRepository */
    protected $campaignTenant;

    public function __construct(
        CampaignTenantRepository $campaignTenant,
        MergeContent $mergeContent,
        ResolveProvider $resolveProvider,
        RelayMessage $relayMessage
    ) {
        $this->resolveProvider = $resolveProvider;
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
            Log::error('Unable to get campaign to send test message.',
                ['workspace_id' => $workspaceId, 'campaign_id' => $campaignId]);
            return null;
        }

        $message = $this->createTestMessage($campaign, $recipientEmail);

        $mergedContent = $this->getMergedContent($message);

        $provider = $this->getProvider($message);

        $trackingOptions = MessageTrackingOptions::fromCampaign($campaign);

        return $this->dispatch($message, $provider, $trackingOptions, $mergedContent);
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

