<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\Api\TagStoreRequest;
use Sendportal\Base\Http\Requests\Api\TagUpdateRequest;
use Sendportal\Base\Http\Resources\Tag as SegmentResource;
use Sendportal\Base\Repositories\TagTenantRepository;
use Sendportal\Base\Services\Tags\ApiTagService;

class SegmentsController extends Controller
{
    /** @var TagTenantRepository */
    private $segments;

    /** @var ApiTagService */
    private $apiService;

    public function __construct(
        TagTenantRepository $segments,
        ApiTagService $apiService
    ) {
        $this->segments = $segments;
        $this->apiService = $apiService;
    }

    /**
     * @throws Exception
     */
    public function index(): AnonymousResourceCollection
    {
        $workspaceId = Sendportal::currentWorkspaceId();

        return SegmentResource::collection($this->segments->paginate($workspaceId, 'name'));
    }

    /**
     * @throws Exception
     */
    public function store(TagStoreRequest $request): SegmentResource
    {
        $input = $request->validated();
        $workspaceId = Sendportal::currentWorkspaceId();
        $segment = $this->apiService->store($workspaceId, collect($input));

        $segment->load('subscribers');

        return new SegmentResource($segment);
    }

    /**
     * @throws Exception
     */
    public function show(int $id): SegmentResource
    {
        $workspaceId = Sendportal::currentWorkspaceId();

        return new SegmentResource($this->segments->find($workspaceId, $id));
    }

    /**
     * @throws Exception
     */
    public function update(TagUpdateRequest $request, int $id): SegmentResource
    {
        $workspaceId = Sendportal::currentWorkspaceId();
        $segment = $this->segments->update($workspaceId, $id, $request->validated());

        return new SegmentResource($segment);
    }

    /**
     * @throws Exception
     */
    public function destroy(int $id): Response
    {
        $workspaceId = Sendportal::currentWorkspaceId();
        $this->segments->destroy($workspaceId, $id);

        return response(null, 204);
    }
}
