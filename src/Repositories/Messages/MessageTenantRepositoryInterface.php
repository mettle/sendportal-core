<?php

declare(strict_types=1);

namespace Sendportal\Base\Repositories\Messages;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Sendportal\Base\Interfaces\BaseTenantInterface;

interface MessageTenantRepositoryInterface extends BaseTenantInterface
{
    public function paginateWithSource(int $workspaceId, string $orderBy = 'name', array $relations = [], int $paginate = 25, array $parameters = []): LengthAwarePaginator;

    public function recipients(int $workspaceId, string $sourceType, int $sourceId): LengthAwarePaginator;

    public function countForSourcesBetween(int $workspaceId, array $sourceIds, Carbon $start, Carbon $end): int;

    public function opens(int $workspaceId, string $sourceType, int $sourceId): LengthAwarePaginator;

    public function clicks(int $workspaceId, string $sourceType, int $sourceId): LengthAwarePaginator;

    public function bounces(int $workspaceId, string $sourceType, int $sourceId): LengthAwarePaginator;

    public function unsubscribes(int $workspaceId, string $sourceType, int $sourceId): LengthAwarePaginator;

    public function getFirstOpenedAt(int $workspaceId, string $sourceType, int $sourceId);

    /**
     * Count the number of unique opens per period for a campaign or automation schedule.
     */
    public function countUniqueOpensPerPeriod(int $workspaceId, string $sourceType, int $sourceId, int $intervalInSeconds): Collection;
}
