<?php

namespace Sendportal\Base\Interfaces;

interface BaseEloquentInterface
{
    /**
     * Return all items
     *
     * @param string $orderBy
     * @param array $relations
     * @param array $parameters
     * @return mixed
     */
    public function all($orderBy = 'id', array $relations = [], array $parameters = []);

    /**
     * Paginate items
     *
     * @param string $orderBy
     * @param array $relations
     * @param integer $paginate
     * @param array $parameters
     * @return mixed
     */
    public function paginate($orderBy = 'name', array $relations = [], $paginate = 50, array $parameters = []);

    /**
     * Get all items by a field
     *
     * @param array $parameters
     * @param array $relations
     * @return mixed
     */
    public function getBy(array $parameters, array $relations = []);

    /**
     * List all items
     *
     * @param string $fieldName
     * @param string $fieldId
     * @return mixed
     */
    public function pluck($fieldName = 'name', $fieldId = 'id');

    /**
     * List records limited by a certain field
     *
     * @param string $field
     * @param string|array $value
     * @param string $listFieldName
     * @param string $listFieldId
     * @return mixed
     */
    public function pluckBy($field, $value, $listFieldName = 'name', $listFieldId = 'id');

    /**
     * Find a single item
     *
     * @param int $id
     * @param array $relations
     * @return mixed
     */
    public function find($id, array $relations = []);

    /**
     * Find a single item by a field
     *
     * @param string $field
     * @param string $value
     * @param array $relations
     * @return mixed
     */
    public function findBy($field, $value, array $relations = []);

    /**
     * Find a single record by multiple fields
     *
     * @param array $data
     * @param array $relations
     * @return mixed
     */
    public function findByMany(array $data, array $relations = []);

    /**
     * Find multiple models
     *
     * @param array $ids
     * @param array $relations
     * @return object
     */
    public function getWhereIn(array $ids, array $relations = []);

    /**
     * Store a newly created item
     *
     * @param array $data
     * @return mixed
     */
    public function store(array $data);

    /**
     * Update an existing item
     *
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function update($id, array $data);

    /**
     * Permanently remove an item from storage
     *
     * @param integer $id
     * @return mixed
     */
    public function destroy($id);

    /**
     * Get count of records
     *
     * @param null
     * @return integer
     */
    public function count();
}
