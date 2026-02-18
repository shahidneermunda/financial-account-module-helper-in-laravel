<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Financial Year Management
    |--------------------------------------------------------------------------
    |
    | Enable or disable financial year wise listing and filtering.
    | When enabled, journal entries will be automatically assigned to
    | financial years and reports can be filtered by financial year.
    | When disabled, the system works without financial year filtering.
    |
    */
    'enable_financial_year' => env('ACCOUNTING_ENABLE_FINANCIAL_YEAR', true),

    /*
    |--------------------------------------------------------------------------
    | Default Financial Year Start Month
    |--------------------------------------------------------------------------
    |
    | The default month when creating custom financial years.
    | Common values: 1 (January), 4 (April), 7 (July), 10 (October)
    |
    */
    'default_financial_year_start_month' => env('ACCOUNTING_FY_START_MONTH', 4),

    /*
    |--------------------------------------------------------------------------
    | Auto-assign Financial Year
    |--------------------------------------------------------------------------
    |
    | Automatically assign financial year to journal entries based on
    | entry date. If disabled, financial year must be manually assigned.
    |
    */
    'auto_assign_financial_year' => env('ACCOUNTING_AUTO_ASSIGN_FY', true),
];
