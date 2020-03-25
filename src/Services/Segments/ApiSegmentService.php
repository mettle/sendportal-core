<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Segments;

use Sendportal\Base\Models\Segment;
use Sendportal\Base\Repositories\SegmentTenantRepository;
use Exception;
use Illuminate\Support\Collection;

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
    public function store(int $teamId, Collection $data): Segment
    {
        $segment = $this->segments->store($teamId, $data->except('subscribers')->toArray());

        if (!empty($data['subscribers'])) {
            $segment->subscribers()->attach($data['subscribers']);
        }

        return $segment;
    }
}
