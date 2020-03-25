<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Api;

use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\Api\SubscriberSegmentDestroyRequest;
use Sendportal\Base\Http\Requests\Api\SubscriberSegmentStoreRequest;
use Sendportal\Base\Http\Requests\Api\SubscriberSegmentUpdateRequest;
use Sendportal\Base\Http\Resources\Segment;
use Sendportal\Base\Http\Resources\Segment as SegmentResource;
use Sendportal\Base\Repositories\SubscriberTenantRepository;
use Sendportal\Base\Services\Subscribers\Segments\ApiSubscriberSegmentService;
use Exception;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SubscriberSegmentsController extends Controller
{
    /** @var SubscriberTenantRepository */
    private $subscribers;

    /** @var ApiSubscriberSegmentService */
    private $apiService;

    public function __construct(
        SubscriberTenantRepository $subscribers,
        ApiSubscriberSegmentService $apiService
    ) {
        $this->subscribers = $subscribers;
        $this->apiService = $apiService;
    }

    /**
     * @throws Exception
     */
    public function index(int $teamId, int $subscriberId): AnonymousResourceCollection
    {
        $subscriber = $this->subscribers->find($teamId, $subscriberId, ['segments']);

        return SegmentResource::collection($subscriber->segments);
    }

    /**
     * @throws Exception
     */
    public function store(SubscriberSegmentStoreRequest $request, int $teamId, int $subscriberId): AnonymousResourceCollection
    {
        $input = $request->validated();

        $segments = $this->apiService->store($teamId, $subscriberId, collect($input['segments']));

        return SegmentResource::collection($segments);
    }

    /**
     * @throws Exception
     */
    public function update(SubscriberSegmentUpdateRequest $request, int $teamId, int $subscriberId): AnonymousResourceCollection
    {
        $input = $request->validated();

        $segments = $this->apiService->update($teamId, $subscriberId, collect($input['segments']));

        return SegmentResource::collection($segments);
    }

    /**
     * @throws Exception
     */
    public function destroy(SubscriberSegmentDestroyRequest $request, int $teamId, int $subscriberId): AnonymousResourceCollection
    {
        $input = $request->validated();

        $segments = $this->apiService->destroy($teamId, $subscriberId, collect($input['segments']));

        return SegmentResource::collection($segments);
    }
}
