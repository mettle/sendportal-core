<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Api;

use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\Api\SegmentStoreRequest;
use Sendportal\Base\Http\Requests\Api\SegmentUpdateRequest;
use Sendportal\Base\Http\Resources\Segment;
use Sendportal\Base\Http\Resources\Segment as SegmentResource;
use Sendportal\Base\Repositories\SegmentTenantRepository;
use Sendportal\Base\Services\Segments\ApiSegmentService;
use Exception;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

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
    public function index(int $teamId): AnonymousResourceCollection
    {
        return SegmentResource::collection($this->segments->paginate($teamId, 'name'));
    }

    /**
     * @throws Exception
     */
    public function store(SegmentStoreRequest $request, int $teamId): SegmentResource
    {
        $input = $request->validated();

        $segment = $this->apiService->store($teamId, collect($input));

        $segment->load('subscribers');

        return new SegmentResource($segment);
    }

    /**
     * @throws Exception
     */
    public function show(int $teamId, int $id): SegmentResource
    {
        return new SegmentResource($this->segments->find($teamId, $id));
    }

    /**
     * @throws Exception
     */
    public function update(SegmentUpdateRequest $request, int $teamId, int $id): SegmentResource
    {
        $segment = $this->segments->update($teamId, $id, $request->validated());

        return new SegmentResource($segment);
    }

    /**
     * @throws Exception
     */
    public function destroy(int $teamId, int $id): Response
    {
        $this->segments->destroy($teamId, $id);

        return response(null, 204);
    }
}
