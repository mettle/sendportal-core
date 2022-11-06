<?php

namespace Sendportal\Base\Repositories;

use Sendportal\Base\Models\Template;

class TemplateTenantRepository extends BaseTenantRepository
{
    protected $modelName = Template::class;

    /**
     * @inheritDoc
     */
    protected function applyFilters(Builder $instance, array $filters = []): void
    {
        $this->applyNameFilter($instance, $filters);
    }

    /**
     * Filter by name or email.
     */
    protected function applyNameFilter(Builder $instance, array $filters): void
    {
        if ($name = Arr::get($filters, 'name')) {
            $filterString = '%' . $name . '%';

            $instance->where(static function (Builder $instance) use ($filterString) {
                $instance->where('sendportal_templates.name', 'like', $filterString);
            });
        }
    }
}
