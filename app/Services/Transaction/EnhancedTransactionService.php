<?php

namespace App\Services\Transaction;

use App\Models\Eloquent\Account;
use App\Models\Eloquent\AuditLog;
use App\Models\Eloquent\DailyBalance;
use App\Models\Eloquent\LedgerEntry;
use App\Models\Eloquent\SystemAccount;
use App\Models\Eloquent\SystemLedgerEntry;
use App\Models\Eloquent\Transaction;
use App\Models\Eloquent\User;
use App\Services\Teller\TellerLimitService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EnhancedTransactionService
{
    protected TellerLimitService $tellerLimitService;

    private $userId;
    private $branchId;

    public function __construct($userId = null, $branchId = null)
    {
        $user = Auth::user();

        $this->userId = $userId ?? ($user ? $user->id : null);
        $this->branchId = $branchId ?? ($user ? $user->branch_id : null);
    }

    /**
     * Process initial deposit with teller cash account
     */
    public function initialDeposit(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $account = Account::findOrFail($data['account_id']);
            // Get the specific teller's account for the current user
            $tellerAccount = $this->getCurrentTellerAccount();

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

                // Refresh account to get updated balance
                $account->refresh();
                /*
            |--------------------------------------------------------------------------
            | UPDATE DAILY BALANCE RECORD
            |--------------------------------------------------------------------------
            | Record today's closing balance so it can be used later to compute
            | the Average Daily Balance (ADB) for interest calculation.
            */

                $today = now()->toDateString();

                $dailyBalance = DailyBalance::where('account_id', $account->id)
                    ->whereDate('balance_date', $today)
                    ->first();

                if ($dailyBalance) {

                    // Update existing record for today
                    $dailyBalance->update([
                        'closing_balance' => $account->current_balance,
                    ]);
                } else {

                    // Create new daily balance record
                    DailyBalance::create([
                        'account_id' => $account->id,
                        'balance_date' => $today,
                        'opening_balance' => $data['amount'],
                        'closing_balance' => $data['amount'],
                    ]);
                }

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
            // Get the specific teller's account for the current user
            $tellerAccount = $this->getCurrentTellerAccount();

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

                // Refresh account to get updated balances
                $account->refresh();

                /*
            |--------------------------------------------------------------------------
            | UPDATE DAILY BALANCE RECORD
            |--------------------------------------------------------------------------
            | Record today's closing balance so it can be used later to compute
            | the Average Daily Balance (ADB) for interest calculation.
            */

                $today = now()->toDateString();

                $dailyBalance = DailyBalance::where('account_id', $account->id)
                    ->whereDate('balance_date', $today)
                    ->first();

                if ($dailyBalance) {

                    // Update existing record for today
                    $dailyBalance->update([
                        'closing_balance' => $account->current_balance,
                    ]);
                } else {

                    // Create new daily balance record
                    DailyBalance::create([
                        'account_id' => $account->id,
                        'balance_date' => $today,
                        'opening_balance' => $account->current_balance + $data['amount'],
                        'closing_balance' => $account->current_balance,
                    ]);
                }

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
     * Process cash deposit with teller cash account
     */
    public function cashDeposit(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $account = Account::findOrFail($data['account_id']);
            // Get the specific teller's account for the current user
            $tellerAccount = $this->getCurrentTellerAccount();

            $transaction = Transaction::create([
                'transaction_reference' => $this->generateReference('DEP'),
                'type' => 'cash_deposit',
                'status' => 'pending',
                'amount' => $data['amount'],
                'currency' => $account->currency,
                'description' => $data['description'] ?? 'Cash deposit',
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
                    'description' => 'Cash deposit credit',
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
                    'description' => 'Cash received for deposit',
                    'created_by' => $this->userId,
                ]);

                // Update customer account balance
                $account->increment('current_balance', $data['amount']);
                $account->increment('available_balance', $data['amount']);

                // Update teller account balance
                $tellerAccount->increment('balance', $data['amount']);

                // Refresh account to get updated balances
                $account->refresh();

                /*
            |--------------------------------------------------------------------------
            | UPDATE DAILY BALANCE RECORD
            |--------------------------------------------------------------------------
            | Record today's closing balance so it can be used later to compute
            | the Average Daily Balance (ADB) for interest calculation.
            */

                $today = now()->toDateString();

                $dailyBalance = DailyBalance::where('account_id', $account->id)
                    ->whereDate('balance_date', $today)
                    ->first();

                if ($dailyBalance) {

                    // Update existing record for today
                    $dailyBalance->update([
                        'closing_balance' => $account->current_balance,
                    ]);
                } else {

                    // Create new daily balance record
                    DailyBalance::create([
                        'account_id' => $account->id,
                        'balance_date' => $today,
                        'opening_balance' => $account->current_balance + $data['amount'],
                        'closing_balance' => $account->current_balance,
                    ]);
                }

                // Mark transaction as completed
                $transaction->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'completed_by' => $this->userId,
                ]);

                $this->logAudit($transaction, 'cash_deposit_completed', [
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
     * Process transfer between accounts
     */
    public function transfer(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $fromAccount = Account::findOrFail($data['from_account_id']);
            $toAccount = Account::findOrFail($data['to_account_id']);

            // Check if source account has sufficient funds
            if ($fromAccount->available_balance < $data['amount']) {
                throw new \Exception('Insufficient funds in source account');
            }

            $transaction = Transaction::create([
                'transaction_reference' => $this->generateReference('TRF'),
                'type' => 'transfer',
                'status' => 'pending',
                'amount' => $data['amount'],
                'currency' => $fromAccount->currency,
                'description' => $data['description'] ?? 'Fund transfer',
                'metadata' => $this->buildMetadata($data),
                'initiated_by' => $this->userId,
                'initiated_at' => now(),
                'source_account_id' => $fromAccount->id,
                'destination_account_id' => $toAccount->id,
                'branch_id' => $this->branchId,
            ]);

            try {
                // DOUBLE-ENTRY #1: Debit source account
                LedgerEntry::create([
                    'transaction_id' => $transaction->id,
                    'account_id' => $fromAccount->id,
                    'entry_type' => 'debit',
                    'amount' => $data['amount'],
                    'currency' => $fromAccount->currency,
                    'balance_before' => $fromAccount->current_balance,
                    'balance_after' => $fromAccount->current_balance - $data['amount'],
                    'available_balance_before' => $fromAccount->available_balance,
                    'available_balance_after' => $fromAccount->available_balance - $data['amount'],
                    'description' => 'Transfer debit',
                ]);

                // DOUBLE-ENTRY #2: Credit destination account
                LedgerEntry::create([
                    'transaction_id' => $transaction->id,
                    'account_id' => $toAccount->id,
                    'entry_type' => 'credit',
                    'amount' => $data['amount'],
                    'currency' => $toAccount->currency,
                    'balance_before' => $toAccount->current_balance,
                    'balance_after' => $toAccount->current_balance + $data['amount'],
                    'available_balance_before' => $toAccount->available_balance,
                    'available_balance_after' => $toAccount->available_balance + $data['amount'],
                    'description' => 'Transfer credit',
                ]);

                // Update balances
                $fromAccount->decrement('current_balance', $data['amount']);
                $fromAccount->decrement('available_balance', $data['amount']);
                $toAccount->increment('current_balance', $data['amount']);
                $toAccount->increment('available_balance', $data['amount']);

                // Mark transaction as completed
                $transaction->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'completed_by' => $this->userId,
                ]);

                $this->logAudit($transaction, 'transfer_completed', [
                    'from_account_id' => $fromAccount->id,
                    'to_account_id' => $toAccount->id,
                    'amount' => $data['amount'],
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
     * Charge monthly fee
     */
    // public function monthlyFee(Account $account, float $feeAmount): Transaction
    // {
    //     return DB::transaction(function () use ($account, $feeAmount) {
    //         $chargeAccount = SystemAccount::where('type', SystemAccount::TYPE_CHARGES)
    //             ->where('code', 'CHG-MAINT-001') 
    //             ->firstOrFail();

    //         $transaction = Transaction::create([
    //             'transaction_reference' => $this->generateReference('FEE'),
    //             'type' => 'fee_charge',
    //             'status' => 'pending',
    //             'amount' => $feeAmount,
    //             'currency' => $account->currency,
    //             'description' => 'Monthly account maintenance fee',
    //             'metadata' => [
    //                 'fee_type' => 'maintenance',
    //                 'charge_period' => now()->format('Y-m'),
    //                 'processed_by_system' => true,
    //             ],
    //             'initiated_by' => $this->userId,
    //             'initiated_at' => now(),
    //             'source_account_id' => $account->id,
    //         ]);

    //         try {
    //             // DOUBLE-ENTRY #1: Debit customer account
    //             LedgerEntry::create([
    //                 'transaction_id' => $transaction->id,
    //                 'account_id' => $account->id,
    //                 'entry_type' => 'debit',
    //                 'amount' => $feeAmount,
    //                 'currency' => $account->currency,
    //                 'balance_before' => $account->current_balance,
    //                 'balance_after' => $account->current_balance - $feeAmount,
    //                 'available_balance_before' => $account->available_balance,
    //                 'available_balance_after' => $account->available_balance - $feeAmount,
    //                 'description' => 'Monthly maintenance fee',
    //             ]);

    //             // DOUBLE-ENTRY #2: Credit charges income account
    //             SystemLedgerEntry::create([
    //                 'system_account_id' => $chargeAccount->id,
    //                 'transaction_id' => $transaction->id,
    //                 'entry_type' => 'credit',
    //                 'amount' => $feeAmount,
    //                 'currency' => $account->currency,
    //                 'balance_before' => $chargeAccount->balance,
    //                 'balance_after' => $chargeAccount->balance + $feeAmount,
    //                 'description' => 'Monthly maintenance fee income',
    //                 'created_by' => $this->userId,
    //             ]);

    //             // Update balances
    //             $account->decrement('current_balance', $feeAmount);
    //             $account->decrement('available_balance', $feeAmount);
    //             $chargeAccount->increment('balance', $feeAmount);

    //             $transaction->update([
    //                 'status' => 'completed',
    //                 'completed_at' => now(),
    //                 'completed_by' => $this->userId,
    //             ]);

    //             return $transaction;
    //         } catch (\Exception $e) {
    //             $transaction->update([
    //                 'status' => 'failed',
    //                 'failed_at' => now(),
    //                 'failure_reason' => $e->getMessage(),
    //             ]);
    //             throw $e;
    //         }
    //     });
    // }

    /**
     * Process monthly fee for an account
     */
    public function chargeMonthlyFee(Account $account, float $feeAmount, Carbon $processingMonth): Transaction
    {
        return DB::transaction(function () use ($account, $feeAmount, $processingMonth) {
            // Get the fee income system account
            $feeAccount = SystemAccount::where('type', SystemAccount::TYPE_FEE_INCOME)
                ->where('code', 'CHG-MONTHLY-001')
                ->firstOrFail();

            // Check if account has sufficient balance
            if ($account->available_balance < $feeAmount) {
                throw new \Exception('Insufficient funds to charge monthly fee');
            }

            $transaction = Transaction::create([
                'transaction_reference' => $this->generateReference('MFEE'),
                'type' => 'fee_charge',
                'status' => 'pending',
                'amount' => $feeAmount,
                'currency' => $account->currency,
                'description' => 'Monthly maintenance fee for ' . $processingMonth->format('F Y'),
                'metadata' => [
                    'fee_type' => 'maintenance',
                    'processing_month' => $processingMonth->format('Y-m'),
                    'account_type_id' => $account->account_type_id,
                    'account_type_name' => $account->accountType?->name,
                    'processed_by_system' => true,
                ],
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
                    'amount' => $feeAmount,
                    'currency' => $account->currency,
                    'balance_before' => $account->current_balance,
                    'balance_after' => $account->current_balance - $feeAmount,
                    'available_balance_before' => $account->available_balance,
                    'available_balance_after' => $account->available_balance - $feeAmount,
                    'description' => 'Monthly maintenance fee - ' . $processingMonth->format('F Y'),
                ]);

                // DOUBLE-ENTRY #2: Credit fee income account
                SystemLedgerEntry::create([
                    'system_account_id' => $feeAccount->id,
                    'transaction_id' => $transaction->id,
                    'entry_type' => 'credit',
                    'amount' => $feeAmount,
                    'currency' => $account->currency,
                    'balance_before' => $feeAccount->balance,
                    'balance_after' => $feeAccount->balance + $feeAmount,
                    'description' => 'Monthly maintenance fee income - ' . $processingMonth->format('F Y'),
                    'created_by' => $this->userId,
                ]);

                // Update balances
                $account->decrement('current_balance', $feeAmount);
                $account->decrement('available_balance', $feeAmount);
                $feeAccount->increment('balance', $feeAmount);

                // Refresh account to get updated balances
                $account->refresh();

                /*
            |--------------------------------------------------------------------------
            | UPDATE DAILY BALANCE RECORD
            |--------------------------------------------------------------------------
            | Record today's closing balance so it can be used later to compute
            | the Average Daily Balance (ADB) for interest calculation.
            */

                $today = now()->toDateString();

                $dailyBalance = DailyBalance::where('account_id', $account->id)
                    ->whereDate('balance_date', $today)
                    ->first();

                if ($dailyBalance) {

                    // Update existing record for today
                    $dailyBalance->update([
                        'closing_balance' => $account->current_balance,
                    ]);
                } else {

                    // Create new daily balance record
                    DailyBalance::create([
                        'account_id' => $account->id,
                        'balance_date' => $today,
                        'opening_balance' => $account->current_balance + $feeAmount,
                        'closing_balance' => $account->current_balance,
                    ]);
                }

                $transaction->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'completed_by' => $this->userId,
                ]);

                $this->logAudit($transaction, 'monthly_fee_charged', [
                    'account_id' => $account->id,
                    'account_number' => $account->account_number,
                    'amount' => $feeAmount,
                    'processing_month' => $processingMonth->format('Y-m'),
                    'balance_after' => $account->current_balance,
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
    // public function creditInterest(Account $account, float $interestAmount, string $type = 'deposit'): Transaction
    // {
    //     return DB::transaction(function () use ($account, $interestAmount, $type) {
    //         // Determine which system account to use
    //         $systemAccountType = $type === 'deposit'
    //             ? SystemAccount::TYPE_INTEREST_EXPENSE  // Interest paid on deposits
    //             : SystemAccount::TYPE_INTEREST_INCOME;   // Interest earned on loans

    //         $systemAccount = SystemAccount::where('type', $systemAccountType)
    //             ->where('currency', $account->currency)
    //             ->first();

    //         if (!$systemAccount) {
    //             $systemAccount = SystemAccount::where('type', $systemAccountType)->first();
    //         }

    //         $transaction = Transaction::create([
    //             'transaction_reference' => $this->generateReference('INT'),
    //             'type' => 'interest_credit',
    //             'status' => 'pending',
    //             'amount' => $interestAmount,
    //             'currency' => $account->currency,
    //             'description' => $type === 'deposit'
    //                 ? 'Interest credited to savings account'
    //                 : 'Interest charged on loan',
    //             'metadata' => [
    //                 'interest_type' => $type,
    //                 'calculation_period' => now()->format('Y-m'),
    //                 'rate' => $account->accountType->interest_rate ?? 0,
    //             ],
    //             'initiated_by' => $this->userId,
    //             'initiated_at' => now(),
    //             'destination_account_id' => $account->id,
    //         ]);

    //         try {
    //             if ($type === 'deposit') {
    //                 // Interest on deposits: Credit customer, Debit interest expense
    //                 LedgerEntry::create([
    //                     'transaction_id' => $transaction->id,
    //                     'account_id' => $account->id,
    //                     'entry_type' => 'credit',
    //                     'amount' => $interestAmount,
    //                     'currency' => $account->currency,
    //                     'balance_before' => $account->current_balance,
    //                     'balance_after' => $account->current_balance + $interestAmount,
    //                     'available_balance_before' => $account->available_balance,
    //                     'available_balance_after' => $account->available_balance + $interestAmount,
    //                     'description' => 'Interest credit',
    //                 ]);

    //                 SystemLedgerEntry::create([
    //                     'system_account_id' => $systemAccount->id,
    //                     'transaction_id' => $transaction->id,
    //                     'entry_type' => 'debit',
    //                     'amount' => $interestAmount,
    //                     'currency' => $account->currency,
    //                     'balance_before' => $systemAccount->balance,
    //                     'balance_after' => $systemAccount->balance - $interestAmount,
    //                     'description' => 'Interest expense on deposits',
    //                     'created_by' => $this->userId,
    //                 ]);

    //                 $account->increment('current_balance', $interestAmount);
    //                 $account->increment('available_balance', $interestAmount);
    //                 $systemAccount->decrement('balance', $interestAmount);
    //             } else {
    //                 // Interest on loans: Debit customer, Credit interest income
    //                 LedgerEntry::create([
    //                     'transaction_id' => $transaction->id,
    //                     'account_id' => $account->id,
    //                     'entry_type' => 'debit',
    //                     'amount' => $interestAmount,
    //                     'currency' => $account->currency,
    //                     'balance_before' => $account->current_balance,
    //                     'balance_after' => $account->current_balance - $interestAmount,
    //                     'available_balance_before' => $account->available_balance,
    //                     'available_balance_after' => $account->available_balance - $interestAmount,
    //                     'description' => 'Interest charge',
    //                 ]);

    //                 SystemLedgerEntry::create([
    //                     'system_account_id' => $systemAccount->id,
    //                     'transaction_id' => $transaction->id,
    //                     'entry_type' => 'credit',
    //                     'amount' => $interestAmount,
    //                     'currency' => $account->currency,
    //                     'balance_before' => $systemAccount->balance,
    //                     'balance_after' => $systemAccount->balance + $interestAmount,
    //                     'description' => 'Interest income on loans',
    //                     'created_by' => $this->userId,
    //                 ]);

    //                 $account->decrement('current_balance', $interestAmount);
    //                 $account->decrement('available_balance', $interestAmount);
    //                 $systemAccount->increment('balance', $interestAmount);
    //             }

    //             $transaction->update([
    //                 'status' => 'completed',
    //                 'completed_at' => now(),
    //                 'completed_by' => $this->userId,
    //             ]);

    //             return $transaction;
    //         } catch (\Exception $e) {
    //             $transaction->update([
    //                 'status' => 'failed',
    //                 'failed_at' => now(),
    //                 'failure_reason' => $e->getMessage(),
    //             ]);
    //             throw $e;
    //         }
    //     });
    // }

    public function creditMonthlyInterest(Account $account, float $interestAmount, Carbon $processingMonth): Transaction
    {
        return DB::transaction(function () use ($account, $interestAmount, $processingMonth) {
            // Get the interest expense system account
            $interestExpenseAccount = SystemAccount::where('type', SystemAccount::TYPE_INTEREST_EXPENSE)
                ->where('currency', $account->currency)
                ->firstOrFail();

            $transaction = Transaction::create([
                'transaction_reference' => $this->generateReference('MINT'),
                'type' => 'monthly_interest',
                'status' => 'pending',
                'amount' => $interestAmount,
                'currency' => $account->currency,
                'description' => 'Monthly interest credit for ' . $processingMonth->format('F Y'),
                'metadata' => [
                    'interest_type' => 'deposit',
                    'processing_month' => $processingMonth->format('Y-m'),
                    'account_type_id' => $account->account_type_id,
                    'account_type_name' => $account->accountType?->name,
                    'annual_rate' => $account->accountType?->interest_rate,
                    'monthly_rate' => $account->accountType?->interest_rate / 12,
                    'processed_by_system' => true,
                ],
                'initiated_by' => $this->userId,
                'initiated_at' => now(),
                'destination_account_id' => $account->id,
                'branch_id' => $this->branchId,
            ]);

            try {
                // DOUBLE-ENTRY #1: Credit customer account (interest earned)
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
                    'description' => 'Monthly interest credit - ' . $processingMonth->format('F Y'),
                ]);

                // DOUBLE-ENTRY #2: Debit interest expense account
                SystemLedgerEntry::create([
                    'system_account_id' => $interestExpenseAccount->id,
                    'transaction_id' => $transaction->id,
                    'entry_type' => 'debit',
                    'amount' => $interestAmount,
                    'currency' => $account->currency,
                    'balance_before' => $interestExpenseAccount->balance,
                    'balance_after' => $interestExpenseAccount->balance - $interestAmount,
                    'description' => 'Monthly interest expense - ' . $processingMonth->format('F Y'),
                    'created_by' => $this->userId,
                ]);

                // Update balances
                $account->increment('current_balance', $interestAmount);
                $account->increment('available_balance', $interestAmount);
                $interestExpenseAccount->decrement('balance', $interestAmount);

                // Refresh account to get updated balances
                $account->refresh();

                /*
            |--------------------------------------------------------------------------
            | UPDATE DAILY BALANCE RECORD
            |--------------------------------------------------------------------------
            | Record today's closing balance so it can be used later to compute
            | the Average Daily Balance (ADB) for interest calculation.
            */

                $today = now()->toDateString();

                $dailyBalance = DailyBalance::where('account_id', $account->id)
                    ->whereDate('balance_date', $today)
                    ->first();

                if ($dailyBalance) {

                    // Update existing record for today
                    $dailyBalance->update([
                        'closing_balance' => $account->current_balance,
                    ]);
                } else {

                    // Create new daily balance record
                    DailyBalance::create([
                        'account_id' => $account->id,
                        'balance_date' => $today,
                        'opening_balance' => $account->current_balance + $interestAmount,
                        'closing_balance' => $account->current_balance,
                    ]);
                }

                $transaction->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'completed_by' => $this->userId,
                ]);

                $this->logAudit($transaction, 'monthly_interest_credited', [
                    'account_id' => $account->id,
                    'account_number' => $account->account_number,
                    'amount' => $interestAmount,
                    'processing_month' => $processingMonth->format('Y-m'),
                    'annual_rate' => $account->accountType?->interest_rate,
                    'balance_after' => $account->current_balance,
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
            $tellerAccount = $this->getCurrentTellerAccount();

            $transaction = Transaction::create([
                'transaction_reference' => $this->generateReference('TOP'),
                'type' => 'teller_topup',
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
            $tellerAccount = $this->getCurrentTellerAccount();

            if ($tellerAccount->balance < $amount) {
                throw new \Exception('Insufficient cash in teller drawer');
            }

            $transaction = Transaction::create([
                'transaction_reference' => $this->generateReference('WDL'),
                'type' => 'teller_withdrawal',
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
     * Get the teller account for the currently authenticated user
     * This is used when a teller processes customer transactions
     */
    private function getCurrentTellerAccount(): SystemAccount
    {
        $userId = $this->userId;

        // Try to find teller account for current user
        $tellerAccount = SystemAccount::where('type', SystemAccount::TYPE_TELLER)
            ->where(function ($query) use ($userId) {
                $query->where('code', 'TELLER-' . str_pad($userId, 5, '0', STR_PAD_LEFT))
                    ->orWhere('metadata->user_id', $userId);
            })
            ->first();

        // If not found, create it
        if (!$tellerAccount) {
            $tellerAccount = $this->createTellerAccountForUser($userId);
        }

        return $tellerAccount;
    }

    /**
     * Get the main teller account (used for system-level operations)
     * This is kept for backward compatibility but not used for customer transactions
     */
    private function getMainTellerAccount(): SystemAccount
    {
        $tellerAccount = SystemAccount::where('type', SystemAccount::TYPE_TELLER)
            ->where('code', 'TELLER-CASH-001')
            ->first();

        if (!$tellerAccount) {
            throw new \Exception('Main teller account not configured. Please run system account migration.');
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

    /**
     * Process teller cash top-up for a specific teller (Manager/Super-admin action)
     */
    public function topUpTellerForUser(int $tellerId, float $amount, string $reference): Transaction
    {
        return DB::transaction(function () use ($tellerId, $amount, $reference) {
            // Get the teller's specific account
            $tellerAccount = $this->getTellerAccountForUser($tellerId);

            if (!$tellerAccount) {
                // Create teller account if it doesn't exist
                $tellerAccount = $this->createTellerAccountForUser($tellerId);
            }

            $teller = User::findOrFail($tellerId);

            $transaction = Transaction::create([
                'transaction_reference' => $this->generateReference('TOP'),
                'type' => 'teller_topup',
                'status' => 'completed',
                'amount' => $amount,
                'currency' => 'GHS',
                'description' => 'Teller cash top-up by manager',
                'metadata' => [
                    'reference' => $reference,
                    'teller_id' => $tellerId,
                    'teller_name' => $teller->full_name,
                    'manager_id' => $this->userId,
                    'branch_id' => $teller->branch_id,
                    'action' => 'manager_topup',
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
                'description' => 'Teller cash top-up by manager: ' . $reference,
                'created_by' => $this->userId,
            ]);

            $tellerAccount->increment('balance', $amount);

            $this->logAudit($transaction, 'teller_topup_by_manager', [
                'teller_id' => $tellerId,
                'amount' => $amount,
                'reference' => $reference,
                'new_balance' => $tellerAccount->balance,
            ]);

            return $transaction;
        });
    }

    /**
     * Process teller cash withdrawal for a specific teller (Manager/Super-admin action)
     */
    public function withdrawTellerCashForUser(int $tellerId, float $amount, string $reference): Transaction
    {
        return DB::transaction(function () use ($tellerId, $amount, $reference) {
            // Get the teller's specific account
            $tellerAccount = $this->getTellerAccountForUser($tellerId);

            if (!$tellerAccount) {
                throw new \Exception('Teller cash account not found for this user.');
            }

            $teller = User::findOrFail($tellerId);

            // Check sufficient balance
            if ($tellerAccount->balance < $amount) {
                throw new \Exception('Insufficient cash in teller drawer. Available: ' . number_format($tellerAccount->balance, 2));
            }

            $transaction = Transaction::create([
                'transaction_reference' => $this->generateReference('WTH'),
                'type' => 'teller_withdrawal',
                'status' => 'completed',
                'amount' => $amount,
                'currency' => 'GHS',
                'description' => 'Teller cash withdrawal by manager',
                'metadata' => [
                    'reference' => $reference,
                    'teller_id' => $tellerId,
                    'teller_name' => $teller->full_name,
                    'manager_id' => $this->userId,
                    'branch_id' => $teller->branch_id,
                    'action' => 'manager_withdrawal',
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
                'description' => 'Teller cash withdrawal by manager: ' . $reference,
                'created_by' => $this->userId,
            ]);

            $tellerAccount->decrement('balance', $amount);

            $this->logAudit($transaction, 'teller_withdrawal_by_manager', [
                'teller_id' => $tellerId,
                'amount' => $amount,
                'reference' => $reference,
                'new_balance' => $tellerAccount->balance,
            ]);

            return $transaction;
        });
    }

    /**
     * Get teller account for a specific user
     */
    private function getTellerAccountForUser(int $userId): ?SystemAccount
    {
        $teller = User::find($userId);

        if (!$teller) {
            return null;
        }

        // Try to find existing teller account
        $tellerAccount = SystemAccount::where('type', SystemAccount::TYPE_TELLER)
            ->where(function ($query) use ($userId) {
                $query->where('code', 'TELLER-' . str_pad($userId, 5, '0', STR_PAD_LEFT))
                    ->orWhere('metadata->user_id', $userId);
            })
            ->first();

        return $tellerAccount;
    }

    /**
     * Create teller account for a specific user
     */
    private function createTellerAccountForUser(int $userId): SystemAccount
    {
        $teller = User::findOrFail($userId);

        // Generate a unique code
        $code = 'TELLER-' . str_pad($userId, 5, '0', STR_PAD_LEFT);

        // Check if account already exists (avoid race condition)
        $existingAccount = SystemAccount::where('code', $code)->first();
        if ($existingAccount) {
            return $existingAccount;
        }

        // Create new teller account for this specific teller
        return SystemAccount::create([
            'type' => SystemAccount::TYPE_TELLER,
            'code' => $code,
            'name' => 'Teller Cash Account - ' . $teller->full_name . ' (ID: ' . $userId . ')',
            'balance' => 0,
            'currency' => 'GHS',
            'is_active' => 1,
            'metadata' => json_encode([
                'user_id' => $userId,
                'branch_id' => $teller->branch_id,
                'created_by' => $this->userId,
                'created_at' => now()->toIso8601String(),
            ]),
        ]);
    }
}
