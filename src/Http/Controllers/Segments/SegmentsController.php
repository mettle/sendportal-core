<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Segments;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\SegmentRequest;
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
        $segments = $this->segmentRepository->paginate(auth()->user()->currentWorkspace()->id, 'name');

        return view('sendportal::segments.index', compact('segments'));
    }

    public function create(): View
    {
        return view('sendportal::segments.create');
    }

    /**
     * @throws Exception
     */
    public function store(SegmentRequest $request): RedirectResponse
    {
        $this->segmentRepository->store(auth()->user()->currentWorkspace()->id, $request->all());

        return redirect()->route('sendportal.segments.index');
    }

    /**
     * @throws Exception
     */
    public function edit(int $id, SubscriberTenantRepositoryInterface $subscriberRepository): View
    {
        $segment = $this->segmentRepository->find(auth()->user()->currentWorkspace()->id, $id, ['subscribers']);

        return view('sendportal::segments.edit', compact('segment'));
    }

    /**
     * @throws Exception
     */
    public function update(int $id, SegmentRequest $request): RedirectResponse
    {
        $this->segmentRepository->update(auth()->user()->currentWorkspace()->id, $id, $request->all());

        return redirect()->route('sendportal.segments.index');
    }

    /**
     * @throws Exception
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->segmentRepository->destroy(auth()->user()->currentWorkspace()->id, $id);

        return redirect()->route('sendportal.segments.index');
    }
}
