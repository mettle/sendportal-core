<?php

namespace Sendportal\Base\Repositories;

use Sendportal\Base\Models\AutomationSchedule;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Traits\ResolvesDatabaseDriver;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MessageTenantRepository extends BaseTenantRepository
{
    use ResolvesDatabaseDriver;

    protected $modelName = Message::class;

    public function paginateWithSource($teamId, $orderBy = 'name', array $relations = [], $paginate = 25, array $parameters = [])
    {
        $this->parseOrder($orderBy);

        $instance = $this->getQueryBuilder($teamId)
            ->with(['source' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    AutomationSchedule::class => ['automation_step.automation:id,name'],
                ]);
            }]);

        $this->applyFilters($instance, $parameters);

        return $instance
            ->orderBy($this->getOrderBy(), $this->getOrderDirection())
            ->paginate($paginate);
    }

    public function recipients($teamId, $sourceType, $sourceId)
    {
        return $this->getQueryBuilder($teamId)
            ->where('team_id', $teamId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->whereNotNull('sent_at')
            ->orderBy('recipient_email')
            ->paginate(50);
    }

    public function opens($teamId, $sourceType, $sourceId)
    {
        return $this->getQueryBuilder($teamId)
            ->where('team_id', $teamId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->whereNotNull('opened_at')
            ->orderBy('opened_at')
            ->paginate(50);
    }

    public function clicks($teamId, $sourceType, $sourceId)
    {
        return $this->getQueryBuilder($teamId)
            ->where('team_id', $teamId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->whereNotNull('clicked_at')
            ->orderBy('clicked_at')
            ->paginate(50);
    }

    public function bounces($teamId, $sourceType, $sourceId)
    {
        return $this->getQueryBuilder($teamId)
            ->with(['failures'])
            ->where('team_id', $teamId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->whereNotNull('bounced_at')
            ->orderBy('bounced_at')
            ->paginate(50);
    }

    public function unsubscribes($teamId, $sourceType, $sourceId)
    {
        return $this->getQueryBuilder($teamId)
            ->where('team_id', $teamId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->whereNotNull('unsubscribed_at')
            ->orderBy('unsubscribed_at')
            ->paginate(50);
    }

    /**
     * {@inheritDoc}
     */
    protected function applyFilters($instance, array $filters = [])
    {
        $this->applySentFilter($instance, $filters);
        $this->applySearchFilter($instance, $filters);
        $this->applyStatusFilter($instance, $filters);
    }

    /**
     * Filter by sent status
     *
     * @param $instance
     * @param array $filters
     */
    protected function applySentFilter($instance, $filters = [])
    {
        if ($sentAt = \Arr::get($filters, 'draft')) {
            $instance->whereNotNull('queued_at')
                ->whereNull('sent_at');
        } elseif ($sentAt = \Arr::get($filters, 'sent')) {
            $instance->whereNotNull('sent_at');
        }
    }

    /**
     * @param $instance
     * @param array $filters
     */
    protected function applySearchFilter($instance, $filters = [])
    {
        if ($search = \Arr::get($filters, 'search')) {
            $search = '%' . $search . '%';

            $instance->where(function ($instance) use ($search) {
                $instance->where('messages.recipient_email', 'like', $search)
                    ->orWhere('messages.subject', 'like', $search);
            });
        }
    }

    /**
     * Filter by status.
     *
     * Note that when we use this filter, we only
     * expect messages that are *at* that status. For example, if
     * a message has been "clicked", then it will not also appear
     * in the "sent" or "delivered" statuses
     * @param $instance
     * @param array $filters
     */
    protected function applyStatusFilter($instance, $filters = [])
    {
        $status = \Arr::get($filters, 'status', 'all');

        if ($status == 'bounced') {
            $instance->whereNotNull('bounced_at');
        } elseif ($status == 'unsubscribed') {
            $instance->whereNotNull('unsubscribed_at');
        } elseif ($status == 'clicked') {
            $instance->whereNotNull('clicked_at');
        } elseif ($status == 'opened') {
            $instance->whereNotNull('opened_at')
                ->whereNull('clicked_at');
        } elseif ($status == 'delivered') {
            $instance->whereNotNull('delivered_at')
                ->whereNull('opened_at');
        } elseif ($status == 'sent') {
            $instance->whereNull('delivered_at');
        }
    }

    public function getFirstLastOpenedAt($teamId, $sourceType, $sourceId)
    {
        return \DB::table('messages')
            ->select(\DB::raw('MIN(opened_at) as first, MAX(opened_at) as last'))
            ->where('team_id', $teamId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->first();
    }

    /**
     * Count the number of unique open per period for a campaign or automation schedule
     *
     * @param int $teamId
     * @param string $sourceType
     * @param int $sourceId
     * @param int $intervalInSeconds
     * @return mixed
     * @throws \Exception
     */
    public function countUniqueOpensPerPeriod($teamId, $sourceType, $sourceId, $intervalInSeconds)
    {
        $intervalInSeconds = (int)$intervalInSeconds;

        $query = \DB::table('messages')
            ->select(\DB::raw('COUNT(*) as open_count, MIN(opened_at) as opened_at, FROM_UNIXTIME(MIN(UNIX_TIMESTAMP(opened_at) DIV '.$intervalInSeconds.') * '.$intervalInSeconds.') as period_start'))
            ->where('team_id', $teamId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->whereNotNull('opened_at');

        if ($this->usingMySQL()) {
            $query->groupBy(\DB::raw("UNIX_TIMESTAMP(opened_at) DIV " . $intervalInSeconds));
        } elseif ($this->usingPostgres()) {
            $query->groupBy(\DB::raw("round(extract('epoch' from timestamp) / ".$intervalInSeconds.")"));
        } else {
            throw new \Exception('Invalid database driver');
        }

        return $query->orderBy('opened_at')
            ->get();
    }
}
