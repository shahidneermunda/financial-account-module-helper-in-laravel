<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account Helper - Documentation</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            background-color: #FDFDFC;
            color: #1b1b18;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        h1 {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #1b1b18;
        }
        h2 {
            font-size: 1.875rem;
            font-weight: 600;
            margin-top: 2rem;
            margin-bottom: 1rem;
            color: #1b1b18;
            border-bottom: 2px solid #e3e3e0;
            padding-bottom: 0.5rem;
        }
        h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
            color: #1b1b18;
        }
        h4 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-top: 1.25rem;
            margin-bottom: 0.5rem;
            color: #1b1b18;
        }
        .section {
            background: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }
        code {
            background-color: #f4f4f4;
            padding: 0.125rem 0.375rem;
            border-radius: 0.25rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.875em;
        }
        pre {
            background-color: #1b1b18;
            color: #FDFDFC;
            padding: 1rem;
            border-radius: 0.5rem;
            overflow-x: auto;
            margin: 1rem 0;
        }
        pre code {
            background-color: transparent;
            color: inherit;
            padding: 0;
        }
        .feature-list {
            list-style: none;
            padding: 0;
        }
        .feature-list li {
            padding: 0.75rem 0;
            border-bottom: 1px solid #e3e3e0;
        }
        .feature-list li:last-child {
            border-bottom: none;
        }
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }
        .badge-primary {
            background-color: #1b1b18;
            color: white;
        }
        .badge-success {
            background-color: #10b981;
            color: white;
        }
        .nav-link {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.5rem 1rem;
            background-color: #1b1b18;
            color: white;
            text-decoration: none;
            border-radius: 0.25rem;
            transition: background-color 0.2s;
        }
        .nav-link:hover {
            background-color: #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e3e3e0;
        }
        th {
            font-weight: 600;
            background-color: #f4f4f4;
        }
        .toc {
            background: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }
        .toc ul {
            list-style: none;
            padding-left: 0;
        }
        .toc li {
            padding: 0.5rem 0;
        }
        .toc a {
            color: #1b1b18;
            text-decoration: none;
            transition: color 0.2s;
        }
        .toc a:hover {
            color: #706f6c;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Account Helper - Complete Documentation</h1>
        
        <div class="toc">
            <h3>Table of Contents</h3>
            <ul>
                <li><a href="#overview">Overview</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="#models">Models</a></li>
                <li><a href="#accountable-trait">Accountable Trait</a></li>
                <li><a href="#accounting-service">AccountingService</a></li>
                <li><a href="#financial-years">Financial Year Management</a></li>
                <li><a href="#account-management">Account & Account Type Management</a></li>
                <li><a href="#reports">Financial Reports</a></li>
                <li><a href="#examples">Usage Examples</a></li>
            </ul>
        </div>

        <div class="section" id="overview">
            <h2>Overview</h2>
            <p>
                Account Helper is a comprehensive Laravel-based accounting system that provides double-entry bookkeeping,
                automatic journal entry creation, account management, and financial reporting capabilities.
            </p>
            <p>
                The system follows standard accounting principles with support for:
            </p>
            <ul>
                <li>Chart of Accounts with hierarchical structure</li>
                <li>Double-entry journal entries</li>
                <li>Automatic balance calculations</li>
                <li>Financial reports (Trial Balance, Balance Sheet, Income Statement, General Ledger)</li>
                <li>Integration with your models via the Accountable trait</li>
            </ul>
        </div>

        <div class="section" id="features">
            <h2>Core Features</h2>
            <ul class="feature-list">
                <li>
                    <strong>Account Management</strong>
                    <span class="badge badge-primary">Model</span>
                    <p>Create and manage accounts with codes, names, types, and hierarchical relationships.</p>
                </li>
                <li>
                    <strong>Account Types</strong>
                    <span class="badge badge-primary">Model</span>
                    <p>Define account types (Asset, Liability, Equity, Revenue, Expense) with normal balance rules.</p>
                </li>
                <li>
                    <strong>Journal Entries</strong>
                    <span class="badge badge-primary">Model</span>
                    <p>Create multi-line journal entries with automatic balance validation.</p>
                </li>
                <li>
                    <strong>Account Balances</strong>
                    <span class="badge badge-primary">Model</span>
                    <p>Track account balances by date with automatic updates.</p>
                </li>
                <li>
                    <strong>Accountable Trait</strong>
                    <span class="badge badge-success">Trait</span>
                    <p>Automatically create journal entries when models are created, updated, or deleted.</p>
                </li>
                <li>
                    <strong>AccountingService</strong>
                    <span class="badge badge-success">Service</span>
                    <p>Service layer for creating transactions, posting entries, and managing accounting operations.</p>
                </li>
                <li>
                    <strong>ReportHelper</strong>
                    <span class="badge badge-success">Helper</span>
                    <p>Generate financial reports including Trial Balance, Balance Sheet, Income Statement, and General Ledger.</p>
                </li>
            </ul>
        </div>

        <div class="section" id="models">
            <h2>Models</h2>
            
            <h3>Account</h3>
            <p>The Account model represents a single account in your chart of accounts.</p>
            <h4>Key Methods:</h4>
            <ul>
                <li><code>getCurrentBalance(?string $date = null): float</code> - Get account balance for a specific date</li>
                <li><code>calculateBalanceFromEntries(?string $date = null): float</code> - Calculate balance from journal entries</li>
                <li><code>updateBalance(?string $date = null): void</code> - Update account balance record</li>
                <li><code>scopeActive($query)</code> - Query scope for active accounts</li>
                <li><code>scopeOfType($query, $accountTypeCode)</code> - Query scope for accounts by type</li>
            </ul>

            <h3>AccountType</h3>
            <p>Defines the type of account (Asset, Liability, Equity, Revenue, Expense).</p>
            <h4>Key Methods:</h4>
            <ul>
                <li><code>isDebitNormal(): bool</code> - Check if normal balance is debit</li>
                <li><code>isCreditNormal(): bool</code> - Check if normal balance is credit</li>
            </ul>

            <h3>JournalEntry</h3>
            <p>Represents a journal entry with multiple lines (debits and credits).</p>
            <h4>Key Methods:</h4>
            <ul>
                <li><code>isBalanced(): bool</code> - Check if debits equal credits</li>
                <li><code>post(?int $userId = null): bool</code> - Post the journal entry</li>
                <li><code>reverse(?string $description = null): JournalEntry</code> - Reverse a posted entry</li>
                <li><code>scopePosted($query)</code> - Query scope for posted entries</li>
                <li><code>scopeDateRange($query, $startDate, $endDate)</code> - Query scope for date range</li>
            </ul>

            <h3>JournalEntryLine</h3>
            <p>Represents a single line in a journal entry (debit or credit).</p>

            <h3>AccountBalance</h3>
            <p>Stores account balance snapshots by date for performance optimization.</p>
        </div>

        <div class="section" id="accountable-trait">
            <h2>Accountable Trait</h2>
            <p>
                The Accountable trait allows any Eloquent model to automatically create journal entries
                when the model is created, updated, or deleted.
            </p>

            <h3>Basic Usage</h3>
            <pre><code>use App\Traits\Accountable;

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
}</code></pre>

            <h3>Configuration Options</h3>
            <table>
                <thead>
                    <tr>
                        <th>Option</th>
                        <th>Type</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>debit_account</code></td>
                        <td>string|int|Closure</td>
                        <td>Account code, ID, or closure returning account identifier</td>
                    </tr>
                    <tr>
                        <td><code>credit_account</code></td>
                        <td>string|int|Closure</td>
                        <td>Account code, ID, or closure returning account identifier</td>
                    </tr>
                    <tr>
                        <td><code>amount_field</code></td>
                        <td>string|Closure</td>
                        <td>Field name or closure returning the amount</td>
                    </tr>
                    <tr>
                        <td><code>description</code></td>
                        <td>string|Closure</td>
                        <td>Description template (supports {id}, {date} placeholders) or closure</td>
                    </tr>
                    <tr>
                        <td><code>date_field</code></td>
                        <td>string|Closure</td>
                        <td>Field name or closure returning the date (default: 'created_at')</td>
                    </tr>
                    <tr>
                        <td><code>auto_post</code></td>
                        <td>bool</td>
                        <td>Automatically post journal entry (default: true)</td>
                    </tr>
                    <tr>
                        <td><code>update_on_change</code></td>
                        <td>bool</td>
                        <td>Reverse and recreate entry on update (default: false)</td>
                    </tr>
                    <tr>
                        <td><code>reverse_on_delete</code></td>
                        <td>bool</td>
                        <td>Reverse entry on delete (default: false)</td>
                    </tr>
                </tbody>
            </table>

            <h3>Available Methods</h3>
            <ul>
                <li><code>createJournalEntry(): ?JournalEntry</code> - Manually create journal entry</li>
                <li><code>updateJournalEntry(): void</code> - Manually update journal entry</li>
                <li><code>reverseJournalEntry(): ?JournalEntry</code> - Manually reverse journal entry</li>
                <li><code>getJournalEntry(): ?JournalEntry</code> - Get associated journal entry</li>
                <li><code>getJournalEntries()</code> - Get all associated journal entries</li>
            </ul>

            <h3>Advanced Example with Closures</h3>
            <pre><code>protected $accountingConfig = [
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
];</code></pre>
        </div>

        <div class="section" id="accounting-service">
            <h2>AccountingService</h2>
            <p>
                The AccountingService provides methods for creating and managing journal entries and transactions.
            </p>

            <h3>Creating a Simple Transaction</h3>
            <pre><code>use App\Services\AccountingService;

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
);</code></pre>

            <h3>Creating a Complex Journal Entry</h3>
            <pre><code>$entry = $accountingService->createJournalEntry(
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
);</code></pre>

            <h3>Service Methods</h3>
            <table>
                <thead>
                    <tr>
                        <th>Method</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>createJournalEntry(array $data, array $lines, bool $autoPost)</code></td>
                        <td>Create a journal entry with multiple lines</td>
                    </tr>
                    <tr>
                        <td><code>createTransaction(int $debitId, int $creditId, float $amount, string $description, array $data, bool $autoPost)</code></td>
                        <td>Create a simple double-entry transaction</td>
                    </tr>
                    <tr>
                        <td><code>postEntry($entry, ?int $userId)</code></td>
                        <td>Post a journal entry</td>
                    </tr>
                    <tr>
                        <td><code>reverseEntry($entry, ?string $description)</code></td>
                        <td>Reverse a posted journal entry</td>
                    </tr>
                    <tr>
                        <td><code>getAccountBalance($account, ?string $date)</code></td>
                        <td>Get account balance for a specific date</td>
                    </tr>
                    <tr>
                        <td><code>updateAccountBalance($account, ?string $date)</code></td>
                        <td>Update account balance for a specific date</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="section" id="financial-years">
            <h2>Financial Year Management</h2>
            <p>
                The system supports financial year management, allowing you to organize transactions and reports
                by financial year periods. Financial years can be calendar-based (Jan-Dec) or custom periods
                (e.g., April-March).
            </p>

            <h3>Enabling/Disabling Financial Year Management</h3>
            <p>
                Financial year management can be enabled or disabled via configuration. When disabled, the system
                works without financial year filtering, and all journal entries and reports operate normally.
            </p>

            <h4>Configuration File</h4>
            <p>Edit <code>config/accounting.php</code> to configure financial year settings:</p>
            <pre><code>return [
    // Enable or disable financial year wise listing
    'enable_financial_year' => env('ACCOUNTING_ENABLE_FINANCIAL_YEAR', true),
    
    // Default financial year start month (1=Jan, 4=Apr, 7=Jul, 10=Oct)
    'default_financial_year_start_month' => env('ACCOUNTING_FY_START_MONTH', 4),
    
    // Auto-assign financial year to journal entries
    'auto_assign_financial_year' => env('ACCOUNTING_AUTO_ASSIGN_FY', true),
];</code></pre>

            <h4>Environment Variables</h4>
            <p>You can also set these in your <code>.env</code> file:</p>
            <pre><code># Enable/disable financial year management
