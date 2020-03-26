<?php

namespace Sendportal\Base\Http\Controllers;

use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Sendportal\Base\Http\Requests\SegmentRequest;
use Sendportal\Base\Repositories\SegmentTenantRepository;
use Sendportal\Base\Repositories\SubscriberTenantRepository;

class SegmentsController extends Controller
{
    /**
     * @var SegmentTenantRepository
     */
    protected $segmentRepository;

    /**
     * SubscribersController constructor.
     *
     * @param SegmentTenantRepository $segmentRepository
     */
    public function __construct(
        SegmentTenantRepository $segmentRepository
    ) {
        $this->segmentRepository = $segmentRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Factory|View
     * @throws Exception
     */
    public function index()
    {
        $segments = $this->segmentRepository->paginate(currentTeamId(), 'name');

        return view('segments.index', compact('segments'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Factory|View
     */
    public function create()
    {
        return view('sendportal::segments.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param SegmentRequest $request
     * @return RedirectResponse
     * @throws Exception
     */
    public function store(SegmentRequest $request)
    {
        $this->segmentRepository->store(currentTeamId(), $request->all());

        return redirect()->route('sendportal.segments.index');
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return Factory|View
     */
    public function show($id)
    {
        app()->abort(404, 'Not implemented');

        return view('sendportal::segments.show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @param SubscriberTenantRepository $subscriberRepository
     * @return Factory|View
     * @throws Exception
     */
    public function edit($id, SubscriberTenantRepository $subscriberRepository)
    {
        $segment = $this->segmentRepository->find(currentTeamId(), $id, ['subscribers']);

        return view('sendportal::segments.edit', compact('segment'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param SegmentRequest $request
     * @param int $id
     * @return RedirectResponse
     * @throws Exception
     */
    public function update(SegmentRequest $request, $id)
    {
        $this->segmentRepository->update(currentTeamId(), $id, $request->all());

        return redirect()->route('sendportal.segments.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return void
     */
    public function destroy($id)
    {
        app()->abort(404, 'Not implemented');
    }
}
