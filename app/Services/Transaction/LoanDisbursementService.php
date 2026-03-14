<?php

namespace App\Services\Transaction;

use App\Models\Eloquent\Account;
use App\Models\Eloquent\AuditLog;
use App\Models\Eloquent\DailyBalance;
use App\Models\Eloquent\LedgerEntry;
use App\Models\Eloquent\Loan;
use App\Models\Eloquent\SystemAccount;
use App\Models\Eloquent\SystemLedgerEntry;
use App\Models\Eloquent\Transaction;
use App\Services\Transaction\EnhancedTransactionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class LoanDisbursementService
{
    protected EnhancedTransactionService $transactionService;

    public function __construct(EnhancedTransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function disburse(Loan $loan, array $disbursementData): Transaction
    {
        return DB::transaction(function () use ($loan, $disbursementData) {
            // Get system accounts
            $loanPayableAccount = SystemAccount::where('code', 'LOAN-PAY-001')->first();

            if (!$loanPayableAccount) {
                throw new \Exception('Loan Payable Account (LOAN-PAY-001) not found. Please run the system account seeder first.');
            }

            // Get the system account for disbursement (for accounting)
            $disbursementSystemAccount = $this->getDisbursementSystemAccount($disbursementData['method']);

            // Get or create a regular Account record for the foreign key constraint
            $disbursementAccount = $this->getOrCreateDisbursementAccount(
                $disbursementData['method'],
                $disbursementSystemAccount
            );

            // Create transaction - IMPORTANT: Set the loan_id directly if it exists in the transactions table
            $transactionData = [
                'transaction_reference' => 'LNDISB' . time() . mt_rand(1000, 9999),
                'type' => 'loan_disbursement',
                'status' => 'pending',
                'amount' => $loan->amount,
                'currency' => 'GHS',
                'description' => 'Loan disbursement for ' . $loan->loan_number,
                'metadata' => [
                    'loan_id' => $loan->id,
                    'customer_id' => $loan->customer_id,
                    'disbursement_method' => $disbursementData['method'],
                    'disbursement_details' => $disbursementData,
                    'system_account_id' => $disbursementSystemAccount->id,
                ],
                'initiated_by' => Auth::id(),
                'initiated_at' => now(),
                'branch_id' => $loan->branch_id,
            ];

            // If there's a loan_id column in transactions table, add it
            // Check your database schema - if transactions has a loan_id column, uncomment this line
            // $transactionData['loan_id'] = $loan->id;

            $transaction = Transaction::create($transactionData);

            try {
                // Get current balances
                $customerAccountBalance = $loan->account ? $loan->account->current_balance : 0;
                $customerAvailableBalance = $loan->account ? $loan->account->available_balance : 0;

                // Entry 1: Debit Loan Receivable (Customer account)
                LedgerEntry::create([
                    'transaction_id' => $transaction->id,
                    'account_id' => $loan->account_id,
                    'entry_type' => 'debit',
                    'amount' => $loan->amount,
                    'currency' => 'GHS',
                    'balance_before' => $customerAccountBalance,
                    'balance_after' => $customerAccountBalance + $loan->amount,
                    'available_balance_before' => $customerAvailableBalance,
                    'available_balance_after' => $customerAvailableBalance + $loan->amount,
                    'description' => 'Loan disbursement - receivable from customer',
                ]);

                // Entry 2: Credit Loan Payable (Liability)
                SystemLedgerEntry::create([
                    'system_account_id' => $loanPayableAccount->id,
                    'transaction_id' => $transaction->id,
                    'entry_type' => 'credit',
                    'amount' => $loan->amount,
                    'currency' => 'GHS',
                    'balance_before' => $loanPayableAccount->balance,
                    'balance_after' => $loanPayableAccount->balance + $loan->amount,
                    'description' => 'Loan payable - funds to be disbursed',
                    'created_by' => Auth::id(),
                ]);

                // Entry 3: Debit Loan Payable (Liability) - reduction
                SystemLedgerEntry::create([
                    'system_account_id' => $loanPayableAccount->id,
                    'transaction_id' => $transaction->id,
                    'entry_type' => 'debit',
                    'amount' => $loan->amount,
                    'currency' => 'GHS',
                    'balance_before' => $loanPayableAccount->balance + $loan->amount,
                    'balance_after' => $loanPayableAccount->balance,
                    'description' => 'Loan payable - cash disbursement',
                    'created_by' => Auth::id(),
                ]);

                // Entry 4: Credit Cash/Bank Account (System Account)
                SystemLedgerEntry::create([
                    'system_account_id' => $disbursementSystemAccount->id,
                    'transaction_id' => $transaction->id,
                    'entry_type' => 'credit',
                    'amount' => $loan->amount,
                    'currency' => 'GHS',
                    'balance_before' => $disbursementSystemAccount->balance,
                    'balance_after' => $disbursementSystemAccount->balance - $loan->amount,
                    'description' => 'Cash disbursement for loan',
                    'created_by' => Auth::id(),
                ]);

                // Update balances
                if ($loan->account) {
                    $loan->account->increment('current_balance', $loan->amount);
                    $loan->account->increment('available_balance', $loan->amount);
                }

                // Update system account balance
                $disbursementSystemAccount->decrement('balance', $loan->amount);

                // Update loan - use the regular Account ID, not SystemAccount ID
                $loan->update([
                    'status' => 'disbursed',
                    'disbursement_date' => now(),
                    'disbursed_at' => now(),
                    'disbursement_account_id' => $disbursementAccount->id, // This is from accounts table
                    'disbursement_method' => $disbursementData['method'],
                    'disbursement_reference' => $this->getDisbursementReference($disbursementData),
                ]);

                // Update daily balance
                if ($loan->account) {
                    $this->updateDailyBalance($loan->account);
                }

                // Complete transaction
                $transaction->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'completed_by' => Auth::id(),
                ]);

                // Instead of attach(), update the transaction with loan_id if needed
                // If your transactions table has a loan_id column, update it here
                if (Schema::hasColumn('transactions', 'loan_id')) {
                    $transaction->update(['loan_id' => $loan->id]);
                }

                // Audit log
                $this->logAudit($transaction, $loan);

                return $transaction;
            } catch (\Exception $e) {
                $transaction->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'failure_reason' => $e->getMessage(),
                ]);

                Log::error('Loan disbursement failed: ' . $e->getMessage(), [
                    'loan_id' => $loan->id,
                    'transaction_id' => $transaction->id,
                    'error' => $e->getMessage(),
                ]);

                throw $e;
            }
        });
    }

    /**
     * Get the system account for accounting entries
     */
    protected function getDisbursementSystemAccount(string $method): SystemAccount
    {
        $accountCode = match ($method) {
            'bank_transfer' => 'BANK-001',
            'cash' => 'CASH-001',
            'cheque' => 'CHEQUE-001',
            'mobile_money' => 'MM-001',
            default => 'CASH-001'
        };

        $account = SystemAccount::where('code', $accountCode)->first();

        if (!$account) {
            throw new \Exception("Disbursement system account with code {$accountCode} not found. Please run the system account seeder first.");
        }

        return $account;
    }

    /**
     * Get or create a regular Account record for the foreign key constraint
     */
    protected function getOrCreateDisbursementAccount(string $method, SystemAccount $systemAccount): Account
    {
        $accountNumber = match ($method) {
            'bank_transfer' => 'DISB-BANK-001',
            'cash' => 'DISB-CASH-001',
            'cheque' => 'DISB-CHEQUE-001',
            'mobile_money' => 'DISB-MM-001',
            default => 'DISB-CASH-001'
        };

        // Try to find existing account
        $account = Account::where('account_number', $accountNumber)->first();

        if (!$account) {
            // Create a new account record in the accounts table
            $account = Account::create([
                'account_number' => $accountNumber,
                'account_type' => 'disbursement',
                'current_balance' => 0,
                'available_balance' => 0,
                'currency' => 'GHS',
                'status' => 'active',
                'metadata' => json_encode([
                    'type' => 'disbursement_account',
                    'method' => $method,
                    'system_account_id' => $systemAccount->id,
                    'system_account_code' => $systemAccount->code,
                    'system_account_name' => $systemAccount->name,
                    'description' => 'Disbursement account for ' . $method . ' transactions',
                ]),
            ]);

            Log::info('Created disbursement account', [
                'account_id' => $account->id,
                'account_number' => $accountNumber,
                'method' => $method
            ]);
        }

        return $account;
    }

    protected function getDisbursementReference(array $data): string
    {
        return $data['cheque_number'] ??
            $data['mobile_money_number'] ??
            ($data['method'] === 'cash' ? 'CASH' : 'TRANSFER');
    }

    protected function updateDailyBalance(?Account $account): void
    {
        if (!$account) return;

        $today = now()->toDateString();

        $dailyBalance = DailyBalance::where('account_id', $account->id)
            ->whereDate('balance_date', $today)
            ->first();

        if ($dailyBalance) {
            $dailyBalance->update(['closing_balance' => $account->current_balance]);
        } else {
            DailyBalance::create([
                'account_id' => $account->id,
                'balance_date' => $today,
                'opening_balance' => $account->current_balance - ($loan->amount ?? 0),
                'closing_balance' => $account->current_balance,
            ]);
        }
    }

    protected function logAudit(Transaction $transaction, Loan $loan): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'loan_disbursement_completed',
            'entity_type' => Loan::class,
            'entity_id' => $loan->id,
            'new_values' => $loan->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => [
                'transaction_reference' => $transaction->transaction_reference,
                'amount' => $loan->amount,
                'loan_number' => $loan->loan_number,
            ],
        ]);
    }
}
