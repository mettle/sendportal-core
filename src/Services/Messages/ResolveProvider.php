<?php

namespace Sendportal\Base\Services\Messages;

use Exception;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\Provider;
use Sendportal\Base\Repositories\Campaigns\CampaignTenantRepository;
use Sendportal\Pro\Repositories\AutomationScheduleRepository;

class ResolveProvider
{
    /** @var CampaignTenantRepository */
    protected $campaignTenantRepository;

    public function __construct(CampaignTenantRepository $campaignTenantRepository)
    {
        $this->campaignTenantRepository = $campaignTenantRepository;
    }

    /**
     * @throws Exception
     */
    public function handle(Message $message): Provider
    {
        if ($message->isAutomation()) {
            return $this->resolveAutomationProvider($message);
        }

        if ($message->isCampaign()) {
            return $this->resolveCampaignProvider($message);
        }

        throw new Exception('Unable to resolve provider for message id=' . $message->id);
    }

    /**
     * Resolve the provider for an automation
     *
     * @param Message $message
     * @return Provider
     * @throws Exception
     */
    protected function resolveAutomationProvider(Message $message): Provider
    {
        if (!$automationSchedule = app(AutomationScheduleRepository::class)->find($message->source_id,
            ['automation_step.automation.provider.type'])) {
            throw new Exception('Unable to resolve automation schedule for message id=' . $message->id);
        }

        if (!$provider = $automationSchedule->automation_step->automation->provider) {
            throw new Exception('Unable to resolve provider for message id=' . $message->id);
        }

        return $provider;
    }

    /**
     * Resolve the provider for a campaign
     *
     * @param Message $message
     * @return Provider
     * @throws Exception
     */
    protected function resolveCampaignProvider(Message $message): Provider
    {
        if (! $campaign = $this->campaignTenantRepository->find($message->workspace_id, $message->source_id, ['provider'])) {
            throw new Exception('Unable to resolve campaign for message id=' . $message->id);
        }

        if (! $provider = $campaign->provider) {
            throw new Exception('Unable to resolve provider for message id=' . $message->id);
        }

        return $provider;
    }
}
