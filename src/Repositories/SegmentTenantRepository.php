<?php

namespace Sendportal\Base\Repositories;

use Illuminate\Support\Arr;
use Sendportal\Base\Models\Segment;

class SegmentTenantRepository extends BaseTenantRepository
{
    /**
     * @var string
     */
    protected $modelName = Segment::class;

    /**
     * {@inheritDoc}
     */
    public function update($teamId, $id, array $data)
    {
        $instance = $this->find($teamId, $id);

        $this->executeSave($teamId, $instance, $data);

        $this->syncSubscribers($instance, Arr::get($data, 'subscribers', []));

        return $instance;
    }

    /**
     * Sync subscribers
     *
     * @param Segment $segment
     * @param array $subscribers
     * @return array
     */
    public function syncSubscribers(Segment $segment, array $subscribers = [])
    {
        return $segment->subscribers()->sync($subscribers);
    }
}
