<?php

namespace App\Services;

use App\Exceptions\AppException;
use App\Exceptions\ResourceNotFoundException;
use App\Repositories\RepositoryInterface;
use CodeIgniter\Log\Logger;

/**
 * Base Service
 *
 * Abstract base class for all services containing common business logic operations
 */
abstract class BaseService
{
	/**
	 * Repository instance
	 *
	 * @var RepositoryInterface
	 */
	protected $repository;

	/**
	 * Logger instance
	 *
	 * @var Logger
	 */
	protected $logger;

	/**
	 * Constructor
	 *
	 * @param RepositoryInterface $repository
	 */
	public function __construct(RepositoryInterface $repository)
	{
		$this->repository = $repository;
		$this->logger = service('logger');
	}

	/**
	 * Get all records
	 *
	 * @return array
	 */
	public function getAll(): array
	{
		try {
			return $this->repository->all();
		} catch (AppException $e) {
			$this->logger->error('Error fetching all records: ' . $e->getMessage());
			throw $e;
		}
	}

	/**
	 * Get record by ID
	 *
	 * @param int $id
	 * @return array
	 * @throws ResourceNotFoundException
	 */
	public function getById(int $id): array
	{
		$record = $this->repository->find($id);

		if (!$record) {
			throw new ResourceNotFoundException('Resource', $id);
		}

		return $record;
	}

	/**
	 * Get paginated records
	 *
	 * @param int $perPage
	 * @param int $page
	 * @return array
	 */
	public function getPaginated(int $perPage = 15, int $page = 1): array
	{
		try {
			return $this->repository->paginate($perPage, $page);
		} catch (AppException $e) {
			$this->logger->error('Error fetching paginated records: ' . $e->getMessage());
			throw $e;
		}
	}

	/**
	 * Create new record
	 *
	 * @param array $data
	 * @return int|string Insert ID
	 */
	public function create(array $data): int|string
	{
		try {
			$id = $this->repository->create($data);
			$this->logger->info("Record created with ID: {$id}");
			return $id;
		} catch (AppException $e) {
			$this->logger->error('Error creating record: ' . $e->getMessage());
			throw $e;
		}
	}

	/**
	 * Update record
	 *
	 * @param int $id
	 * @param array $data
	 * @return bool
	 * @throws ResourceNotFoundException
	 */
	public function update(int $id, array $data): bool
	{
		if (!$this->repository->exists($id)) {
			throw new ResourceNotFoundException('Resource', $id);
		}

		try {
			$result = $this->repository->update($id, $data);
			if ($result) {
				$this->logger->info("Record updated: ID {$id}");
			}
			return $result;
		} catch (AppException $e) {
			$this->logger->error("Error updating record {$id}: " . $e->getMessage());
			throw $e;
		}
	}

	/**
	 * Delete record
	 *
	 * @param int $id
	 * @return bool
	 * @throws ResourceNotFoundException
	 */
	public function delete(int $id): bool
	{
		if (!$this->repository->exists($id)) {
			throw new ResourceNotFoundException('Resource', $id);
		}

		try {
			$result = $this->repository->delete($id);
			if ($result) {
				$this->logger->info("Record deleted: ID {$id}");
			}
			return $result;
		} catch (AppException $e) {
			$this->logger->error("Error deleting record {$id}: " . $e->getMessage());
			throw $e;
		}
	}

	/**
	 * Get the repository
	 *
	 * @return RepositoryInterface
	 */
	protected function getRepository(): RepositoryInterface
	{
		return $this->repository;
	}
}
