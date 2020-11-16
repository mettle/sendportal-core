<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\Api\SegmentSubscriberDestroyRequest;
use Sendportal\Base\Http\Requests\Api\SegmentSubscriberStoreRequest;
use Sendportal\Base\Http\Requests\Api\SegmentSubscriberUpdateRequest;
use Sendportal\Base\Http\Resources\Subscriber as SubscriberResource;
use Sendportal\Base\Repositories\SegmentTenantRepository;
use Sendportal\Base\Services\Segments\ApiSegmentSubscriberService;

class SegmentSubscribersController extends Controller
{
    /** @var SegmentTenantRepository */
    private $segments;

    /** @var ApiSegmentSubscriberService */
    private $apiService;

    public function __construct(
        SegmentTenantRepository $segments,
        ApiSegmentSubscriberService $apiService
    ) {
        $this->segments = $segments;
        $this->apiService = $apiService;
    }

    /**
     * @throws Exception
     */
    public function index(int $segmentId): AnonymousResourceCollection
    {
        $workspaceId = Sendportal::currentWorkspaceId();
        $segment = $this->segments->find($workspaceId, $segmentId, ['subscribers']);

        return SubscriberResource::collection($segment->subscribers);
    }

    /**
     * @throws Exception
     */
    public function store(SegmentSubscriberStoreRequest $request, int $segmentId): AnonymousResourceCollection
    {
        $input = $request->validated();
        $workspaceId = Sendportal::currentWorkspaceId();
        $subscribers = $this->apiService->store($workspaceId, $segmentId, collect($input['subscribers']));

        return SubscriberResource::collection($subscribers);
    }

    /**
     * @throws Exception
     */
    public function update(SegmentSubscriberUpdateRequest $request, int $segmentId): AnonymousResourceCollection
    {
        $input = $request->validated();
        $workspaceId = Sendportal::currentWorkspaceId();
        $subscribers = $this->apiService->update($workspaceId, $segmentId, collect($input['subscribers']));

        return SubscriberResource::collection($subscribers);
    }

    /**
     * @throws Exception
     */
    public function destroy(SegmentSubscriberDestroyRequest $request, int $segmentId): AnonymousResourceCollection
    {
        $input = $request->validated();
        $workspaceId = Sendportal::currentWorkspaceId();
        $subscribers = $this->apiService->destroy($workspaceId, $segmentId, collect($input['subscribers']));

        return SubscriberResource::collection($subscribers);
    }
}
