<?php

namespace App\Services\Transaction;

use App\Models\Eloquent\Account;
use App\Models\Eloquent\AccountFee;
use App\Models\Eloquent\FeeConfiguration;
use App\Models\Eloquent\SystemAccount;
use App\Models\Eloquent\Transaction;
use App\Models\Eloquent\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AutomatedFeeService
{
    protected EnhancedTransactionService $transactionService;
    protected ?int $userId;
    protected ?int $branchId;

    public function __construct(?int $userId = null, ?int $branchId = null)
    {
        $this->transactionService = new EnhancedTransactionService($userId, $branchId);
        $this->userId = $userId;
        $this->branchId = $branchId;
    }

    /**
     * Generate pending fees for all eligible accounts
     */
    public function generatePendingFees(Carbon $chargeDate): array
    {
        $results = [
            'total_processed' => 0,
            'total_fees_generated' => 0,
            'total_amount' => 0,
            'accounts_processed' => [],
            'errors' => []
        ];

        // Get all active fee configurations
        $configurations = FeeConfiguration::active()->get();

        foreach ($configurations as $config) {
            try {
                $configResults = $this->generateFeesForConfiguration($config, $chargeDate);
                
                $results['total_processed'] += $configResults['processed'];
                $results['total_fees_generated'] += $configResults['fees_generated'];
                $results['total_amount'] += $configResults['total_amount'];
                $results['accounts_processed'] = array_merge(
                    $results['accounts_processed'],
                    $configResults['accounts']
                );
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'configuration' => $config->code,
                    'error' => $e->getMessage()
                ];
                Log::error('Fee generation failed for config: ' . $config->code, [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return $results;
    }

    /**
     * Generate pending fees for a specific configuration
     */
    protected function generateFeesForConfiguration(FeeConfiguration $config, Carbon $chargeDate): array
    {
        $results = [
            'processed' => 0,
            'fees_generated' => 0,
            'total_amount' => 0,
            'accounts' => []
        ];

        // Get all active accounts of this type
        $accounts = Account::where('account_type_id', $config->account_type_id)
            ->active()
            ->where('currency', $config->currency)
            ->get();

        $periodStart = $this->getPeriodStart($config->frequency, $chargeDate);
        $periodEnd = $this->getPeriodEnd($config->frequency, $chargeDate);

        foreach ($accounts as $account) {
            $results['processed']++;

            try {
                // Check if fee already exists for this period
                $existingFee = AccountFee::where('account_id', $account->id)
                    ->where('fee_configuration_id', $config->id)
                    ->where('period_start', $periodStart)
                    ->where('period_end', $periodEnd)
                    ->first();

                if ($existingFee) {
                    continue; // Skip if already generated
                }

                // Check if fee should be waived
                if ($this->shouldWaiveFee($account, $config)) {
                    $this->createWaivedFeeRecord($account, $config, $periodStart, $periodEnd, $chargeDate);
                    continue;
                }

                // Calculate fee amount
                $feeAmount = $this->calculateFeeAmount($account, $config);

                // Create pending fee record
                $accountFee = AccountFee::create([
                    'account_id' => $account->id,
                    'fee_configuration_id' => $config->id,
                    'fee_reference' => $this->generateFeeReference($account, $config),
                    'amount' => $feeAmount,
                    'currency' => $account->currency,
                    'status' => AccountFee::STATUS_PENDING,
                    'period_type' => $config->frequency,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'charge_date' => $chargeDate,
                    'balance_before' => $account->current_balance,
                    'metadata' => [
                        'fee_configuration' => $config->only(['code', 'name', 'calculation_method']),
                        'generated_at' => now()->toIso8601String(),
                    ]
                ]);

                $results['fees_generated']++;
                $results['total_amount'] += $feeAmount;
                $results['accounts'][] = [
                    'account_id' => $account->id,
                    'account_number' => $account->account_number,
                    'fee_id' => $accountFee->id,
                    'amount' => $feeAmount
                ];

            } catch (\Exception $e) {
                Log::error('Failed to generate fee for account: ' . $account->id, [
                    'error' => $e->getMessage(),
                    'account_id' => $account->id,
                    'config_id' => $config->id
                ]);
            }
        }

        return $results;
    }

    /**
     * Process pending fees for a specific date
     */
    public function processPendingFees(Carbon $processDate): array
    {
        $results = [
            'total_processed' => 0,
            'total_amount' => 0,
            'successful' => 0,
            'failed' => 0,
            'waived' => 0,
            'transactions' => [],
            'errors' => []
        ];

        // Get all pending fees for the charge date
        $pendingFees = AccountFee::where('status', AccountFee::STATUS_PENDING)
            ->where('charge_date', $processDate->toDateString())
            ->with(['account', 'feeConfiguration'])
            ->get();

        // Get charge system account
        $chargeAccount = SystemAccount::where('type', SystemAccount::TYPE_CHARGES)
            ->where('code', 'CHG-MAINT-001')
            ->firstOrFail();

        foreach ($pendingFees as $accountFee) {
            try {
                DB::transaction(function () use ($accountFee, $chargeAccount, &$results) {
                    $account = $accountFee->account;

                    // Double-check if fee should be waived (in case balance changed)
                    if ($this->shouldWaiveFee($account, $accountFee->feeConfiguration)) {
                        $accountFee->update([
                            'status' => AccountFee::STATUS_WAIVED,
                            'waived' => true,
                            'waiver_reason' => 'Minimum balance met at processing time'
                        ]);
                        $results['waived']++;
                        return;
                    }

                    // Check if account has sufficient balance
                    if ($account->available_balance < $accountFee->amount) {
                        throw new \Exception('Insufficient funds for fee charge. Available: ' . 
                            number_format($account->available_balance, 2) . 
                            ', Required: ' . number_format($accountFee->amount, 2));
                    }

                    // Process fee using enhanced transaction service
                    $transaction = $this->transactionService->chargeMonthlyMaintenanceFee(
                        $account,
                        $accountFee->amount
                    );

                    // Update fee record
                    $accountFee->update([
                        'transaction_id' => $transaction->id,
                        'status' => AccountFee::STATUS_PROCESSED,
                        'processed_at' => now(),
                        'balance_before' => $account->current_balance + $accountFee->amount, // Balance before fee
                        'balance_after' => $account->current_balance,
                        'metadata' => array_merge($accountFee->metadata ?? [], [
                            'processed_at' => now()->toIso8601String(),
                            'transaction_reference' => $transaction->transaction_reference,
                        ])
                    ]);

                    $results['successful']++;
                    $results['total_amount'] += $accountFee->amount;
                    $results['transactions'][] = [
                        'fee_id' => $accountFee->id,
                        'transaction_id' => $transaction->id,
                        'reference' => $transaction->transaction_reference,
                        'account_id' => $account->id,
                        'amount' => $accountFee->amount
                    ];
                });

            } catch (\Exception $e) {
                $accountFee->update([
                    'status' => AccountFee::STATUS_FAILED,
                    'failure_reason' => $e->getMessage()
                ]);

                $results['failed']++;
                $results['errors'][] = [
                    'fee_id' => $accountFee->id,
                    'account_id' => $accountFee->account_id,
                    'error' => $e->getMessage()
                ];

                Log::error('Fee processing failed for fee: ' . $accountFee->id, [
                    'error' => $e->getMessage(),
                    'fee_id' => $accountFee->id
                ]);
            }

            $results['total_processed']++;
        }

        return $results;
    }

    /**
     * Check if fee should be waived based on minimum balance
     */
    protected function shouldWaiveFee(Account $account, FeeConfiguration $config): bool
    {
        if (!$config->has_minimum_balance_waiver) {
            return false;
        }

        return $account->current_balance >= $config->minimum_balance_threshold;
    }

    /**
     * Calculate fee amount based on configuration
     */
    protected function calculateFeeAmount(Account $account, FeeConfiguration $config): float
    {
        switch ($config->calculation_method) {
            case 'fixed':
                return (float) $config->fee_amount;

            case 'percentage':
                return round($account->current_balance * ($config->percentage_rate / 100), 4);

            case 'tiered':
                return $this->calculateTieredFee($account, $config);

            default:
                return (float) $config->fee_amount;
        }
    }

    /**
     * Calculate tiered fee based on balance tiers
     */
    protected function calculateTieredFee(Account $account, FeeConfiguration $config): float
    {
        $tiers = $config->tiers ?? [];
        $balance = $account->current_balance;

        foreach ($tiers as $tier) {
            if ($balance >= ($tier['min'] ?? 0) && $balance <= ($tier['max'] ?? PHP_FLOAT_MAX)) {
                return (float) ($tier['fee'] ?? $config->fee_amount);
            }
        }

        return (float) $config->fee_amount;
    }

    /**
     * Create waived fee record
     */
    protected function createWaivedFeeRecord(
        Account $account,
        FeeConfiguration $config,
        Carbon $periodStart,
        Carbon $periodEnd,
        Carbon $chargeDate
    ): AccountFee {
        return AccountFee::create([
            'account_id' => $account->id,
            'fee_configuration_id' => $config->id,
            'fee_reference' => $this->generateFeeReference($account, $config, 'WAIVED'),
            'amount' => $config->fee_amount,
            'currency' => $account->currency,
            'status' => AccountFee::STATUS_WAIVED,
            'period_type' => $config->frequency,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'charge_date' => $chargeDate,
            'waived' => true,
            'waiver_reason' => 'Minimum balance threshold met',
            'balance_before' => $account->current_balance,
            'metadata' => [
                'fee_configuration' => $config->only(['code', 'name']),
                'minimum_balance_threshold' => $config->minimum_balance_threshold,
                'waived_at' => now()->toIso8601String(),
            ]
        ]);
    }

    /**
     * Generate unique fee reference
     */
    protected function generateFeeReference(Account $account, FeeConfiguration $config, string $suffix = ''): string
    {
        $prefix = 'FEE';
        $accountPart = str_pad($account->id, 8, '0', STR_PAD_LEFT);
        $configPart = str_pad($config->id, 4, '0', STR_PAD_LEFT);
        $datePart = now()->format('Ymd');
        $random = Str::upper(Str::random(4));
        
        return $prefix . $datePart . $accountPart . $configPart . $random . $suffix;
    }

    /**
     * Get period start date based on frequency
     */
    protected function getPeriodStart(string $frequency, Carbon $chargeDate): Carbon
    {
        switch ($frequency) {
            case 'daily':
                return $chargeDate->copy()->startOfDay();
            case 'weekly':
                return $chargeDate->copy()->startOfWeek();
            case 'monthly':
                return $chargeDate->copy()->startOfMonth();
            case 'quarterly':
                return $chargeDate->copy()->startOfQuarter();
            case 'yearly':
                return $chargeDate->copy()->startOfYear();
            default:
                return $chargeDate->copy()->startOfMonth();
        }
    }

    /**
     * Get period end date based on frequency
     */
    protected function getPeriodEnd(string $frequency, Carbon $chargeDate): Carbon
    {
        switch ($frequency) {
            case 'daily':
                return $chargeDate->copy()->endOfDay();
            case 'weekly':
                return $chargeDate->copy()->endOfWeek();
            case 'monthly':
                return $chargeDate->copy()->endOfMonth();
            case 'quarterly':
                return $chargeDate->copy()->endOfQuarter();
            case 'yearly':
                return $chargeDate->copy()->endOfYear();
            default:
                return $chargeDate->copy()->endOfMonth();
        }
    }

    /**
     * Get fee summary for reporting
     */
    public function getFeeSummary(array $filters = []): array
    {
        $query = AccountFee::query()
            ->with(['account', 'feeConfiguration']);

        if (!empty($filters['from_date'])) {
            $query->where('charge_date', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->where('charge_date', '<=', $filters['to_date']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['account_type_id'])) {
            $query->whereHas('account', function ($q) use ($filters) {
                $q->where('account_type_id', $filters['account_type_id']);
            });
        }

        $fees = $query->get();

        return [
            'total_fees' => $fees->count(),
            'total_amount' => $fees->sum('amount'),
            'by_status' => $fees->groupBy('status')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('amount')
                ];
            }),
            'by_account_type' => $fees->groupBy('account.account_type_id')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('amount')
                ];
            }),
            'fees' => $fees
        ];
    }
}