<?php

namespace Sendportal\Base\Http\Controllers\Ajax;

use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\Api\SegmentStoreRequest;

use Sendportal\Base\Http\Resources\Segment as SegmentResource;
use Sendportal\Base\Services\Segments\ApiSegmentService;

class TagsController extends Controller
{
    /**
     * @var ApiSegmentService
     */
    protected $apiService;

    /**
     * SegmentsController constructor.
     *
     * @param ApiSegmentService $apiService
     */
    public function __construct(
        ApiSegmentService $apiService
    ) {
        $this->apiService = $apiService;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param SegmentStoreRequest $request
     *
     * @return SegmentResource
     */
    public function store(SegmentStoreRequest $request)
    {
        $input = $request->validated();

        $segment = $this->apiService->store($input);

        return new SegmentResource($segment);
    }
}
