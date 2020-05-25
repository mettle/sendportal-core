<?php

namespace Sendportal\Base\Interfaces;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

interface BaseTenantInterface
{
    /**
     * Return all records
     *
     * @param int $workspaceId
     * @param string $orderBy
     * @param array $relations
     * @param array $parameters
     * @return mixed
     * @throws Exception
     */
    public function all($workspaceId, $orderBy = 'id', array $relations = [], array $parameters = []);

    /**
     * Return paginated items
     *
     * @param int $workspaceId
     * @param string $orderBy
     * @param array $relations
     * @param int $paginate
     * @param array $parameters
     * @return mixed
     * @throws Exception
     */
    public function paginate($workspaceId, $orderBy = 'name', array $relations = [], $paginate = 25, array $parameters = []);

    /**
     * Get many records by a field and value
     *
     * @param int $workspaceId
     * @param array $parameters
     * @param array $relations
     * @return mixed
     * @throws Exception
     */
    public function getBy($workspaceId, array $parameters, array $relations = []);

    /**
     * List all records
     *
     * @param int $workspaceId
     * @param string $fieldName
     * @param string $fieldId
     * @return mixed
     * @throws Exception
     */
    public function pluck($workspaceId, $fieldName = 'name', $fieldId = 'id');

    /**
     * List all records matching a field's value
     *
     * @param int $workspaceId
     * @param string $field
     * @param mixed $value
     * @param string $listFieldName
     * @param string $listFieldId
     * @return mixed
     * @throws Exception
     */
    public function pluckBy($workspaceId, $field, $value, $listFieldName = 'name', $listFieldId = 'id');

    /**
     * Find a single record
     *
     * @param int $workspaceId
     * @param int $id
     * @param array $relations
     * @return mixed
     * @throws Exception
     */
    public function find($workspaceId, $id, array $relations = []);

    /**
     * Find a single record by a field and value
     *
     * @param int $workspaceId
     * @param string $field
     * @param mixed $value
     * @param array $relations
     * @return mixed
     * @throws Exception
     */
    public function findBy($workspaceId, $field, $value, array $relations = []);

    /**
     * Find a single record by multiple fields
     *
     * @param int $workspaceId
     * @param array $data
     * @param array $relations
     * @return mixed
     * @throws Exception
     */
    public function findByMany($workspaceId, array $data, array $relations = []);

    /**
     * Find multiple models
     *
     * @param int $workspaceId
     * @param array $ids
     * @param array $relations
     * @return mixed
     * @throws Exception
     */
    public function getWhereIn($workspaceId, array $ids, array $relations = []);

    /**
     * Create a new record
     *
     * @param int $workspaceId
     * @param array $data
     * @return mixed
     * @throws Exception
     */
    public function store($workspaceId, array $data);

    /**
     * Update the model instance
     *
     * @param int $workspaceId
     * @param int $id
     * @param array $data
     * @return mixed
     * @throws Exception
     */
    public function update($workspaceId, $id, array $data);

    /**
     * Delete a record
     *
     * @param int $workspaceId
     * @param int $id
     * @return mixed
     * @throws Exception
     */
    public function destroy($workspaceId, $id);

    /**
     * Count of all records
     *
     * @return int
     * @throws Exception
     */
    public function count(): int;

    /**
     * Return model name
     *
     * @return string
     * @throws RuntimeException If model has not been set.
     */
    public function getModelName(): string;

    /**
     * Return a new query builder instance.
     */
    public function getQueryBuilder(int $workspaceId): Builder;

    /**
     * Returns new model instance.
     *
     * @return Model
     */
    public function getNewInstance();

    /**
     * Set the order by field
     *
     * @param string $orderBy
     * @return void
     */
    public function setOrderBy($orderBy);

    /**
     * Get the order by field
     *
     * @return string
     */
    public function getOrderBy();

    /**
     * Set the order direction
     *
     * @param string $orderDirection
     * @return void
     */
    public function setOrderDirection($orderDirection);

    /**
     * Get the order direction
     *
     * @return string
     */
    public function getOrderDirection();
}
