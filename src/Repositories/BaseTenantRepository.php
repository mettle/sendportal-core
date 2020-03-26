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
    protected $tenantKey = 'team_id';

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
     * @param int $teamId
     * @param string $orderBy
     * @param array $relations
     * @param array $parameters
     * @return mixed
     * @throws \Exception
     */
    public function all($teamId, $orderBy = 'id', array $relations = [], array $parameters = [])
    {
        $instance = $this->getQueryBuilder($teamId);

        $this->parseOrder($orderBy);

        $this->applyFilters($instance, $parameters);

        return $instance->with($relations)
            ->orderBy($this->getOrderBy(), $this->getOrderDirection())
            ->get();
    }

    /**
     * Return paginated items
     *
     * @param int $teamId
     * @param string $orderBy
     * @param array $relations
     * @param int $paginate
     * @param array $parameters
     * @return mixed
     * @throws \Exception
     */
    public function paginate($teamId, $orderBy = 'name', array $relations = [], $paginate = 25, array $parameters = [])
    {
        $instance = $this->getQueryBuilder($teamId);

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
     * @param int $teamId
     * @param array $parameters
     * @param array $relations
     * @return mixed
     * @throws \Exception
     */
    public function getBy($teamId, $parameters, array $relations = [])
    {
        $instance = $this->getQueryBuilder($teamId)
            ->with($relations);

        foreach ($parameters as $field => $value) {
            $instance->where($field, $value);
        }

        return $instance->get();
    }

    /**
     * List all records
     *
     * @param int $teamId
     * @param string $fieldName
     * @param string $fieldId
     * @return mixed
     * @throws \Exception
     */
    public function pluck($teamId, $fieldName = 'name', $fieldId = 'id')
    {
        return $this->getQueryBuilder($teamId)
            ->orderBy($fieldName)
            ->pluck($fieldName, $fieldId)
            ->all();
    }

    /**
     * List all records matching a field's value
     *
     * @param int $teamId
     * @param string $field
     * @param mixed $value
     * @param string $listFieldName
     * @param string $listFieldId
     * @return mixed
     * @throws \Exception
     */
    public function pluckBy($teamId, $field, $value, $listFieldName = 'name', $listFieldId = 'id')
    {
        if (! is_array($value)) {
            $value = [$value];
        }

        return $this->getQueryBuilder($teamId)
            ->whereIn($field, $value)
            ->orderBy($listFieldName)
            ->pluck($listFieldName, $listFieldId)
            ->all();
    }

    /**
     * Find a single record
     *
     * @param int $teamId
     * @param int $id
     * @param array $relations
     * @return mixed
     * @throws \Exception
     */
    public function find($teamId, $id, array $relations = [])
    {
        return $this->getQueryBuilder($teamId)->with($relations)->findOrFail($id);
    }

    /**
     * Find a single record by a field and value
     *
     * @param int $teamId
     * @param string $field
     * @param mixed $value
     * @param array $relations
     * @return mixed
     * @throws \Exception
     */
    public function findBy($teamId, $field, $value, array $relations = [])
    {
        return $this->getQueryBuilder($teamId)
            ->with($relations)
            ->where($field, $value)
            ->first();
    }

    /**
     * Find a single record by multiple fields
     *
     * @param int $teamId
     * @param array $data
     * @param array $relations
     * @return mixed
     * @throws \Exception
     */
    public function findByMany($teamId, array $data, array $relations = [])
    {
        $model = $this->getQueryBuilder($teamId)->with($relations);

        foreach ($data as $key => $value) {
            $model->where($key, $value);
        }

        return $model->first();
    }

    /**
     * Find multiple models
     *
     * @param int $teamId
     * @param array $ids
     * @param array $relations
     * @return mixed
     * @throws \Exception
     */
    public function getWhereIn($teamId, array $ids, array $relations = [])
    {
        return $this->getQueryBuilder($teamId)
            ->with($relations)
            ->whereIn('id', $ids)->get();
    }

    /**
     * Create a new record
     *
     * @param int $teamId
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function store($teamId, array $data)
    {
        $this->checkTenantData($data);

        $instance = $this->getNewInstance();

        return $this->executeSave($teamId, $instance, $data);
    }

    /**
     * Update the model instance
     *
     * @param int $teamId
     * @param int $id
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function update($teamId, $id, array $data)
    {
        $this->checkTenantData($data);

        $instance = $this->find($teamId, $id);

        return $this->executeSave($teamId, $instance, $data);
    }

    /**
     * Save the model
     *
     * @param int $teamId
     * @param mixed $instance
     * @param array $data
     * @return mixed
     */
    protected function executeSave($teamId, $instance, array $data)
    {
        $data = $this->setBooleanFields($instance, $data);

        $instance->fill($data);
        $instance->{$this->getTenantKey()} = $teamId;
        $instance->save();

        return $instance;
    }

    /**
     * Delete a record
     *
     * @param int $teamId
     * @param int $id
     * @return mixed
     * @throws \Exception
     */
    public function destroy($teamId, $id)
    {
        $instance = $this->find($teamId, $id);

        return $instance->delete();
    }

    /**
     * Count of all records
     *
     * @param int $teamId
     * @return mixed
     * @throws \Exception
     */
    public function count($teamId)
    {
        return $this->getNewInstance($teamId)->count();
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
     * @param int $teamId
     * @return mixed
     * @throws \Exception
     */
    public function getQueryBuilder($teamId)
    {
        return $this->getNewInstance()->newQuery()
            ->where('team_id', $teamId);
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
