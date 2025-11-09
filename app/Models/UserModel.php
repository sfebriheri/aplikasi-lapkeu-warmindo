<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * User Model
 * Handles all user-related database operations
 */
class UserModel extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'nama',
        'email',
        'password',
        'gambar',
        'role_id',
        'is_active',
        'date_created',
        'created_by',
        'updated_by'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'nama' => 'required|min_length[3]|max_length[100]',
        'email' => 'required|valid_email|max_length[128]|is_unique[user.email,id,{id}]',
        'password' => 'required|min_length[8]',
        'role_id' => 'required|integer',
    ];

    protected $validationMessages = [
        'email' => [
            'is_unique' => 'Email already exists',
            'valid_email' => 'Please enter a valid email address'
        ],
        'password' => [
            'min_length' => 'Password must be at least 8 characters long'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPasswordOnUpdate'];

    /**
     * Hash password before insert
     */
    protected function hashPassword(array $data): array
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }

        if (!isset($data['data']['date_created'])) {
            $data['data']['date_created'] = date('Y-m-d H:i:s');
        }

        return $data;
    }

    /**
     * Hash password before update if changed
     */
    protected function hashPasswordOnUpdate(array $data): array
    {
        if (isset($data['data']['password']) && !empty($data['data']['password'])) {
            // Only hash if it's not already hashed
            if (strlen($data['data']['password']) < 60) {
                $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
            }
        } else {
            // Remove password from update if empty
            unset($data['data']['password']);
        }

        return $data;
    }

    /**
     * Get user by email
     */
    public function getUserByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Get user by ID
     */
    public function getUserById(int $id): ?array
    {
        return $this->find($id);
    }

    /**
     * Get users by role
     */
    public function getUsersByRole(int $roleId): array
    {
        return $this->where('role_id', $roleId)
            ->orderBy('nama', 'ASC')
            ->findAll();
    }

    /**
     * Get active users
     */
    public function getActiveUsers(): array
    {
        return $this->where('is_active', 1)
            ->orderBy('nama', 'ASC')
            ->findAll();
    }

    /**
     * Activate user account
     */
    public function activateUser(int $id): bool
    {
        return $this->update($id, ['is_active' => 1]);
    }

    /**
     * Deactivate user account
     */
    public function deactivateUser(int $id): bool
    {
        return $this->update($id, ['is_active' => 0]);
    }

    /**
     * Update user password
     */
    public function updatePassword(string $email, string $password): bool
    {
        $user = $this->getUserByEmail($email);
        if (!$user) {
            return false;
        }

        return $this->update($user['id'], [
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);
    }

    /**
     * Check if email exists
     */
    public function emailExists(string $email): bool
    {
        return $this->where('email', $email)->countAllResults() > 0;
    }

    /**
     * Get user with role information
     */
    public function getUserWithRole(int $id): ?array
    {
        return $this->select('user.*, user_role.role, user_role.description as role_description')
            ->join('user_role', 'user_role.id = user.role_id', 'left')
            ->where('user.id', $id)
            ->first();
    }

    /**
     * Get all users with role information and pagination
     */
    public function getUsersWithRole(int $perPage = 10): array
    {
        return $this->select('user.*, user_role.role, user_role.description as role_description')
            ->join('user_role', 'user_role.id = user.role_id', 'left')
            ->orderBy('user.date_created', 'DESC')
            ->paginate($perPage);
    }

    /**
     * Search users
     */
    public function searchUsers(string $keyword): array
    {
        return $this->groupStart()
            ->like('nama', $keyword)
            ->orLike('email', $keyword)
            ->groupEnd()
            ->findAll();
    }
}
