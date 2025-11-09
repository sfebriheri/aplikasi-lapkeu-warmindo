<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: Create User Role Table
 * Database: PostgreSQL
 */
class CreateUserRoleTable extends Migration
{
    public function up()
    {
        // Create user_role table
        $this->forge->addField([
            'id' => [
                'type' => 'SERIAL',
                'constraint' => 11,
            ],
            'role' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'unique' => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('user_role', true);

        // Insert default roles
        $data = [
            [
                'id' => 1,
                'role' => 'Admin',
                'description' => 'Administrator with full access',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 2,
                'role' => 'User',
                'description' => 'Regular user with limited access',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 3,
                'role' => 'Auditor',
                'description' => 'User who can view reports only',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 4,
                'role' => 'Accountant',
                'description' => 'User who can manage financial transactions',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('user_role')->insertBatch($data);
    }

    public function down()
    {
        $this->forge->dropTable('user_role', true);
    }
}
