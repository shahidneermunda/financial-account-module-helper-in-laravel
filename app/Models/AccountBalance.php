<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountBalance extends Model
{
    protected $fillable = [
        'account_id',
        'balance_date',
        'debit_balance',
        'credit_balance',
        'net_balance',
    ];

    protected function casts(): array
    {
        return [
            'balance_date' => 'date',
            'debit_balance' => 'decimal:2',
            'credit_balance' => 'decimal:2',
            'net_balance' => 'decimal:2',
        ];
    }

    /**
     * Get the account
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Scope to get balances by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('balance_date', [$startDate, $endDate]);
    }

    /**
     * Scope to get latest balance
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('balance_date', 'desc');
    }
}
