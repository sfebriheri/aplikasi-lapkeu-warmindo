<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Exceptions\ValidationException;

/**
 * User Service
 *
 * Contains all user-related business logic
 */
class UserService extends BaseService
{
	/**
	 * User repository
	 *
	 * @var UserRepository
	 */
	protected $repository;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->repository = new UserRepository();
		parent::__construct($this->repository);
	}

	/**
	 * Get user by email
	 *
	 * @param string $email
	 * @return array|null
	 */
	public function getUserByEmail(string $email): ?array
	{
		return $this->repository->findByEmail($email);
	}

	/**
	 * Get all active users
	 *
	 * @return array
	 */
	public function getActiveUsers(): array
	{
		return $this->repository->findActive();
	}

	/**
	 * Get users by role
	 *
	 * @param int $roleId
	 * @return array
	 */
	public function getUsersByRole(int $roleId): array
	{
		return $this->repository->findByRole($roleId);
	}

	/**
	 * Search users
	 *
	 * @param string $keyword
	 * @return array
	 */
	public function searchUsers(string $keyword): array
	{
		if (strlen($keyword) < 2) {
			throw new ValidationException('Search keyword must be at least 2 characters');
		}

		return $this->repository->search($keyword);
	}

	/**
	 * Register new user
	 *
	 * @param array $data
	 * @return int User ID
	 * @throws ValidationException
	 */
	public function register(array $data): int
	{
		// Check if email already exists
		if ($this->repository->findByEmail($data['email'])) {
			throw new ValidationException('Email already registered', ['email' => 'Email already in use']);
		}

		// Hash password
		$data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

		// Set default role
		$data['role_id'] = $data['role_id'] ?? 2;
		$data['is_active'] = 1;

		return $this->create($data);
	}

	/**
	 * Update user
	 *
	 * @param int $userId
	 * @param array $data
	 * @return bool
	 */
	public function updateUser(int $userId, array $data): bool
	{
		// Don't allow email change if already in use
		if (isset($data['email'])) {
			$existing = $this->repository->findByEmail($data['email']);
			if ($existing && $existing['id'] !== $userId) {
				throw new ValidationException('Email already in use', ['email' => 'This email is already registered']);
			}
		}

		// Hash password if provided
		if (isset($data['password']) && !empty($data['password'])) {
			$data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
		} else {
			unset($data['password']);
		}

		return $this->update($userId, $data);
	}

	/**
	 * Activate user
	 *
	 * @param int $userId
	 * @return bool
	 */
	public function activate(int $userId): bool
	{
		return $this->update($userId, ['is_active' => 1]);
	}

	/**
	 * Deactivate user
	 *
	 * @param int $userId
	 * @return bool
	 */
	public function deactivate(int $userId): bool
	{
		return $this->update($userId, ['is_active' => 0]);
	}

	/**
	 * Change user password
	 *
	 * @param int $userId
	 * @param string $currentPassword
	 * @param string $newPassword
	 * @return bool
	 * @throws ValidationException
	 */
	public function changePassword(int $userId, string $currentPassword, string $newPassword): bool
	{
		$user = $this->getById($userId);

		// Verify current password
		if (!password_verify($currentPassword, $user['password'])) {
			throw new ValidationException('Incorrect current password', ['current_password' => 'Current password is incorrect']);
		}

		// Update password
		return $this->update($userId, [
			'password' => password_hash($newPassword, PASSWORD_DEFAULT)
		]);
	}
}