ACCOUNTING_ENABLE_FINANCIAL_YEAR=true

# Default financial year start month
ACCOUNTING_FY_START_MONTH=4

# Auto-assign financial year
ACCOUNTING_AUTO_ASSIGN_FY=true</code></pre>

            <h4>Checking if Financial Year is Enabled</h4>
            <pre><code>use App\Helpers\AccountingHelper;

// Check if financial year is enabled
if (AccountingHelper::isFinancialYearEnabled()) {
    // Use financial year features
    $fy = $financialYearService->getCurrentFinancialYear();
}

// Or use config directly
if (config('accounting.enable_financial_year')) {
    // Financial year features available
}</code></pre>

            <h4>Behavior When Disabled</h4>
            <p>
                When financial year management is disabled:
            </p>
            <ul>
                <li>Journal entries are created without financial year assignment</li>
                <li>Reports work normally using date ranges instead of financial years</li>
                <li>Financial year-specific methods will throw exceptions if called</li>
                <li>The system behaves as if financial year management never existed</li>
            </ul>
            <p>
                <strong>Note:</strong> The <code>financial_year_id</code> column in the database will still exist
                (it's nullable), but it won't be used when the feature is disabled.
            </p>

            <h3>FinancialYear Model</h3>
            <p>The FinancialYear model represents a financial year period with start and end dates.</p>
            <h4>Key Methods:</h4>
            <ul>
                <li><code>containsDate(string $date): bool</code> - Check if a date falls within this financial year</li>
                <li><code>close(): bool</code> - Close the financial year</li>
                <li><code>activate(): bool</code> - Activate this financial year (deactivates others)</li>
                <li><code>static::forDate(string $date): ?FinancialYear</code> - Get financial year for a date</li>
                <li><code>static::active(): ?FinancialYear</code> - Get the active financial year</li>
            </ul>

            <h3>FinancialYearService</h3>
            <p>The FinancialYearService provides methods to manage financial years.</p>

            <h4>Creating Financial Years</h4>
            <pre><code>use App\Services\FinancialYearService;

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
);</code></pre>

            <h4>Getting Financial Years</h4>
            <pre><code>// Get active financial year
