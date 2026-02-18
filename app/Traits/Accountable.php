<?php

namespace App\Traits;

use App\Models\JournalEntry;
use App\Services\AccountingService;

/**
 * Trait for models that need accounting integration
 * 
 * Usage:
 * 
 * class Sale extends Model {
 *     use Accountable;
 *     
 *     protected $accountingConfig = [
 *         'debit_account' => 'accounts_receivable', // account code or closure
 *         'credit_account' => 'sales_revenue', // account code or closure
 *         'amount_field' => 'total_amount', // field name or closure
 *         'description' => 'Sale #{id}', // description template or closure
 *     ];
 * }
 */
trait Accountable
{
    /**
     * Boot the trait
     */
    protected static function bootAccountable()
    {
        // Auto-create journal entry when model is created
        static::created(function ($model) {
            if ($model->shouldCreateJournalEntry()) {
                $model->createJournalEntry();
            }
        });

        // Auto-reverse and recreate journal entry when model is updated
        static::updated(function ($model) {
            if ($model->shouldUpdateJournalEntry()) {
                $model->updateJournalEntry();
            }
        });

        // Auto-reverse journal entry when model is deleted
        static::deleted(function ($model) {
            if ($model->shouldReverseJournalEntry()) {
                $model->reverseJournalEntry();
            }
        });
    }

    /**
     * Get accounting configuration
     */
    protected function getAccountingConfig(): array
    {
        return $this->accountingConfig ?? [];
    }

    /**
     * Check if journal entry should be created
     */
    protected function shouldCreateJournalEntry(): bool
    {
        $config = $this->getAccountingConfig();
        return !empty($config['debit_account']) && !empty($config['credit_account']);
    }

    /**
     * Check if journal entry should be updated
     */
    protected function shouldUpdateJournalEntry(): bool
    {
        $config = $this->getAccountingConfig();
        return !empty($config['update_on_change']) && $config['update_on_change'] === true;
    }

    /**
     * Check if journal entry should be reversed
     */
    protected function shouldReverseJournalEntry(): bool
    {
        $config = $this->getAccountingConfig();
        return !empty($config['reverse_on_delete']) && $config['reverse_on_delete'] === true;
    }

    /**
     * Get debit account ID
     */
    protected function getDebitAccountId(): ?int
    {
        $config = $this->getAccountingConfig();
        $account = $config['debit_account'] ?? null;

        if (!$account) {
            return null;
        }

        if (is_callable($account)) {
            $account = $account($this);
        }

        return $this->resolveAccountId($account);
    }

    /**
     * Get credit account ID
     */
    protected function getCreditAccountId(): ?int
    {
        $config = $this->getAccountingConfig();
        $account = $config['credit_account'] ?? null;

        if (!$account) {
            return null;
        }

        if (is_callable($account)) {
            $account = $account($this);
        }

        return $this->resolveAccountId($account);
    }

    /**
     * Get amount for journal entry
     */
    protected function getJournalEntryAmount(): float
    {
        $config = $this->getAccountingConfig();
        $amountField = $config['amount_field'] ?? 'amount';

        if (is_callable($amountField)) {
            return (float) $amountField($this);
        }

        return (float) ($this->{$amountField} ?? 0);
    }

    /**
     * Get description for journal entry
     */
    protected function getJournalEntryDescription(): string
    {
        $config = $this->getAccountingConfig();
        $description = $config['description'] ?? class_basename($this) . ' #' . $this->id;

        if (is_callable($description)) {
            return $description($this);
        }

        // Replace placeholders like {id}, {date}, etc.
        return preg_replace_callback('/\{(\w+)\}/', function ($matches) {
            $key = $matches[1];
            return $this->{$key} ?? $matches[0];
        }, $description);
    }

    /**
     * Resolve account ID from code or ID
     */
    protected function resolveAccountId($account): ?int
    {
        if (is_numeric($account)) {
            return (int) $account;
        }

        if (is_string($account)) {
            $accountModel = \App\Models\Account::where('code', $account)->first();
            return $accountModel ? $accountModel->id : null;
        }

        return null;
    }

    /**
     * Create journal entry for this model
     */
    public function createJournalEntry(): ?JournalEntry
    {
        $debitAccountId = $this->getDebitAccountId();
        $creditAccountId = $this->getCreditAccountId();
        $amount = $this->getJournalEntryAmount();

        if (!$debitAccountId || !$creditAccountId || $amount <= 0) {
            return null;
        }

        $accountingService = app(AccountingService::class);

        $config = $this->getAccountingConfig();
        $autoPost = $config['auto_post'] ?? true;

        return $accountingService->createTransaction(
            $debitAccountId,
            $creditAccountId,
            $amount,
            $this->getJournalEntryDescription(),
            [
                'reference_type' => get_class($this),
                'reference_id' => $this->id,
                'entry_date' => $this->getJournalEntryDate(),
            ],
            $autoPost
        );
    }

    /**
     * Update journal entry for this model
     */
    public function updateJournalEntry(): void
    {
        $existingEntry = $this->getJournalEntry();

        if ($existingEntry && $existingEntry->status === 'posted') {
            // Reverse the old entry
            $existingEntry->reverse('Updated: ' . $this->getJournalEntryDescription());
        }

        // Create new entry
        $this->createJournalEntry();
    }

    /**
     * Reverse journal entry for this model
     */
    public function reverseJournalEntry(): ?JournalEntry
    {
        $existingEntry = $this->getJournalEntry();

        if ($existingEntry && $existingEntry->status === 'posted') {
            return $existingEntry->reverse('Reversed: ' . $this->getJournalEntryDescription());
        }

        return null;
    }

    /**
     * Get journal entry date
     */
    protected function getJournalEntryDate(): string
    {
        $config = $this->getAccountingConfig();
        $dateField = $config['date_field'] ?? 'created_at';

        if (is_callable($dateField)) {
            $date = $dateField($this);
        } else {
            $date = $this->{$dateField} ?? now();
        }

        if ($date instanceof \DateTime || $date instanceof \Carbon\Carbon) {
            return $date->toDateString();
        }

        return is_string($date) ? $date : now()->toDateString();
    }

    /**
     * Get associated journal entry
     */
    public function getJournalEntry(): ?JournalEntry
    {
        return JournalEntry::where('reference_type', get_class($this))
            ->where('reference_id', $this->id)
            ->where('status', '!=', 'reversed')
            ->latest()
            ->first();
    }

    /**
     * Get all associated journal entries
     */
    public function getJournalEntries()
    {
        return JournalEntry::where('reference_type', get_class($this))
            ->where('reference_id', $this->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
