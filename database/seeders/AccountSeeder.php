<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountType;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get account types
        $asset = AccountType::where('code', 'ASSET')->first();
        $liability = AccountType::where('code', 'LIABILITY')->first();
        $equity = AccountType::where('code', 'EQUITY')->first();
        $revenue = AccountType::where('code', 'REVENUE')->first();
        $expense = AccountType::where('code', 'EXPENSE')->first();

        if (!$asset || !$liability || !$equity || !$revenue || !$expense) {
            $this->command->error('Please run AccountTypeSeeder first!');
            return;
        }

        // Store created accounts for parent reference
        $createdAccounts = [];

        $accounts = [
            // Assets - Parent accounts first
            ['account_type_id' => $asset->id, 'code' => '1000', 'name' => 'Current Assets', 'description' => 'Assets that can be converted to cash within one year', 'is_system' => true, 'sort_order' => 1, 'parent_code' => null],
            ['account_type_id' => $asset->id, 'code' => '2000', 'name' => 'Fixed Assets', 'description' => 'Long-term assets', 'is_system' => true, 'sort_order' => 2, 'parent_code' => null],
            
            // Assets - Child accounts
            ['account_type_id' => $asset->id, 'code' => '1100', 'name' => 'Cash and Cash Equivalents', 'description' => 'Cash on hand and in bank accounts', 'is_system' => true, 'sort_order' => 1, 'parent_code' => '1000'],
            ['account_type_id' => $asset->id, 'code' => '1200', 'name' => 'Accounts Receivable', 'description' => 'Amounts owed by customers', 'is_system' => true, 'sort_order' => 2, 'parent_code' => '1000'],
            ['account_type_id' => $asset->id, 'code' => '1300', 'name' => 'Inventory', 'description' => 'Goods held for sale', 'is_system' => true, 'sort_order' => 3, 'parent_code' => '1000'],
            ['account_type_id' => $asset->id, 'code' => '2100', 'name' => 'Property, Plant & Equipment', 'description' => 'Tangible fixed assets', 'is_system' => true, 'sort_order' => 1, 'parent_code' => '2000'],
            ['account_type_id' => $asset->id, 'code' => '2200', 'name' => 'Accumulated Depreciation', 'description' => 'Accumulated depreciation on fixed assets', 'is_system' => true, 'sort_order' => 2, 'parent_code' => '2000'],

            // Liabilities - Parent accounts first
            ['account_type_id' => $liability->id, 'code' => '3000', 'name' => 'Current Liabilities', 'description' => 'Liabilities due within one year', 'is_system' => true, 'sort_order' => 1, 'parent_code' => null],
            ['account_type_id' => $liability->id, 'code' => '4000', 'name' => 'Long-term Liabilities', 'description' => 'Liabilities due after one year', 'is_system' => true, 'sort_order' => 2, 'parent_code' => null],
            
            // Liabilities - Child accounts
            ['account_type_id' => $liability->id, 'code' => '3100', 'name' => 'Accounts Payable', 'description' => 'Amounts owed to suppliers', 'is_system' => true, 'sort_order' => 1, 'parent_code' => '3000'],
            ['account_type_id' => $liability->id, 'code' => '3200', 'name' => 'Accrued Expenses', 'description' => 'Expenses incurred but not yet paid', 'is_system' => true, 'sort_order' => 2, 'parent_code' => '3000'],
            ['account_type_id' => $liability->id, 'code' => '4100', 'name' => 'Long-term Debt', 'description' => 'Long-term loans and borrowings', 'is_system' => true, 'sort_order' => 1, 'parent_code' => '4000'],

            // Equity - Parent accounts first
            ['account_type_id' => $equity->id, 'code' => '5000', 'name' => 'Owner\'s Equity', 'description' => 'Owner\'s investment in the business', 'is_system' => true, 'sort_order' => 1, 'parent_code' => null],
            
            // Equity - Child accounts
            ['account_type_id' => $equity->id, 'code' => '5100', 'name' => 'Capital', 'description' => 'Owner\'s capital contribution', 'is_system' => true, 'sort_order' => 1, 'parent_code' => '5000'],
            ['account_type_id' => $equity->id, 'code' => '5200', 'name' => 'Retained Earnings', 'description' => 'Accumulated profits retained in the business', 'is_system' => true, 'sort_order' => 2, 'parent_code' => '5000'],

            // Revenue - Parent accounts first
            ['account_type_id' => $revenue->id, 'code' => '6000', 'name' => 'Sales Revenue', 'description' => 'Revenue from sales of goods or services', 'is_system' => true, 'sort_order' => 1, 'parent_code' => null],
            ['account_type_id' => $revenue->id, 'code' => '7000', 'name' => 'Other Income', 'description' => 'Other sources of income', 'is_system' => true, 'sort_order' => 2, 'parent_code' => null],
            
            // Revenue - Child accounts
            ['account_type_id' => $revenue->id, 'code' => '6100', 'name' => 'Product Sales', 'description' => 'Revenue from product sales', 'is_system' => true, 'sort_order' => 1, 'parent_code' => '6000'],
            ['account_type_id' => $revenue->id, 'code' => '6200', 'name' => 'Service Revenue', 'description' => 'Revenue from services', 'is_system' => true, 'sort_order' => 2, 'parent_code' => '6000'],

            // Expenses - Parent accounts first
            ['account_type_id' => $expense->id, 'code' => '8000', 'name' => 'Cost of Goods Sold', 'description' => 'Direct costs of producing goods or services', 'is_system' => true, 'sort_order' => 1, 'parent_code' => null],
            ['account_type_id' => $expense->id, 'code' => '9000', 'name' => 'Operating Expenses', 'description' => 'Expenses related to business operations', 'is_system' => true, 'sort_order' => 2, 'parent_code' => null],
            
            // Expenses - Child accounts
            ['account_type_id' => $expense->id, 'code' => '8100', 'name' => 'Direct Materials', 'description' => 'Cost of materials used in production', 'is_system' => true, 'sort_order' => 1, 'parent_code' => '8000'],
            ['account_type_id' => $expense->id, 'code' => '8200', 'name' => 'Direct Labor', 'description' => 'Cost of labor directly involved in production', 'is_system' => true, 'sort_order' => 2, 'parent_code' => '8000'],
            ['account_type_id' => $expense->id, 'code' => '9100', 'name' => 'Salaries and Wages', 'description' => 'Employee salaries and wages', 'is_system' => true, 'sort_order' => 1, 'parent_code' => '9000'],
            ['account_type_id' => $expense->id, 'code' => '9200', 'name' => 'Rent Expense', 'description' => 'Rent for office or facilities', 'is_system' => true, 'sort_order' => 2, 'parent_code' => '9000'],
            ['account_type_id' => $expense->id, 'code' => '9300', 'name' => 'Utilities', 'description' => 'Electricity, water, internet, etc.', 'is_system' => true, 'sort_order' => 3, 'parent_code' => '9000'],
            ['account_type_id' => $expense->id, 'code' => '9400', 'name' => 'Marketing and Advertising', 'description' => 'Marketing and advertising expenses', 'is_system' => true, 'sort_order' => 4, 'parent_code' => '9000'],
        ];

        // First pass: Create all accounts without parent_id
        foreach ($accounts as $accountData) {
            $parentCode = $accountData['parent_code'] ?? null;
            unset($accountData['parent_code']);

            $account = Account::updateOrCreate(
                ['code' => $accountData['code']],
                $accountData
            );

            $createdAccounts[$account->code] = $account;
        }

        // Second pass: Update parent_id for child accounts
        foreach ($accounts as $accountData) {
            if (!empty($accountData['parent_code'])) {
                $account = $createdAccounts[$accountData['code']];
                $parent = $createdAccounts[$accountData['parent_code']] ?? null;

                if ($parent) {
                    $account->parent_id = $parent->id;
                    $account->save();
                }
            }
        }
    }
}
