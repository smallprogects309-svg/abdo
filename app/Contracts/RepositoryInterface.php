<?php

namespace App\Contracts;

/**
 * Repository Contract
 * Interface لضمان أن جميع Repositories تطبق نفس الـ Methods
 */
interface RepositoryInterface
{
    /**
     * Get all records
     */
    public function all(array $columns = ['*']);

    /**
     * Get paginated records
     */
    public function paginate(int $perPage = 15, array $columns = ['*']);

    /**
     * Get record by ID
     */
    public function find(int $id, array $columns = ['*']);

    /**
     * Get record by attribute
     */
    public function findBy(string $attribute, $value, array $columns = ['*']);

    /**
     * Get all records by attribute
     */
    public function findAllBy(string $attribute, $value, array $columns = ['*']);

    /**
     * Create new record
     */
    public function create(array $data);

    /**
     * Update record
     */
    public function update(int $id, array $data);

    /**
     * Delete record
     */
    public function delete(int $id): bool;

    /**
     * Check if record exists
     */
    public function exists(int $id): bool;

    /**
     * Get count of records
     */
    public function count();

    /**
     * Search records
     */
    public function search(string $query, array $columns = ['*']);
}
