<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\Api\SubscriberSegmentDestroyRequest;
use Sendportal\Base\Http\Requests\Api\SubscriberSegmentStoreRequest;
use Sendportal\Base\Http\Requests\Api\SubscriberSegmentUpdateRequest;
use Sendportal\Base\Http\Resources\Segment as SegmentResource;
use Sendportal\Base\Repositories\Subscribers\SubscriberTenantRepositoryInterface;
use Sendportal\Base\Services\Subscribers\Segments\ApiSubscriberSegmentService;

class SubscriberSegmentsController extends Controller
{
    /** @var SubscriberTenantRepositoryInterface */
    private $subscribers;

    /** @var ApiSubscriberSegmentService */
    private $apiService;

    public function __construct(
        SubscriberTenantRepositoryInterface $subscribers,
        ApiSubscriberSegmentService $apiService
    ) {
        $this->subscribers = $subscribers;
        $this->apiService = $apiService;
    }

    /**
     * @throws Exception
     */
    public function index(int $workspaceId, int $subscriberId): AnonymousResourceCollection
    {
        $subscriber = $this->subscribers->find($workspaceId, $subscriberId, ['segments']);

        return SegmentResource::collection($subscriber->segments);
    }

    /**
     * @throws Exception
     */
    public function store(SubscriberSegmentStoreRequest $request, int $workspaceId, int $subscriberId): AnonymousResourceCollection
    {
        $input = $request->validated();

        $segments = $this->apiService->store($workspaceId, $subscriberId, collect($input['segments']));

        return SegmentResource::collection($segments);
    }

    /**
     * @throws Exception
     */
    public function update(SubscriberSegmentUpdateRequest $request, int $workspaceId, int $subscriberId): AnonymousResourceCollection
    {
        $input = $request->validated();

        $segments = $this->apiService->update($workspaceId, $subscriberId, collect($input['segments']));

        return SegmentResource::collection($segments);
    }

    /**
     * @throws Exception
     */
    public function destroy(SubscriberSegmentDestroyRequest $request, int $workspaceId, int $subscriberId): AnonymousResourceCollection
    {
        $input = $request->validated();

        $segments = $this->apiService->destroy($workspaceId, $subscriberId, collect($input['segments']));

        return SegmentResource::collection($segments);
    }
}
