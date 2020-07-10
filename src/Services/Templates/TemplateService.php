<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Templates;

use Exception;
use Sendportal\Base\Models\Template;
use Sendportal\Base\Repositories\TemplateTenantRepository;
use Sendportal\Base\Traits\NormalizeTags;

class TemplateService
{
    use NormalizeTags;

    /** @var TemplateTenantRepository */
    private $templates;

    public function __construct(TemplateTenantRepository $templates)
    {
        $this->templates = $templates;
    }

    /**
     * @throws Exception
     */
    public function store(int $workspaceId, array $data): Template
    {
        $data['content'] = $this->normalizeTags($data['content'], 'content');

        return $this->templates->store($workspaceId, $data);
    }

    /**
     * @throws Exception
     */
    public function update(int $workspaceId, int $templateId, array $data): Template
    {
        $data['content'] = $this->normalizeTags($data['content'], 'content');

        return $this->templates->update($workspaceId, $templateId, $data);
    }

    /**
     * @throws Exception
     */
    public function delete(int $workspaceId, int $templateId): bool
    {
        return $this->templates->destroy($workspaceId, $templateId);
    }
}
