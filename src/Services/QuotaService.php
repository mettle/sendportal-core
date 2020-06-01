<?php

namespace Sendportal\Base\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Sendportal\Base\Adapters\BaseMailAdapter;
use Sendportal\Base\Factories\MailAdapterFactory;
use Sendportal\Base\Interfaces\QuotaServiceInterface;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\EmailServiceType;

class QuotaService implements QuotaServiceInterface
{
    public function exceedsQuota(Campaign $campaign): bool
    {
        switch ($campaign->email_service->type_id) {
            case EmailServiceType::SES:
                return $this->exceedsSesQuota($campaign);

            case EmailServiceType::SENDGRID:
            case EmailServiceType::MAILGUN:
            case EmailServiceType::POSTMARK:
                return false;
        }

        throw new \DomainException('Unrecognised email service type');
    }

    protected function resolveMailAdapter(EmailService $emailService): BaseMailAdapter
    {
        return app(MailAdapterFactory::class)->adapter($emailService);
    }

    protected function exceedsSesQuota(Campaign $campaign): bool
    {
        $mailAdapter = $this->resolveMailAdapter($campaign->email_service);

        $quota = $mailAdapter->getSendQuota();

        if (empty($quota)) {
            Log::error(
                'Failed to fetch quota from SES',
                [
                    'campaign_id' => $campaign->id,
                    'email_service_id' => $campaign->email_service->id,
                ]
            );

            return false;
        }

        $limit = Arr::get($quota, 'Max24HourSend');

        // -1 signifies an unlimited quota
        if ($limit === -1) {
            return true;
        }

        $sent = Arr::get($quota, 'SentLast24Hours');

        $remaining = (int)floor($limit - $sent);

        return $campaign->unsent_count > $remaining;
    }
}
