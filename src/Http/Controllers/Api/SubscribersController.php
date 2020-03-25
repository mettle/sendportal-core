<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Api;

use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\Api\SubscriberStoreRequest;
use Sendportal\Base\Http\Requests\Api\SubscriberUpdateRequest;
use Sendportal\Base\Http\Resources\Subscriber as SubscriberResource;
use Sendportal\Base\Repositories\SubscriberTenantRepository;
use Sendportal\Base\Services\Subscribers\ApiSubscriberService;
use Exception;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class SubscribersController extends Controller
{
    /** @var SubscriberTenantRepository */
    protected $subscribers;

    /** @var ApiSubscriberService */
    protected $apiService;

    public function __construct(
        SubscriberTenantRepository $subscribers,
        ApiSubscriberService $apiService
    ) {
        $this->subscribers = $subscribers;
        $this->apiService = $apiService;
    }

    /**
     * @throws Exception
     */
    public function index(int $teamId): AnonymousResourceCollection
    {
        $subscribers = $this->subscribers->paginate($teamId, 'last_name');

        return SubscriberResource::collection($subscribers);
    }

    /**
     * @throws Exception
     */
    public function store(SubscriberStoreRequest $request, int $teamId): SubscriberResource
    {
        $subscriber = $this->apiService->store($teamId, collect($request->validated()));

        $subscriber->load('segments');

        return new SubscriberResource($subscriber);
    }

    /**
     * @throws Exception
     */
    public function show(int $teamId, int $id): SubscriberResource
    {
        return new SubscriberResource($this->subscribers->find($teamId, $id, ['segments']));
    }

    /**
     * @throws Exception
     */
    public function update(SubscriberUpdateRequest $request, int $teamId, int $id): SubscriberResource
    {
        $subscriber = $this->subscribers->update($teamId, $id, $request->validated());

        return new SubscriberResource($subscriber);
    }

    /**
     * @throws Exception
     */
    public function destroy(int $teamId, int $id): Response
    {
        $this->apiService->delete($teamId, $this->subscribers->find($teamId, $id));

        return response(null, 204);
    }
}
