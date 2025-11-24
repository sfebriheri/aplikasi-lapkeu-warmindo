<?php

namespace App\Repositories;

use App\Models\UserModel;

/**
 * User Repository
 *
 * Repository for user data access
 */
class UserRepository extends BaseRepository
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(new UserModel());
	}

	/**
	 * Find user by email
	 *
	 * @param string $email
	 * @return array|null
	 */
	public function findByEmail(string $email): ?array
	{
		return $this->findBy('email', $email);
	}

	/**
	 * Find active users
	 *
	 * @return array
	 */
	public function findActive(): array
	{
		return $this->model
			->where('is_active', 1)
			->findAll();
	}

	/**
	 * Find users by role
	 *
	 * @param int $roleId
	 * @return array
	 */
	public function findByRole(int $roleId): array
	{
		return $this->model
			->where('role_id', $roleId)
			->findAll();
	}

	/**
	 * Search users by name or email
	 *
	 * @param string $keyword
	 * @return array
	 */
	public function search(string $keyword): array
	{
		return $this->model
			->like('nama', $keyword)
			->orLike('email', $keyword)
			->findAll();
	}
}
