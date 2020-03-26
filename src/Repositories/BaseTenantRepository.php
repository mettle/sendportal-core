<?php namespace Sendportal\Base\Repositories;

use Sendportal\Base\Interfaces\BaseTenantInterface;

abstract class BaseTenantRepository implements BaseTenantInterface
{
    /**
     * @var string
     */
    protected $modelName;

    /**
     * @var string
     */
    protected $tenantKey = 'workspace_id';

    /**
     * Order Options
     *
     * @var array
     */
    protected $orderOptions = [];

    /**
     * Default order by
     *
     * @var string
     */
    private $orderBy = 'name';

    /**
     * Default order direction
     *
     * @var string
     */
    private $orderDirection = 'asc';

    /**
     * Return all records
     *
     * @param int $workspaceId
     * @param string $orderBy
     * @param array $relations
     * @param array $parameters
     * @return mixed
     * @throws \Exception
     */
    public function all($workspaceId, $orderBy = 'id', array $relations = [], array $parameters = [])
    {
        $instance = $this->getQueryBuilder($workspaceId);

        $this->parseOrder($orderBy);

        $this->applyFilters($instance, $parameters);

        return $instance->with($relations)
            ->orderBy($this->getOrderBy(), $this->getOrderDirection())
            ->get();
    }

    /**
     * Return paginated items
     *
     * @param int $workspaceId
     * @param string $orderBy
     * @param array $relations
     * @param int $paginate
     * @param array $parameters
     * @return mixed
     * @throws \Exception
     */
    public function paginate($workspaceId, $orderBy = 'name', array $relations = [], $paginate = 25, array $parameters = [])
    {
        $instance = $this->getQueryBuilder($workspaceId);

        $this->parseOrder($orderBy);

        $this->applyFilters($instance, $parameters);

        return $instance->with($relations)
            ->orderBy($this->getOrderBy(), $this->getOrderDirection())
            ->paginate($paginate);
    }

    /**
     * Apply parameters, which can be extended in child classes for filtering
     *
     * @param $instance
     * @param array $filters
     * @return mixed
     */
    protected function applyFilters($instance, array $filters = [])
    {
        return;
    }

    /**
     * Get many records by a field and value
     *
     * @param int $workspaceId
     * @param array $parameters
     * @param array $relations
     * @return mixed
     * @throws \Exception
     */
    public function getBy($workspaceId, $parameters, array $relations = [])
    {
        $instance = $this->getQueryBuilder($workspaceId)
            ->with($relations);

        foreach ($parameters as $field => $value) {
            $instance->where($field, $value);
        }

        return $instance->get();
    }

    /**
     * List all records
     *
     * @param int $workspaceId
     * @param string $fieldName
     * @param string $fieldId
     * @return mixed
     * @throws \Exception
     */
    public function pluck($workspaceId, $fieldName = 'name', $fieldId = 'id')
    {
        return $this->getQueryBuilder($workspaceId)
            ->orderBy($fieldName)
            ->pluck($fieldName, $fieldId)
            ->all();
    }

    /**
     * List all records matching a field's value
     *
     * @param int $workspaceId
     * @param string $field
     * @param mixed $value
     * @param string $listFieldName
     * @param string $listFieldId
     * @return mixed
     * @throws \Exception
     */
    public function pluckBy($workspaceId, $field, $value, $listFieldName = 'name', $listFieldId = 'id')
    {
        if (! is_array($value)) {
            $value = [$value];
        }

        return $this->getQueryBuilder($workspaceId)
            ->whereIn($field, $value)
            ->orderBy($listFieldName)
            ->pluck($listFieldName, $listFieldId)
            ->all();
    }

    /**
     * Find a single record
     *
     * @param int $workspaceId
     * @param int $id
     * @param array $relations
     * @return mixed
     * @throws \Exception
     */
    public function find($workspaceId, $id, array $relations = [])
    {
        return $this->getQueryBuilder($workspaceId)->with($relations)->findOrFail($id);
    }

    /**
     * Find a single record by a field and value
     *
     * @param int $workspaceId
     * @param string $field
     * @param mixed $value
     * @param array $relations
     * @return mixed
     * @throws \Exception
     */
    public function findBy($workspaceId, $field, $value, array $relations = [])
    {
        return $this->getQueryBuilder($workspaceId)
            ->with($relations)
            ->where($field, $value)
            ->first();
    }

    /**
     * Find a single record by multiple fields
     *
     * @param int $workspaceId
     * @param array $data
     * @param array $relations
     * @return mixed
     * @throws \Exception
     */
    public function findByMany($workspaceId, array $data, array $relations = [])
    {
        $model = $this->getQueryBuilder($workspaceId)->with($relations);

        foreach ($data as $key => $value) {
            $model->where($key, $value);
        }

        return $model->first();
    }

