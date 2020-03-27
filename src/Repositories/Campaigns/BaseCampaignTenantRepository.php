<?php

declare(strict_types=1);

namespace Sendportal\Base\Repositories\Campaigns;

use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Repositories\BaseTenantRepository;
use Sendportal\Base\Traits\SecondsToHms;

abstract class BaseCampaignTenantRepository extends BaseTenantRepository implements CampaignTenantRepositoryInterface
{
    use SecondsToHms;

    /** @var string */
    protected $modelName = Campaign::class;
}
