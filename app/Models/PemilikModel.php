<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Pemilik Model
 * Handles owner/user management data operations
 */
class PemilikModel extends Model
{
	protected $table = 'user';
	protected $primaryKey = 'id';
	protected $allowedFields = ['is_active', 'role_id'];

	/**
	 * Activate user
	 *
	 * @param int $id
	 * @return bool
	 */
	public function updateAktif($id)
	{
		return $this->update($id, ['is_active' => 1]);
	}

	/**
	 * Deactivate user
	 *
	 * @param int $id
	 * @return bool
	 */
	public function updateNonaktif($id)
	{
		return $this->update($id, ['is_active' => 2]);
	}

	/**
	 * Promote user to admin (role_id = 1)
	 *
	 * @param int $id
	 * @return bool
	 */
	public function upLevel($id)
	{
		return $this->update($id, ['role_id' => 1]);
	}

	/**
	 * Demote user from admin (role_id = 2)
	 *
	 * @param int $id
	 * @return bool
	 */
	public function downLevel($id)
	{
		return $this->update($id, ['role_id' => 2]);
	}

	/**
	 * Delete user
	 *
	 * @param int $id
	 * @return bool
	 */
	public function hapus($id)
	{
		return $this->delete($id);
	}
}
