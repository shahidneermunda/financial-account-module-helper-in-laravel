<?php

namespace App\Services;

use App\Models\FinancialYear;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialYearService
{
    /**
     * Get the financial year for a given date
     *
     * @param string|null $date
     * @return FinancialYear|null
     */
    public function getFinancialYearForDate(?string $date = null): ?FinancialYear
    {
        if (!config('accounting.enable_financial_year')) {
            return null;
        }

        $date = $date ?? now()->toDateString();
        return FinancialYear::forDate($date);
    }

    /**
     * Get the active financial year
     *
     * @return FinancialYear|null
     */
    public function getActiveFinancialYear(): ?FinancialYear
    {
        if (!config('accounting.enable_financial_year')) {
            return null;
        }

        return FinancialYear::active();
    }

    /**
     * Get current financial year (active or for today's date)
     *
     * @return FinancialYear|null
     */
    public function getCurrentFinancialYear(): ?FinancialYear
    {
        if (!config('accounting.enable_financial_year')) {
            return null;
        }

        $active = $this->getActiveFinancialYear();
        if ($active) {
            return $active;
        }

        return $this->getFinancialYearForDate(now()->toDateString());
    }

    /**
     * Create a new financial year
     *
     * @param string $startDate
     * @param string $endDate
     * @param string|null $name
     * @param string|null $code
     * @param bool $activate
     * @return FinancialYear
     */
    public function createFinancialYear(
        string $startDate,
        string $endDate,
        ?string $name = null,
        ?string $code = null,
        bool $activate = false
    ): FinancialYear {
        // Generate name if not provided
        if (!$name) {
            $startYear = Carbon::parse($startDate)->format('Y');
            $endYear = Carbon::parse($endDate)->format('Y');
            $name = "FY {$startYear}-{$endYear}";
        }

        // Generate code if not provided
        if (!$code) {
            $startYear = Carbon::parse($startDate)->format('Y');
            $endYear = Carbon::parse($endDate)->format('y');
            $code = "FY{$startYear}-{$endYear}";
        }

        $financialYear = FinancialYear::create([
            'name' => $name,
            'code' => $code,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => $activate,
        ]);

        if ($activate) {
            $financialYear->activate();
        }

        return $financialYear;
    }

    /**
     * Create a financial year based on calendar year
     *
     * @param int $year
     * @param bool $activate
     * @return FinancialYear
     */
    public function createCalendarYearFinancialYear(int $year, bool $activate = false): FinancialYear
    {
        $startDate = Carbon::create($year, 1, 1)->toDateString();
        $endDate = Carbon::create($year, 12, 31)->toDateString();

        return $this->createFinancialYear($startDate, $endDate, null, null, $activate);
    }

    /**
     * Create a financial year with custom start month (e.g., April to March)
     *
     * @param int $startYear Year when the financial year starts
     * @param int $startMonth Month when the financial year starts (1-12)
     * @param bool $activate
     * @return FinancialYear
     */
    public function createCustomFinancialYear(int $startYear, int $startMonth = 4, bool $activate = false): FinancialYear
    {
        $startDate = Carbon::create($startYear, $startMonth, 1)->toDateString();
        $endDate = Carbon::create($startYear, $startMonth, 1)
            ->addYear()
            ->subDay()
            ->toDateString();

        return $this->createFinancialYear($startDate, $endDate, null, null, $activate);
    }

    /**
     * Get financial year dates for a given date
     *
     * @param string|null $date
     * @return array|null Returns ['start_date' => ..., 'end_date' => ...] or null
     */
    public function getFinancialYearDates(?string $date = null): ?array
    {
        $financialYear = $this->getFinancialYearForDate($date);
        
        if (!$financialYear) {
            return null;
        }

        return [
            'start_date' => $financialYear->start_date->toDateString(),
            'end_date' => $financialYear->end_date->toDateString(),
            'financial_year' => $financialYear,
        ];
    }

    /**
     * Check if a date is within the active financial year
     *
     * @param string|null $date
     * @return bool
     */
    public function isDateInActiveFinancialYear(?string $date = null): bool
    {
        $date = $date ?? now()->toDateString();
        $activeYear = $this->getActiveFinancialYear();
        
        if (!$activeYear) {
            return false;
        }

        return $activeYear->containsDate($date);
    }
}
