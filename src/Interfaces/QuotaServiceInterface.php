<?php

namespace Sendportal\Base\Interfaces;

use Sendportal\Base\Models\Campaign;

interface QuotaServiceInterface
{
    public function exceedsQuota(Campaign $campaign): bool;
}
