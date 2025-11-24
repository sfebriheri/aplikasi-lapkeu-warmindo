<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Libraries\CurrencyHandler;

/**
 * Accounting Model
 * Handles all accounting-related database operations
 */
class AccountingModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    /**
     * Get chart of accounts
     */
    public function getChartOfAccounts(?string $accountType = null): array
    {
        $builder = $this->db->table('chart_of_accounts');
        $builder->where('is_active', 1);

        if ($accountType) {
            $builder->where('account_type', $accountType);
        }

        return $builder->orderBy('code', 'ASC')->get()->getResultArray();
    }

    /**
     * Get account by code
     */
    public function getAccountByCode(string $code): ?array
    {
        $result = $this->db->table('chart_of_accounts')
            ->where('code', $code)
            ->where('is_active', 1)
            ->get()
            ->getRowArray();

        return $result ?: null;
    }

    /**
     * Get account by ID
     */
    public function getAccountById(int $id): ?array
    {
        $result = $this->db->table('chart_of_accounts')
            ->where('id', $id)
            ->where('is_active', 1)
            ->get()
            ->getRowArray();

        return $result ?: null;
    }

    /**
     * Create journal entry with details
     */
    public function createJournalEntry(array $entryData, array $details): int|bool
    {
        $this->db->transStart();

        try {
            // Insert journal entry
            $this->db->table('journal_entries')->insert($entryData);
            $entryId = $this->db->insertID();

            // Insert journal details
            foreach ($details as $detail) {
                $detail['journal_entry_id'] = $entryId;
                $detail['created_at'] = date('Y-m-d H:i:s');
                $this->db->table('journal_details')->insert($detail);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                log_message('error', 'Transaction failed in createJournalEntry');
                return false;
            }

            return $entryId;
        } catch (\Exception $e) {
            log_message('error', 'Error in createJournalEntry: ' . $e->getMessage());
            $this->db->transRollback();
            return false;
        }
    }

    /**
     * Get journal entry with details
     */
    public function getJournalEntry(int $id): ?array
    {
        $entry = $this->db->table('journal_entries')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if (!$entry) {
            return null;
        }

        $entry['details'] = $this->db->table('journal_details')
            ->select('journal_details.*, chart_of_accounts.code, chart_of_accounts.name as account_name')
            ->join('chart_of_accounts', 'chart_of_accounts.id = journal_details.account_id', 'left')
            ->where('journal_entry_id', $id)
            ->get()
            ->getResultArray();

        return $entry;
    }

    /**
     * Get journal entries with filters
     */
    public function getJournalEntries(?string $status = null, ?string $startDate = null, ?string $endDate = null, int $limit = 50, int $offset = 0): array
    {
        $builder = $this->db->table('journal_entries');
        $builder->select('journal_entries.*, user.nama as created_by_name');
        $builder->join('user', 'user.id = journal_entries.created_by', 'left');

        if ($status) {
            $builder->where('journal_entries.status', $status);
        }

        if ($startDate) {
            $builder->where('journal_entries.entry_date >=', $startDate);
        }

        if ($endDate) {
            $builder->where('journal_entries.entry_date <=', $endDate);
        }

        return $builder->orderBy('journal_entries.entry_date', 'DESC')
            ->orderBy('journal_entries.id', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();
    }

    /**
     * Get account balance
     */
    public function getAccountBalance(int $accountId, ?string $period = null): array
    {
        $builder = $this->db->table('account_balance');
        $builder->where('account_id', $accountId);

        if ($period) {
            $builder->where('period', $period);
        }

        $result = $builder->get()->getRowArray();

        if ($result) {
            return $result;
        }

        return [
            'account_id' => $accountId,
            'current_balance' => 0,
            'debit_balance' => 0,
            'credit_balance' => 0
        ];
    }

    /**
     * Calculate account balance from transactions with decimal precision
     */
    public function calculateAccountBalance(int $accountId): array
    {
        $query = $this->db->query("
            SELECT
                SUM(CASE WHEN jd.debit_amount > 0 THEN jd.debit_amount ELSE 0 END) as total_debit,
                SUM(CASE WHEN jd.credit_amount > 0 THEN jd.credit_amount ELSE 0 END) as total_credit
            FROM journal_details jd
            JOIN journal_entries je ON je.id = jd.journal_entry_id
            WHERE jd.account_id = ? AND je.status = 'approved'
        ", [$accountId]);

        $result = $query->getRowArray();

        // Use CurrencyHandler for precise decimal arithmetic
        $totalDebit = CurrencyHandler::create($result['total_debit'] ?? 0);
        $totalCredit = CurrencyHandler::create($result['total_credit'] ?? 0);
        $currentBalance = CurrencyHandler::subtract($totalDebit, $totalCredit);

        return [
            'total_debit' => CurrencyHandler::toNumericString($totalDebit),
            'total_credit' => CurrencyHandler::toNumericString($totalCredit),
            'current_balance' => CurrencyHandler::toNumericString($currentBalance)
        ];
    }

    /**
     * Get trial balance
     */
    public function getTrialBalance(?string $startDate = null, ?string $endDate = null): array
    {
        $builder = $this->db->table('chart_of_accounts coa');
        $builder->select('
            coa.id,
            coa.code,
            coa.name,
            coa.account_type,
            coa.normal_balance,
            COALESCE(SUM(CASE WHEN jd.debit_amount > 0 THEN jd.debit_amount ELSE 0 END), 0) as total_debit,
            COALESCE(SUM(CASE WHEN jd.credit_amount > 0 THEN jd.credit_amount ELSE 0 END), 0) as total_credit
        ');
        $builder->join('journal_details jd', 'jd.account_id = coa.id', 'left');
        $builder->join('journal_entries je', 'je.id = jd.journal_entry_id AND je.status = \'approved\'', 'left');
        $builder->where('coa.is_active', 1);

        if ($startDate) {
            $builder->where('je.entry_date >=', $startDate);
        }

        if ($endDate) {
            $builder->where('je.entry_date <=', $endDate);
        }

        $builder->groupBy('coa.id, coa.code, coa.name, coa.account_type, coa.normal_balance');
        $builder->orderBy('coa.code', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * Get general ledger for an account
     */
    public function getGeneralLedger(int $accountId, ?string $startDate = null, ?string $endDate = null): array
    {
        $builder = $this->db->table('journal_details jd');
        $builder->select('
            je.entry_date,
            je.journal_number,
            je.description as journal_description,
            jd.description as detail_description,
            jd.debit_amount,
            jd.credit_amount,
            je.status
        ');
        $builder->join('journal_entries je', 'je.id = jd.journal_entry_id');
        $builder->where('jd.account_id', $accountId);
        $builder->where('je.status', 'approved');

        if ($startDate) {
            $builder->where('je.entry_date >=', $startDate);
        }

        if ($endDate) {
            $builder->where('je.entry_date <=', $endDate);
        }

        $builder->orderBy('je.entry_date', 'ASC');
        $builder->orderBy('je.id', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * Approve journal entry
     */
    public function approveJournalEntry(int $id, int $approvedBy): bool
    {
        return $this->db->table('journal_entries')
            ->where('id', $id)
            ->update([
                'status' => 'approved',
                'approved_by' => $approvedBy,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }

    /**
     * Update account balance with decimal precision
     *
     * @param int $accountId Account ID
     * @param int|float|string $currentBalance Current balance amount
     * @param int|float|string $debitBalance Debit balance amount
     * @param int|float|string $creditBalance Credit balance amount
     * @param string|null $period Period (YYYY-MM format)
     * @return bool
     */
    public function updateAccountBalance(int $accountId, $currentBalance, $debitBalance, $creditBalance, ?string $period = null): bool
    {
        // Convert to numeric strings using CurrencyHandler to ensure precision
        $data = [
            'account_id' => $accountId,
            'current_balance' => CurrencyHandler::toNumericString($currentBalance),
            'debit_balance' => CurrencyHandler::toNumericString($debitBalance),
            'credit_balance' => CurrencyHandler::toNumericString($creditBalance),
            'period' => $period ?? date('Y-m'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Check if exists
        $existing = $this->getAccountBalance($accountId, $period);

        if (isset($existing['id'])) {
            return $this->db->table('account_balance')
                ->where('id', $existing['id'])
                ->update($data);
        } else {
            return $this->db->table('account_balance')->insert($data);
        }
    }

    /**
     * Get accounting summary (assets, liabilities, equity) with decimal precision
     */
    public function getAccountingSummary(): array
    {
        $summary = [];

        // Get total assets
        $assets = $this->db->table('account_balance ab')
            ->select('COALESCE(SUM(ab.current_balance), 0) as total')
            ->join('chart_of_accounts coa', 'coa.id = ab.account_id')
            ->where('coa.account_type', 'Asset')
            ->get()
            ->getRowArray();
        $summary['total_assets'] = CurrencyHandler::toNumericString($assets['total'] ?? 0);

        // Get total liabilities
        $liabilities = $this->db->table('account_balance ab')
            ->select('COALESCE(SUM(ab.current_balance), 0) as total')
            ->join('chart_of_accounts coa', 'coa.id = ab.account_id')
            ->where('coa.account_type', 'Liability')
            ->get()
            ->getRowArray();
        $summary['total_liabilities'] = CurrencyHandler::toNumericString($liabilities['total'] ?? 0);

        // Get total equity
        $equity = $this->db->table('account_balance ab')
            ->select('COALESCE(SUM(ab.current_balance), 0) as total')
            ->join('chart_of_accounts coa', 'coa.id = ab.account_id')
            ->where('coa.account_type', 'Equity')
            ->get()
            ->getRowArray();
        $summary['total_equity'] = CurrencyHandler::toNumericString($equity['total'] ?? 0);

        // Get total revenue
        $revenue = $this->db->table('account_balance ab')
            ->select('COALESCE(SUM(ab.current_balance), 0) as total')
            ->join('chart_of_accounts coa', 'coa.id = ab.account_id')
            ->where('coa.account_type', 'Revenue')
            ->get()
            ->getRowArray();
        $summary['total_revenue'] = CurrencyHandler::toNumericString($revenue['total'] ?? 0);

        // Get total expenses
        $expenses = $this->db->table('account_balance ab')
            ->select('COALESCE(SUM(ab.current_balance), 0) as total')
            ->join('chart_of_accounts coa', 'coa.id = ab.account_id')
            ->where('coa.account_type', 'Expense')
            ->get()
            ->getRowArray();
        $summary['total_expenses'] = CurrencyHandler::toNumericString($expenses['total'] ?? 0);

        // Calculate net income with precision
        $revenue = CurrencyHandler::create($summary['total_revenue']);
        $expenses = CurrencyHandler::create($summary['total_expenses']);
        $netIncome = CurrencyHandler::subtract($revenue, $expenses);
        $summary['net_income'] = CurrencyHandler::toNumericString($netIncome);

        return $summary;
    }

    /**
     * Delete journal entry
     */
    public function deleteJournalEntry(int $id): bool
    {
        $this->db->transStart();

        try {
            // Delete details first (cascade should handle this, but being explicit)
            $this->db->table('journal_details')->where('journal_entry_id', $id)->delete();

            // Delete journal entry
            $this->db->table('journal_entries')->where('id', $id)->delete();

            $this->db->transComplete();

            return $this->db->transStatus();
        } catch (\Exception $e) {
            log_message('error', 'Error in deleteJournalEntry: ' . $e->getMessage());
            $this->db->transRollback();
            return false;
        }
    }
}
