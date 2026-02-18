<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountingService
{
    /**
     * Create a journal entry with lines
     *
     * @param array $data Entry data (entry_date, description, notes, reference_type, reference_id)
     * @param array $lines Array of lines [['account_id' => 1, 'type' => 'debit', 'amount' => 100, 'description' => '...'], ...]
     * @param bool $autoPost Whether to automatically post the entry
     * @return JournalEntry
     * @throws \Exception
     */
    public function createJournalEntry(array $data, array $lines, bool $autoPost = false): JournalEntry
    {
        return DB::transaction(function () use ($data, $lines, $autoPost) {
            // Validate lines
            $this->validateJournalEntryLines($lines);

            // Create journal entry
            $entry = JournalEntry::create([
                'entry_date' => $data['entry_date'] ?? now()->toDateString(),
                'description' => $data['description'] ?? '',
                'notes' => $data['notes'] ?? null,
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'status' => 'draft',
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);

            // Create lines
            foreach ($lines as $index => $line) {
                $entry->lines()->create([
                    'account_id' => $line['account_id'],
                    'type' => strtolower($line['type']),
                    'amount' => $line['amount'],
                    'description' => $line['description'] ?? null,
                    'line_number' => $line['line_number'] ?? $index + 1,
                    'metadata' => $line['metadata'] ?? null,
                ]);
            }

            // Reload to get lines
            $entry->load('lines');

            // Validate balance
            if (!$entry->isBalanced()) {
                throw new \Exception('Journal entry is not balanced. Total debits must equal total credits.');
            }

            // Auto post if requested
            if ($autoPost) {
                $entry->post($data['created_by'] ?? auth()->id());
            }

            return $entry->fresh();
        });
    }

    /**
     * Validate journal entry lines
     *
     * @param array $lines
     * @throws \Exception
     */
    protected function validateJournalEntryLines(array $lines): void
    {
        if (count($lines) < 2) {
            throw new \Exception('Journal entry must have at least 2 lines (one debit and one credit).');
        }

        $debitTotal = 0;
        $creditTotal = 0;

        foreach ($lines as $line) {
            // Validate required fields
            if (!isset($line['account_id']) || !isset($line['type']) || !isset($line['amount'])) {
                throw new \Exception('Each line must have account_id, type, and amount.');
            }

            // Validate account exists and is active
            $account = Account::find($line['account_id']);
            if (!$account) {
                throw new \Exception("Account with ID {$line['account_id']} not found.");
            }
            if (!$account->is_active) {
                throw new \Exception("Account {$account->code} is not active.");
            }

            // Validate type
            $type = strtolower($line['type']);
            if (!in_array($type, ['debit', 'credit'])) {
                throw new \Exception("Invalid line type: {$line['type']}. Must be 'debit' or 'credit'.");
            }

            // Validate amount
            $amount = (float) $line['amount'];
            if ($amount <= 0) {
                throw new \Exception('Amount must be greater than zero.');
            }

            // Calculate totals
            if ($type === 'debit') {
                $debitTotal += $amount;
            } else {
                $creditTotal += $amount;
            }
        }

        // Validate balance
        if (abs($debitTotal - $creditTotal) > 0.01) {
            throw new \Exception(
                "Journal entry is not balanced. Debits: {$debitTotal}, Credits: {$creditTotal}"
            );
        }
    }

    /**
     * Create a simple double-entry transaction
     *
     * @param int $debitAccountId
     * @param int $creditAccountId
     * @param float $amount
     * @param string $description
     * @param array $additionalData
     * @param bool $autoPost
     * @return JournalEntry
     */
    public function createTransaction(
        int $debitAccountId,
        int $creditAccountId,
        float $amount,
        string $description,
        array $additionalData = [],
        bool $autoPost = true
    ): JournalEntry {
        $data = array_merge([
            'entry_date' => now()->toDateString(),
            'description' => $description,
        ], $additionalData);

        $lines = [
            [
                'account_id' => $debitAccountId,
                'type' => 'debit',
                'amount' => $amount,
                'description' => $description,
            ],
            [
                'account_id' => $creditAccountId,
                'type' => 'credit',
                'amount' => $amount,
                'description' => $description,
            ],
        ];

        return $this->createJournalEntry($data, $lines, $autoPost);
    }

    /**
     * Post a journal entry
     *
     * @param int|JournalEntry $entry
     * @param int|null $userId
     * @return JournalEntry
     * @throws \Exception
     */
    public function postEntry($entry, ?int $userId = null): JournalEntry
    {
        if (is_int($entry)) {
            $entry = JournalEntry::findOrFail($entry);
        }

        if (!$entry instanceof JournalEntry) {
            throw new \Exception('Invalid journal entry provided.');
        }

        $entry->post($userId);

        return $entry->fresh();
    }

    /**
     * Reverse a journal entry
     *
     * @param int|JournalEntry $entry
     * @param string|null $description
     * @return JournalEntry
     * @throws \Exception
     */
    public function reverseEntry($entry, ?string $description = null): JournalEntry
    {
        if (is_int($entry)) {
            $entry = JournalEntry::findOrFail($entry);
        }

        if (!$entry instanceof JournalEntry) {
            throw new \Exception('Invalid journal entry provided.');
        }

        return $entry->reverse($description);
    }

    /**
     * Get account balance
     *
     * @param int|Account $account
     * @param string|null $date
     * @return float
     */
    public function getAccountBalance($account, ?string $date = null): float
    {
        if (is_int($account)) {
            $account = Account::findOrFail($account);
        }

        if (!$account instanceof Account) {
            throw new \Exception('Invalid account provided.');
        }

        return $account->getCurrentBalance($date);
    }

    /**
     * Update account balance for a specific date
     *
     * @param int|Account $account
     * @param string|null $date
     * @return void
     */
    public function updateAccountBalance($account, ?string $date = null): void
    {
        if (is_int($account)) {
            $account = Account::findOrFail($account);
        }

        if (!$account instanceof Account) {
            throw new \Exception('Invalid account provided.');
        }

        $account->updateBalance($date);
    }
}