$activeFY = $financialYearService->getActiveFinancialYear();

// Get current financial year (active or for today's date)
$currentFY = $financialYearService->getCurrentFinancialYear();

// Get financial year for a specific date
$fy = $financialYearService->getFinancialYearForDate('2024-06-15');

// Get financial year dates
$dates = $financialYearService->getFinancialYearDates('2024-06-15');
// Returns: ['start_date' => '2024-04-01', 'end_date' => '2025-03-31', 'financial_year' => ...]</code></pre>

            <h3>Automatic Financial Year Assignment</h3>
            <p>
                Journal entries automatically get assigned to the appropriate financial year based on their
                entry date. The system finds the financial year that contains the entry date and assigns it
                automatically.
            </p>

            <h3>Financial Year in Reports</h3>
            <p>
                All reports support financial year filtering. You can generate reports for specific financial years
                or let the system automatically use the current financial year.
            </p>
            <pre><code>use App\Helpers\ReportHelper;
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
$incomeStatement = ReportHelper::getIncomeStatement(null, null, $fy);</code></pre>

            <h3>Closing a Financial Year</h3>
            <pre><code>$financialYear = FinancialYear::find(1);

// Close the financial year
$financialYear->close();

// This will:
// - Set is_closed = true
// - Set is_active = false
// - Set closed_at = current date</code></pre>
        </div>

        <div class="section" id="account-management">
            <h2>Account & Account Type Management</h2>
            <p>
                The system provides RESTful API endpoints to create, read, update, and delete Account Types and Accounts.
                All endpoints return JSON responses and include proper validation.
            </p>

            <h3>Account Type API Endpoints</h3>
            <table>
                <thead>
                    <tr>
                        <th>Method</th>
                        <th>Endpoint</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>GET</code></td>
                        <td><code>/api/account-types</code></td>
                        <td>List all account types (supports filtering and search)</td>
                    </tr>
                    <tr>
                        <td><code>POST</code></td>
                        <td><code>/api/account-types</code></td>
                        <td>Create a new account type</td>
                    </tr>
                    <tr>
                        <td><code>GET</code></td>
                        <td><code>/api/account-types/{id}</code></td>
                        <td>Get a specific account type</td>
                    </tr>
                    <tr>
                        <td><code>PUT/PATCH</code></td>
                        <td><code>/api/account-types/{id}</code></td>
                        <td>Update an account type</td>
                    </tr>
                    <tr>
                        <td><code>DELETE</code></td>
                        <td><code>/api/account-types/{id}</code></td>
                        <td>Delete an account type (only if no accounts exist)</td>
                    </tr>
                </tbody>
            </table>

            <h4>Create Account Type Example</h4>
            <pre><code>POST /api/account-types
