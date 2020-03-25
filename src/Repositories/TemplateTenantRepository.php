<?php

namespace Sendportal\Base\Repositories;

use Sendportal\Base\Models\Template;

class TemplateTenantRepository extends BaseTenantRepository
{
    protected $modelName = Template::class;
}
