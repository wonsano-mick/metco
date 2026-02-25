<?php

namespace App\Services\Transaction;

use App\Models\Eloquent\Transaction;
use App\Models\Eloquent\User;
use App\Models\Eloquent\SupervisorApproval;
use App\Services\Teller\TellerLimitService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TellerTransactionService extends EnhancedTransactionService
{
    protected TellerLimitService $tellerLimitService;

    /**
     * Constructor - pass required parameters to parent
     */
    public function __construct(TellerLimitService $tellerLimitService)
    {
        // Call parent constructor with any required parameters
        // Based on your EnhancedTransactionService, it might need user and branch info
        parent::__construct();

        $this->tellerLimitService = $tellerLimitService;
    }

    /**
     * Process withdrawal with supervisor approval tracking
     */
    public function processWithdrawal(array $data, User $teller, ?array $supervisorData = null): Transaction
    {
        return DB::transaction(function () use ($data, $teller, $supervisorData) {
            // Create the transaction using parent method
            $transaction = parent::withdraw($data);

            // Log supervisor approval if provided
            if ($supervisorData && isset($supervisorData['supervisor_id'])) {
                $this->logSupervisorApproval(
                    $transaction,
                    $teller,
                    User::find($supervisorData['supervisor_id']),
                    $supervisorData['reason'] ?? 'Supervisor approval required',
                    $supervisorData['metadata'] ?? []
                );
            }

            return $transaction;
        });
    }

    /**
     * Process cash deposit with supervisor approval tracking
     * FIXED: Use cashDeposit() instead of deposit()
     */
    public function processCashDeposit(array $data, User $teller, ?array $supervisorData = null): Transaction
    {
        return DB::transaction(function () use ($data, $teller, $supervisorData) {
            // Use cashDeposit method from parent
            $transaction = parent::cashDeposit($data);

            // Log supervisor approval if provided
            if ($supervisorData && isset($supervisorData['supervisor_id'])) {
                $this->logSupervisorApproval(
                    $transaction,
                    $teller,
                    User::find($supervisorData['supervisor_id']),
                    $supervisorData['reason'] ?? 'Supervisor approval required',
                    $supervisorData['metadata'] ?? []
                );
            }

            return $transaction;
        });
    }

    /**
     * Process cheque deposit with supervisor approval tracking
     * ADDED: Handle cheque deposits separately
     */
    public function processChequeDeposit(array $data, User $teller, ?array $supervisorData = null): Transaction
    {
        return DB::transaction(function () use ($data, $teller, $supervisorData) {
            // You might need to add a chequeDeposit method to EnhancedTransactionService
            // For now, we'll use cashDeposit as a placeholder or throw an exception
            throw new \Exception('Cheque deposit method not implemented in EnhancedTransactionService');
        });
    }

    /**
     * Process transfer with supervisor approval tracking
     */
    public function processTransfer(array $data, User $teller, ?array $supervisorData = null): Transaction
    {
        return DB::transaction(function () use ($data, $teller, $supervisorData) {
            $transaction = parent::transfer($data);

            // Log supervisor approval if provided
            if ($supervisorData && isset($supervisorData['supervisor_id'])) {
                $this->logSupervisorApproval(
                    $transaction,
                    $teller,
                    User::find($supervisorData['supervisor_id']),
                    $supervisorData['reason'] ?? 'Supervisor approval required',
                    $supervisorData['metadata'] ?? []
                );
            }

            return $transaction;
        });
    }

    /**
     * Process initial deposit with supervisor approval tracking
     */
    public function processInitialDeposit(array $data, User $teller, ?array $supervisorData = null): Transaction
    {
        return DB::transaction(function () use ($data, $teller, $supervisorData) {
            $transaction = parent::initialDeposit($data);

            // Log supervisor approval if provided
            if ($supervisorData && isset($supervisorData['supervisor_id'])) {
                $this->logSupervisorApproval(
                    $transaction,
                    $teller,
                    User::find($supervisorData['supervisor_id']),
                    $supervisorData['reason'] ?? 'Supervisor approval required',
                    $supervisorData['metadata'] ?? []
                );
            }

            return $transaction;
        });
    }

    /**
     * Generic deposit handler that routes to the appropriate method
     */
    public function processDeposit(array $data, User $teller, ?array $supervisorData = null): Transaction
    {
        $transactionType = $data['transaction_type'] ?? 'cash_deposit';

        switch ($transactionType) {
            case 'cash_deposit':
                return $this->processCashDeposit($data, $teller, $supervisorData);
            case 'cheque_deposit':
                return $this->processChequeDeposit($data, $teller, $supervisorData);
            default:
                return $this->processCashDeposit($data, $teller, $supervisorData);
        }
    }

    /**
     * Log supervisor approval to database
     */
    private function logSupervisorApproval(
        Transaction $transaction,
        User $teller,
        User $supervisor,
        string $reason,
        array $metadata = []
    ): void {
        try {
            SupervisorApproval::create([
                'transaction_id' => $transaction->id,
                'teller_id' => $teller->id,
                'supervisor_id' => $supervisor->id,
                'amount' => $transaction->amount,
                'reason' => $reason,
                'status' => 'approved',
                'approved_at' => now(),
                'metadata' => array_merge($metadata, [
                    'teller_limit_at_time' => $this->tellerLimitService->getTellerLimit($teller),
                    'daily_usage_before' => Transaction::where('initiated_by', $teller->id)
                        ->whereDate('created_at', today())
                        ->whereIn('status', ['completed', 'pending'])
                        ->where('id', '!=', $transaction->id)
                        ->sum('amount'),
                ]),
            ]);

            Log::info('Supervisor approval logged', [
                'transaction_id' => $transaction->id,
                'teller_id' => $teller->id,
                'supervisor_id' => $supervisor->id,
                'amount' => $transaction->amount,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log supervisor approval', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->id,
            ]);
        }
    }
}