Content-Type: application/json

{
    "name": "Other Income",
    "code": "OTHER_INCOME",
    "normal_balance": "CREDIT",
    "description": "Miscellaneous income sources",
    "sort_order": 6,
    "is_active": true
}</code></pre>

            <h4>List Account Types with Filters</h4>
            <pre><code>GET /api/account-types?active_only=1&is_system=0&search=income&sort_by=sort_order&sort_order=asc

Query Parameters:
- active_only: Filter only active account types
- is_system: Filter by system status (1 for system, 0 for non-system)
- search: Search in name, code, or description
- sort_by: Field to sort by (default: sort_order)
- sort_order: asc or desc (default: asc)</code></pre>

            <h3>Account API Endpoints</h3>
            <table>
                <thead>
                    <tr>
                        <th>Method</th>
                        <th>Endpoint</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>GET</code></td>
                        <td><code>/api/accounts</code></td>
                        <td>List all accounts (supports filtering and search)</td>
                    </tr>
                    <tr>
                        <td><code>POST</code></td>
                        <td><code>/api/accounts</code></td>
                        <td>Create a new account</td>
                    </tr>
                    <tr>
                        <td><code>GET</code></td>
                        <td><code>/api/accounts/{id}</code></td>
                        <td>Get a specific account (includes current balance)</td>
                    </tr>
                    <tr>
                        <td><code>PUT/PATCH</code></td>
                        <td><code>/api/accounts/{id}</code></td>
                        <td>Update an account</td>
                    </tr>
                    <tr>
                        <td><code>DELETE</code></td>
                        <td><code>/api/accounts/{id}</code></td>
                        <td>Delete an account (system accounts cannot be deleted)</td>
                    </tr>
                    <tr>
                        <td><code>GET</code></td>
                        <td><code>/api/accounts/options/account-types</code></td>
                        <td>Get account types for dropdowns</td>
                    </tr>
                    <tr>
                        <td><code>GET</code></td>
                        <td><code>/api/accounts/options/parent-accounts</code></td>
                        <td>Get parent accounts for dropdowns</td>
                    </tr>
                </tbody>
            </table>

            <h4>Create Account Example</h4>
            <pre><code>POST /api/accounts
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
}</code></pre>

            <h4>List Accounts with Filters</h4>
            <pre><code>GET /api/accounts?account_type_id=1&active_only=1&include_balance=1&search=cash

