<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JournalEntry extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'entry_number',
        'entry_date',
        'reference_type',
        'reference_id',
        'description',
        'notes',
        'status',
        'created_by',
        'posted_by',
        'posted_at',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'posted_at' => 'datetime',
        ];
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($journalEntry) {
            if (empty($journalEntry->entry_number)) {
                $journalEntry->entry_number = static::generateEntryNumber();
            }
        });
    }

    /**
     * Generate unique entry number
     */
    public static function generateEntryNumber(): string
    {
        $prefix = 'JE-' . date('Ymd');
        $lastEntry = static::where('entry_number', 'like', $prefix . '%')
            ->orderBy('entry_number', 'desc')
            ->first();

        if ($lastEntry) {
            $lastNumber = (int) substr($lastEntry->entry_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . '-' . $newNumber;
    }

    /**
     * Get the user who created the entry
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who posted the entry
     */
    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    /**
     * Get journal entry lines
     */
    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class)->orderBy('line_number');
    }

    /**
     * Get the reference model (polymorphic)
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if entry is balanced (debits = credits)
     */
    public function isBalanced(): bool
    {
        $debitTotal = $this->lines()->where('type', 'debit')->sum('amount');
        $creditTotal = $this->lines()->where('type', 'credit')->sum('amount');

        return abs($debitTotal - $creditTotal) < 0.01; // Allow for floating point precision
    }

    /**
     * Get total debits
     */
    public function getTotalDebitsAttribute(): float
    {
        return (float) $this->lines()->where('type', 'debit')->sum('amount');
    }

    /**
     * Get total credits
     */
    public function getTotalCreditsAttribute(): float
    {
        return (float) $this->lines()->where('type', 'credit')->sum('amount');
    }

    /**
     * Post the journal entry
     */
    public function post(?int $userId = null): bool
    {
        if ($this->status === 'posted') {
            return false;
        }

        if (!$this->isBalanced()) {
            throw new \Exception('Journal entry is not balanced. Debits must equal credits.');
        }

        DB::transaction(function () use ($userId) {
            $this->status = 'posted';
            $this->posted_by = $userId ?? auth()->id();
            $this->posted_at = now();
            $this->save();

            // Update account balances
            foreach ($this->lines as $line) {
                $line->account->updateBalance($this->entry_date);
            }
        });

        return true;
    }

    /**
     * Reverse the journal entry
     */
    public function reverse(?string $description = null): self
    {
        if ($this->status !== 'posted') {
            throw new \Exception('Only posted entries can be reversed.');
        }

        $reversal = static::create([
            'entry_date' => now()->toDateString(),
            'description' => $description ?? 'Reversal of ' . $this->entry_number,
            'notes' => 'Reversal of entry: ' . $this->entry_number,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        foreach ($this->lines as $line) {
            $reversal->lines()->create([
                'account_id' => $line->account_id,
                'type' => $line->type === 'debit' ? 'credit' : 'debit',
                'amount' => $line->amount,
                'description' => 'Reversal: ' . ($line->description ?? ''),
                'line_number' => $line->line_number,
            ]);
        }

        $reversal->post();

        $this->status = 'reversed';
        $this->save();

        return $reversal;
    }

    /**
     * Scope to get posted entries
     */
    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    /**
     * Scope to get entries by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('entry_date', [$startDate, $endDate]);
    }
}
