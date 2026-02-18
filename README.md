Account Helper - Complete Documentation
Table of Contents
Overview
Features
Models
Accountable Trait
AccountingService
Financial Year Management
Account & Account Type Management
Financial Reports
Usage Examples
Overview
Account Helper is a comprehensive Laravel-based accounting system that provides double-entry bookkeeping, automatic journal entry creation, account management, and financial reporting capabilities.

The system follows standard accounting principles with support for:

Chart of Accounts with hierarchical structure
Double-entry journal entries
Automatic balance calculations
Financial reports (Trial Balance, Balance Sheet, Income Statement, General Ledger)
Integration with your models via the Accountable trait
Core Features
Account Management Model
Create and manage accounts with codes, names, types, and hierarchical relationships.

Account Types Model
Define account types (Asset, Liability, Equity, Revenue, Expense) with normal balance rules.

Journal Entries Model
Create multi-line journal entries with automatic balance validation.

Account Balances Model
Track account balances by date with automatic updates.

Accountable Trait Trait
Automatically create journal entries when models are created, updated, or deleted.

AccountingService Service
Service layer for creating transactions, posting entries, and managing accounting operations.

ReportHelper Helper
Generate financial reports including Trial Balance, Balance Sheet, Income Statement, and General Ledger.

Models
Account
The Account model represents a single account in your chart of accounts.

Key Methods:
getCurrentBalance(?string $date = null): float - Get account balance for a specific date
calculateBalanceFromEntries(?string $date = null): float - Calculate balance from journal entries
updateBalance(?string $date = null): void - Update account balance record
scopeActive($query) - Query scope for active accounts
scopeOfType($query, $accountTypeCode) - Query scope for accounts by type
AccountType
Defines the type of account (Asset, Liability, Equity, Revenue, Expense).

Key Methods:
isDebitNormal(): bool - Check if normal balance is debit
isCreditNormal(): bool - Check if normal balance is credit
JournalEntry
Represents a journal entry with multiple lines (debits and credits).

Key Methods:
isBalanced(): bool - Check if debits equal credits
post(?int $userId = null): bool - Post the journal entry
reverse(?string $description = null): JournalEntry - Reverse a posted entry
scopePosted($query) - Query scope for posted entries
scopeDateRange($query, $startDate, $endDate) - Query scope for date range
JournalEntryLine
Represents a single line in a journal entry (debit or credit).

AccountBalance
Stores account balance snapshots by date for performance optimization.

Accountable Trait
The Accountable trait allows any Eloquent model to automatically create journal entries when the model is created, updated, or deleted.

Basic Usage
use App\Traits\Accountable;

class Sale extends Model
{
    use Accountable;

    protected $fillable = ['customer_id', 'total_amount', 'sale_date'];

    protected $accountingConfig = [
        'debit_account' => '1200', // Accounts Receivable
        'credit_account' => '6100', // Product Sales
        'amount_field' => 'total_amount',
        'description' => 'Sale #{id}',
        'date_field' => 'sale_date',
        'auto_post' => true,
        'update_on_change' => true,
        'reverse_on_delete' => true,
    ];
}
Configuration Options
Option	Type	Description
debit_account	string|int|Closure	Account code, ID, or closure returning account identifier
credit_account	string|int|Closure	Account code, ID, or closure returning account identifier
amount_field	string|Closure	Field name or closure returning the amount
description	string|Closure	Description template (supports {id}, {date} placeholders) or closure
date_field	string|Closure	Field name or closure returning the date (default: 'created_at')
auto_post	bool	Automatically post journal entry (default: true)
update_on_change	bool	Reverse and recreate entry on update (default: false)
reverse_on_delete	bool	Reverse entry on delete (default: false)
Available Methods
createJournalEntry(): ?JournalEntry - Manually create journal entry
updateJournalEntry(): void - Manually update journal entry
reverseJournalEntry(): ?JournalEntry - Manually reverse journal entry
getJournalEntry(): ?JournalEntry - Get associated journal entry
getJournalEntries() - Get all associated journal entries
Advanced Example with Closures
protected $accountingConfig = [
    'debit_account' => function($model) {
        return $model->payment_method === 'cash' ? '1100' : '1200';
    },
    'credit_account' => '6100',
    'amount_field' => function($model) {
        return $model->subtotal + $model->tax;
    },
    'description' => function($model) {
        return "Sale #{$model->id} to {$model->customer->name}";
    },
];
AccountingService
The AccountingService provides methods for creating and managing journal entries and transactions.