Query Parameters:
- account_type_id: Filter by account type
- parent_id: Filter by parent account (use 'null' for root accounts)
- active_only: Filter only active accounts
- include_balance: Include current balance in response
- search: Search in name, code, or description
- sort_by: Field to sort by (default: code)
- sort_order: asc or desc (default: asc)</code></pre>

            <h3>Validation Rules</h3>
            <h4>Account Type Validation</h4>
            <ul>
                <li><code>name</code>: Required, string, max 255 characters (cannot be changed for system account types)</li>
                <li><code>code</code>: Required, string, max 50 characters, unique, uppercase (cannot be changed for system account types)</li>
                <li><code>normal_balance</code>: Required, must be 'DEBIT' or 'CREDIT' (cannot be changed for system account types)</li>
                <li><code>description</code>: Optional, string, max 1000 characters</li>
                <li><code>sort_order</code>: Optional, integer, min 0</li>
                <li><code>is_active</code>: Optional, boolean</li>
                <li><code>is_system</code>: Optional, boolean (automatically set for default account types)</li>
            </ul>

            <h4>Account Validation</h4>
            <ul>
                <li><code>account_type_id</code>: Required, must exist in account_types table</li>
                <li><code>parent_id</code>: Optional, must exist in accounts table</li>
                <li><code>code</code>: Required, string, max 50 characters, unique</li>
                <li><code>name</code>: Required, string, max 255 characters</li>
                <li><code>description</code>: Optional, string, max 1000 characters</li>
                <li><code>opening_balance</code>: Optional, numeric, min 0</li>
                <li><code>opening_balance_date</code>: Optional, valid date</li>
                <li><code>is_active</code>: Optional, boolean</li>
                <li><code>sort_order</code>: Optional, integer, min 0</li>
                <li><code>metadata</code>: Optional, array</li>
            </ul>

            <h3>System Account Types</h3>
            <p>
                System account types (Asset, Liability, Equity, Revenue, Expense) are protected and cannot be:
            </p>
            <ul>
                <li>Deleted</li>
                <li>Modified in code, name, or normal_balance</li>
                <li>Only description and is_active can be updated for system account types</li>
            </ul>
            <p>
                The <code>is_system</code> field marks account types that are essential to the accounting system.
                Default account types (ASSET, LIABILITY, EQUITY, REVENUE, EXPENSE) are automatically marked as system.
            </p>

            <h3>Deletion Restrictions</h3>
            <ul>
                <li><strong>Account Types:</strong> 
                    <ul>
                        <li>System account types cannot be deleted</li>
                        <li>Cannot be deleted if they have associated accounts</li>
                    </ul>
                </li>
                <li><strong>Accounts:</strong> 
                    <ul>
                        <li>System accounts cannot be deleted</li>
                        <li>Cannot be deleted if they have child accounts</li>
                        <li>Cannot be deleted if they have journal entries (use soft delete instead)</li>
                    </ul>
                </li>
            </ul>
        </div>

        <div class="section" id="reports">
            <h2>Financial Reports</h2>
            <p>
                The ReportHelper class provides methods to generate various financial reports.
            </p>

            <h3>Trial Balance</h3>
            <pre><code>use App\Helpers\ReportHelper;

