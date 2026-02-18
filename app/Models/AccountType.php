<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'normal_balance',
        'description',
        'sort_order',
        'is_active',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_system' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get all accounts of this type
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Check if normal balance is debit
     */
    public function isDebitNormal(): bool
    {
        return strtoupper($this->normal_balance) === 'DEBIT';
    }

    /**
     * Check if normal balance is credit
     */
    public function isCreditNormal(): bool
    {
        return strtoupper($this->normal_balance) === 'CREDIT';
    }

    /**
     * Scope to get active account types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get system account types
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope to get non-system account types
     */
    public function scopeNonSystem($query)
    {
        return $query->where('is_system', false);
    }
}
