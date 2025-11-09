<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration: Create User Token Table (for password reset)
 * Database: PostgreSQL
 */
class Migration_Create_user_token_table extends CI_Migration
{
	public function up()
	{
		// Drop existing table if it exists
		$this->db->query("DROP TABLE IF EXISTS user_token CASCADE");

		// Create user_token table
		$sql = "
			CREATE TABLE user_token (
				id SERIAL PRIMARY KEY,
				email VARCHAR(128) NOT NULL,
				token TEXT NOT NULL,
				type VARCHAR(50) DEFAULT 'password_reset',
				date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				expires_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP + INTERVAL '1 hour',
				FOREIGN KEY (email) REFERENCES \"user\"(email) ON DELETE CASCADE
			)
		";
		$this->db->query($sql);

		// Create indexes
		$this->db->query("CREATE INDEX idx_user_token_email ON user_token (email)");
		$this->db->query("CREATE INDEX idx_user_token_token ON user_token (token)");
		$this->db->query("CREATE INDEX idx_user_token_type ON user_token (type)");

		echo "✓ Table 'user_token' created successfully\n";
	}

	public function down()
	{
		$this->db->query("DROP TABLE IF EXISTS user_token CASCADE");
		echo "✓ Table 'user_token' dropped successfully\n";
	}
}