Creating a Simple Transaction
use App\Services\AccountingService;

$accountingService = app(AccountingService::class);

$entry = $accountingService->createTransaction(
    $debitAccountId,      // Debit account ID
    $creditAccountId,     // Credit account ID
    1000.00,               // Amount
    'Sale of products',    // Description
    [
        'entry_date' => now()->toDateString(),
        'reference_type' => 'App\Models\Sale',
        'reference_id' => 1,
    ],
    true // Auto post
);
Creating a Complex Journal Entry
$entry = $accountingService->createJournalEntry(
    [
        'entry_date' => now()->toDateString(),
        'description' => 'Sale with inventory cost',
        'notes' => 'Sold 10 units at $100 each',
    ],
    [
        [
            'account_id' => $cashAccount->id,
            'type' => 'debit',
            'amount' => 1000.00,
            'description' => 'Cash received from sale',
        ],
        [
            'account_id' => $salesAccount->id,
            'type' => 'credit',
            'amount' => 1000.00,
            'description' => 'Sales revenue',
        ],
        [
            'account_id' => $cogsAccount->id,
            'type' => 'debit',
            'amount' => 600.00,
            'description' => 'Cost of goods sold',
        ],
        [
            'account_id' => $inventoryAccount->id,
            'type' => 'credit',
            'amount' => 600.00,
            'description' => 'Inventory reduction',
        ],
    ],
    true // Auto post
);
Service Methods
Method	Description
createJournalEntry(array $data, array $lines, bool $autoPost)	Create a journal entry with multiple lines
createTransaction(int $debitId, int $creditId, float $amount, string $description, array $data, bool $autoPost)	Create a simple double-entry transaction
postEntry($entry, ?int $userId)	Post a journal entry
reverseEntry($entry, ?string $description)	Reverse a posted journal entry
getAccountBalance($account, ?string $date)	Get account balance for a specific date
updateAccountBalance($account, ?string $date)	Update account balance for a specific date
Financial Year Management
The system supports financial year management, allowing you to organize transactions and reports by financial year periods. Financial years can be calendar-based (Jan-Dec) or custom periods (e.g., April-March).

Enabling/Disabling Financial Year Management
Financial year management can be enabled or disabled via configuration. When disabled, the system works without financial year filtering, and all journal entries and reports operate normally.

Configuration File
Edit config/accounting.php to configure financial year settings:

return [
    // Enable or disable financial year wise listing
    'enable_financial_year' => env('ACCOUNTING_ENABLE_FINANCIAL_YEAR', true),
    
    // Default financial year start month (1=Jan, 4=Apr, 7=Jul, 10=Oct)
    'default_financial_year_start_month' => env('ACCOUNTING_FY_START_MONTH', 4),
    
    // Auto-assign financial year to journal entries
    'auto_assign_financial_year' => env('ACCOUNTING_AUTO_ASSIGN_FY', true),
];
Environment Variables
You can also set these in your .env file:

# Enable/disable financial year management
ACCOUNTING_ENABLE_FINANCIAL_YEAR=true

# Default financial year start month
ACCOUNTING_FY_START_MONTH=4

# Auto-assign financial year
ACCOUNTING_AUTO_ASSIGN_FY=true
Checking if Financial Year is Enabled
use App\Helpers\AccountingHelper;

// Check if financial year is enabled
if (AccountingHelper::isFinancialYearEnabled()) {
    // Use financial year features
    $fy = $financialYearService->getCurrentFinancialYear();
}

// Or use config directly
if (config('accounting.enable_financial_year')) {
    // Financial year features available
}
Behavior When Disabled
When financial year management is disabled:

