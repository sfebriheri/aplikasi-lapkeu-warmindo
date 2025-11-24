<?php

namespace App\Repositories;

/**
 * Repository Interface
 *
 * Defines the contract for all repository classes
 */
interface RepositoryInterface
{
	/**
	 * Get all records
	 *
	 * @param array $columns
	 * @return array
	 */
	public function all(array $columns = ['*']): array;

	/**
	 * Find record by ID
	 *
	 * @param int $id
	 * @param array $columns
	 * @return array|null
	 */
	public function find(int $id, array $columns = ['*']): ?array;

	/**
	 * Find record by custom field
	 *
	 * @param string $field
	 * @param mixed $value
	 * @param array $columns
	 * @return array|null
	 */
	public function findBy(string $field, mixed $value, array $columns = ['*']): ?array;

	/**
	 * Get paginated records
	 *
	 * @param int $perPage
	 * @param int $page
	 * @param array $columns
	 * @return array
	 */
	public function paginate(int $perPage = 15, int $page = 1, array $columns = ['*']): array;

	/**
	 * Create new record
	 *
	 * @param array $data
	 * @return int|string Insert ID
	 */
	public function create(array $data): int|string;

	/**
	 * Update record
	 *
	 * @param int $id
	 * @param array $data
	 * @return bool
	 */
	public function update(int $id, array $data): bool;

	/**
	 * Delete record
	 *
	 * @param int $id
	 * @return bool
	 */
	public function delete(int $id): bool;

	/**
	 * Count records
	 *
	 * @return int
	 */
	public function count(): int;

	/**
	 * Check if record exists
	 *
	 * @param int $id
	 * @return bool
	 */
	public function exists(int $id): bool;
}
