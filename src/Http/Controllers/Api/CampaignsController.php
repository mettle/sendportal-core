<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\CampaignStoreRequest;
use Sendportal\Base\Http\Resources\Campaign as CampaignResource;
use Sendportal\Base\Repositories\Campaigns\CampaignTenantRepositoryInterface;

class CampaignsController extends Controller
{
    /** @var CampaignTenantRepositoryInterface */
    private $campaigns;

    public function __construct(CampaignTenantRepositoryInterface $campaigns)
    {
        $this->campaigns = $campaigns;
    }

    /**
     * @throws Exception
     */
    public function index(int $workspaceId): AnonymousResourceCollection
    {
        return CampaignResource::collection($this->campaigns->paginate($workspaceId, 'id', ['segments']));
    }

    /**
     * @throws Exception
     */
    public function store(CampaignStoreRequest $request, int $workspaceId): CampaignResource
    {
        $campaign = $this->campaigns->store($workspaceId, $request->validated());

        return new CampaignResource($campaign);
    }

    /**
     * @throws Exception
     */
    public function show(int $workspaceId, int $id): CampaignResource
    {
        $campaign = $this->campaigns->find($workspaceId, $id);

        return new CampaignResource($campaign);
    }

    /**
     * @throws Exception
     */
    public function update(CampaignStoreRequest $request, int $workspaceId, int $id): CampaignResource
    {
        $campaign = $this->campaigns->update($workspaceId, $id, $request->validated());

        return new CampaignResource($campaign);
    }
}
