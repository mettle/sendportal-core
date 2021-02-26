<?php

namespace Sendportal\Base\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Sendportal\Base\Adapters\BaseMailAdapter;
use Sendportal\Base\Factories\MailAdapterFactory;
use Sendportal\Base\Interfaces\QuotaServiceInterface;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\EmailServiceType;

class QuotaService implements QuotaServiceInterface
{
    public function exceedsQuota(EmailService $emailService, int $messageCount): bool
    {
        switch ($emailService->type_id) {
            case EmailServiceType::SES:
                return $this->exceedsSesQuota($emailService, $messageCount);

            case EmailServiceType::SENDGRID:
            case EmailServiceType::MAILGUN:
            case EmailServiceType::POSTMARK:
            case EmailServiceType::MAILJET:
            case EmailServiceType::SMTP:
                return false;
        }

        throw new \DomainException('Unrecognised email service type');
    }

    protected function resolveMailAdapter(EmailService $emailService): BaseMailAdapter
    {
        return app(MailAdapterFactory::class)->adapter($emailService);
    }

    protected function exceedsSesQuota(EmailService $emailService, int $messageCount): bool
    {
        $mailAdapter = $this->resolveMailAdapter($emailService);

        $quota = $mailAdapter->getSendQuota();

        if (empty($quota)) {
            Log::error(
                'Failed to fetch quota from SES',
                [
                    'email_service_id' => $emailService->id,
                ]
            );

            return false;
        }

        $limit = Arr::get($quota, 'Max24HourSend');

        // -1 signifies an unlimited quota
        if ($limit === -1) {
            return false;
        }

        $sent = Arr::get($quota, 'SentLast24Hours');

        $remaining = (int)floor($limit - $sent);

        return $messageCount > $remaining;
    }
}
