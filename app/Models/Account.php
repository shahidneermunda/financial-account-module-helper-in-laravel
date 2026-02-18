<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Account extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'account_type_id',
        'parent_id',
        'code',
        'name',
        'description',
        'opening_balance',
        'opening_balance_date',
        'is_active',
        'is_system',
        'sort_order',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'opening_balance_date' => 'date',
            'is_active' => 'boolean',
            'is_system' => 'boolean',
            'sort_order' => 'integer',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the account type
     */
    public function accountType(): BelongsTo
    {
        return $this->belongsTo(AccountType::class);
    }

    /**
     * Get the parent account
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    /**
     * Get child accounts
     */
    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    /**
     * Get journal entry lines
     */
    public function journalEntryLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    /**
     * Get account balances
     */
    public function balances(): HasMany
    {
        return $this->hasMany(AccountBalance::class);
    }

    /**
     * Get current balance for this account
     */
    public function getCurrentBalance(?string $date = null): float
    {
        $date = $date ?? now()->toDateString();
        
        $balance = $this->balances()
            ->where('balance_date', '<=', $date)
            ->orderBy('balance_date', 'desc')
            ->first();

        if ($balance) {
            return (float) $balance->net_balance;
        }

        // Calculate from journal entries if no balance record exists
        return $this->calculateBalanceFromEntries($date);
    }

    /**
     * Calculate balance from journal entries
     */
    public function calculateBalanceFromEntries(?string $date = null): float
    {
        $date = $date ?? now()->toDateString();
        
        $debitTotal = $this->journalEntryLines()
            ->whereHas('journalEntry', function ($query) use ($date) {
                $query->where('status', 'posted')
                    ->where('entry_date', '<=', $date);
            })
            ->where('type', 'debit')
            ->sum('amount');

        $creditTotal = $this->journalEntryLines()
            ->whereHas('journalEntry', function ($query) use ($date) {
                $query->where('status', 'posted')
                    ->where('entry_date', '<=', $date);
            })
            ->where('type', 'credit')
            ->sum('amount');

        $netBalance = (float) $debitTotal - (float) $creditTotal;

        // Adjust based on account type normal balance
        if ($this->accountType && $this->accountType->isCreditNormal()) {
            $netBalance = (float) $creditTotal - (float) $debitTotal;
        }

        // Add opening balance
        if ($this->opening_balance_date && $this->opening_balance_date <= $date) {
            $netBalance += (float) $this->opening_balance;
        }

        return $netBalance;
    }

    /**
     * Update account balance for a specific date
     */
    public function updateBalance(?string $date = null): void
    {
        $date = $date ?? now()->toDateString();
        
        $debitTotal = $this->journalEntryLines()
            ->whereHas('journalEntry', function ($query) use ($date) {
                $query->where('status', 'posted')
                    ->where('entry_date', '<=', $date);
            })
            ->where('type', 'debit')
            ->sum('amount');

        $creditTotal = $this->journalEntryLines()
            ->whereHas('journalEntry', function ($query) use ($date) {
                $query->where('status', 'posted')
                    ->where('entry_date', '<=', $date);
            })
            ->where('type', 'credit')
            ->sum('amount');

        $netBalance = (float) $debitTotal - (float) $creditTotal;

        // Adjust based on account type normal balance
        if ($this->accountType && $this->accountType->isCreditNormal()) {
            $netBalance = (float) $creditTotal - (float) $debitTotal;
        }

        // Add opening balance
        if ($this->opening_balance_date && $this->opening_balance_date <= $date) {
            $netBalance += (float) $this->opening_balance;
        }

        // Update or create balance record
        $this->balances()->updateOrCreate(
            ['balance_date' => $date],
            [
                'debit_balance' => (float) $debitTotal,
                'credit_balance' => (float) $creditTotal,
                'net_balance' => $netBalance,
            ]
        );
    }

    /**
     * Scope to get active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get accounts by type
     */
    public function scopeOfType($query, $accountTypeCode)
    {
        return $query->whereHas('accountType', function ($q) use ($accountTypeCode) {
            $q->where('code', $accountTypeCode);
        });
    }

    /**
     * Get full account path (for hierarchical display)
     */
    public function getFullPathAttribute(): string
    {
        $path = [$this->code];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($path, $parent->code);
            $parent = $parent->parent;
        }
        
        return implode(' > ', $path);
    }
}
