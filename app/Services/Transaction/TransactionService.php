<?php

namespace App\Services\Transaction;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Eloquent\Account;
use App\Models\Eloquent\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Eloquent\LedgerEntry;
use App\Models\Eloquent\Transaction;
use Illuminate\Support\Facades\Auth;
use App\Models\Eloquent\TransactionLimit;

class TransactionService
{
    private $tenantId;
    private $userId;

    public function __construct()
    {
        $this->tenantId = Auth::user()->tenant_id ?? null;
        $this->userId = Auth::id();
    }

    /**
     * Process initial deposit when creating an account
     */
    public function initialDeposit(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $accountId = $data['account_id'] ?? null;
            if (!$accountId) {
                throw new \Exception('Account ID is required for initial deposit');
            }

            $account = Account::findOrFail($accountId);

            // Check if account already has initial deposit
            $existingDeposit = Transaction::where('destination_account_id', $accountId)
                ->where('type', 'initial_deposit')
                ->where('status', 'completed')
                ->first();

            if ($existingDeposit) {
                throw new \Exception('Account already has an initial deposit');
            }

            $transaction = Transaction::create([
                // 'tenant_id' => $this->tenantId,
                'transaction_reference' => $this->generateReference(),
                'type' => 'initial_deposit',
                'status' => 'pending',
                'amount' => $data['amount'],
                'currency' => $account->currency,
                'description' => $data['description'] ?? 'Initial account deposit',
                'metadata' => $data['metadata'] ?? [
                    'account' => $account->account_number,
                    'initiator' => Auth::user()->email,
                    'method' => $data['method'] ?? 'cash',
                    'reference' => $data['external_reference'] ?? null,
                    'ip_address' => request()->ip(),
                ],
                'initiated_by' => $this->userId,
                'initiated_at' => now(),
                'destination_account_id' => $account->id,
            ]);

            try {
                // Create credit ledger entry
                LedgerEntry::create([
                    // 'tenant_id' => $this->tenantId,
                    'transaction_id' => $transaction->id,
                    'account_id' => $account->id,
                    'entry_type' => 'credit',
                    'amount' => $data['amount'],
                    'currency' => $account->currency,
                    'balance_after' => $account->current_balance + $data['amount'],
                    'available_balance_after'=> $account->current_balance + $data['amount'] - $account->minimum_balance,
                    'balance_before' => $account->current_balance,
                ]);

                // Update account balances
                $account->increment('current_balance', $data['amount']);
                $account->increment('available_balance', $data['amount']);
                // Update ledger_balance if exists
                if (isset($account->ledger_balance)) {
                    $account->increment('ledger_balance', $data['amount']);
                }

                $transaction->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

                // Log audit
                $this->logAudit($transaction, 'initial_deposit_completed', [
                    'account_id' => $account->id,
                    'amount' => $data['amount'],
                    // 'account_number' => $account->account_number,
                ]);

                return $transaction;
            } catch (\Exception $e) {
                $transaction->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'failure_reason' => $e->getMessage(),
                ]);

