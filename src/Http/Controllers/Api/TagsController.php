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
use Sendportal\Base\Http\Resources\Tag as TagResource;
use Sendportal\Base\Repositories\TagTenantRepository;
use Sendportal\Base\Services\Tags\ApiTagService;

class TagsController extends Controller
{
    /** @var TagTenantRepository */
    private $tags;

    /** @var ApiTagService */
    private $apiService;

    public function __construct(
        TagTenantRepository $tags,
        ApiTagService $apiService
    ) {
        $this->tags = $tags;
        $this->apiService = $apiService;
    }

    /**
     * @throws Exception
     */
    public function index(): AnonymousResourceCollection
    {
        $workspaceId = Sendportal::currentWorkspaceId();

        return TagResource::collection($this->tags->paginate($workspaceId, 'name'));
    }

    /**
     * @throws Exception
     */
    public function store(TagStoreRequest $request): TagResource
    {
        $input = $request->validated();
        $workspaceId = Sendportal::currentWorkspaceId();
        $tag = $this->apiService->store($workspaceId, collect($input));

        $tag->load('subscribers');

        return new TagResource($tag);
    }

    /**
     * @throws Exception
     */
    public function show(int $id): TagResource
    {
        $workspaceId = Sendportal::currentWorkspaceId();

        return new TagResource($this->tags->find($workspaceId, $id));
    }

    /**
     * @throws Exception
     */
    public function update(TagUpdateRequest $request, int $id): TagResource
    {
        $workspaceId = Sendportal::currentWorkspaceId();
        $tag = $this->tags->update($workspaceId, $id, $request->validated());

        return new TagResource($tag);
    }

    /**
     * @throws Exception
     */
    public function destroy(int $id): Response
    {
        $workspaceId = Sendportal::currentWorkspaceId();
        $this->tags->destroy($workspaceId, $id);

        return response(null, 204);
    }
}
