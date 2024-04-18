<?php

namespace Sendportal\Base\Repositories;

use Sendportal\Base\Interfaces\BaseEloquentInterface;

class BaseEloquentRepository implements BaseEloquentInterface
{
    /**
     * Model name
     *
     * @var string
     */
    protected $modelName;

    /**
     * Current Object instance
     *
     * @var object
     */
    protected $instance;

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
     * @param string $orderBy
     * @param array $relations
     * @param array $parameters
     * @return mixed
     */
    public function all($orderBy = 'id', array $relations = [], array $parameters = [])
    {
        $instance = $this->getQueryBuilder();

        $this->parseOrder($orderBy);

        $this->applyFilters($instance, $parameters);

        return $instance->with($relations)
            ->orderBy($this->getOrderBy(), $this->getOrderDirection())
            ->get();
    }

    /**
     * Return paginated items
     *
     * @param string $orderBy
     * @param array $relations
     * @param int $paginate
     * @param array $parameters
     * @return mixed
     */
    public function paginate($orderBy = 'name', array $relations = [], $paginate = 25, array $parameters = [])
    {
        $instance = $this->getQueryBuilder();

        $this->parseOrder($orderBy);

        $this->applyFilters($instance, $parameters);

        return $instance->with($relations)
            ->orderBy($this->getOrderBy(), $this->getOrderDirection())
            ->paginate($paginate);
    }

    /**
     * Apply parameters, which can be extended in child classes for filtering
     *
     * @param $query
     * @param array $filters
     * @return mixed
     */
    protected function applyFilters($instance, array $filters = [])
    {

    }

    /**
     * Get many records by a field and value
     *
     * @param array $parameters
     * @param array $relations
     * @return mixed
     * @throws \Exception
     */
    public function getBy(array $parameters, array $relations = [])
    {
        $instance = $this->getQueryBuilder()
            ->with($relations);

        foreach ($parameters as $field => $value) {
            $instance->where($field, $value);
        }

        return $instance->get();
    }

    /**
     * List all records
     *
     * @param string $fieldName
     * @param string $fieldId
     * @return mixed
     * @throws \Exception
     */
    public function pluck($fieldName = 'name', $fieldId = 'id')
    {
        $instance = $this->getQueryBuilder();

        return $instance
            ->orderBy($fieldName)
            ->pluck($fieldName, $fieldId)
            ->all();
    }

    /**
     * List all records
     *
     * @param string $field
     * @param string|array $value
     * @param string $listFieldName
     * @param string $listFieldId
     * @return mixed
     * @throws \Exception
     */
    public function pluckBy($field, $value, $listFieldName = 'name', $listFieldId = 'id')
    {
        $instance = $this->getQueryBuilder();

        if (! is_array($value)) {
            $value = [$value];
        }

        return $instance
            ->whereIn($field, $value)
            ->orderBy($listFieldName)
            ->pluck($listFieldName, $listFieldId)
            ->all();
    }

    /**
     * Find a single record
     *
     * @param int $id
     * @param array $relations
     * @return mixed
     * @throws \Exception
     */
    public function find($id, array $relations = [])
    {
        $this->instance = $this->getQueryBuilder()->with($relations)->find($id);

        return $this->instance;
    }

    /**
     * Find a single record by a field and value
     *
     * @param string $field
     * @param string $value
     * @param array $relations
     * @return mixed
     * @throws \Exception
     */
    public function findBy($field, $value, array $relations = [])
    {
        $this->instance = $this->getQueryBuilder()->with($relations)->where($field, $value)->first();

        return $this->instance;
    }

    /**
     * Find a single record by multiple fields
     *
     * @param array $data
     * @param array $relations
     * @return mixed
     * @throws \Exception
     */
    public function findByMany(array $data, array $relations = [])
    {
        $model = $this->getQueryBuilder()->with($relations);

        foreach ($data as $key => $value) {
            $model->where($key, $value);
        }

        $this->instance = $model->first();

        return $this->instance;
    }

    /**
     * Find multiple models
     *
     * @param array $ids
     * @param array $relations
     * @return object
     * @throws \Exception
     */
    public function getWhereIn(array $ids, array $relations = [])
    {
        $this->instance = $this->getQueryBuilder()->with($relations)->whereIn('id', $ids)->get();

        return $this->instance;
    }

    /**
     * Create a new record
     *
     * @param array $data The input data
     * @return object model instance
     * @throws \Exception
     */
    public function store(array $data)
    {
        $this->instance = $this->getNewInstance();

        return $this->executeSave($data);
    }

    /**
     * Update the model instance
     *
     * @param int $id The model id
     * @param array $data The input data
     * @return object model instance
     * @throws \Exception
     */
    public function update($id, array $data)
    {
        $this->instance = $this->find($id);

        return $this->executeSave($data);
    }

    /**
     * Save the model
     *
     * NB - check BaseTenantRepo if any changes
     * are made here
     *
     * @param array $data
     * @return mixed
     */
    protected function executeSave(array $data)
    {
        $data = $this->setBooleanFields($data);

        $this->instance->fill($data);
        $this->instance->save();

        return $this->instance;
    }

    /**
     * Delete a record
     *
     * @param int $id Model id
     * @return object model instance
     * @throws \Exception
     */
    public function destroy($id)
    {
        $instance = $this->find($id);

        return $instance->delete();
    }

    /**
     * Set the model's boolean fields from the input data
     *
     * @param array $data
     * @return array
     */
    protected function setBooleanFields(array $data)
    {
        foreach ($this->getModelBooleanFields() as $booleanField) {
            $data[$booleanField] = \Arr::get($data, $booleanField, 0);
        }

        return $data;
    }

    /**
     * Retrieve the boolean fields from the model
     *
     * @return array
     */
    protected function getModelBooleanFields()
    {
        return $this->instance->getBooleanFields();
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
     * @return mixed
     * @throws \Exception#
     */
    public function getQueryBuilder()
    {
        return $this->getNewInstance()->newQuery();
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

        return new $model();
    }

    /**
     * Resolve order by
     *
     * @param string $orderBy
     * @return void
     */
    protected function resolveOrder($orderBy)
    {
        if (! \Input::get('sort_by')) {
            $this->parseOrder($orderBy);
            return;
        }

        $this->resolveDirection();
        $this->resolveOrderBy($orderBy);
    }

    /**
     * Resolve direction
     * @return void
     */
    protected function resolveDirection()
    {
        $direction = strtolower(\Input::get('direction', 'asc'));

        if (! in_array($direction, ['asc', 'desc'])) {
            $direction = 'asc';
        }

        $this->setOrderDirection($direction);
    }

    /**
     * Resolve order by
     * @return void
     */
    protected function resolveOrderBy($column)
    {
        $orderBy = \Input::get('sort_by');

        $orderBy = \Arr::get($this->orderOptions, $orderBy, $column);

        $this->setOrderBy($orderBy);
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
     * Get count of records
     *
     * @param null
     * @return integer
     * @throws \Exception
     */
    public function count()
    {
        return $this->getNewInstance()->count();
    }
}
