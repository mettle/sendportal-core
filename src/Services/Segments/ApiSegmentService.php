<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Segments;

use Exception;
use Illuminate\Support\Collection;
use Sendportal\Base\Models\Segment;
use Sendportal\Base\Repositories\SegmentTenantRepository;

class ApiSegmentService
{
    /** @var SegmentTenantRepository */
    private $segments;

    public function __construct(SegmentTenantRepository $segments)
    {
        $this->segments = $segments;
    }

    /**
     * Store a new segment, optionally including attached subscribers.
     *
     * @throws Exception
     */
    public function store(int $workspaceId, Collection $data): Segment
    {
        $segment = $this->segments->store($workspaceId, $data->except('subscribers')->toArray());

        if (!empty($data['subscribers'])) {
            $segment->subscribers()->attach($data['subscribers']);
        }

        return $segment;
    }
}
