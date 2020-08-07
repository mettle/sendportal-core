<?php

namespace Sendportal\Base\Services\Messages;

use Exception;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Repositories\Campaigns\CampaignTenantRepositoryInterface;
use Sendportal\Pro\Repositories\AutomationScheduleRepository;

class ResolveEmailService
{
    /** @var CampaignTenantRepositoryInterface */
    protected $campaignTenantRepository;

    public function __construct(CampaignTenantRepositoryInterface $campaignTenantRepository)
    {
        $this->campaignTenantRepository = $campaignTenantRepository;
    }

    /**
     * @throws Exception
     */
    public function handle(Message $message): EmailService
    {
        if ($message->isAutomation()) {
            return $this->resolveAutomationEmailService($message);
        }

        if ($message->isCampaign()) {
            return $this->resolveCampaignEmailService($message);
        }

        throw new Exception('Unable to resolve email service for message id=' . $message->id);
    }

    /**
     * Resolve the email service for an automation
     *
     * @param Message $message
     * @return EmailService
     * @throws Exception
     */
    protected function resolveAutomationEmailService(Message $message): EmailService
    {
        if (!$automationSchedule = app(AutomationScheduleRepository::class)->find(
            $message->source_id,
            ['automation_step.automation.email_service.type']
        )) {
            throw new Exception('Unable to resolve automation schedule for message id=' . $message->id);
        }

        if (!$emailService = $automationSchedule->automation_step->automation->email_service) {
            throw new Exception('Unable to resolve email service for message id=' . $message->id);
        }

        return $emailService;
    }

    /**
     * Resolve the provider for a campaign
     *
     * @param Message $message
     * @return EmailService
     * @throws Exception
     */
    protected function resolveCampaignEmailService(Message $message): EmailService
    {
        if (! $campaign = $this->campaignTenantRepository->find($message->workspace_id, $message->source_id, ['email_service'])) {
            throw new Exception('Unable to resolve campaign for message id=' . $message->id);
        }

        if (! $emailService = $campaign->email_service) {
            throw new Exception('Unable to resolve email service for message id=' . $message->id);
        }

        return $emailService;
    }
}
