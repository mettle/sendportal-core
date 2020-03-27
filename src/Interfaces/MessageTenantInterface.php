<?php


namespace Sendportal\Base\Interfaces;


interface MessageTenantInterface
{
    public function paginateWithSource($workspaceId, $orderBy = 'name', array $relations = [], $paginate = 25, array $parameters = []);

    public function recipients($workspaceId, $sourceType, $sourceId);

    public function opens($workspaceId, $sourceType, $sourceId);

    public function clicks($workspaceId, $sourceType, $sourceId);

    public function bounces($workspaceId, $sourceType, $sourceId);

    public function unsubscribes($workspaceId, $sourceType, $sourceId);

    public function getFirstLastOpenedAt($workspaceId, $sourceType, $sourceId);

    /**
     * Count the number of unique open per period for a campaign or automation schedule
     *
     * @param int $workspaceId
     * @param string $sourceType
     * @param int $sourceId
     * @param int $intervalInSeconds
     * @return mixed
     */
    public function countUniqueOpensPerPeriod($workspaceId, $sourceType, $sourceId, $intervalInSeconds);
}