$trialBalance = ReportHelper::getTrialBalance(now()->toDateString());

// Returns:
// [
//     'date' => '2024-01-31',
//     'accounts' => [...],
//     'total_debits' => 10000.00,
//     'total_credits' => 10000.00,
//     'is_balanced' => true,
// ]</code></pre>

            <h3>Balance Sheet</h3>
            <pre><code>$balanceSheet = ReportHelper::getBalanceSheet(now()->toDateString());

// Returns:
// [
//     'date' => '2024-01-31',
//     'assets' => ['accounts' => [...], 'total' => 50000.00],
//     'liabilities' => ['accounts' => [...], 'total' => 20000.00],
//     'equity' => ['accounts' => [...], 'retained_earnings' => 10000.00, 'total' => 30000.00],
//     'total_liabilities_and_equity' => 50000.00,
//     'is_balanced' => true,
// ]</code></pre>

            <h3>Income Statement (Profit & Loss)</h3>
            <pre><code>$startDate = now()->startOfYear()->toDateString();
$endDate = now()->toDateString();

$incomeStatement = ReportHelper::getIncomeStatement($startDate, $endDate);

// Returns:
// [
//     'start_date' => '2024-01-01',
//     'end_date' => '2024-01-31',
//     'revenues' => ['accounts' => [...], 'total' => 50000.00],
//     'expenses' => ['accounts' => [...], 'total' => 30000.00],
//     'net_income' => 20000.00,
// ]</code></pre>

            <h3>General Ledger</h3>
            <pre><code>$cashAccount = Account::where('code', '1100')->first();

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
// ]</code></pre>

            <h3>Chart of Accounts</h3>
            <pre><code>$chartOfAccounts = ReportHelper::getChartOfAccounts();

