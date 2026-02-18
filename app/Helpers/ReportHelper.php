<?php

namespace App\Helpers;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\FinancialYear;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportHelper
{
    /**
     * Get Trial Balance Report
     *
     * @param string|null $date
     * @return array
     */
    public static function getTrialBalance(?string $date = null): array
    {
        $date = $date ?? now()->toDateString();
        $accounts = Account::with('accountType')->active()->get();
        
        $trialBalance = [];
        $totalDebits = 0;
        $totalCredits = 0;

        foreach ($accounts as $account) {
            $balance = $account->getCurrentBalance($date);
            
            // Determine debit/credit based on account type
            $isDebitNormal = $account->accountType->isDebitNormal();
            $debitAmount = $isDebitNormal && $balance >= 0 ? abs($balance) : 0;
            $creditAmount = !$isDebitNormal && $balance >= 0 ? abs($balance) : 0;
            
            // Handle negative balances
            if ($balance < 0) {
                if ($isDebitNormal) {
                    $creditAmount = abs($balance);
                } else {
                    $debitAmount = abs($balance);
                }
            }

            if ($debitAmount > 0 || $creditAmount > 0) {
                $trialBalance[] = [
                    'account_code' => $account->code,
                    'account_name' => $account->name,
                    'account_type' => $account->accountType->name,
                    'debit' => $debitAmount,
                    'credit' => $creditAmount,
                ];

                $totalDebits += $debitAmount;
                $totalCredits += $creditAmount;
            }
        }

        return [
            'date' => $date,
            'accounts' => $trialBalance,
            'total_debits' => $totalDebits,
            'total_credits' => $totalCredits,
            'is_balanced' => abs($totalDebits - $totalCredits) < 0.01,
        ];
    }

    /**
     * Get Balance Sheet
     *
     * @param string|null $date
     * @return array
     */
    public static function getBalanceSheet(?string $date = null): array
    {
        $date = $date ?? now()->toDateString();
        
        $assets = self::getAccountsByType('ASSET', $date);
        $liabilities = self::getAccountsByType('LIABILITY', $date);
        $equity = self::getAccountsByType('EQUITY', $date);

        $totalAssets = array_sum(array_column($assets, 'balance'));
        $totalLiabilities = array_sum(array_column($liabilities, 'balance'));
        $totalEquity = array_sum(array_column($equity, 'balance'));

        // Calculate retained earnings (if not explicitly tracked)
        $retainedEarnings = self::calculateRetainedEarnings($date);
        $totalEquity += $retainedEarnings;

        return [
            'date' => $date,
            'assets' => [
                'accounts' => $assets,
                'total' => $totalAssets,
            ],
            'liabilities' => [
                'accounts' => $liabilities,
                'total' => $totalLiabilities,
            ],
            'equity' => [
                'accounts' => $equity,
                'retained_earnings' => $retainedEarnings,
                'total' => $totalEquity,
            ],
            'total_liabilities_and_equity' => $totalLiabilities + $totalEquity,
            'is_balanced' => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01,
        ];
    }

