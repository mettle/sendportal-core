<?php

namespace Sendportal\Base\Interfaces;

use Sendportal\Base\Models\EmailService;

interface QuotaServiceInterface
{
    public function hasReachedMessageLimit(EmailService $emailService): bool;
}
