<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration: Create User Role Table
 * Database: PostgreSQL
 */
class Migration_Create_user_role_table extends CI_Migration
{
	public function up()
	{
		// Drop existing table if it exists
		$this->db->query("DROP TABLE IF EXISTS user_role CASCADE");

		// Create user_role table
		$sql = "
			CREATE TABLE user_role (
				id SERIAL PRIMARY KEY,
				role VARCHAR(50) NOT NULL UNIQUE,
				description TEXT,
				created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
			)
		";
		$this->db->query($sql);

		// Insert default roles
		$roles = array(
			array(1, 'Admin', 'Administrator with full access'),
			array(2, 'User', 'Regular user with limited access'),
			array(3, 'Auditor', 'User who can view reports only'),
			array(4, 'Accountant', 'User who can manage financial transactions')
		);

		foreach ($roles as $role) {
			$this->db->query("INSERT INTO user_role (id, role, description) VALUES ({$role[0]}, '{$role[1]}', '{$role[2]}')");
		}

		// Add foreign key to user table
		$this->db->query("
			ALTER TABLE \"user\"
			ADD CONSTRAINT fk_user_role_id
			FOREIGN KEY (role_id) REFERENCES user_role(id)
			ON DELETE RESTRICT ON UPDATE CASCADE
		");

		echo "✓ Table 'user_role' created successfully\n";
	}

	public function down()
	{
		// Drop foreign key first
		$this->db->query("ALTER TABLE \"user\" DROP CONSTRAINT IF EXISTS fk_user_role_id");
		$this->db->query("DROP TABLE IF EXISTS user_role CASCADE");
		echo "✓ Table 'user_role' dropped successfully\n";
	}
}
