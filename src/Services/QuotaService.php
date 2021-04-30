<?php

namespace Sendportal\Base\Services;

use DomainException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Sendportal\Base\Adapters\BaseMailAdapter;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Factories\MailAdapterFactory;
use Sendportal\Base\Interfaces\QuotaServiceInterface;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\EmailServiceType;
use Sendportal\Base\Repositories\Messages\MessageTenantRepositoryInterface;

class QuotaService implements QuotaServiceInterface
{
    public function hasReachedMessageLimit(EmailService $emailService): bool
    {
        switch ($emailService->type_id) {
            case EmailServiceType::SES:
                return $this->exceedsSesQuota($emailService);

            case EmailServiceType::SMTP:
                return $this->exceedsSmtpQuota($emailService);

            case EmailServiceType::SENDGRID:
            case EmailServiceType::MAILGUN:
            case EmailServiceType::POSTMARK:
            case EmailServiceType::MAILJET:
                return false;
        }

        throw new \DomainException('Unrecognised email service type');
    }

    protected function resolveMailAdapter(EmailService $emailService): BaseMailAdapter
    {
        return app(MailAdapterFactory::class)->adapter($emailService);
    }

    protected function exceedsSesQuota(EmailService $emailService): bool
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

        return $sent >= $limit;
    }

    protected function exceedsSmtpQuota(EmailService $emailService): bool
    {
        $quotaLimit = Arr::get($emailService, 'settings.quota_limit');

        if (! $quotaLimit) {
            return false;
        }

        $quotaPeriod = Arr::get($emailService, 'settings.quota_period');

        switch($quotaPeriod) {
            case EmailService::QUOTA_PERIOD_HOUR:
                $start = now()->subHour();
                break;

            case EmailService::QUOTA_PERIOD_DAY:
                $start = now()->subDay();
                break;

            default:
                throw new DomainException('Unrecognised quota period');
        }

        $messageCount = app(MessageTenantRepositoryInterface::class)
            ->countForSourcesBetween(
                Sendportal::currentWorkspaceId(),
                $emailService->campaigns->pluck('id')->toArray(),
                $start,
                now()
            );

        return $messageCount >= $quotaLimit;
    }
}
