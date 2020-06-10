<?php

declare(strict_types=1);

namespace Sendportal\Base\Repositories\Subscribers;

use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Sendportal\Base\Interfaces\BaseTenantInterface;
use Sendportal\Base\Models\Subscriber;

interface SubscriberTenantRepositoryInterface extends BaseTenantInterface
{
    public function syncSegments(Subscriber $subscriber, array $segments = []);

    public function countActive($workspaceId): int;

    public function getRecentSubscribers(int $workspaceId): Collection;

    public function getGrowthChartData(CarbonPeriod $period, int $workspaceId): array;
}
