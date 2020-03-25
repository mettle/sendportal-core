<?php

namespace Sendportal\Base\Repositories;

use Sendportal\Base\Models\Provider;
use Sendportal\Base\Models\ProviderType;

class ProviderTenantRepository extends BaseTenantRepository
{

    /**
     * @var string
     */
    protected $modelName = Provider::class;

    /**
     * @return mixed
     */
    public function getProviderTypes()
    {
        return ProviderType::orderBy('name')->get();
    }

    /**
     * @param $providerTypeId
     * @return mixed
     */
    public function findType($providerTypeId)
    {
        return ProviderType::findOrFail($providerTypeId);
    }

    /**
     * @param $providerTypeId
     * @return array
     */
    public function findSettings($providerTypeId)
    {
        if ($provider = Provider::where('type_id', $providerTypeId)->first()) {
            return $provider->settings;
        }

        return [];
    }
}
