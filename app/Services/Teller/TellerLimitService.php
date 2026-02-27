<?php

namespace App\Services\Teller;

use App\Models\Eloquent\User;
use App\Models\Eloquent\Account;
use App\Models\Eloquent\Transaction;
use App\Models\Eloquent\TransactionLimit;

class TellerLimitService
{
    /**
     * Check if teller can process transaction
     */
    public function canProcessTransaction(User $teller, float $amount, ?Account $account = null, ?string $transactionType = null): array
    {
        $result = [
            'can_process' => true,
            'requires_supervisor' => false,
            'reasons' => [],
            'limit_violations' => [],
        ];

        // Check if transaction type is excluded
        if ($this->isExcludedTransactionType($transactionType)) {
            return $result;
        }

        // Check teller's personal limit
        $tellerLimit = $this->getTellerLimit($teller);
        if ($amount > $tellerLimit) {
            $result['can_process'] = false;
            $result['requires_supervisor'] = true;
            $result['reasons'][] = "Amount exceeds teller limit of " . number_format($tellerLimit, 2);
            $result['limit_violations'][] = [
                'type' => 'teller_limit',
                'limit' => $tellerLimit,
                'amount' => $amount,
                'message' => "Exceeds personal teller limit"
            ];
        }

        // Check daily aggregate limit - FIXED: Use initiated_by instead of created_by
        $dailyUsage = $this->getTellerDailyUsage($teller);
        $dailyLimit = $this->getTellerDailyLimit($teller);
        if (($dailyUsage + $amount) > $dailyLimit) {
            $result['can_process'] = false;
            $result['requires_supervisor'] = true;
            $result['reasons'][] = "Daily transaction total would exceed daily limit of " . number_format($dailyLimit, 2);
            $result['limit_violations'][] = [
                'type' => 'daily_limit',
                'limit' => $dailyLimit,
                'current' => $dailyUsage,
                'proposed' => $dailyUsage + $amount,
                'message' => "Exceeds daily aggregate limit"
            ];
        }

        // Check account-specific transaction limits if account provided
        if ($account && $transactionType) {
            $accountLimitCheck = $this->checkAccountTransactionLimit($account, $amount, $transactionType);
            if (!$accountLimitCheck['allowed']) {
                $result['can_process'] = false;
                $result['requires_supervisor'] = true;
                $result['reasons'][] = $accountLimitCheck['reason'];
                $result['limit_violations'][] = $accountLimitCheck['violation'];
            }
        }

        return $result;
    }

    /**
     * Get teller's per-transaction limit
     */
    public function getTellerLimit(User $teller): float
    {
        // Check if user has custom limit set in database
        if (isset($teller->teller_limit) && $teller->teller_limit > 0) {
            return (float) $teller->teller_limit;
        }

        // Default limits based on role
        switch ($teller->role) {
            case 'supervisor':
            case 'manager':
                return 50000.00;
            case 'senior_teller':
                return 25000.00;
            case 'teller':
            default:
                return 10000.00;
        }
    }

    /**
     * Get teller's daily aggregate limit
     */
    public function getTellerDailyLimit(User $teller): float
    {
        // Check if user has custom daily limit set
        if (isset($teller->daily_teller_limit) && $teller->daily_teller_limit > 0) {
            return (float) $teller->daily_teller_limit;
        }

        // Default daily limits based on role
        switch ($teller->role) {
            case 'supervisor':
            case 'manager':
                return 200000.00;
            case 'senior_teller':
                return 100000.00;
            case 'teller':
            default:
                return 50000.00;
        }
    }

    /**
     * Get teller's today's total transaction amount
     * FIXED: Use initiated_by instead of created_by
     */
    public function getTellerDailyUsage(User $teller): float
    {
        return (float) Transaction::where('initiated_by', $teller->id)
            ->whereDate('created_at', today())
            ->whereIn('status', ['completed', 'pending'])
            ->sum('amount');
    }

    /**
     * Check account-specific transaction limits
     */
    public function checkAccountTransactionLimit(Account $account, float $amount, string $transactionType): array
    {
        $result = [
            'allowed' => true,
            'reason' => null,
            'violation' => null,
        ];

        if (!$account->accountType) {
            return $result;
        }

        // Find active limit for this account type and transaction type
        $limit = TransactionLimit::where('account_type_id', $account->account_type_id)
            ->where('transaction_type', $transactionType)
            ->where('period', 'per_transaction')
            ->where('is_active', true)
            ->first();

        if ($limit && $limit->max_amount && $amount > $limit->max_amount) {
            $result['allowed'] = false;
            $result['reason'] = "Amount exceeds per-transaction limit of " . number_format($limit->max_amount, 2) . " for {$transactionType}";
            $result['violation'] = [
                'type' => 'transaction_type_limit',
                'limit_id' => $limit->id,
                'limit' => $limit->max_amount,
                'amount' => $amount,
                'transaction_type' => $transactionType,
                'message' => $result['reason']
            ];
        }

        return $result;
    }

    /**
     * Get default supervisor for teller
     */
    public function getDefaultSupervisor(User $teller): ?User
    {
        // Check if teller has a default supervisor assigned
        if (isset($teller->supervisor_id) && $teller->supervisor_id) {
            return User::find($teller->supervisor_id);
        }

        // Otherwise, get first available supervisor from same branch
        return User::where('branch_id', $teller->branch_id)
            ->whereIn('role', ['supervisor', 'manager'])
            ->where('status', 'active')
            ->where('id', '!=', $teller->id)
            ->first();
    }

    /**
     * Check if transaction type is excluded from supervisor approval
     */
    public function isExcludedTransactionType(?string $transactionType): bool
    {
        $excludedTypes = ['initial_deposit', 'fee_collection'];
        return in_array($transactionType, $excludedTypes);
    }
}