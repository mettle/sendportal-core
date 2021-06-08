<?php

namespace Sendportal\Base\Interfaces;

use Sendportal\Base\Models\EmailService;

interface QuotaServiceInterface
{
    public function exceedsQuota(EmailService $emailService, int $messageCount): bool;
}
