<?php

namespace App\Repositories;

use CodeIgniter\Model;

/**
 * Base Repository
 *
 * Abstract base class for all repositories providing common database operations
 */
abstract class BaseRepository implements RepositoryInterface
{
	/**
	 * Model instance
	 *
	 * @var Model
	 */
	protected $model;

	/**
	 * Constructor
	 *
	 * @param Model $model
	 */
	public function __construct(Model $model)
	{
		$this->model = $model;
	}

	/**
	 * Get all records
	 *
	 * @param array $columns
	 * @return array
	 */
	public function all(array $columns = ['*']): array
	{
		return $this->model->select($columns)->findAll();
	}

	/**
	 * Find record by ID
	 *
	 * @param int $id
	 * @param array $columns
	 * @return array|null
	 */
	public function find(int $id, array $columns = ['*']): ?array
	{
		return $this->model->select($columns)->find($id);
	}

	/**
	 * Find record by custom field
	 *
	 * @param string $field
	 * @param mixed $value
	 * @param array $columns
	 * @return array|null
	 */
	public function findBy(string $field, mixed $value, array $columns = ['*']): ?array
	{
		return $this->model
			->select($columns)
			->where($field, $value)
			->first();
	}

	/**
	 * Get paginated records
	 *
	 * @param int $perPage
	 * @param int $page
	 * @param array $columns
	 * @return array
	 */
	public function paginate(int $perPage = 15, int $page = 1, array $columns = ['*']): array
	{
		$total = $this->count();
		$records = $this->model
			->select($columns)
			->paginate($perPage, 'default', 1, $page);

		return [
			'data' => $records,
			'total' => $total,
			'per_page' => $perPage,
			'current_page' => $page,
			'last_page' => ceil($total / $perPage),
			'from' => (($page - 1) * $perPage) + 1,
			'to' => min($page * $perPage, $total),
		];
	}

	/**
	 * Create new record
	 *
	 * @param array $data
	 * @return int|string Insert ID
	 */
	public function create(array $data): int|string
	{
		$this->model->insert($data);
		return $this->model->getInsertID();
	}

	/**
	 * Update record
	 *
	 * @param int $id
	 * @param array $data
	 * @return bool
	 */
	public function update(int $id, array $data): bool
	{
		return $this->model->update($id, $data);
	}

	/**
	 * Delete record
	 *
	 * @param int $id
	 * @return bool
	 */
	public function delete(int $id): bool
	{
		return $this->model->delete($id);
	}

	/**
	 * Count records
	 *
	 * @return int
	 */
	public function count(): int
	{
		return $this->model->countAll();
	}

	/**
	 * Check if record exists
	 *
	 * @param int $id
	 * @return bool
	 */
	public function exists(int $id): bool
	{
		return $this->find($id) !== null;
	}

	/**
	 * Get the model instance
	 *
	 * @return Model
	 */
	public function getModel(): Model
	{
		return $this->model;
	}
}