                $this->logAudit($transaction, 'initial_deposit_failed', [
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        });
    }

    /**
     * Transfer funds between accounts
     */
    public function transfer(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            // FIX: Use correct parameter names
            $fromAccountId = $data['from_account_id'] ?? $data['source_account_id'] ?? null;
            $toAccountId = $data['to_account_id'] ?? $data['destination_account_id'] ?? null;

            if (!$fromAccountId || !$toAccountId) {
                throw new \Exception('Source and destination accounts are required');
            }

            $fromAccount = $this->validateAccount($fromAccountId, 'debit');
            $toAccount = $this->validateAccount($toAccountId, 'credit');

            // Check currencies match
            if ($fromAccount->currency !== $toAccount->currency) {
                throw new \Exception('Currency mismatch between accounts');
            }

            // Validate transaction limits
            $this->validateLimits($fromAccount, $data['amount'], 'transfer');

            // Check sufficient funds (including overdraft if applicable)
            $availableBalance = $fromAccount->available_balance;
            if ($data['amount'] > $availableBalance + $fromAccount->overdraft_limit) {
                throw new \Exception('Insufficient funds');
            }

            // Create transaction record with all required fields
            $transaction = Transaction::create([
                // 'tenant_id' => $this->tenantId,
                'transaction_reference' => $this->generateReference(),
                'type' => 'transfer',
                'status' => 'pending',
                'amount' => $data['amount'],
                'currency' => $fromAccount->currency,
                'description' => $data['description'] ?? 'Funds transfer',
                'metadata' => $data['metadata'] ?? [
                    'from_account' => $fromAccount->account_number,
                    'to_account' => $toAccount->account_number,
                    'initiator' => Auth::user()->email,
                    'ip_address' => request()->ip(),
                ],
                'initiated_by' => $this->userId,
                'initiated_at' => now(),
                'source_account_id' => $fromAccount->id,
                'destination_account_id' => $toAccount->id,
            ]);

            try {
                // Create ledger entries
                $this->createLedgerEntries($transaction, $fromAccount, $toAccount, $data['amount']);

                // Update account balances
                $this->updateAccountBalances($fromAccount, $toAccount, $data['amount']);

                // Mark transaction as completed
                $transaction->complete();

                // Log audit
                $this->logAudit($transaction, 'transfer_completed', [
                    'from_account_id' => $fromAccount->id,
                    'to_account_id' => $toAccount->id,
                    'amount' => $data['amount'],
                    'currency' => $fromAccount->currency,
                ]);

                return $transaction;
            } catch (\Exception $e) {
                $transaction->fail($e->getMessage());
                $this->logAudit($transaction, 'transfer_failed', [
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        });
    }

    /**
     * Process withdrawal
     */
    public function withdraw(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $accountId = $data['account_id'] ?? null;
            if (!$accountId) {
                throw new \Exception('Account ID is required');
            }

            $account = $this->validateAccount($accountId, 'debit');

            $this->validateLimits($account, $data['amount'], 'withdrawal');

            // Check sufficient funds
            $availableBalance = $account->available_balance;
            if ($data['amount'] > $availableBalance + $account->overdraft_limit) {
                throw new \Exception('Insufficient funds');
            }

            $transaction = Transaction::create([
                // 'tenant_id' => $this->tenantId,
                'transaction_reference' => $this->generateReference(),
                'type' => 'withdrawal',
                'status' => 'pending',
                'amount' => $data['amount'],
                'currency' => $account->currency,
                'description' => $data['description'] ?? 'Cash withdrawal',
                'metadata' => $data['metadata'] ?? [
                    'account' => $account->account_number,
                    'initiator' => Auth::user()->email,
                    'method' => $data['method'] ?? 'cash',
                    'ip_address' => request()->ip(),
                ],
                'initiated_by' => $this->userId,
                'initiated_at' => now(),
                'source_account_id' => $account->id,
            ]);

            try {
                // Create debit ledger entry
                LedgerEntry::create([
                    // 'tenant_id' => $this->tenantId,
                    'transaction_id' => $transaction->id,
                    'account_id' => $account->id,
                    'entry_type' => 'debit',
                    'amount' => $data['amount'],
                    'currency' => $account->currency,
                    'balance_after' => $account->current_balance - $data['amount'],
                    'available_balance_after' => $account->current_balance - $data['amount'] - $account->minimum_balance,
                    'balance_before' => $account->current_balance,
                ]);

                // Update account balance
                $account->decrement('current_balance', $data['amount']);
                $account->decrement('available_balance', $data['amount']);

                $transaction->complete();

                $this->logAudit($transaction, 'withdrawal_completed', [
                    'account_id' => $account->id,
                    'amount' => $data['amount'],
                ]);

                return $transaction;
            } catch (\Exception $e) {
                $transaction->fail($e->getMessage());
                $this->logAudit($transaction, 'withdrawal_failed', [
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        });
    }

    /**
     * Process deposit
     */
    public function deposit(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $accountId = $data['account_id'] ?? null;
            if (!$accountId) {
                throw new \Exception('Account ID is required');
            }

            $account = $this->validateAccount($accountId, 'credit');

            $this->validateLimits($account, $data['amount'], 'deposit');

            $transaction = Transaction::create([
                // 'tenant_id' => $this->tenantId,
                'transaction_reference' => $this->generateReference(),
                'type' => 'deposit',
                'status' => 'pending',
                'amount' => $data['amount'],
                'currency' => $account->currency,
                'description' => $data['description'] ?? 'Cash deposit',
                'metadata' => $data['metadata'] ?? [
                    'account' => $account->account_number,
                    'initiator' => Auth::user()->email,
                    'method' => $data['method'] ?? 'cash',
                    'reference' => $data['external_reference'] ?? null,
                    'ip_address' => request()->ip(),
                ],
                'initiated_by' => $this->userId,
                'initiated_at' => now(),
                'destination_account_id' => $account->id,
            ]);

            try {
                // Create credit ledger entry
                LedgerEntry::create([
                    // 'tenant_id' => $this->tenantId,
                    'transaction_id' => $transaction->id,
                    'account_id' => $account->id,
                    'entry_type' => 'credit',
                    'amount' => $data['amount'],
                    'currency' => $account->currency,
                    'balance_after' => $account->current_balance + $data['amount'],
                    'available_balance_after' => $account->current_balance + $data['amount'] - $account->minimum_balance,
                    'balance_before'=> $account->current_balance,
                ]);

                // Update account balance
                $account->increment('current_balance', $data['amount']);
                $account->increment('available_balance', $data['amount']);

                $transaction->complete();

                $this->logAudit($transaction, 'deposit_completed', [
                    'account_id' => $account->id,
                    'amount' => $data['amount'],
                ]);

                return $transaction;
            } catch (\Exception $e) {
                $transaction->fail($e->getMessage());
                $this->logAudit($transaction, 'deposit_failed', [
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        });
    }

    /**
     * Process cash deposit specifically
     */
    public function cashDeposit(array $data): Transaction
    {
        $data['method'] = 'cash';
        $data['description'] = $data['description'] ?? 'Cash deposit';
        $data['type'] = 'cash_deposit';

        return $this->deposit($data);
    }

    /**
     * Reverse a transaction
     */
    public function reverse(string $transactionId, ?string $reason = null): Transaction
    {
        return DB::transaction(function () use ($transactionId, $reason) {
            $originalTransaction = Transaction::findOrFail($transactionId);

            if (!$originalTransaction->isCompleted()) {
                throw new \Exception('Only completed transactions can be reversed');
            }

            // Check if already reversed
            if ($originalTransaction->type === 'reversal') {
                throw new \Exception('Transaction already reversed');
            }

            // Create reversal transaction
            $reversal = Transaction::create([
                'tenant_id' => $this->tenantId,
                'transaction_reference' => $this->generateReference(),
                'type' => 'reversal',
                'status' => 'pending',
                'amount' => $originalTransaction->amount,
                'currency' => $originalTransaction->currency,
                'description' => 'Reversal: ' . $originalTransaction->description,
                'metadata' => [
                    'original_transaction_id' => $originalTransaction->id,
                    'original_reference' => $originalTransaction->transaction_reference,
                    'reason' => $reason,
                    'initiator' => Auth::user()->email,
                ],
                'initiated_by' => $this->userId,
                'initiated_at' => now(),
            ]);

            try {
                // Get original ledger entries
                $originalEntries = LedgerEntry::where('transaction_id', $originalTransaction->id)->get();

                foreach ($originalEntries as $entry) {
                    // Reverse the entry
                    $reversedType = $entry->entry_type === 'credit' ? 'debit' : 'credit';

                    LedgerEntry::create([
                        'tenant_id' => $this->tenantId,
                        'transaction_id' => $reversal->id,
                        'account_id' => $entry->account_id,
                        'entry_type' => $reversedType,
                        'amount' => $entry->amount,
                        'currency' => $entry->currency,
                        'balance_after' => $reversedType === 'credit'
                            ? $entry->account->current_balance + $entry->amount
                            : $entry->account->current_balance - $entry->amount,
                    ]);

                    // Update account balance
                    $account = $entry->account;
                    if ($reversedType === 'credit') {
                        $account->increment('current_balance', $entry->amount);
                        $account->increment('available_balance', $entry->amount);
                        // $account->increment('available_balance', $entry->amount);
                        // $account->increment('ledger_balance', $entry->amount);
                    } else {
                        $account->decrement('current_balance', $entry->amount);
                        $account->decrement('available_balance', $entry->amount);
                        // $account->decrement('available_balance', $entry->amount);
                        // $account->decrement('ledger_balance', $entry->amount);
                    }
                }

                // Mark original as reversed
                $originalTransaction->update([
                    'status' => 'reversed',
                    'reversed_at' => now(),
                ]);

                // Complete reversal transaction
                $reversal->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

                $this->logAudit($reversal, 'reversal_completed', [
                    'original_transaction_id' => $originalTransaction->id,
                    'reason' => $reason,
                ]);

                return $reversal;
            } catch (\Exception $e) {
                $reversal->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'failure_reason' => $e->getMessage(),
                ]);
                $this->logAudit($reversal, 'reversal_failed', [
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        });
    }

    /**
     * Validate transaction against limits
     */
    private function validateLimits(Account $account, float $amount, string $transactionType): void
    {
        // Skip validation for initial deposits
        if ($transactionType === 'initial_deposit') {
            return;
        }

        $limits = TransactionLimit::where('account_type_id', $account->account_type_id)
            ->where('transaction_type', $transactionType)
            ->where('is_active', true)
            ->get();

        foreach ($limits as $limit) {
            if ($limit->max_amount && $amount > $limit->max_amount) {
                throw new \Exception("Transaction amount exceeds limit of {$limit->max_amount}");
            }

            if ($limit->period && $limit->max_count) {
                $periodStart = $this->getPeriodStartDate($limit->period);
                $count = Transaction::where('type', $transactionType)
                    ->whereHas('ledgerEntries', function ($query) use ($account) {
                        $query->where('account_id', $account->id);
                    })
                    ->where('initiated_at', '>=', $periodStart)
                    ->count();

                if ($count >= $limit->max_count) {
                    throw new \Exception("Transaction count limit reached for {$limit->period} period");
                }
            }
        }
    }


    /**
     * Create double-entry ledger entries
     */
    private function createLedgerEntries(Transaction $transaction, Account $from, Account $to, float $amount): void
    {
        $minBalanceFromAcc = Account::where('id', $from->id)->latest();
        $minBalanceToAcc = Account::where('id', $to->id)->latest();
        // Debit from source account
        LedgerEntry::create([
            // 'tenant_id' => $this->tenantId,
            'transaction_id' => $transaction->id,
            'account_id' => $from->id,
            'entry_type' => 'debit',
            'amount' => $amount,
            'currency' => $from->currency,
            'balance_after' => $from->current_balance - $amount,
            'available_balance_after' => $from->current_balance - $amount - $minBalanceFromAcc->minimum_balance,
            'balance_before' => $from->current_balance,
        ]);

        // Credit to destination account
        LedgerEntry::create([
            // 'tenant_id' => $this->tenantId,
            'transaction_id' => $transaction->id,
            'account_id' => $to->id,
            'entry_type' => 'credit',
            'amount' => $amount,
            'currency' => $to->currency,
            'balance_after' => $to->current_balance + $amount,
            'available_balance_after' => $from->current_balance + $amount - $minBalanceToAcc->minimum_balance,
            'balance_before' => $to->current_balance,
        ]);
    }

    /**
     * Update account balances after transaction
     */
    private function updateAccountBalances(Account $from, Account $to, float $amount): void
    {
        // Update source account
        $from->decrement('current_balance', $amount);
        $from->decrement('available_balance', $amount);

        // Update ledger_balance if exists
        if (isset($from->ledger_balance)) {
            $from->decrement('ledger_balance', $amount);
        }

        // Update destination account
        $to->increment('current_balance', $amount);
        $to->increment('available_balance', $amount);

        // Update ledger_balance if exists
        if (isset($to->ledger_balance)) {
            $to->increment('ledger_balance', $amount);
        }
    }

    /**
     * Validate account status and permissions
     */
    private function validateAccount(int $accountId, string $operation): Account
    {
        $account = Account::findOrFail($accountId);

        // Check tenant - comment out if not using multi-tenancy
        // if ($account->tenant_id !== $this->tenantId) {
        //     throw new \Exception('Account not found');
        // }

        // Check if account is active
        if ($account->status !== 'active') {
            throw new \Exception('Account is not active');
        }

        // Check if account is frozen
        if ($account->status === 'frozen') {
            throw new \Exception('Account is frozen');
        }

        // Check if account is closed
        if ($account->status === 'closed') {
            throw new \Exception('Account is closed');
        }

        // Check minimum balance for debits (skip for initial deposits)
        if ($operation === 'debit' && isset($account->accountType->min_balance)) {
            $minBalance = $account->accountType->min_balance ?? 0;
            $projectedBalance = $account->current_balance - request('amount', 0);

            if ($projectedBalance < $minBalance) {
                throw new \Exception("Transaction would violate minimum balance requirement");
            }
        }

        return $account;
    }

    /**
     * Generate unique transaction reference
     */
    private function generateReference(): string
    {
        return 'TXN' . now()->format('YmdHis') . Str::random(6);
    }

    /**
     * Get period start date for limit checks
     */
    private function getPeriodStartDate(string $period): Carbon
    {
        return match ($period) {
            'daily' => now()->startOfDay(),
            'weekly' => now()->startOfWeek(),
            'monthly' => now()->startOfMonth(),
            'yearly' => now()->startOfYear(),
            default => now()->subDay(),
        };
    }

    /**
     * Get transaction history for an account
     */
    public function getAccountHistory(int $accountId, array $filters = [])
    {
        $query = Transaction::with(['ledgerEntries', 'initiator'])
            ->whereHas('ledgerEntries', function ($query) use ($accountId) {
                $query->where('account_id', $accountId);
            });

        // Apply filters
        if (!empty($filters['start_date'])) {
            $query->where('initiated_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('initiated_at', '<=', $filters['end_date']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['min_amount'])) {
            $query->where('amount', '>=', $filters['min_amount']);
        }

        if (!empty($filters['max_amount'])) {
            $query->where('amount', '<=', $filters['max_amount']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('transaction_reference', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('initiated_at', 'desc')->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Log audit entry
     */
    private function logAudit(Transaction $transaction, string $action, array $details = []): void
    {
        try {
            AuditLog::create([
                // 'tenant_id' => $this->tenantId,
                'user_id' => $this->userId,
                'action' => $action,
                'entity_type' => Transaction::class,
                'entity_id' => $transaction->id,
                'old_values' => [],
                'new_values' => $transaction->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => array_merge([
                    'transaction_reference' => $transaction->transaction_reference,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                ], $details),
            ]);
        } catch (\Exception $e) {
            // Log the error but don't fail the transaction because of audit logging
            Log::error('Failed to log audit: ' . $e->getMessage(), [
                'transaction_id' => $transaction->id,
                'action' => $action,
            ]);
        }
    }

    // In your account creation controller or service
    public function createAccountWithDeposit(array $accountData, ?array $depositData = null)
    {
        DB::transaction(function () use ($accountData, $depositData) {
            // Create the account
            $account = Account::create($accountData);

            // If deposit amount is provided, process initial deposit
            if ($depositData && $depositData['amount'] > 0) {
                $transactionService = app(TransactionService::class);

                $transactionData = [
                    'account_id' => $account->id,
                    'amount' => $depositData['amount'],
                    'currency' => $account->currency,
                    'description' => 'Initial account deposit',
                    'metadata' => [
                        'account' => $account->account_number,
                        'initiator' => Auth::user()->email,
                        'method' => $depositData['method'] ?? 'cash',
                        'branch_id' => Auth::user()->branch_id,
                        'teller_id' => Auth::id(),
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'description' => 'Initial deposit for new account',
                        'transaction_type' => 'initial_deposit',
                        'customer_verified' => true,
                        'verification_method' => 'signature',
                        'processed_by_teller' => true,
                    ],
                    'initiated_by' => Auth::id(),
                ];

                $transaction = $transactionService->initialDeposit($transactionData);

                // Update account balance (already done in transaction service, but just in case)
                $account->refresh();
            }

            return $account;
        });
    }
}
