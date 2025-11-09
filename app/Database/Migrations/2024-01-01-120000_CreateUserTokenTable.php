<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: Create User Token Table (for password reset)
 * Database: PostgreSQL
 */
class CreateUserTokenTable extends Migration
{
    public function up()
    {
        // Create user_token table
        $this->forge->addField([
            'id' => [
                'type' => 'SERIAL',
                'constraint' => 11,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => '128',
            ],
            'token' => [
                'type' => 'TEXT',
            ],
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'default' => 'password_reset',
            ],
            'date_created' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'expires_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('email');
        $this->forge->addKey('type');
        $this->forge->createTable('user_token', true);

        // Add foreign key manually for PostgreSQL
        $this->db->query('ALTER TABLE user_token ADD CONSTRAINT fk_user_token_email FOREIGN KEY (email) REFERENCES "user"(email) ON DELETE CASCADE');
    }

    public function down()
    {
        $this->forge->dropTable('user_token', true);
    }
}