    /**
     * Find multiple models
     *
     * @param int $workspaceId
     * @param array $ids
     * @param array $relations
     * @return mixed
     * @throws \Exception
     */
    public function getWhereIn($workspaceId, array $ids, array $relations = [])
    {
        return $this->getQueryBuilder($workspaceId)
            ->with($relations)
            ->whereIn('id', $ids)->get();
    }

    /**
     * Create a new record
     *
     * @param int $workspaceId
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function store($workspaceId, array $data)
    {
        $this->checkTenantData($data);

        $instance = $this->getNewInstance();

        return $this->executeSave($workspaceId, $instance, $data);
    }

    /**
     * Update the model instance
     *
     * @param int $workspaceId
     * @param int $id
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function update($workspaceId, $id, array $data)
    {
        $this->checkTenantData($data);

        $instance = $this->find($workspaceId, $id);

        return $this->executeSave($workspaceId, $instance, $data);
    }

    /**
     * Save the model
     *
     * @param int $workspaceId
     * @param mixed $instance
     * @param array $data
     * @return mixed
     */
    protected function executeSave($workspaceId, $instance, array $data)
    {
        $data = $this->setBooleanFields($instance, $data);

        $instance->fill($data);
        $instance->{$this->getTenantKey()} = $workspaceId;
        $instance->save();

        return $instance;
    }

    /**
     * Delete a record
     *
     * @param int $workspaceId
     * @param int $id
     * @return mixed
     * @throws \Exception
     */
    public function destroy($workspaceId, $id)
    {
        $instance = $this->find($workspaceId, $id);

        return $instance->delete();
    }

    /**
     * Count of all records
     *
     * @param int $workspaceId
     * @return mixed
     * @throws \Exception
     */
    public function count($workspaceId)
    {
        return $this->getNewInstance($workspaceId)->count();
    }

    /**
     * Return model name
     *
     * @return string
     * @throws \Exception If model has not been set.
     */
    public function getModelName()
    {
        if (! $this->modelName) {
            throw new \Exception('Model has not been set in ' . get_called_class());
        }

        return $this->modelName;
    }

    /**
     * Return a new query builder instance
     *
     * @param int $workspaceId
     * @return mixed
     * @throws \Exception
     */
    public function getQueryBuilder($workspaceId)
    {
        return $this->getNewInstance()->newQuery()
            ->where('workspace_id', $workspaceId);
    }

    /**
     * Returns new model instance
     *
     * @return mixed
     * @throws \Exception
     */
    public function getNewInstance()
    {
        $model = $this->getModelName();

        return new $model;
    }

    /**
     * Parse the order by data
     *
     * @param string $orderBy
     * @return void
     */
    protected function parseOrder($orderBy)
    {
        if (substr($orderBy, -3) == 'Asc') {
            $this->setOrderDirection('asc');
            $orderBy = substr_replace($orderBy, '', -3);
        } elseif (substr($orderBy, -4) == 'Desc') {
            $this->setOrderDirection('desc');
            $orderBy = substr_replace($orderBy, '', -4);
        }

        $this->setOrderBy($orderBy);
    }

    /**
     * Set the order by field
     *
     * @param string $orderBy
     * @return void
     */
    public function setOrderBy($orderBy)
    {
        $this->orderBy = $orderBy;
    }

    /**
     * Get the order by field
     *
     * @return string
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * Set the order direction
     *
     * @param string $orderDirection
     * @return void
     */
    public function setOrderDirection($orderDirection)
    {
        $this->orderDirection = $orderDirection;
    }

    /**
     * Get the order direction
     *
     * @return string
     */
    public function getOrderDirection()
    {
        return $this->orderDirection;
    }

    /**
     * Set the tenant key when saving data
     *
     * @param array $data
     * @throws \Exception If Tenant value is found in data.
     * @return void
     */
    protected function checkTenantData(array $data)
    {
        if (isset($data[$this->getTenantKey()])) {
            throw new \Exception('Tenant value should not be provided in data.');
        }
    }

    /**
     * Returns tenant key
     *
     * @return string
     */
    protected function getTenantKey()
    {
        return $this->tenantKey;
    }


    /**
     * Set the model's boolean fields from the input data
     *
     * @param mixed $instance
     * @param array $data
     * @return array
     */
    protected function setBooleanFields($instance, array $data)
    {
        foreach ($this->getModelBooleanFields($instance) as $booleanField) {
            $data[$booleanField] = \Arr::get($data, $booleanField, 0);
        }

        return $data;
    }

    /**
     * Retrieve the boolean fields from the model
     *
     * @param mixed $instance
     * @return array
     */
    protected function getModelBooleanFields($instance)
    {
        return $instance->getBooleanFields();
    }
}
