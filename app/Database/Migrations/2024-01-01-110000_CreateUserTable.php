<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: Create User Table
 * Database: PostgreSQL
 */
class CreateUserTable extends Migration
{
    public function up()
    {
        // Create user table
        $this->forge->addField([
            'id' => [
                'type' => 'SERIAL',
                'constraint' => 11,
            ],
            'nama' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => '128',
                'unique' => true,
            ],
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'gambar' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'default' => 'default.jpg',
            ],
            'role_id' => [
                'type' => 'INT',
                'default' => 2,
            ],
            'is_active' => [
                'type' => 'SMALLINT',
                'default' => 1,
            ],
            'date_created' => [
                'type' => 'TIMESTAMP',
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
            'created_by' => [
                'type' => 'INT',
                'null' => true,
            ],
            'updated_by' => [
                'type' => 'INT',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('email');
        $this->forge->addKey('role_id');
        $this->forge->addKey('is_active');
        $this->forge->addForeignKey('role_id', 'user_role', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('user', true);
    }

    public function down()
    {
        $this->forge->dropTable('user', true);
    }
}
