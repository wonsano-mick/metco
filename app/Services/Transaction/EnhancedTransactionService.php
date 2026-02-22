<?php

namespace App\Services\Transaction;

use Illuminate\Support\Str;
use App\Models\Eloquent\Account;
use App\Models\Eloquent\SystemAccount;
use App\Models\Eloquent\AuditLog;
use App\Models\Eloquent\LedgerEntry;
use App\Models\Eloquent\SystemLedgerEntry;
use App\Models\Eloquent\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class EnhancedTransactionService
{
    private $tenantId;
    private $userId;
    private $branchId;

    public function __construct()
    {
        $this->userId = Auth::id();
        $this->branchId = Auth::user()->branch_id ?? null;
    }

    /**
     * Process initial deposit with teller cash account
     */
    public function initialDeposit(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $account = Account::findOrFail($data['account_id']);
            $tellerAccount = $this->getTellerAccount();

            // Create transaction record
            $transaction = Transaction::create([
                'transaction_reference' => $this->generateReference('INIT'),
                'type' => 'initial_deposit',
                'status' => 'pending',
                'amount' => $data['amount'],
                'currency' => $account->currency,
                'description' => $data['description'] ?? 'Initial account deposit',
                'metadata' => $this->buildMetadata($data),
                'initiated_by' => $this->userId,
                'initiated_at' => now(),
                'destination_account_id' => $account->id,
                'branch_id' => $this->branchId,
            ]);

            try {
                // DOUBLE-ENTRY #1: Credit customer account
                LedgerEntry::create([
                    'transaction_id' => $transaction->id,
                    'account_id' => $account->id,
                    'entry_type' => 'credit',
                    'amount' => $data['amount'],
                    'currency' => $account->currency,
                    'balance_before' => $account->current_balance,
                    'balance_after' => $account->current_balance + $data['amount'],
                    'available_balance_before' => $account->available_balance,
                    'available_balance_after' => $account->available_balance + $data['amount'],
                    'description' => 'Initial deposit credit',
                ]);

                // DOUBLE-ENTRY #2: Debit teller cash account (cash received)
                SystemLedgerEntry::create([
                    'system_account_id' => $tellerAccount->id,
                    'transaction_id' => $transaction->id,
                    'entry_type' => 'debit',
                    'amount' => $data['amount'],
                    'currency' => $account->currency,
                    'balance_before' => $tellerAccount->balance,
                    'balance_after' => $tellerAccount->balance + $data['amount'],
                    'description' => 'Cash received for initial deposit',
                    'created_by' => $this->userId,
                ]);

                // Update customer account balance
                $account->increment('current_balance', $data['amount']);
                $account->increment('available_balance', $data['amount']);

                // Update teller account balance
                $tellerAccount->increment('balance', $data['amount']);

                // Mark transaction as completed
                $transaction->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'completed_by' => $this->userId,
                ]);

                $this->logAudit($transaction, 'initial_deposit_completed', [
                    'account_id' => $account->id,
                    'amount' => $data['amount'],
                    'teller_balance' => $tellerAccount->balance,
                ]);

                return $transaction;
            } catch (\Exception $e) {
                $transaction->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'failure_reason' => $e->getMessage(),
                ]);
                throw $e;
            }
        });
    }

    /**
     * Process withdrawal with teller cash account
     */
    public function withdraw(array $data): Transaction 
    {
        return DB::transaction(function () use ($data) {
            $account = Account::findOrFail($data['account_id']);
            $tellerAccount = $this->getTellerAccount();

            // Check teller has sufficient cash
            if ($tellerAccount->balance < $data['amount']) {
                throw new \Exception('Insufficient cash in teller drawer. Available: ' . number_format($tellerAccount->balance, 2));
            }

            // Check customer has sufficient funds
            if ($account->available_balance < $data['amount']) {
                throw new \Exception('Insufficient funds in customer account');
            }

            $transaction = Transaction::create([
                'transaction_reference' => $this->generateReference('WDL'),
                'type' => 'withdrawal',
                'status' => 'pending',
                'amount' => $data['amount'],
                'currency' => $account->currency,
                'description' => $data['description'] ?? 'Cash withdrawal',
                'metadata' => $this->buildMetadata($data),
                'initiated_by' => $this->userId,
                'initiated_at' => now(),
                'source_account_id' => $account->id,
                'branch_id' => $this->branchId,
            ]);

            try {
                // DOUBLE-ENTRY #1: Debit customer account
                LedgerEntry::create([
                    'transaction_id' => $transaction->id,
                    'account_id' => $account->id,
                    'entry_type' => 'debit',
                    'amount' => $data['amount'],
                    'currency' => $account->currency,
                    'balance_before' => $account->current_balance,
                    'balance_after' => $account->current_balance - $data['amount'],
                    'available_balance_before' => $account->available_balance,
                    'available_balance_after' => $account->available_balance - $data['amount'],
                    'description' => 'Withdrawal debit',
                ]);

                // DOUBLE-ENTRY #2: Credit teller cash account (cash given out)
                SystemLedgerEntry::create([
                    'system_account_id' => $tellerAccount->id,
                    'transaction_id' => $transaction->id,
                    'entry_type' => 'credit',
                    'amount' => $data['amount'],
                    'currency' => $account->currency,
                    'balance_before' => $tellerAccount->balance,
                    'balance_after' => $tellerAccount->balance - $data['amount'],
                    'description' => 'Cash paid out for withdrawal',
                    'created_by' => $this->userId,
                ]);

                // Update balances
                $account->decrement('current_balance', $data['amount']);
                $account->decrement('available_balance', $data['amount']);
                $tellerAccount->decrement('balance', $data['amount']);

                $transaction->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'completed_by' => $this->userId,
                ]);

                $this->logAudit($transaction, 'withdrawal_completed', [
                    'account_id' => $account->id,
                    'amount' => $data['amount'],
                    'teller_balance' => $tellerAccount->balance,
                ]);

                return $transaction;
            } catch (\Exception $e) {
                $transaction->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'failure_reason' => $e->getMessage(),
                ]);
                throw $e;
            }
        });
    }

    /**
     * Charge monthly maintenance fee
     */
    public function chargeMonthlyMaintenanceFee(Account $account, float $feeAmount): Transaction
    {
        return DB::transaction(function () use ($account, $feeAmount) {
            $chargeAccount = SystemAccount::where('type', SystemAccount::TYPE_CHARGES)
                ->where('code', 'CHG-MAINT-001')
                ->firstOrFail();

            $transaction = Transaction::create([
                'transaction_reference' => $this->generateReference('FEE'),
                'type' => 'fee_charge',
                'status' => 'pending',
                'amount' => $feeAmount,
                'currency' => $account->currency,
                'description' => 'Monthly account maintenance fee',
                'metadata' => [
                    'fee_type' => 'maintenance',
                    'charge_period' => now()->format('Y-m'),
                    'processed_by_system' => true,
                ],
                'initiated_by' => $this->userId,
                'initiated_at' => now(),
                'source_account_id' => $account->id,
            ]);

            try {
                // DOUBLE-ENTRY #1: Debit customer account
                LedgerEntry::create([
                    'transaction_id' => $transaction->id,
                    'account_id' => $account->id,
                    'entry_type' => 'debit',
                    'amount' => $feeAmount,
                    'currency' => $account->currency,
                    'balance_before' => $account->current_balance,
                    'balance_after' => $account->current_balance - $feeAmount,
                    'available_balance_before' => $account->available_balance,
                    'available_balance_after' => $account->available_balance - $feeAmount,
                    'description' => 'Monthly maintenance fee',
                ]);

                // DOUBLE-ENTRY #2: Credit charges income account
                SystemLedgerEntry::create([
                    'system_account_id' => $chargeAccount->id,
                    'transaction_id' => $transaction->id,
                    'entry_type' => 'credit',
                    'amount' => $feeAmount,
                    'currency' => $account->currency,
                    'balance_before' => $chargeAccount->balance,
                    'balance_after' => $chargeAccount->balance + $feeAmount,
                    'description' => 'Monthly maintenance fee income',
                    'created_by' => $this->userId,
                ]);

                // Update balances
                $account->decrement('current_balance', $feeAmount);
                $account->decrement('available_balance', $feeAmount);
                $chargeAccount->increment('balance', $feeAmount);

                $transaction->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'completed_by' => $this->userId,
                ]);

                return $transaction;
            } catch (\Exception $e) {
                $transaction->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'failure_reason' => $e->getMessage(),
                ]);
                throw $e;
            }
        });
    }

    /**
     * Credit interest to customer account
     */
    public function creditInterest(Account $account, float $interestAmount, string $type = 'deposit'): Transaction
    {
        return DB::transaction(function () use ($account, $interestAmount, $type) {
            // Determine which system account to use
            $systemAccountType = $type === 'deposit'
                ? SystemAccount::TYPE_INTEREST_EXPENSE  // Interest paid on deposits
                : SystemAccount::TYPE_INTEREST_INCOME;   // Interest earned on loans

            $systemAccount = SystemAccount::where('type', $systemAccountType)
                ->where('currency', $account->currency)
                ->first();

            if (!$systemAccount) {
                $systemAccount = SystemAccount::where('type', $systemAccountType)->first();
            }

            $transaction = Transaction::create([
                'transaction_reference' => $this->generateReference('INT'),
                'type' => 'interest_credit',
                'status' => 'pending',
                'amount' => $interestAmount,
                'currency' => $account->currency,
                'description' => $type === 'deposit'
                    ? 'Interest credited to savings account'
                    : 'Interest charged on loan',
                'metadata' => [
                    'interest_type' => $type,
                    'calculation_period' => now()->format('Y-m'),
                    'rate' => $account->accountType->interest_rate ?? 0,
                ],
                'initiated_by' => $this->userId,
                'initiated_at' => now(),
                'destination_account_id' => $account->id,
            ]);

            try {
                if ($type === 'deposit') {
                    // Interest on deposits: Credit customer, Debit interest expense
                    LedgerEntry::create([
                        'transaction_id' => $transaction->id,
                        'account_id' => $account->id,
                        'entry_type' => 'credit',
                        'amount' => $interestAmount,
                        'currency' => $account->currency,
                        'balance_before' => $account->current_balance,
                        'balance_after' => $account->current_balance + $interestAmount,
                        'available_balance_before' => $account->available_balance,
                        'available_balance_after' => $account->available_balance + $interestAmount,
                        'description' => 'Interest credit',
                    ]);

                    SystemLedgerEntry::create([
                        'system_account_id' => $systemAccount->id,
                        'transaction_id' => $transaction->id,
                        'entry_type' => 'debit',
                        'amount' => $interestAmount,
                        'currency' => $account->currency,
                        'balance_before' => $systemAccount->balance,
                        'balance_after' => $systemAccount->balance - $interestAmount,
                        'description' => 'Interest expense on deposits',
                        'created_by' => $this->userId,
                    ]);

                    $account->increment('current_balance', $interestAmount);
                    $account->increment('available_balance', $interestAmount);
                    $systemAccount->decrement('balance', $interestAmount);
                } else {
                    // Interest on loans: Debit customer, Credit interest income
                    LedgerEntry::create([
                        'transaction_id' => $transaction->id,
                        'account_id' => $account->id,
                        'entry_type' => 'debit',
                        'amount' => $interestAmount,
                        'currency' => $account->currency,
                        'balance_before' => $account->current_balance,
                        'balance_after' => $account->current_balance - $interestAmount,
                        'available_balance_before' => $account->available_balance,
                        'available_balance_after' => $account->available_balance - $interestAmount,
                        'description' => 'Interest charge',
                    ]);

                    SystemLedgerEntry::create([
                        'system_account_id' => $systemAccount->id,
                        'transaction_id' => $transaction->id,
                        'entry_type' => 'credit',
                        'amount' => $interestAmount,
                        'currency' => $account->currency,
                        'balance_before' => $systemAccount->balance,
                        'balance_after' => $systemAccount->balance + $interestAmount,
                        'description' => 'Interest income on loans',
                        'created_by' => $this->userId,
                    ]);

                    $account->decrement('current_balance', $interestAmount);
                    $account->decrement('available_balance', $interestAmount);
                    $systemAccount->increment('balance', $interestAmount);
                }

                $transaction->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'completed_by' => $this->userId,
                ]);

                return $transaction;
            } catch (\Exception $e) {
                $transaction->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'failure_reason' => $e->getMessage(),
                ]);
                throw $e;
            }
        });
    }

    /**
     * Process teller cash top-up (when teller adds cash to drawer)
     */
    public function topUpTeller(float $amount, string $reference): Transaction
    {
        return DB::transaction(function () use ($amount, $reference) {
            $tellerAccount = $this->getTellerAccount();

            $transaction = Transaction::create([
                'transaction_reference' => $this->generateReference('TOP'),
                'type' => 'adjustment',
                'status' => 'completed',
                'amount' => $amount,
                'currency' => 'GHS',
                'description' => 'Teller cash top-up',
                'metadata' => [
                    'reference' => $reference,
                    'teller_id' => $this->userId,
                    'branch_id' => $this->branchId,
                ],
                'initiated_by' => $this->userId,
                'initiated_at' => now(),
                'completed_at' => now(),
                'completed_by' => $this->userId,
            ]);

            SystemLedgerEntry::create([
                'system_account_id' => $tellerAccount->id,
                'transaction_id' => $transaction->id,
                'entry_type' => 'debit',
                'amount' => $amount,
                'currency' => 'GHS',
                'balance_before' => $tellerAccount->balance,
                'balance_after' => $tellerAccount->balance + $amount,
                'description' => 'Teller cash top-up',
                'created_by' => $this->userId,
            ]);

            $tellerAccount->increment('balance', $amount);

            return $transaction;
        });
    }

    /**
     * Process teller cash withdrawal (teller removes cash from drawer)
     */
    public function withdrawTellerCash(float $amount, string $reference): Transaction
    {
        return DB::transaction(function () use ($amount, $reference) {
            $tellerAccount = $this->getTellerAccount();

            if ($tellerAccount->balance < $amount) {
                throw new \Exception('Insufficient cash in teller drawer');
            }

            $transaction = Transaction::create([
                'transaction_reference' => $this->generateReference('WDL'),
                'type' => 'adjustment',
                'status' => 'completed',
                'amount' => $amount,
                'currency' => 'GHS',
                'description' => 'Teller cash withdrawal',
                'metadata' => [
                    'reference' => $reference,
                    'teller_id' => $this->userId,
                    'branch_id' => $this->branchId,
                ],
                'initiated_by' => $this->userId,
                'initiated_at' => now(),
                'completed_at' => now(),
                'completed_by' => $this->userId,
            ]);

            SystemLedgerEntry::create([
                'system_account_id' => $tellerAccount->id,
                'transaction_id' => $transaction->id,
                'entry_type' => 'credit',
                'amount' => $amount,
                'currency' => 'GHS',
                'balance_before' => $tellerAccount->balance,
                'balance_after' => $tellerAccount->balance - $amount,
                'description' => 'Teller cash withdrawal',
                'created_by' => $this->userId,
            ]);

            $tellerAccount->decrement('balance', $amount);

            return $transaction;
        });
    }

    /**
     * Get or create teller account for current user/branch
     */
    private function getTellerAccount(): SystemAccount
    {
        $tellerAccount = SystemAccount::where('type', SystemAccount::TYPE_TELLER)
            ->where('code', 'TELLER-CASH-001')
            ->first();

        if (!$tellerAccount) {
            throw new \Exception('Teller account not configured. Please run system account migration.');
        }

        return $tellerAccount;
    }

    /**
     * Generate unique transaction reference
     */
    private function generateReference(string $prefix = 'TXN'): string
    {
        return $prefix . now()->format('YmdHis') . Str::random(4);
    }

    /**
     * Build metadata array
     */
    private function buildMetadata(array $data): array
    {
        return array_merge([
            'branch_id' => $this->branchId,
            'teller_id' => $this->userId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ], $data['metadata'] ?? []);
    }

    /**
     * Log audit entry
     */
    private function logAudit(Transaction $transaction, string $action, array $details = []): void
    {
        try {
            AuditLog::create([
                'user_id' => $this->userId,
                'action' => $action,
                'entity_type' => Transaction::class,
                'entity_id' => $transaction->id,
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
            Log::error('Failed to log audit: ' . $e->getMessage());
        }
    }
}
