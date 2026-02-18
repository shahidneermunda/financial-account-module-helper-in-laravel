<?php

namespace Database\Seeders;

use App\Models\AccountType;
use Illuminate\Database\Seeder;

class AccountTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accountTypes = [
            [
                'name' => 'Asset',
                'code' => 'ASSET',
                'normal_balance' => 'DEBIT',
                'description' => 'Resources owned by the business',
                'sort_order' => 1,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'name' => 'Liability',
                'code' => 'LIABILITY',
                'normal_balance' => 'CREDIT',
                'description' => 'Obligations owed by the business',
                'sort_order' => 2,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'name' => 'Equity',
                'code' => 'EQUITY',
                'normal_balance' => 'CREDIT',
                'description' => 'Owner\'s equity in the business',
                'sort_order' => 3,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'name' => 'Revenue',
                'code' => 'REVENUE',
                'normal_balance' => 'CREDIT',
                'description' => 'Income generated from business operations',
                'sort_order' => 4,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'name' => 'Expense',
                'code' => 'EXPENSE',
                'normal_balance' => 'DEBIT',
                'description' => 'Costs incurred in business operations',
                'sort_order' => 5,
                'is_active' => true,
                'is_system' => true,
            ],
        ];

        foreach ($accountTypes as $type) {
            AccountType::updateOrCreate(
                ['code' => $type['code']],
                $type
            );
        }
    }
}
