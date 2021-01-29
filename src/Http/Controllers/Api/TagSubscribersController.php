<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\Api\TagSubscriberDestroyRequest;
use Sendportal\Base\Http\Requests\Api\TagSubscriberStoreRequest;
use Sendportal\Base\Http\Requests\Api\TagSubscriberUpdateRequest;
use Sendportal\Base\Http\Resources\Subscriber as SubscriberResource;
use Sendportal\Base\Repositories\TagTenantRepository;
use Sendportal\Base\Services\Tags\ApiTagSubscriberService;

class TagSubscribersController extends Controller
{
    /** @var TagTenantRepository */
    private $segments;

    /** @var ApiTagSubscriberService */
    private $apiService;

    public function __construct(
        TagTenantRepository $segments,
        ApiTagSubscriberService $apiService
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
    public function store(TagSubscriberStoreRequest $request, int $segmentId): AnonymousResourceCollection
    {
        $input = $request->validated();
        $workspaceId = Sendportal::currentWorkspaceId();
        $subscribers = $this->apiService->store($workspaceId, $segmentId, collect($input['subscribers']));

        return SubscriberResource::collection($subscribers);
    }

    /**
     * @throws Exception
     */
    public function update(TagSubscriberUpdateRequest $request, int $segmentId): AnonymousResourceCollection
    {
        $input = $request->validated();
        $workspaceId = Sendportal::currentWorkspaceId();
        $subscribers = $this->apiService->update($workspaceId, $segmentId, collect($input['subscribers']));

        return SubscriberResource::collection($subscribers);
    }

    /**
     * @throws Exception
     */
    public function destroy(TagSubscriberDestroyRequest $request, int $segmentId): AnonymousResourceCollection
    {
        $input = $request->validated();
        $workspaceId = Sendportal::currentWorkspaceId();
        $subscribers = $this->apiService->destroy($workspaceId, $segmentId, collect($input['subscribers']));

        return SubscriberResource::collection($subscribers);
    }
}