// Returns array of accounts with:
// - id, code, name, account_type, parent_code, is_active, current_balance</code></pre>

            <h3>Journal Entries Report</h3>
            <pre><code>$journalEntries = ReportHelper::getJournalEntriesReport(
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
// ]</code></pre>

            <h3>Cash Book</h3>
            <p>
                The Cash Book shows all cash receipts and payments for a date range. It tracks all transactions
                involving the cash account, showing receipts (debits to cash) and payments (credits to cash).
            </p>
            <pre><code>$cashBook = ReportHelper::getCashBook(
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
// ]</code></pre>

            <h3>Day Book</h3>
            <p>
                The Day Book shows all transactions for a specific day. It provides a complete record of all
                journal entries posted on a given date, including all debit and credit lines.
            </p>
            <pre><code>$dayBook = ReportHelper::getDayBook('2024-01-15');

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
// ]</code></pre>
        </div>

        <div class="section" id="examples">
            <h2>Complete Usage Examples</h2>

            <h3>Example 1: Using Accountable Trait</h3>
            <pre><code>// In your Sale model
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

// Journal entry is automatically created and posted!</code></pre>

            <h3>Example 2: Manual Journal Entry Creation</h3>
            <pre><code>use App\Services\AccountingService;
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
);</code></pre>

            <h3>Example 3: Getting Account Balance</h3>
            <pre><code>use App\Models\Account;

$cashAccount = Account::where('code', '1100')->first();

// Get current balance
$balance = $cashAccount->getCurrentBalance();

// Get balance for a specific date
$balance = $cashAccount->getCurrentBalance('2024-01-31');

// Or using the service
$accountingService = app(AccountingService::class);
$balance = $accountingService->getAccountBalance($cashAccount->id);</code></pre>

            <h3>Example 4: Reversing a Journal Entry</h3>
            <pre><code>use App\Models\JournalEntry;

$entry = JournalEntry::find(1);

// Reverse the entry
$reversal = $entry->reverse('Reversed due to error');

// Or using the service
$accountingService = app(AccountingService::class);
$reversal = $accountingService->reverseEntry($entry, 'Reversed due to error');</code></pre>

            <h3>Example 5: Generating Financial Reports</h3>
            <pre><code>use App\Helpers\ReportHelper;

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
$dayBook = ReportHelper::getDayBook(now()->toDateString());</code></pre>

            <h3>Example 6: Working with Financial Years</h3>
            <pre><code>use App\Services\FinancialYearService;
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
$nextFY = $financialYearService->createCustomFinancialYear(2025, 4, true);</code></pre>
        </div>

        <div class="section">
            <h2>Best Practices</h2>
            <ul>
                <li>Always use account codes instead of IDs when possible for better maintainability</li>
                <li>Set up your chart of accounts using seeders before using the system</li>
                <li>Use the Accountable trait for automatic journal entry creation when appropriate</li>
                <li>Post journal entries only after validation to maintain data integrity</li>
                <li>Use closures in accounting config for dynamic account selection</li>
                <li>Regularly update account balances for better performance on large datasets</li>
                <li>Always validate that journal entries are balanced before posting</li>
            </ul>
        </div>

        <a href="{{ url('/') }}" class="nav-link">← Back to Home</a>
    </div>
</body>
</html>
