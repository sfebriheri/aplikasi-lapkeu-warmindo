<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration: Create Accounting Core Tables
 * Database: PostgreSQL
 * Includes: Chart of Accounts, Transactions, Journals
 */
class Migration_Create_accounting_core_tables extends CI_Migration
{
	public function up()
	{
		// 1. Chart of Accounts Table
		$this->db->query("DROP TABLE IF EXISTS chart_of_accounts CASCADE");
		$coa_sql = "
			CREATE TABLE chart_of_accounts (
				id SERIAL PRIMARY KEY,
				code VARCHAR(20) NOT NULL UNIQUE,
				name VARCHAR(100) NOT NULL,
				account_type VARCHAR(50) NOT NULL,
				category VARCHAR(50),
				sub_category VARCHAR(50),
				normal_balance VARCHAR(10),
				description TEXT,
				is_active BOOLEAN DEFAULT true,
				created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				created_by INTEGER REFERENCES \"user\"(id),
				updated_by INTEGER REFERENCES \"user\"(id)
			)
		";
		$this->db->query($coa_sql);
		$this->db->query("CREATE INDEX idx_coa_code ON chart_of_accounts (code)");
		$this->db->query("CREATE INDEX idx_coa_type ON chart_of_accounts (account_type)");

		// 2. Journal Entries Table
		$this->db->query("DROP TABLE IF EXISTS journal_entries CASCADE");
		$journal_sql = "
			CREATE TABLE journal_entries (
				id SERIAL PRIMARY KEY,
				journal_number VARCHAR(50) NOT NULL UNIQUE,
				entry_date DATE NOT NULL,
				description TEXT,
				total_debit NUMERIC(15, 2) DEFAULT 0,
				total_credit NUMERIC(15, 2) DEFAULT 0,
				status VARCHAR(20) DEFAULT 'draft',
				created_by INTEGER NOT NULL REFERENCES \"user\"(id),
				approved_by INTEGER REFERENCES \"user\"(id),
				created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
			)
		";
		$this->db->query($journal_sql);
		$this->db->query("CREATE INDEX idx_journal_date ON journal_entries (entry_date)");
		$this->db->query("CREATE INDEX idx_journal_status ON journal_entries (status)");

		// 3. Journal Details Table
		$this->db->query("DROP TABLE IF EXISTS journal_details CASCADE");
		$journal_detail_sql = "
			CREATE TABLE journal_details (
				id SERIAL PRIMARY KEY,
				journal_entry_id INTEGER NOT NULL REFERENCES journal_entries(id) ON DELETE CASCADE,
				account_id INTEGER NOT NULL REFERENCES chart_of_accounts(id),
				debit_amount NUMERIC(15, 2) DEFAULT 0,
				credit_amount NUMERIC(15, 2) DEFAULT 0,
				description TEXT,
				created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
			)
		";
		$this->db->query($journal_detail_sql);
		$this->db->query("CREATE INDEX idx_journal_detail_entry ON journal_details (journal_entry_id)");
		$this->db->query("CREATE INDEX idx_journal_detail_account ON journal_details (account_id)");

		// 4. Account Balance Table (for performance)
		$this->db->query("DROP TABLE IF EXISTS account_balance CASCADE");
		$balance_sql = "
			CREATE TABLE account_balance (
				id SERIAL PRIMARY KEY,
				account_id INTEGER NOT NULL UNIQUE REFERENCES chart_of_accounts(id) ON DELETE CASCADE,
				current_balance NUMERIC(15, 2) DEFAULT 0,
				debit_balance NUMERIC(15, 2) DEFAULT 0,
				credit_balance NUMERIC(15, 2) DEFAULT 0,
				period VARCHAR(7),
				updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
			)
		";
		$this->db->query($balance_sql);
		$this->db->query("CREATE INDEX idx_balance_period ON account_balance (period)");

		echo "✓ Accounting core tables created successfully\n";
	}

	public function down()
	{
		$this->db->query("DROP TABLE IF EXISTS account_balance CASCADE");
		$this->db->query("DROP TABLE IF EXISTS journal_details CASCADE");
		$this->db->query("DROP TABLE IF EXISTS journal_entries CASCADE");
		$this->db->query("DROP TABLE IF EXISTS chart_of_accounts CASCADE");
		echo "✓ Accounting core tables dropped successfully\n";
	}
}
