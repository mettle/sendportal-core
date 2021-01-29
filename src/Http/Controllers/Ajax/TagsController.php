<?php

namespace Sendportal\Base\Http\Controllers\Ajax;

use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\Api\TagStoreRequest;

use Sendportal\Base\Http\Resources\Tag as TagResource;
use Sendportal\Base\Services\Tags\ApiTagService;

class TagsController extends Controller
{
    /**
     * @var ApiTagService
     */
    protected $apiService;

    /**
     * SegmentsController constructor.
     *
     * @param ApiTagService $apiService
     */
    public function __construct(
        ApiTagService $apiService
    ) {
        $this->apiService = $apiService;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param TagStoreRequest $request
     *
     * @return TagResource
     */
    public function store(TagStoreRequest $request)
    {
        $input = $request->validated();

        $tag = $this->apiService->store($input);

        return new TagResource($tag);
    }
}
