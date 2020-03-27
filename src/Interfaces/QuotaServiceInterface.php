<?php

namespace Sendportal\Base\Interfaces;

use Sendportal\Base\Models\Campaign;

interface QuotaServiceInterface
{
    public function campaignCanBeSent(Campaign $campaign): bool;
}
