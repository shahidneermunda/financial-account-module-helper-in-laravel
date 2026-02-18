<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class FinancialYear extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'start_date',
        'end_date',
        'is_active',
        'is_closed',
        'closed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'closed_at' => 'date',
            'is_active' => 'boolean',
            'is_closed' => 'boolean',
        ];
    }

    /**
     * Get journal entries for this financial year
     */
    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    /**
     * Check if a date falls within this financial year
     */
    public function containsDate(string $date): bool
    {
        $date = Carbon::parse($date);
        return $date->gte($this->start_date) && $date->lte($this->end_date);
    }

    /**
     * Get the financial year for a given date
     */
    public static function forDate(string $date): ?self
    {
        return static::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();
    }

    /**
     * Get the active financial year
     */
    public static function active(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Scope to get active financial years
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get closed financial years
     */
    public function scopeClosed($query)
    {
        return $query->where('is_closed', true);
    }

    /**
     * Scope to get open financial years
     */
    public function scopeOpen($query)
    {
        return $query->where('is_closed', false);
    }

    /**
     * Close the financial year
     */
    public function close(): bool
    {
        if ($this->is_closed) {
            return false;
        }

        $this->is_closed = true;
        $this->is_active = false;
        $this->closed_at = now()->toDateString();
        return $this->save();
    }

    /**
     * Activate this financial year
     */
    public function activate(): bool
    {
        // Deactivate all other financial years
        static::where('id', '!=', $this->id)->update(['is_active' => false]);

        $this->is_active = true;
        return $this->save();
    }
}
