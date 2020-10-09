<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\Api\SegmentStoreRequest;
use Sendportal\Base\Http\Requests\Api\SegmentUpdateRequest;
use Sendportal\Base\Http\Resources\Segment as SegmentResource;
use Sendportal\Base\Repositories\SegmentTenantRepository;
use Sendportal\Base\Services\Segments\ApiSegmentService;

class SegmentsController extends Controller
{
    /** @var SegmentTenantRepository */
    private $segments;

    /** @var ApiSegmentService */
    private $apiService;

    public function __construct(
        SegmentTenantRepository $segments,
        ApiSegmentService $apiService
    ) {
        $this->segments = $segments;
        $this->apiService = $apiService;
    }

    /**
     * @throws Exception
     */
    public function index(int $workspaceId): AnonymousResourceCollection
    {
        return SegmentResource::collection($this->segments->paginate($workspaceId, 'name'));
    }

    /**
     * @throws Exception
     */
    public function store(SegmentStoreRequest $request, int $workspaceId): SegmentResource
    {
        $input = $request->validated();

        $segment = $this->apiService->store($workspaceId, collect($input));

        $segment->load('subscribers');

        return new SegmentResource($segment);
    }

    /**
     * @throws Exception
     */
    public function show(int $workspaceId, int $id): SegmentResource
    {
        return new SegmentResource($this->segments->find($workspaceId, $id));
    }

    /**
     * @throws Exception
     */
    public function update(SegmentUpdateRequest $request, int $workspaceId, int $id): SegmentResource
    {
        $segment = $this->segments->update($workspaceId, $id, $request->validated());

        return new SegmentResource($segment);
    }

    /**
     * @throws Exception
     */
    public function destroy(int $workspaceId, int $id): Response
    {
        $this->segments->destroy($workspaceId, $id);

        return response(null, 204);
    }
}
