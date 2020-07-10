<?php

declare(strict_types=1);

namespace Sendportal\Base\Services\Templates;

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

    public function store(int $workspaceId, array $data): Template
    {
        $data['content'] = $this->normalizeTags($data['content'], 'content');

        return $this->templates->store($workspaceId, $data);
    }

    public function update(int $workspaceId, int $templateId, array $data): Template
    {
        $data['content'] = $this->normalizeTags($data['content'], 'content');

        return $this->templates->update($workspaceId, $templateId, $data);
    }

    public function delete(int $workspaceId, int $templateId): bool
    {
        $template = $this->templates->find($workspaceId, $templateId);

        // TODO(david): I don't think `is_in_use` has been implemented.
        if ($template->is_in_use) {
            return false;
        }

        return $this->templates->destroy($workspaceId, $template->id);
    }
}
