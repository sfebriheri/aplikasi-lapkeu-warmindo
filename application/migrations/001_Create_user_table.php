<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration: Create User Table
 * Database: PostgreSQL
 */
class Migration_Create_user_table extends CI_Migration
{
	public function up()
	{
		// Drop existing table if it exists
		$this->db->query("DROP TABLE IF EXISTS \"user\" CASCADE");

		// Create user table
		$sql = "
			CREATE TABLE \"user\" (
				id SERIAL PRIMARY KEY,
				nama VARCHAR(100) NOT NULL,
				email VARCHAR(128) NOT NULL UNIQUE,
				password VARCHAR(255) NOT NULL,
				gambar VARCHAR(255) DEFAULT 'default.jpg',
				role_id INTEGER NOT NULL DEFAULT 2,
				is_active SMALLINT DEFAULT 1,
				date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				created_by INTEGER,
				updated_by INTEGER
			)
		";
		$this->db->query($sql);

		// Create indexes
		$this->db->query("CREATE INDEX idx_user_email ON \"user\" (email)");
		$this->db->query("CREATE INDEX idx_user_role_id ON \"user\" (role_id)");
		$this->db->query("CREATE INDEX idx_user_is_active ON \"user\" (is_active)");

		echo "✓ Table 'user' created successfully\n";
	}

	public function down()
	{
		$this->db->query("DROP TABLE IF EXISTS \"user\" CASCADE");
		echo "✓ Table 'user' dropped successfully\n";
	}
}
