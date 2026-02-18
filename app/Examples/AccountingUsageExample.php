<?php

namespace App\Examples;

use App\Helpers\ReportHelper;
use App\Models\Account;
use App\Services\AccountingService;

/**
 * Example usage of the Accounting Module
 * 
 * This file demonstrates how to use the accounting module in your application.
 * You can delete this file or use it as a reference.
 */
class AccountingUsageExample
{
    /**
     * Example: Create a simple journal entry
     */
    public function createSimpleJournalEntry()
    {
        $accountingService = app(AccountingService::class);

        // Get accounts (assuming they exist from seeders)
        $cashAccount = Account::where('code', '1100')->first(); // Cash
        $salesAccount = Account::where('code', '6100')->first(); // Product Sales

        // Create a transaction: Debit Cash, Credit Sales
        $entry = $accountingService->createTransaction(
            $cashAccount->id,      // Debit account
            $salesAccount->id,     // Credit account
            1000.00,                // Amount
            'Sale of products',     // Description
            [
                'entry_date' => now()->toDateString(),
                'reference_type' => 'App\Models\Sale',
                'reference_id' => 1,
            ],
            true // Auto post
        );

        return $entry;
    }

    /**
     * Example: Create a complex journal entry with multiple lines
     */
    public function createComplexJournalEntry()
    {
        $accountingService = app(AccountingService::class);

        $cashAccount = Account::where('code', '1100')->first();
        $inventoryAccount = Account::where('code', '1300')->first();
        $cogsAccount = Account::where('code', '8100')->first();
        $salesAccount = Account::where('code', '6100')->first();

        // Create a journal entry with multiple lines
        $entry = $accountingService->createJournalEntry(
            [
                'entry_date' => now()->toDateString(),
                'description' => 'Sale with inventory cost',
                'notes' => 'Sold 10 units at $100 each',
            ],
            [
                // Debit: Cash received
                [
                    'account_id' => $cashAccount->id,
                    'type' => 'debit',
                    'amount' => 1000.00,
                    'description' => 'Cash received from sale',
                ],
                // Credit: Sales revenue
                [
                    'account_id' => $salesAccount->id,
                    'type' => 'credit',
                    'amount' => 1000.00,
                    'description' => 'Sales revenue',
                ],
                // Debit: Cost of goods sold
                [
                    'account_id' => $cogsAccount->id,
                    'type' => 'debit',
                    'amount' => 600.00,
                    'description' => 'Cost of goods sold',
                ],
                // Credit: Inventory
                [
                    'account_id' => $inventoryAccount->id,
                    'type' => 'credit',
                    'amount' => 600.00,
                    'description' => 'Inventory reduction',
                ],
            ],
            true // Auto post
        );

        return $entry;
    }

    /**
     * Example: Get account balance
     */
    public function getAccountBalance()
    {
        $accountingService = app(AccountingService::class);
        $cashAccount = Account::where('code', '1100')->first();

        $balance = $accountingService->getAccountBalance($cashAccount->id);
        // Or: $balance = $cashAccount->getCurrentBalance();

        return $balance;
    }

    /**
     * Example: Generate Trial Balance Report
     */
    public function generateTrialBalance()
    {
        $trialBalance = ReportHelper::getTrialBalance(now()->toDateString());

        return $trialBalance;
    }

    /**
     * Example: Generate Balance Sheet
     */
    public function generateBalanceSheet()
    {
        $balanceSheet = ReportHelper::getBalanceSheet(now()->toDateString());

        return $balanceSheet;
    }

    /**
     * Example: Generate Income Statement
     */
    public function generateIncomeStatement()
    {
        $startDate = now()->startOfYear()->toDateString();
        $endDate = now()->toDateString();

        $incomeStatement = ReportHelper::getIncomeStatement($startDate, $endDate);

        return $incomeStatement;
    }

    /**
     * Example: Get General Ledger for an account
     */
    public function getGeneralLedger()
    {
        $cashAccount = Account::where('code', '1100')->first();
        
        $startDate = now()->startOfMonth()->toDateString();
        $endDate = now()->toDateString();

        $generalLedger = ReportHelper::getGeneralLedger($cashAccount, $startDate, $endDate);

        return $generalLedger;
    }

    /**
     * Example: Get Chart of Accounts
     */
    public function getChartOfAccounts()
    {
        $chartOfAccounts = ReportHelper::getChartOfAccounts();

        return $chartOfAccounts;
    }

    /**
     * Example: Generate Cash Book
     */
    public function generateCashBook()
    {
        $startDate = now()->startOfMonth()->toDateString();
        $endDate = now()->toDateString();

        $cashBook = ReportHelper::getCashBook($startDate, $endDate, '1100');

        return $cashBook;
    }

    /**
     * Example: Generate Day Book
     */
    public function generateDayBook()
    {
        $dayBook = ReportHelper::getDayBook(now()->toDateString());

        return $dayBook;
    }
}

/**
 * Example: Using the Accountable trait in a Sale model
 * 
 * In your Sale model (app/Models/Sale.php):
 * 
 * <?php
 * 
 * namespace App\Models;
 * 
 * use App\Traits\Accountable;
 * use Illuminate\Database\Eloquent\Model;
 * 
 * class Sale extends Model
 * {
 *     use Accountable;
 * 
 *     protected $fillable = ['customer_id', 'total_amount', 'sale_date'];
 * 
 *     protected $accountingConfig = [
 *         'debit_account' => '1200', // Accounts Receivable (or use closure)
 *         'credit_account' => '6100', // Product Sales
 *         'amount_field' => 'total_amount',
 *         'description' => 'Sale #{id}',
 *         'date_field' => 'sale_date',
 *         'auto_post' => true,
 *         'update_on_change' => true,
 *         'reverse_on_delete' => true,
 *     ];
 * }
 * 
 * Now, when you create a Sale, it will automatically create a journal entry:
 * 
 * $sale = Sale::create([
 *     'customer_id' => 1,
 *     'total_amount' => 500.00,
 *     'sale_date' => now()->toDateString(),
 * ]);
 * 
 * // Journal entry is automatically created and posted!
 */
