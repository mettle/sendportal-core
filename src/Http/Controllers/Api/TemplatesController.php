<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\Api\TemplateStoreRequest;
use Sendportal\Base\Http\Requests\Api\TemplateUpdateRequest;
use Sendportal\Base\Http\Resources\Template as TemplateResource;
use Sendportal\Base\Repositories\TemplateTenantRepository;
use Sendportal\Base\Services\Templates\TemplateService;

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
    public function index(int $workspaceId): AnonymousResourceCollection
    {
        $templates = $this->templates->paginate($workspaceId, 'name');

        return TemplateResource::collection($templates);
    }


    /**
     * @throws Exception
     */
    public function show(int $workspaceId, int $id): TemplateResource
    {
        return new TemplateResource($this->templates->find($workspaceId, $id));
    }

    /**
     * @throws Exception
     */
    public function store(TemplateStoreRequest $request, int $workspaceId): TemplateResource
    {
        $template = $this->service->store($workspaceId, $request->validated());

        return new TemplateResource($template);
    }

    /**
     * @throws Exception
     */
    public function update(TemplateUpdateRequest $request, int $workspaceId, int $id): TemplateResource
    {
        $template = $this->service->update($workspaceId, $id, $request->validated());

        return new TemplateResource($template);
    }

    /**
     * @throws Exception
     */
    public function destroy(int $workspaceId, int $id): Response
    {
        $this->service->delete($workspaceId, $id);

        return response(null, 204);
    }
}
