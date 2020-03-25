<?php

namespace Sendportal\Base\Interfaces;

interface BaseTenantInterface
{
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
    public function all($teamId, $orderBy = 'id', array $relations = [], array $parameters = []);

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
    public function paginate($teamId, $orderBy = 'name', array $relations = [], $paginate = 25, array $parameters = []);

    /**
     * Get many records by a field and value
     *
     * @param int $teamId
     * @param array $parameters
     * @param array $relations
     * @return mixed
     * @throws \Exception
     */
    public function getBy($teamId, array $parameters, array $relations = []);

    /**
     * List all records
     *
     * @param int $teamId
     * @param string $fieldName
     * @param string $fieldId
     * @return mixed
     * @throws \Exception
     */
    public function pluck($teamId, $fieldName = 'name', $fieldId = 'id');

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
    public function pluckBy($teamId, $field, $value, $listFieldName = 'name', $listFieldId = 'id');

    /**
     * Find a single record
     *
     * @param int $teamId
     * @param int $id
     * @param array $relations
     * @return mixed
     * @throws \Exception
     */
    public function find($teamId, $id, array $relations = []);

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
    public function findBy($teamId, $field, $value, array $relations = []);

    /**
     * Find a single record by multiple fields
     *
     * @param int $teamId
     * @param array $data
     * @param array $relations
     * @return mixed
     * @throws \Exception
     */
    public function findByMany($teamId, array $data, array $relations = []);

    /**
     * Find multiple models
     *
     * @param int $teamId
     * @param array $ids
     * @param array $relations
     * @return mixed
     * @throws \Exception
     */
    public function getWhereIn($teamId, array $ids, array $relations = []);

    /**
     * Create a new record
     *
     * @param int $teamId
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function store($teamId, array $data);

    /**
     * Update the model instance
     *
     * @param int $teamId
     * @param int $id
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function update($teamId, $id, array $data);

    /**
     * Delete a record
     *
     * @param int $teamId
     * @param int $id
     * @return mixed
     * @throws \Exception
     */
    public function destroy($teamId, $id);

    /**
     * Count of all records
     *
     * @param int $teamId
     * @return mixed
     * @throws \Exception
     */
    public function count($teamId);

    /**
     * Return model name
     *
     * @return string
     * @throws \Exception If model has not been set.
     */
    public function getModelName();

    /**
     * Return a new query builder instance
     *
     * @param int $teamId
     * @return mixed
     * @throws \Exception
     */
    public function getQueryBuilder($teamId);

    /**
     * Returns new model instance
     *
     * @return mixed
     * @throws \Exception
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
