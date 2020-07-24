<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\Api\CampaignStoreRequest;
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
        $data = Arr::except($request->validated(), ['segments']);

        $data['save_as_draft'] = $request->get('save_as_draft') ?? 0;

        $campaign = $this->campaigns->store($workspaceId, $data);

        $campaign->segments()->sync($request->get('segments'));

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
        $data = Arr::except($request->validated(), ['segments']);

        $data['save_as_draft'] = $request->get('save_as_draft') ?? 0;

        $campaign = $this->campaigns->update($workspaceId, $id, $data);

        $campaign->segments()->sync($request->get('segments'));

        return new CampaignResource($campaign);
    }
}
