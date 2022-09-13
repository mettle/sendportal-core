<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Tags;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\TagStoreRequest;
use Sendportal\Base\Http\Requests\TagUpdateRequest;
use Sendportal\Base\Repositories\TagTenantRepository;

class TagsController extends Controller
{
    /** @var TagTenantRepository */
    private $tagRepository;

    public function __construct(TagTenantRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    /**
     * @throws Exception
     */
    public function index(): View
    {
        $tags = $this->tagRepository->paginate(Sendportal::currentWorkspaceId(), 'name');

        return view('sendportal::tags.index', compact('tags'));
    }

    public function create(): View
    {
        return view('sendportal::tags.create');
    }

    /**
     * @throws Exception
     */
    public function store(TagStoreRequest $request): RedirectResponse
    {
        return $requuest->all();
        $this->tagRepository->store(Sendportal::currentWorkspaceId(), $request->all());

        return redirect()->route('sendportal.tags.index');
    }

    /**
     * @throws Exception
     */
    public function edit(int $id): View
    {
        $tag = $this->tagRepository->find(Sendportal::currentWorkspaceId(), $id, ['subscribers']);

        return view('sendportal::tags.edit', compact('tag'));
    }

    /**
     * @throws Exception
     */
    public function update(int $id, TagUpdateRequest $request): RedirectResponse
    {
        $this->tagRepository->update(Sendportal::currentWorkspaceId(), $id, $request->all());

        return redirect()->route('sendportal.tags.index');
    }

    /**
     * @throws Exception
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->tagRepository->destroy(Sendportal::currentWorkspaceId(), $id);

        return redirect()->route('sendportal.tags.index');
    }
}
