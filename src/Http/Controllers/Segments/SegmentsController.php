<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Segments;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\SegmentStoreRequest;
use Sendportal\Base\Http\Requests\SegmentUpdateRequest;
use Sendportal\Base\Repositories\SegmentTenantRepository;
use Sendportal\Base\Repositories\Subscribers\SubscriberTenantRepositoryInterface;

class SegmentsController extends Controller
{
    /** @var SegmentTenantRepository */
    private $segmentRepository;

    public function __construct(SegmentTenantRepository $segmentRepository)
    {
        $this->segmentRepository = $segmentRepository;
    }

    /**
     * @throws Exception
     */
    public function index(): View
    {
        $segments = $this->segmentRepository->paginate(Sendportal::currentWorkspaceId(), 'name');

        return view('sendportal::segments.index', compact('segments'));
    }

    public function create(): View
    {
        return view('sendportal::segments.create');
    }

    /**
     * @throws Exception
     */
    public function store(SegmentStoreRequest $request): RedirectResponse
    {
        $this->segmentRepository->store(Sendportal::currentWorkspaceId(), $request->all());

        return redirect()->route('sendportal.segments.index');
    }

    /**
     * @throws Exception
     */
    public function edit(int $id, SubscriberTenantRepositoryInterface $subscriberRepository): View
    {
        $segment = $this->segmentRepository->find(Sendportal::currentWorkspaceId(), $id, ['subscribers']);

        return view('sendportal::segments.edit', compact('segment'));
    }

    /**
     * @throws Exception
     */
    public function update(int $id, SegmentUpdateRequest $request): RedirectResponse
    {
        $this->segmentRepository->update(Sendportal::currentWorkspaceId(), $id, $request->all());

        return redirect()->route('sendportal.segments.index');
    }

    /**
     * @throws Exception
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->segmentRepository->destroy(Sendportal::currentWorkspaceId(), $id);

        return redirect()->route('sendportal.segments.index');
    }
}
