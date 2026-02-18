<?php

namespace App\Helpers;

class AccountingHelper
{
    /**
     * Check if financial year management is enabled
     *
     * @return bool
     */
    public static function isFinancialYearEnabled(): bool
    {
        return config('accounting.enable_financial_year', true);
    }

    /**
     * Check if auto-assign financial year is enabled
     *
     * @return bool
     */
    public static function isAutoAssignFinancialYearEnabled(): bool
    {
        return config('accounting.auto_assign_financial_year', true) && 
               self::isFinancialYearEnabled();
    }
}
