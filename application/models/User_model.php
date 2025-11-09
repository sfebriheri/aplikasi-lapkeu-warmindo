<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * User Model
 * Handles all user-related database operations
 */
class User_model extends CI_Model
{
	protected $table = 'user';

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * Get user by email
	 */
	public function get_user_by_email($email)
	{
		try {
			$query = $this->db->where('email', $email)
				->limit(1)
				->get($this->table);

			if ($query->num_rows() > 0) {
				return $query->row_array();
			}
			return null;
		} catch (Exception $e) {
			log_message('error', 'Error in get_user_by_email: ' . $e->getMessage());
			return null;
		}
	}

	/**
	 * Get user by ID
	 */
	public function get_user_by_id($id)
	{
		try {
			$query = $this->db->where('id', $id)
				->limit(1)
				->get($this->table);

			if ($query->num_rows() > 0) {
				return $query->row_array();
			}
			return null;
		} catch (Exception $e) {
			log_message('error', 'Error in get_user_by_id: ' . $e->getMessage());
			return null;
		}
	}

	/**
	 * Insert new user
	 */
	public function insert_user($data)
	{
		try {
			// Validate required fields
			if (empty($data['email']) || empty($data['password']) || empty($data['nama'])) {
				log_message('error', 'Missing required user fields');
				return false;
			}

			// Check if email already exists
			if ($this->get_user_by_email($data['email'])) {
				log_message('warning', 'Email already exists: ' . $data['email']);
				return false;
			}

			// Add timestamps
			if (!isset($data['date_created'])) {
				$data['date_created'] = date('Y-m-d H:i:s');
			}
			if (!isset($data['created_at'])) {
				$data['created_at'] = date('Y-m-d H:i:s');
			}

			return $this->db->insert($this->table, $data);
		} catch (Exception $e) {
			log_message('error', 'Error in insert_user: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Update user
	 */
	public function update_user($id, $data)
	{
		try {
			// Add update timestamp
			if (!isset($data['updated_at'])) {
				$data['updated_at'] = date('Y-m-d H:i:s');
			}

			$this->db->where('id', $id);
			return $this->db->update($this->table, $data);
		} catch (Exception $e) {
			log_message('error', 'Error in update_user: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Get all users with pagination
	 */
	public function get_users($limit = 10, $offset = 0)
	{
		try {
			$query = $this->db->limit($limit, $offset)
				->order_by('date_created', 'DESC')
				->get($this->table);

			return $query->result_array();
		} catch (Exception $e) {
			log_message('error', 'Error in get_users: ' . $e->getMessage());
			return array();
		}
	}

	/**
	 * Count total users
	 */
	public function count_users()
	{
		try {
			return $this->db->count_all($this->table);
		} catch (Exception $e) {
			log_message('error', 'Error in count_users: ' . $e->getMessage());
			return 0;
		}
	}

	/**
	 * Get users by role
	 */
	public function get_users_by_role($role_id)
	{
		try {
			$query = $this->db->where('role_id', $role_id)
				->order_by('nama', 'ASC')
				->get($this->table);

			return $query->result_array();
		} catch (Exception $e) {
			log_message('error', 'Error in get_users_by_role: ' . $e->getMessage());
			return array();
		}
	}

	/**
	 * Delete user
	 */
	public function delete_user($id)
	{
		try {
			$this->db->where('id', $id);
			return $this->db->delete($this->table);
		} catch (Exception $e) {
			log_message('error', 'Error in delete_user: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Update user password
	 */
	public function update_password($email, $password)
	{
		try {
			$data = array(
				'password' => password_hash($password, PASSWORD_DEFAULT),
				'updated_at' => date('Y-m-d H:i:s')
			);
			$this->db->where('email', $email);
			return $this->db->update($this->table, $data);
		} catch (Exception $e) {
			log_message('error', 'Error in update_password: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Activate user account
	 */
	public function activate_user($id)
	{
		try {
			$data = array('is_active' => 1);
			$this->db->where('id', $id);
			return $this->db->update($this->table, $data);
		} catch (Exception $e) {
			log_message('error', 'Error in activate_user: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Check if email exists
	 */
	public function email_exists($email)
	{
		$query = $this->db->where('email', $email)
			->limit(1)
			->get($this->table);

		return $query->num_rows() > 0;
	}
}
