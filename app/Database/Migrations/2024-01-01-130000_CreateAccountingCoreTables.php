<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: Create Accounting Core Tables
 * Database: PostgreSQL
 * Includes: Chart of Accounts, Journal Entries, Journal Details, Account Balance
 */
class CreateAccountingCoreTables extends Migration
{
    public function up()
    {
        // 1. Chart of Accounts Table
        $this->forge->addField([
            'id' => [
                'type' => 'SERIAL',
                'constraint' => 11,
            ],
            'code' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
                'unique' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
            ],
            'account_type' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
            ],
            'category' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
            ],
            'sub_category' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
            ],
            'normal_balance' => [
                'type' => 'VARCHAR',
                'constraint' => '10',
                'null' => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_active' => [
                'type' => 'BOOLEAN',
                'default' => true,
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
        $this->forge->addKey('code');
        $this->forge->addKey('account_type');
        $this->forge->addForeignKey('created_by', 'user', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('updated_by', 'user', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('chart_of_accounts', true);

        // 2. Journal Entries Table
        $this->forge->addField([
            'id' => [
                'type' => 'SERIAL',
                'constraint' => 11,
            ],
            'journal_number' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'unique' => true,
            ],
            'entry_date' => [
                'type' => 'DATE',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'total_debit' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'total_credit' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
                'default' => 'draft',
            ],
            'created_by' => [
                'type' => 'INT',
            ],
            'approved_by' => [
                'type' => 'INT',
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
        $this->forge->addKey('entry_date');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('created_by', 'user', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('approved_by', 'user', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('journal_entries', true);

        // 3. Journal Details Table
        $this->forge->addField([
            'id' => [
                'type' => 'SERIAL',
                'constraint' => 11,
            ],
            'journal_entry_id' => [
                'type' => 'INT',
            ],
            'account_id' => [
                'type' => 'INT',
            ],
            'debit_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'credit_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
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
        $this->forge->addKey('journal_entry_id');
        $this->forge->addKey('account_id');
        $this->forge->addForeignKey('journal_entry_id', 'journal_entries', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('account_id', 'chart_of_accounts', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('journal_details', true);

        // 4. Account Balance Table
        $this->forge->addField([
            'id' => [
                'type' => 'SERIAL',
                'constraint' => 11,
            ],
            'account_id' => [
                'type' => 'INT',
                'unique' => true,
            ],
            'current_balance' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'debit_balance' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'credit_balance' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'period' => [
                'type' => 'VARCHAR',
                'constraint' => '7',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('period');
        $this->forge->addForeignKey('account_id', 'chart_of_accounts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('account_balance', true);
    }

    public function down()
    {
        $this->forge->dropTable('account_balance', true);
        $this->forge->dropTable('journal_details', true);
        $this->forge->dropTable('journal_entries', true);
        $this->forge->dropTable('chart_of_accounts', true);
    }
}
