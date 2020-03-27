<?php

declare(strict_types=1);

namespace Sendportal\Base\Repositories\Messages;

use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Repositories\BaseTenantRepository;

abstract class BaseMessageTenantRepository extends BaseTenantRepository implements MessageTenantRepository
{
    /** @var string */
    protected $modelName = Message::class;

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function paginateWithSource(int $workspaceId, string $orderBy = 'name', array $relations = [], int $paginate = 25, array $parameters = []): LengthAwarePaginator
    {
        $this->parseOrder($orderBy);

        $instance = $this->getQueryBuilder($workspaceId)
            ->with([
                'source' => static function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        AutomationSchedule::class => ['automation_step.automation:id,name'],
                    ]);
                }
            ]);

        $this->applyFilters($instance, $parameters);

        return $instance
            ->orderBy($this->getOrderBy(), $this->getOrderDirection())
            ->paginate($paginate);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function recipients(int $workspaceId, string $sourceType, int $sourceId): LengthAwarePaginator
    {
        return $this->getQueryBuilder($workspaceId)
            ->where('workspace_id', $workspaceId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->whereNotNull('sent_at')
            ->orderBy('recipient_email')
            ->paginate(50);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function opens(int $workspaceId, string $sourceType, int $sourceId): LengthAwarePaginator
    {
        return $this->getQueryBuilder($workspaceId)
            ->where('workspace_id', $workspaceId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->whereNotNull('opened_at')
            ->orderBy('opened_at')
            ->paginate(50);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function clicks(int $workspaceId, string $sourceType, int $sourceId): LengthAwarePaginator
    {
        return $this->getQueryBuilder($workspaceId)
            ->where('workspace_id', $workspaceId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->whereNotNull('clicked_at')
            ->orderBy('clicked_at')
            ->paginate(50);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function bounces(int $workspaceId, string $sourceType, int $sourceId): LengthAwarePaginator
    {
        return $this->getQueryBuilder($workspaceId)
            ->with(['failures'])
            ->where('workspace_id', $workspaceId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->whereNotNull('bounced_at')
            ->orderBy('bounced_at')
            ->paginate(50);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function unsubscribes(int $workspaceId, string $sourceType, int $sourceId): LengthAwarePaginator
    {
        return $this->getQueryBuilder($workspaceId)
            ->where('workspace_id', $workspaceId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->whereNotNull('unsubscribed_at')
            ->orderBy('unsubscribed_at')
            ->paginate(50);
    }

    /**
     * @inheritDoc
     */
    public function getFirstLastOpenedAt(int $workspaceId, string $sourceType, int $sourceId)
    {
        return DB::table('messages')
            ->select(DB::raw('MIN(opened_at) as first, MAX(opened_at) as last'))
            ->where('workspace_id', $workspaceId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->first();
    }

    /**
     * @inheritDoc
     */
    protected function applyFilters(Builder $instance, array $filters = []): void
    {
        $this->applySentFilter($instance, $filters);
        $this->applySearchFilter($instance, $filters);
        $this->applyStatusFilter($instance, $filters);
    }

    /**
     * Filter by sent status.
     */
    protected function applySentFilter(Builder $instance, array $filters = []): void
    {
        if ($sentAt = Arr::get($filters, 'draft')) {
            $instance->whereNotNull('queued_at')
                ->whereNull('sent_at');
        } elseif ($sentAt = Arr::get($filters, 'sent')) {
            $instance->whereNotNull('sent_at');
        }
    }

    /**
     * Apply a search filter over recipient email or subject.
     */
    protected function applySearchFilter(Builder $instance, array $filters = []): void
    {
        if ($search = Arr::get($filters, 'search')) {
            $searchString = '%' . $search . '%';

            $instance->where(static function (Builder $instance) use ($searchString) {
                $instance->where('messages.recipient_email', 'like', $searchString)
                    ->orWhere('messages.subject', 'like', $searchString);
            });
        }
    }

    /**
     * Filter by status.
     *
     * Note that when we use this filter, we only expect messages that are *at* that status. For example, if
     * a message has been "clicked", then it will not also appear in the "sent" or "delivered" statuses.
     */
    protected function applyStatusFilter(Builder $instance, array $filters = [])
    {
        $status = Arr::get($filters, 'status', 'all');

        if ($status === 'bounced') {
            $instance->whereNotNull('bounced_at');
        } elseif ($status === 'unsubscribed') {
            $instance->whereNotNull('unsubscribed_at');
        } elseif ($status === 'clicked') {
            $instance->whereNotNull('clicked_at');
        } elseif ($status === 'opened') {
            $instance->whereNotNull('opened_at')
                ->whereNull('clicked_at');
        } elseif ($status === 'delivered') {
            $instance->whereNotNull('delivered_at')
                ->whereNull('opened_at');
        } elseif ($status === 'sent') {
            $instance->whereNull('delivered_at');
        }
    }
}
