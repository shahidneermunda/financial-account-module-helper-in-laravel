<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntryLine extends Model
{
    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'type',
        'amount',
        'description',
        'line_number',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'line_number' => 'integer',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the journal entry
     */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    /**
     * Get the account
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Check if this is a debit line
     */
    public function isDebit(): bool
    {
        return strtolower($this->type) === 'debit';
    }

    /**
     * Check if this is a credit line
     */
    public function isCredit(): bool
    {
        return strtolower($this->type) === 'credit';
    }
}