Journal entries are created without financial year assignment
Reports work normally using date ranges instead of financial years
Financial year-specific methods will throw exceptions if called
The system behaves as if financial year management never existed
Note: The financial_year_id column in the database will still exist (it's nullable), but it won't be used when the feature is disabled.

FinancialYear Model
The FinancialYear model represents a financial year period with start and end dates.

Key Methods:
containsDate(string $date): bool - Check if a date falls within this financial year
close(): bool - Close the financial year
activate(): bool - Activate this financial year (deactivates others)
static::forDate(string $date): ?FinancialYear - Get financial year for a date
static::active(): ?FinancialYear - Get the active financial year
FinancialYearService
The FinancialYearService provides methods to manage financial years.

Creating Financial Years
use App\Services\FinancialYearService;

$financialYearService = app(FinancialYearService::class);

// Create a calendar year financial year (Jan 1 - Dec 31)
$fy2024 = $financialYearService->createCalendarYearFinancialYear(2024, true);

// Create a custom financial year (e.g., April 1 - March 31)
$fy2024_25 = $financialYearService->createCustomFinancialYear(2024, 4, true);

// Create with custom dates
$customFY = $financialYearService->createFinancialYear(
    '2024-04-01',
    '2025-03-31',
    'FY 2024-25',
    'FY2024-25',
    true // Activate immediately
);
Getting Financial Years
// Get active financial year
$activeFY = $financialYearService->getActiveFinancialYear();

// Get current financial year (active or for today's date)
$currentFY = $financialYearService->getCurrentFinancialYear();

// Get financial year for a specific date
$fy = $financialYearService->getFinancialYearForDate('2024-06-15');

// Get financial year dates
$dates = $financialYearService->getFinancialYearDates('2024-06-15');
// Returns: ['start_date' => '2024-04-01', 'end_date' => '2025-03-31', 'financial_year' => ...]
Automatic Financial Year Assignment
Journal entries automatically get assigned to the appropriate financial year based on their entry date. The system finds the financial year that contains the entry date and assigns it automatically.

Financial Year in Reports
All reports support financial year filtering. You can generate reports for specific financial years or let the system automatically use the current financial year.

use App\Helpers\ReportHelper;
use App\Models\FinancialYear;

// Get financial year
$fy = FinancialYear::find(1);

// Generate reports for a specific financial year
$trialBalance = ReportHelper::getTrialBalanceForFinancialYear($fy);
$balanceSheet = ReportHelper::getBalanceSheetForFinancialYear($fy);
$incomeStatement = ReportHelper::getIncomeStatementForFinancialYear($fy);

// Or pass financial year ID
$incomeStatement = ReportHelper::getIncomeStatementForFinancialYear(1);

// Income statement automatically uses financial year dates if financial year is provided
$incomeStatement = ReportHelper::getIncomeStatement(null, null, $fy);
Closing a Financial Year
$financialYear = FinancialYear::find(1);

// Close the financial year
$financialYear->close();

// This will:
// - Set is_closed = true
// - Set is_active = false
// - Set closed_at = current date
Account & Account Type Management
The system provides RESTful API endpoints to create, read, update, and delete Account Types and Accounts. All endpoints return JSON responses and include proper validation.

Account Type API Endpoints
Method	Endpoint	Description
GET	/api/account-types	List all account types (supports filtering and search)
POST	/api/account-types	Create a new account type
GET	/api/account-types/{id}	Get a specific account type
PUT/PATCH	/api/account-types/{id}	Update an account type
DELETE	/api/account-types/{id}	Delete an account type (only if no accounts exist)
Create Account Type Example
POST /api/account-types
Content-Type: application/json

{
    "name": "Other Income",
    "code": "OTHER_INCOME",
    "normal_balance": "CREDIT",
    "description": "Miscellaneous income sources",
    "sort_order": 6,
    "is_active": true
}
List Account Types with Filters
GET /api/account-types?active_only=1&is_system=0&search=income&sort_by=sort_order&sort_order=asc

Query Parameters:
- active_only: Filter only active account types
- is_system: Filter by system status (1 for system, 0 for non-system)
- search: Search in name, code, or description
- sort_by: Field to sort by (default: sort_order)
- sort_order: asc or desc (default: asc)
Account API Endpoints
Method	Endpoint	Description
GET	/api/accounts	List all accounts (supports filtering and search)
POST	/api/accounts	Create a new account
GET	/api/accounts/{id}	Get a specific account (includes current balance)
PUT/PATCH	/api/accounts/{id}	Update an account
DELETE	/api/accounts/{id}	Delete an account (system accounts cannot be deleted)
GET	/api/accounts/options/account-types	Get account types for dropdowns
GET	/api/accounts/options/parent-accounts	Get parent accounts for dropdowns
Create Account Example
POST /api/accounts
Content-Type: application/json

{
    "account_type_id": 1,
    "parent_id": null,
    "code": "1100",
    "name": "Cash",
    "description": "Cash on hand and in bank",
    "opening_balance": 10000.00,
    "opening_balance_date": "2024-01-01",
    "is_active": true,
    "sort_order": 1
}
List Accounts with Filters
GET /api/accounts?account_type_id=1&active_only=1&include_balance=1&search=cash

Query Parameters:
- account_type_id: Filter by account type
- parent_id: Filter by parent account (use 'null' for root accounts)
- active_only: Filter only active accounts
- include_balance: Include current balance in response
- search: Search in name, code, or description
- sort_by: Field to sort by (default: code)
- sort_order: asc or desc (default: asc)
Validation Rules
Account Type Validation
name: Required, string, max 255 characters (cannot be changed for system account types)
code: Required, string, max 50 characters, unique, uppercase (cannot be changed for system account types)
normal_balance: Required, must be 'DEBIT' or 'CREDIT' (cannot be changed for system account types)
description: Optional, string, max 1000 characters
sort_order: Optional, integer, min 0
is_active: Optional, boolean
is_system: Optional, boolean (automatically set for default account types)
Account Validation
account_type_id: Required, must exist in account_types table
parent_id: Optional, must exist in accounts table
code: Required, string, max 50 characters, unique
name: Required, string, max 255 characters
description: Optional, string, max 1000 characters
opening_balance: Optional, numeric, min 0
opening_balance_date: Optional, valid date
is_active: Optional, boolean
sort_order: Optional, integer, min 0
metadata: Optional, array
System Account Types
System account types (Asset, Liability, Equity, Revenue, Expense) are protected and cannot be:

Deleted
Modified in code, name, or normal_balance
Only description and is_active can be updated for system account types
The is_system field marks account types that are essential to the accounting system. Default account types (ASSET, LIABILITY, EQUITY, REVENUE, EXPENSE) are automatically marked as system.

Deletion Restrictions
Account Types:
System account types cannot be deleted
Cannot be deleted if they have associated accounts
Accounts:
System accounts cannot be deleted
Cannot be deleted if they have child accounts
Cannot be deleted if they have journal entries (use soft delete instead)
Financial Reports
The ReportHelper class provides methods to generate various financial reports.

Trial Balance
use App\Helpers\ReportHelper;

$trialBalance = ReportHelper::getTrialBalance(now()->toDateString());

// Returns:
// [
//     'date' => '2024-01-31',
//     'accounts' => [...],
//     'total_debits' => 10000.00,
//     'total_credits' => 10000.00,
//     'is_balanced' => true,
// ]
Balance Sheet
$balanceSheet = ReportHelper::getBalanceSheet(now()->toDateString());

// Returns:
// [
//     'date' => '2024-01-31',
//     'assets' => ['accounts' => [...], 'total' => 50000.00],
//     'liabilities' => ['accounts' => [...], 'total' => 20000.00],
//     'equity' => ['accounts' => [...], 'retained_earnings' => 10000.00, 'total' => 30000.00],
//     'total_liabilities_and_equity' => 50000.00,
//     'is_balanced' => true,
// ]
Income Statement (Profit & Loss)
$startDate = now()->startOfYear()->toDateString();
$endDate = now()->toDateString();

$incomeStatement = ReportHelper::getIncomeStatement($startDate, $endDate);

// Returns:
// [
//     'start_date' => '2024-01-01',
//     'end_date' => '2024-01-31',
//     'revenues' => ['accounts' => [...], 'total' => 50000.00],
//     'expenses' => ['accounts' => [...], 'total' => 30000.00],
//     'net_income' => 20000.00,
// ]
General Ledger
$cashAccount = Account::where('code', '1100')->first();

$generalLedger = ReportHelper::getGeneralLedger(
    $cashAccount,
    now()->startOfMonth()->toDateString(),
    now()->toDateString()
);

// Returns:
// [
//     'account' => ['id' => 1, 'code' => '1100', 'name' => 'Cash'],
//     'start_date' => '2024-01-01',
//     'end_date' => '2024-01-31',
//     'opening_balance' => 10000.00,
//     'closing_balance' => 15000.00,
//     'transactions' => [...],
// ]
Chart of Accounts
$chartOfAccounts = ReportHelper::getChartOfAccounts();

// Returns array of accounts with:
// - id, code, name, account_type, parent_code, is_active, current_balance
Journal Entries Report
$journalEntries = ReportHelper::getJournalEntriesReport(
    now()->startOfMonth()->toDateString(),
    now()->toDateString(),
    'posted' // status: 'draft', 'posted', or null for all
);

// Returns:
// [
//     'start_date' => '2024-01-01',
//     'end_date' => '2024-01-31',
//     'status' => 'posted',
//     'entries' => [...],
// ]
Cash Book
The Cash Book shows all cash receipts and payments for a date range. It tracks all transactions involving the cash account, showing receipts (debits to cash) and payments (credits to cash).

$cashBook = ReportHelper::getCashBook(
    now()->startOfMonth()->toDateString(),
    now()->toDateString(),
    '1100' // Optional: cash account code (default: '1100')
);

// Returns:
// [
//     'cash_account' => ['id' => 1, 'code' => '1100', 'name' => 'Cash'],
//     'start_date' => '2024-01-01',
//     'end_date' => '2024-01-31',
//     'opening_balance' => 10000.00,
//     'receipts' => [
//         [
//             'date' => '2024-01-15',
//             'entry_number' => 'JE-20240115-0001',
//             'description' => 'Sale of products',
//             'contra_account_code' => '6100',
//             'contra_account_name' => 'Product Sales',
//             'amount' => 1000.00,
//             'reference_type' => 'App\Models\Sale',
//             'reference_id' => 1,
//         ],
//         // ... more receipts
//     ],
//     'payments' => [
//         [
//             'date' => '2024-01-20',
//             'entry_number' => 'JE-20240120-0001',
//             'description' => 'Payment to supplier',
//             'contra_account_code' => '2100',
//             'contra_account_name' => 'Accounts Payable',
//             'amount' => 500.00,
//             'reference_type' => 'App\Models\Purchase',
//             'reference_id' => 1,
//         ],
//         // ... more payments
//     ],
//     'total_receipts' => 5000.00,
//     'total_payments' => 2000.00,
//     'closing_balance' => 13000.00,
// ]
Day Book
The Day Book shows all transactions for a specific day. It provides a complete record of all journal entries posted on a given date, including all debit and credit lines.

$dayBook = ReportHelper::getDayBook('2024-01-15');

// Returns:
// [
//     'date' => '2024-01-15',
//     'transactions' => [
//         [
//             'entry_number' => 'JE-20240115-0001',
//             'date' => '2024-01-15',
//             'description' => 'Sale of products',
//             'notes' => null,
//             'reference_type' => 'App\Models\Sale',
//             'reference_id' => 1,
//             'created_by' => 'John Doe',
//             'posted_by' => 'John Doe',
//             'posted_at' => '2024-01-15 10:30:00',
//             'lines' => [
//                 [
//                     'account_code' => '1100',
//                     'account_name' => 'Cash',
//                     'type' => 'debit',
//                     'amount' => 1000.00,
//                     'description' => 'Cash received',
//                 ],
//                 [
//                     'account_code' => '6100',
//                     'account_name' => 'Product Sales',
//                     'type' => 'credit',
//                     'amount' => 1000.00,
//                     'description' => 'Sales revenue',
//                 ],
//             ],
//             'total_debits' => 1000.00,
//             'total_credits' => 1000.00,
//         ],
//         // ... more transactions
//     ],
//     'total_debits' => 5000.00,
//     'total_credits' => 5000.00,
//     'is_balanced' => true,
//     'total_entries' => 5,
// ]
Complete Usage Examples
Example 1: Using Accountable Trait
// In your Sale model
use App\Traits\Accountable;

class Sale extends Model
{
    use Accountable;

    protected $accountingConfig = [
        'debit_account' => '1200', // Accounts Receivable
        'credit_account' => '6100', // Product Sales
        'amount_field' => 'total_amount',
        'description' => 'Sale #{id}',
        'auto_post' => true,
    ];
}

// When you create a sale, journal entry is automatically created
$sale = Sale::create([
    'customer_id' => 1,
    'total_amount' => 500.00,
    'sale_date' => now()->toDateString(),
]);

// Journal entry is automatically created and posted!
Example 2: Manual Journal Entry Creation
use App\Services\AccountingService;
use App\Models\Account;

$accountingService = app(AccountingService::class);

$cashAccount = Account::where('code', '1100')->first();
$salesAccount = Account::where('code', '6100')->first();

$entry = $accountingService->createTransaction(
    $cashAccount->id,
    $salesAccount->id,
    1000.00,
    'Sale of products',
    [
        'entry_date' => now()->toDateString(),
        'reference_type' => 'App\Models\Sale',
        'reference_id' => 1,
    ],
    true // Auto post
);
Example 3: Getting Account Balance
use App\Models\Account;

$cashAccount = Account::where('code', '1100')->first();

// Get current balance
$balance = $cashAccount->getCurrentBalance();

// Get balance for a specific date
$balance = $cashAccount->getCurrentBalance('2024-01-31');

// Or using the service
$accountingService = app(AccountingService::class);
$balance = $accountingService->getAccountBalance($cashAccount->id);
Example 4: Reversing a Journal Entry
use App\Models\JournalEntry;

$entry = JournalEntry::find(1);

// Reverse the entry
$reversal = $entry->reverse('Reversed due to error');

// Or using the service
$accountingService = app(AccountingService::class);
$reversal = $accountingService->reverseEntry($entry, 'Reversed due to error');
Example 5: Generating Financial Reports
use App\Helpers\ReportHelper;

// Trial Balance
$trialBalance = ReportHelper::getTrialBalance(now()->toDateString());

// Balance Sheet
$balanceSheet = ReportHelper::getBalanceSheet(now()->toDateString());

// Income Statement
$incomeStatement = ReportHelper::getIncomeStatement(
    now()->startOfYear()->toDateString(),
    now()->toDateString()
);

// General Ledger
$cashAccount = Account::where('code', '1100')->first();
$generalLedger = ReportHelper::getGeneralLedger(
    $cashAccount,
    now()->startOfMonth()->toDateString(),
    now()->toDateString()
);

// Cash Book
$cashBook = ReportHelper::getCashBook(
    now()->startOfMonth()->toDateString(),
    now()->toDateString(),
    '1100' // Optional: cash account code
);

// Day Book
$dayBook = ReportHelper::getDayBook(now()->toDateString());
Example 6: Working with Financial Years
use App\Services\FinancialYearService;
use App\Helpers\ReportHelper;
use App\Models\FinancialYear;

$financialYearService = app(FinancialYearService::class);

// Create a financial year (April 1, 2024 to March 31, 2025)
$fy = $financialYearService->createCustomFinancialYear(2024, 4, true);

// Get current financial year
$currentFY = $financialYearService->getCurrentFinancialYear();

// Generate reports for a specific financial year
$trialBalance = ReportHelper::getTrialBalanceForFinancialYear($currentFY);
$balanceSheet = ReportHelper::getBalanceSheetForFinancialYear($currentFY);
$incomeStatement = ReportHelper::getIncomeStatementForFinancialYear($currentFY);

// Close financial year at year end
$currentFY->close();

// Activate next financial year
$nextFY = $financialYearService->createCustomFinancialYear(2025, 4, true);
Best Practices
Always use account codes instead of IDs when possible for better maintainability
Set up your chart of accounts using seeders before using the system
Use the Accountable trait for automatic journal entry creation when appropriate
Post journal entries only after validation to maintain data integrity
Use closures in accounting config for dynamic account selection
Regularly update account balances for better performance on large datasets
Always validate that journal entries are balanced before posting