    /**
     * Get Income Statement (Profit & Loss)
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @param int|FinancialYear|null $financialYear Financial year ID, FinancialYear instance, or null to auto-detect
     * @return array
     */
    public static function getIncomeStatement(?string $startDate = null, ?string $endDate = null, $financialYear = null): array
    {
        // If financial year is provided, use its dates
        if ($financialYear && config('accounting.enable_financial_year')) {
            $fy = self::resolveFinancialYear($financialYear);
            if ($fy) {
                $startDate = $startDate ?? $fy->start_date->toDateString();
                $endDate = $endDate ?? $fy->end_date->toDateString();
            }
        } elseif (config('accounting.enable_financial_year')) {
            // Try to get financial year for current date
            $financialYearService = app(\App\Services\FinancialYearService::class);
            $currentFY = $financialYearService->getCurrentFinancialYear();
            if ($currentFY && !$startDate && !$endDate) {
                $startDate = $currentFY->start_date->toDateString();
                $endDate = $currentFY->end_date->toDateString();
            } else {
                $startDate = $startDate ?? Carbon::now()->startOfYear()->toDateString();
                $endDate = $endDate ?? now()->toDateString();
            }
        } else {
            // Financial year disabled, use default date logic
            $startDate = $startDate ?? Carbon::now()->startOfYear()->toDateString();
            $endDate = $endDate ?? now()->toDateString();
        }

        $revenues = self::getAccountsByType('REVENUE', $endDate, $startDate);
        $expenses = self::getAccountsByType('EXPENSE', $endDate, $startDate);

        $totalRevenue = array_sum(array_column($revenues, 'balance'));
        $totalExpenses = array_sum(array_column($expenses, 'balance'));
        $netIncome = $totalRevenue - $totalExpenses;

        $fy = null;
        if (config('accounting.enable_financial_year')) {
            $fy = self::resolveFinancialYear($financialYear) ?? 
                  app(\App\Services\FinancialYearService::class)->getFinancialYearForDate($startDate);
        }

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'financial_year' => $fy ? [
                'id' => $fy->id,
                'name' => $fy->name,
                'code' => $fy->code,
            ] : null,
            'revenues' => [
                'accounts' => $revenues,
                'total' => $totalRevenue,
            ],
            'expenses' => [
                'accounts' => $expenses,
                'total' => $totalExpenses,
            ],
            'net_income' => $netIncome,
        ];
    }

    /**
     * Resolve financial year from ID, instance, or null
     *
     * @param int|FinancialYear|null $financialYear
     * @return FinancialYear|null
     */
    protected static function resolveFinancialYear($financialYear): ?FinancialYear
    {
        if ($financialYear instanceof FinancialYear) {
            return $financialYear;
        }

        if (is_int($financialYear)) {
            return FinancialYear::find($financialYear);
        }

        return null;
    }

    /**
     * Get accounts by type with balances
     *
     * @param string $typeCode
     * @param string|null $date
     * @param string|null $startDate For period-based calculations
     * @return array
     */
    protected static function getAccountsByType(string $typeCode, ?string $date = null, ?string $startDate = null): array
    {
        $date = $date ?? now()->toDateString();
        $accountType = AccountType::where('code', $typeCode)->first();
        
        if (!$accountType) {
            return [];
        }

        $accounts = Account::where('account_type_id', $accountType->id)
            ->active()
            ->orderBy('code')
            ->get();

        $result = [];

        foreach ($accounts as $account) {
            if ($startDate) {
                // For period-based (like income statement), calculate balance for the period
                $balance = self::getAccountBalanceForPeriod($account, $startDate, $date);
            } else {
                // For point-in-time (like balance sheet), get current balance
                $balance = $account->getCurrentBalance($date);
            }

            if ($balance != 0 || $account->opening_balance != 0) {
                $result[] = [
                    'code' => $account->code,
                    'name' => $account->name,
                    'balance' => abs($balance),
                ];
            }
        }

        return $result;
    }

    /**
     * Get account balance for a period
     *
     * @param Account $account
     * @param string $startDate
     * @param string $endDate
     * @return float
     */
    protected static function getAccountBalanceForPeriod(Account $account, string $startDate, string $endDate): float
    {
        $debitTotal = $account->journalEntryLines()
            ->whereHas('journalEntry', function ($query) use ($startDate, $endDate) {
                $query->where('status', 'posted')
                    ->whereBetween('entry_date', [$startDate, $endDate]);
            })
            ->where('type', 'debit')
            ->sum('amount');

        $creditTotal = $account->journalEntryLines()
            ->whereHas('journalEntry', function ($query) use ($startDate, $endDate) {
                $query->where('status', 'posted')
                    ->whereBetween('entry_date', [$startDate, $endDate]);
            })
            ->where('type', 'credit')
            ->sum('amount');

        $netBalance = (float) $debitTotal - (float) $creditTotal;

        // Adjust based on account type normal balance
        if ($account->accountType && $account->accountType->isCreditNormal()) {
            $netBalance = (float) $creditTotal - (float) $debitTotal;
        }

        return $netBalance;
    }

    /**
     * Calculate retained earnings
     *
     * @param string|null $date
     * @return float
     */
    protected static function calculateRetainedEarnings(?string $date = null): float
    {
        $date = $date ?? now()->toDateString();
        $startOfYear = Carbon::parse($date)->startOfYear()->toDateString();

        // Get net income for the period
        $incomeStatement = self::getIncomeStatement($startOfYear, $date);
        
        return $incomeStatement['net_income'];
    }

    /**
     * Get General Ledger for an account
     *
     * @param int|Account $account
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public static function getGeneralLedger($account, ?string $startDate = null, ?string $endDate = null): array
    {
        if (is_int($account)) {
            $account = Account::findOrFail($account);
        }

        $startDate = $startDate ?? Carbon::now()->startOfYear()->toDateString();
        $endDate = $endDate ?? now()->toDateString();

        $openingBalance = $account->getCurrentBalance($startDate);
        
        $lines = JournalEntryLine::where('account_id', $account->id)
            ->whereHas('journalEntry', function ($query) use ($startDate, $endDate) {
                $query->where('status', 'posted')
                    ->whereBetween('entry_date', [$startDate, $endDate]);
            })
            ->with(['journalEntry' => function ($query) {
                $query->select('id', 'entry_number', 'entry_date', 'description');
            }])
            ->orderBy('journal_entry_id')
            ->orderBy('id')
            ->get();

        $runningBalance = $openingBalance;
        $transactions = [];

        foreach ($lines as $line) {
            if ($line->isDebit()) {
                $runningBalance += $line->amount;
            } else {
                $runningBalance -= $line->amount;
            }

            $transactions[] = [
                'date' => $line->journalEntry->entry_date,
                'entry_number' => $line->journalEntry->entry_number,
                'description' => $line->description ?? $line->journalEntry->description,
                'debit' => $line->isDebit() ? $line->amount : 0,
                'credit' => $line->isCredit() ? $line->amount : 0,
                'balance' => $runningBalance,
            ];
        }

        return [
            'account' => [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
            ],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'opening_balance' => $openingBalance,
            'closing_balance' => $runningBalance,
            'transactions' => $transactions,
        ];
    }

    /**
     * Get Chart of Accounts
     *
     * @param bool $includeInactive
     * @return array
     */
    public static function getChartOfAccounts(bool $includeInactive = false): array
    {
        $query = Account::with(['accountType', 'parent'])
            ->orderBy('account_type_id')
            ->orderBy('code');

        if (!$includeInactive) {
            $query->active();
        }

        $accounts = $query->get();

        $result = [];
        foreach ($accounts as $account) {
            $result[] = [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'account_type' => $account->accountType->name,
                'parent_code' => $account->parent ? $account->parent->code : null,
                'is_active' => $account->is_active,
                'current_balance' => $account->getCurrentBalance(),
            ];
        }

        return $result;
    }

    /**
     * Get Journal Entries Report
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @param string|null $status
     * @return array
     */
    public static function getJournalEntriesReport(
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $status = 'posted'
    ): array {
        $startDate = $startDate ?? Carbon::now()->startOfMonth()->toDateString();
        $endDate = $endDate ?? now()->toDateString();

        $query = JournalEntry::with(['lines.account', 'creator', 'poster'])
            ->dateRange($startDate, $endDate);

        if ($status) {
            $query->where('status', $status);
        }

        $entries = $query->orderBy('entry_date')
            ->orderBy('entry_number')
            ->get();

        $result = [];
        foreach ($entries as $entry) {
            $result[] = [
                'entry_number' => $entry->entry_number,
                'date' => $entry->entry_date,
                'description' => $entry->description,
                'status' => $entry->status,
                'total_debits' => $entry->total_debits,
                'total_credits' => $entry->total_credits,
                'lines' => $entry->lines->map(function ($line) {
                    return [
                        'account_code' => $line->account->code,
                        'account_name' => $line->account->name,
                        'type' => $line->type,
                        'amount' => $line->amount,
                        'description' => $line->description,
                    ];
                })->toArray(),
            ];
        }

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status,
            'entries' => $result,
        ];
    }

    /**
     * Get Cash Book Report
     * Shows all cash receipts and payments for a date range
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @param string|int|Account|null $cashAccount Cash account code, ID, or Account instance (default: '1100')
     * @return array
     */
    public static function getCashBook(
        ?string $startDate = null,
        ?string $endDate = null,
        $cashAccount = null
    ): array {
        $startDate = $startDate ?? Carbon::now()->startOfMonth()->toDateString();
        $endDate = $endDate ?? now()->toDateString();

        // Find cash account
        if ($cashAccount === null) {
            $cashAccount = Account::where('code', '1100')->first();
        } elseif (is_string($cashAccount)) {
            $cashAccount = Account::where('code', $cashAccount)->first();
        } elseif (is_int($cashAccount)) {
            $cashAccount = Account::find($cashAccount);
        }

        if (!$cashAccount instanceof Account) {
            throw new \Exception('Cash account not found. Please provide a valid cash account.');
        }

        // Get opening balance
        $openingBalance = $cashAccount->getCurrentBalance($startDate);

        // Get all journal entry lines for cash account in the date range
        $lines = JournalEntryLine::where('account_id', $cashAccount->id)
            ->whereHas('journalEntry', function ($query) use ($startDate, $endDate) {
                $query->where('status', 'posted')
                    ->whereBetween('entry_date', [$startDate, $endDate]);
            })
            ->with(['journalEntry' => function ($query) {
                $query->select('id', 'entry_number', 'entry_date', 'description', 'reference_type', 'reference_id');
            }])
            ->with(['account' => function ($query) {
                $query->select('id', 'code', 'name');
            }])
            ->orderBy('journal_entry_id')
            ->orderBy('id')
            ->get();

        $receipts = [];
        $payments = [];
        $runningBalance = $openingBalance;
        $totalReceipts = 0;
        $totalPayments = 0;

        foreach ($lines as $line) {
            $entry = $line->journalEntry;
            
            // Get the contra account (the other side of the transaction)
            $contraAccount = JournalEntryLine::where('journal_entry_id', $entry->id)
                ->where('account_id', '!=', $cashAccount->id)
                ->with('account')
                ->first();

            $transaction = [
                'date' => $entry->entry_date,
                'entry_number' => $entry->entry_number,
                'description' => $line->description ?? $entry->description,
                'contra_account_code' => $contraAccount ? $contraAccount->account->code : null,
                'contra_account_name' => $contraAccount ? $contraAccount->account->name : null,
                'reference_type' => $entry->reference_type,
                'reference_id' => $entry->reference_id,
            ];

            if ($line->isDebit()) {
                // Cash receipt (debit to cash)
                $transaction['amount'] = $line->amount;
                $runningBalance += $line->amount;
                $totalReceipts += $line->amount;
                $receipts[] = $transaction;
            } else {
                // Cash payment (credit to cash)
                $transaction['amount'] = $line->amount;
                $runningBalance -= $line->amount;
                $totalPayments += $line->amount;
                $payments[] = $transaction;
            }
        }

        return [
            'cash_account' => [
                'id' => $cashAccount->id,
                'code' => $cashAccount->code,
                'name' => $cashAccount->name,
            ],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'opening_balance' => $openingBalance,
            'receipts' => $receipts,
            'payments' => $payments,
            'total_receipts' => $totalReceipts,
            'total_payments' => $totalPayments,
            'closing_balance' => $runningBalance,
        ];
    }

    /**
     * Get Day Book Report
     * Shows all transactions for a specific day
     *
     * @param string|null $date
     * @return array
     */
    public static function getDayBook(?string $date = null): array
    {
        $date = $date ?? now()->toDateString();

        // Get all posted journal entries for the date
        $entries = JournalEntry::with(['lines.account', 'creator', 'poster'])
            ->where('status', 'posted')
            ->where('entry_date', $date)
            ->orderBy('entry_number')
            ->get();

        $transactions = [];
        $totalDebits = 0;
        $totalCredits = 0;

        foreach ($entries as $entry) {
            $entryDebits = 0;
            $entryCredits = 0;
            $lines = [];

            foreach ($entry->lines as $line) {
                if ($line->isDebit()) {
                    $entryDebits += $line->amount;
                    $totalDebits += $line->amount;
                } else {
                    $entryCredits += $line->amount;
                    $totalCredits += $line->amount;
                }

                $lines[] = [
                    'account_code' => $line->account->code,
                    'account_name' => $line->account->name,
                    'type' => $line->type,
                    'amount' => $line->amount,
                    'description' => $line->description,
                ];
            }

            $transactions[] = [
                'entry_number' => $entry->entry_number,
                'date' => $entry->entry_date,
                'description' => $entry->description,
                'notes' => $entry->notes,
                'reference_type' => $entry->reference_type,
                'reference_id' => $entry->reference_id,
                'created_by' => $entry->creator ? $entry->creator->name : null,
                'posted_by' => $entry->poster ? $entry->poster->name : null,
                'posted_at' => $entry->posted_at,
                'lines' => $lines,
                'total_debits' => $entryDebits,
                'total_credits' => $entryCredits,
            ];
        }

        return [
            'date' => $date,
            'transactions' => $transactions,
            'total_debits' => $totalDebits,
            'total_credits' => $totalCredits,
            'is_balanced' => abs($totalDebits - $totalCredits) < 0.01,
            'total_entries' => count($transactions),
        ];
    }

    /**
     * Get Trial Balance for a specific financial year
     *
     * @param int|FinancialYear|null $financialYear
     * @param string|null $date Optional date within the financial year
     * @return array
     */
    public static function getTrialBalanceForFinancialYear($financialYear, ?string $date = null): array
    {
        if (!config('accounting.enable_financial_year')) {
            throw new \Exception('Financial year management is disabled. Enable it in config/accounting.php');
        }

        $fy = self::resolveFinancialYear($financialYear);
        
        if (!$fy) {
            throw new \Exception('Financial year not found.');
        }

        $date = $date ?? $fy->end_date->toDateString();
        
        // Ensure date is within financial year
        if (!$fy->containsDate($date)) {
            $date = $fy->end_date->toDateString();
        }

        return self::getTrialBalance($date);
    }

    /**
     * Get Balance Sheet for a specific financial year
     *
     * @param int|FinancialYear|null $financialYear
     * @param string|null $date Optional date within the financial year
     * @return array
     */
    public static function getBalanceSheetForFinancialYear($financialYear, ?string $date = null): array
    {
        if (!config('accounting.enable_financial_year')) {
            throw new \Exception('Financial year management is disabled. Enable it in config/accounting.php');
        }

        $fy = self::resolveFinancialYear($financialYear);
        
        if (!$fy) {
            throw new \Exception('Financial year not found.');
        }

        $date = $date ?? $fy->end_date->toDateString();
        
        // Ensure date is within financial year
        if (!$fy->containsDate($date)) {
            $date = $fy->end_date->toDateString();
        }

        return self::getBalanceSheet($date);
    }

    /**
     * Get Income Statement for a specific financial year
     *
     * @param int|FinancialYear|null $financialYear
     * @return array
     */
    public static function getIncomeStatementForFinancialYear($financialYear): array
    {
        if (!config('accounting.enable_financial_year')) {
            throw new \Exception('Financial year management is disabled. Enable it in config/accounting.php');
        }

        $fy = self::resolveFinancialYear($financialYear);
        
        if (!$fy) {
            throw new \Exception('Financial year not found.');
        }

        return self::getIncomeStatement(
            $fy->start_date->toDateString(),
            $fy->end_date->toDateString(),
            $fy
        );
    }
}
