<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Accounting Model
 * Handles all accounting-related database operations
 */
class Accounting_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * Get chart of accounts
	 */
	public function get_chart_of_accounts($account_type = null)
	{
		try {
			$this->db->where('is_active', 1);
			if ($account_type) {
				$this->db->where('account_type', $account_type);
			}
			$query = $this->db->order_by('code', 'ASC')->get('chart_of_accounts');
			return $query->result_array();
		} catch (Exception $e) {
			log_message('error', 'Error in get_chart_of_accounts: ' . $e->getMessage());
			return array();
		}
	}

	/**
	 * Get account by code
	 */
	public function get_account_by_code($code)
	{
		try {
			$query = $this->db->where('code', $code)
				->where('is_active', 1)
				->limit(1)
				->get('chart_of_accounts');

			if ($query->num_rows() > 0) {
				return $query->row_array();
			}
			return null;
		} catch (Exception $e) {
			log_message('error', 'Error in get_account_by_code: ' . $e->getMessage());
			return null;
		}
	}

	/**
	 * Create journal entry
	 */
	public function create_journal_entry($entry_data, $details)
	{
		try {
			$this->db->trans_start();

			// Insert journal entry
			$this->db->insert('journal_entries', $entry_data);
			$entry_id = $this->db->insert_id();

			// Insert journal details
			foreach ($details as $detail) {
				$detail['journal_entry_id'] = $entry_id;
				$this->db->insert('journal_details', $detail);
			}

			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE) {
				log_message('error', 'Transaction failed in create_journal_entry');
				return false;
			}

			return $entry_id;
		} catch (Exception $e) {
			log_message('error', 'Error in create_journal_entry: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Get journal entry
	 */
	public function get_journal_entry($id)
	{
		try {
			$entry = $this->db->where('id', $id)
				->limit(1)
				->get('journal_entries')
				->row_array();

			if (!$entry) {
				return null;
			}

			$entry['details'] = $this->db->where('journal_entry_id', $id)
				->get('journal_details')
				->result_array();

			return $entry;
		} catch (Exception $e) {
			log_message('error', 'Error in get_journal_entry: ' . $e->getMessage());
			return null;
		}
	}

	/**
	 * Get account balance
	 */
	public function get_account_balance($account_id, $period = null)
	{
		try {
			$this->db->where('account_id', $account_id);
			if ($period) {
				$this->db->where('period', $period);
			}
			$query = $this->db->limit(1)->get('account_balance');

			if ($query->num_rows() > 0) {
				return $query->row_array();
			}
			return array(
				'account_id' => $account_id,
				'current_balance' => 0,
				'debit_balance' => 0,
				'credit_balance' => 0
			);
		} catch (Exception $e) {
			log_message('error', 'Error in get_account_balance: ' . $e->getMessage());
			return array();
		}
	}

	/**
	 * Calculate account balance
	 */
	public function calculate_account_balance($account_id)
	{
		try {
			$this->db->select('
				SUM(CASE WHEN debit_amount > 0 THEN debit_amount ELSE 0 END) as total_debit,
				SUM(CASE WHEN credit_amount > 0 THEN credit_amount ELSE 0 END) as total_credit
			');
			$this->db->where('account_id', $account_id);
			$this->db->join('journal_entries', 'journal_entries.id = journal_details.journal_entry_id');
			$this->db->where('journal_entries.status', 'approved');

			$query = $this->db->get('journal_details');
			$result = $query->row_array();

			$total_debit = $result['total_debit'] ?? 0;
			$total_credit = $result['total_credit'] ?? 0;
			$current_balance = $total_debit - $total_credit;

			return array(
				'total_debit' => $total_debit,
				'total_credit' => $total_credit,
				'current_balance' => $current_balance
			);
		} catch (Exception $e) {
			log_message('error', 'Error in calculate_account_balance: ' . $e->getMessage());
			return array(
				'total_debit' => 0,
				'total_credit' => 0,
				'current_balance' => 0
			);
		}
	}

	/**
	 * Get trial balance
	 */
	public function get_trial_balance($start_date = null, $end_date = null)
	{
		try {
			$this->db->select('
				coa.id,
				coa.code,
				coa.name,
				coa.account_type,
				SUM(CASE WHEN jd.debit_amount > 0 THEN jd.debit_amount ELSE 0 END) as total_debit,
				SUM(CASE WHEN jd.credit_amount > 0 THEN jd.credit_amount ELSE 0 END) as total_credit
			');
			$this->db->from('chart_of_accounts coa');
			$this->db->join('journal_details jd', 'jd.account_id = coa.id', 'left');
			$this->db->join('journal_entries je', 'je.id = jd.journal_entry_id', 'left');
			$this->db->where('coa.is_active', 1);
			$this->db->where('je.status', 'approved');

			if ($start_date) {
				$this->db->where('je.entry_date >=', $start_date);
			}
			if ($end_date) {
				$this->db->where('je.entry_date <=', $end_date);
			}

			$this->db->group_by('coa.id, coa.code, coa.name, coa.account_type');
			$this->db->order_by('coa.code', 'ASC');

			return $this->db->get()->result_array();
		} catch (Exception $e) {
			log_message('error', 'Error in get_trial_balance: ' . $e->getMessage());
			return array();
		}
	}

	/**
	 * Approve journal entry
	 */
	public function approve_journal_entry($id, $approved_by)
	{
		try {
			$data = array(
				'status' => 'approved',
				'approved_by' => $approved_by,
				'updated_at' => date('Y-m-d H:i:s')
			);
			$this->db->where('id', $id);
			return $this->db->update('journal_entries', $data);
		} catch (Exception $e) {
			log_message('error', 'Error in approve_journal_entry: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Get accounting summary
	 */
	public function get_accounting_summary()
	{
		try {
			$summary = array();

			// Total assets
			$this->db->select_sum('current_balance', 'total');
			$this->db->where('account_type', 'Asset');
			$summary['total_assets'] = $this->db->get('account_balance')->row_array()['total'] ?? 0;

			// Total liabilities
			$this->db->select_sum('current_balance', 'total');
			$this->db->where('account_type', 'Liability');
			$summary['total_liabilities'] = $this->db->get('account_balance')->row_array()['total'] ?? 0;

			// Total equity
			$this->db->select_sum('current_balance', 'total');
			$this->db->where('account_type', 'Equity');
			$summary['total_equity'] = $this->db->get('account_balance')->row_array()['total'] ?? 0;

			return $summary;
		} catch (Exception $e) {
			log_message('error', 'Error in get_accounting_summary: ' . $e->getMessage());
			return array();
		}
	}
}
