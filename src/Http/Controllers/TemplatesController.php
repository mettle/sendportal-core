<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Sendportal\Base\Http\Requests\TemplateStoreRequest;
use Sendportal\Base\Http\Requests\TemplateUpdateRequest;
use Sendportal\Base\Repositories\TemplateTenantRepository;
use Sendportal\Base\Services\Templates\TemplateService;
use Throwable;

class TemplatesController extends Controller
{
    /** @var TemplateTenantRepository */
    private $templates;

    /** @var TemplateService */
    private $service;

    public function __construct(TemplateTenantRepository $templates, TemplateService $service)
    {
        $this->templates = $templates;
        $this->service = $service;
    }

    /**
     * @throws Exception
     */
    public function index(): View
    {
        $templates = $this->templates->paginate(auth()->user()->currentWorkspace()->id, 'name');

        return view('sendportal::templates.index', compact('templates'));
    }

    public function create(): View
    {
        return view('sendportal::templates.create');
    }

    /**
     * @throws Exception
     */
    public function store(TemplateStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (Arr::has($data, 'json')) {
            $data['content'] = $data['html'];

            unset($data['html']);
        }

        $this->service->store(auth()->user()->currentWorkspace()->id, $data);

        return redirect()
            ->route('sendportal.templates.index');
    }

    /**
     * @throws Exception
     */
    public function edit(int $id): View
    {
        $template = $this->templates->find(auth()->user()->currentWorkspace()->id, $id);

        return view('sendportal::templates.edit', compact('template'));
    }

    /**
     * @throws Exception
     */
    public function update(TemplateUpdateRequest $request, int $id): RedirectResponse
    {
        $data = $request->validated();

        if (Arr::has($data, 'json')) {
            $data['content'] = $data['html'];

            unset($data['html']);
        }

        $this->service->update(auth()->user()->currentWorkspace()->id, $id, $data);

        return redirect()
            ->route('sendportal.templates.index');
    }

    /**
     * @throws Throwable
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->service->delete(auth()->user()->currentWorkspace()->id, $id);

        return redirect()
            ->route('sendportal.templates.index')
            ->with('success', __('Template successfully deleted.'));
    }
}
