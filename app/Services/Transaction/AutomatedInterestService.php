<?php

namespace App\Services\Transaction;

use App\Models\Eloquent\Account;
use App\Models\Eloquent\AccountInterest;
use App\Models\Eloquent\InterestConfiguration;
use App\Models\Eloquent\SystemAccount;
use App\Models\Eloquent\Transaction;
use App\Models\Eloquent\LedgerEntry;
use App\Models\Eloquent\SystemLedgerEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AutomatedInterestService
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
     * Calculate and generate pending interest for all eligible accounts
     */
    public function generatePendingInterest(Carbon $postingDate): array
    {
        $results = [
            'total_processed' => 0,
            'total_interest_generated' => 0,
            'total_amount' => 0,
            'accounts_processed' => [],
            'errors' => []
        ];

        // Get all active interest configurations
        $configurations = InterestConfiguration::active()->get();

        foreach ($configurations as $config) {
            try {
                $configResults = $this->generateInterestForConfiguration($config, $postingDate);

                $results['total_processed'] += $configResults['processed'];
                $results['total_interest_generated'] += $configResults['interest_generated'];
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
                Log::error('Interest generation failed for config: ' . $config->code, [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return $results;
    }

    /**
     * Generate pending interest for a specific configuration
     */
    protected function generateInterestForConfiguration(InterestConfiguration $config, Carbon $postingDate): array
    {
        $results = [
            'processed' => 0,
            'interest_generated' => 0,
            'total_amount' => 0,
            'accounts' => []
        ];

        // Get all active accounts of this type
        $accounts = Account::where('account_type_id', $config->account_type_id)
            ->active()
            ->where('currency', $config->currency)
            ->get();

        $periodStart = $this->getPeriodStart($config->frequency, $postingDate);
        $periodEnd = $this->getPeriodEnd($config->frequency, $postingDate);

        foreach ($accounts as $account) {
            $results['processed']++;

            try {
                // Check if interest already exists for this period
                $existingInterest = AccountInterest::where('account_id', $account->id)
                    ->where('interest_configuration_id', $config->id)
                    ->where('period_start', $periodStart)
                    ->where('period_end', $periodEnd)
                    ->first();

                if ($existingInterest) {
                    continue; // Skip if already generated
                }

                // Calculate interest amount
                $interestAmount = $this->calculateInterestAmount($account, $config, $periodStart, $periodEnd);

                if ($interestAmount <= 0) {
                    continue; // Skip if no interest earned
                }

                // Create pending interest record
                $accountInterest = AccountInterest::create([
                    'account_id' => $account->id,
                    'interest_configuration_id' => $config->id,
                    'interest_reference' => $this->generateInterestReference($account, $config),
                    'amount' => $interestAmount,
                    'interest_rate' => $config->interest_rate,
                    'currency' => $account->currency,
                    'status' => AccountInterest::STATUS_PENDING,
                    'calculation_method' => $config->calculation_method,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'posting_date' => $postingDate,
                    'balance_before' => $account->current_balance,
                    'calculation_details' => $this->getCalculationDetails($account, $config, $periodStart, $periodEnd),
                    'metadata' => [
                        'interest_configuration' => $config->only(['code', 'name', 'calculation_method']),
                        'generated_at' => now()->toIso8601String(),
                    ]
                ]);

                $results['interest_generated']++;
                $results['total_amount'] += $interestAmount;
                $results['accounts'][] = [
                    'account_id' => $account->id,
                    'account_number' => $account->account_number,
                    'interest_id' => $accountInterest->id,
                    'amount' => $interestAmount
                ];
            } catch (\Exception $e) {
                Log::error('Failed to generate interest for account: ' . $account->id, [
                    'error' => $e->getMessage(),
                    'account_id' => $account->id,
                    'config_id' => $config->id
                ]);
            }
        }

        return $results;
    }

    /**
     * Process pending interest for a specific date
     */
    public function processPendingInterest(Carbon $processDate): array
    {
        $results = [
            'total_processed' => 0,
            'total_amount' => 0,
            'successful' => 0,
            'failed' => 0,
            'transactions' => [],
            'errors' => []
        ];

        // Get all pending interest for the posting date
        $pendingInterest = AccountInterest::where('status', AccountInterest::STATUS_PENDING)
            ->where('posting_date', $processDate->toDateString())
            ->with(['account', 'interestConfiguration'])
            ->get();

        // Get system accounts
        $interestExpenseAccount = SystemAccount::where('type', SystemAccount::TYPE_INTEREST_EXPENSE)
            ->where('code', 'INT-EXP-001')
            ->firstOrFail();

        foreach ($pendingInterest as $accountInterest) {
            try {
                DB::transaction(function () use ($accountInterest, &$results) {
                    $account = $accountInterest->account;

                    // Process interest credit using enhanced transaction service
                    $transaction = $this->transactionService->creditInterest(
                        $account,
                        $accountInterest->amount,
                        'deposit' // Assuming deposit accounts for now
                    );

                    // Update interest record
                    $accountInterest->update([
                        'transaction_id' => $transaction->id,
                        'status' => AccountInterest::STATUS_PROCESSED,
                        'processed_at' => now(),
                        'balance_after' => $account->current_balance,
                        'metadata' => array_merge($accountInterest->metadata ?? [], [
                            'processed_at' => now()->toIso8601String(),
                            'transaction_reference' => $transaction->transaction_reference,
                        ])
                    ]);

                    $results['successful']++;
                    $results['total_amount'] += $accountInterest->amount;
                    $results['transactions'][] = [
                        'interest_id' => $accountInterest->id,
                        'transaction_id' => $transaction->id,
                        'reference' => $transaction->transaction_reference,
                        'account_id' => $account->id,
                        'amount' => $accountInterest->amount
                    ];
                });
            } catch (\Exception $e) {
                $accountInterest->update([
                    'status' => AccountInterest::STATUS_FAILED,
                    'failure_reason' => $e->getMessage()
                ]);

                $results['failed']++;
                $results['errors'][] = [
                    'interest_id' => $accountInterest->id,
                    'account_id' => $accountInterest->account_id,
                    'error' => $e->getMessage()
                ];

                Log::error('Interest processing failed for interest: ' . $accountInterest->id, [
                    'error' => $e->getMessage(),
                    'interest_id' => $accountInterest->id
                ]);
            }

            $results['total_processed']++;
        }

        return $results;
    }

    /**
     * Calculate interest amount based on configuration
     */
    protected function calculateInterestAmount(
        Account $account,
        InterestConfiguration $config,
        Carbon $periodStart,
        Carbon $periodEnd
    ): float {
        switch ($config->calculation_method) {
            case 'daily_balance':
                return $this->calculateDailyBalanceInterest($account, $config, $periodStart, $periodEnd);

            case 'minimum_balance':
                return $this->calculateMinimumBalanceInterest($account, $config, $periodStart, $periodEnd);

            case 'average_daily_balance':
                return $this->calculateAverageDailyBalanceInterest($account, $config, $periodStart, $periodEnd);

            case 'tiered':
                return $this->calculateTieredInterest($account, $config, $periodStart, $periodEnd);

            default:
                return 0;
        }
    }

    /**
     * Calculate interest based on daily balance
     */
    protected function calculateDailyBalanceInterest(
        Account $account,
        InterestConfiguration $config,
        Carbon $periodStart,
        Carbon $periodEnd
    ): float {
        // Get daily balances for the period
        $dailyBalances = $this->getDailyBalances($account, $periodStart, $periodEnd);

        $totalInterest = 0;
        $daysInPeriod = $periodStart->diffInDays($periodEnd) + 1;

        foreach ($dailyBalances as $date => $balance) {
            $dailyInterest = $balance * ($config->interest_rate / 100 / 365);
            $totalInterest += $dailyInterest;
        }

        // Apply compounding if configured
        if ($config->posting_method === 'compound' && $config->compound_frequency_days) {
            $totalInterest = $this->applyCompounding($totalInterest, $config->compound_frequency_days);
        }

        return round($totalInterest, 4);
    }

    /**
     * Calculate interest based on minimum balance
     */
    protected function calculateMinimumBalanceInterest(
        Account $account,
        InterestConfiguration $config,
        Carbon $periodStart,
        Carbon $periodEnd
    ): float {
        $dailyBalances = $this->getDailyBalances($account, $periodStart, $periodEnd);
        $minimumBalance = min($dailyBalances);

        $daysInPeriod = $periodStart->diffInDays($periodEnd) + 1;
        $interest = $minimumBalance * ($config->interest_rate / 100) * ($daysInPeriod / 365);

        return round($interest, 4);
    }

    /**
     * Calculate interest based on average daily balance
     */
    protected function calculateAverageDailyBalanceInterest(
        Account $account,
        InterestConfiguration $config,
        Carbon $periodStart,
        Carbon $periodEnd
    ): float {
        $dailyBalances = $this->getDailyBalances($account, $periodStart, $periodEnd);
        $averageBalance = array_sum($dailyBalances) / count($dailyBalances);

        $daysInPeriod = $periodStart->diffInDays($periodEnd) + 1;
        $interest = $averageBalance * ($config->interest_rate / 100) * ($daysInPeriod / 365);

        return round($interest, 4);
    }

    /**
     * Calculate tiered interest
     */
    protected function calculateTieredInterest(
        Account $account,
        InterestConfiguration $config,
        Carbon $periodStart,
        Carbon $periodEnd
    ): float {
        $tiers = $config->tiers ?? [];
        $dailyBalances = $this->getDailyBalances($account, $periodStart, $periodEnd);

        $totalInterest = 0;
        $daysInPeriod = $periodStart->diffInDays($periodEnd) + 1;

        foreach ($dailyBalances as $balance) {
            $applicableRate = $config->interest_rate;

            // Find applicable tier rate
            foreach ($tiers as $tier) {
                if ($balance >= ($tier['min'] ?? 0) && $balance <= ($tier['max'] ?? PHP_FLOAT_MAX)) {
                    $applicableRate = $tier['rate'] ?? $config->interest_rate;
                    break;
                }
            }

            $dailyInterest = $balance * ($applicableRate / 100 / 365);
            $totalInterest += $dailyInterest;
        }

        return round($totalInterest, 4);
    }

    /**
     * Get daily balances for an account over a period
     */
    protected function getDailyBalances(Account $account, Carbon $start, Carbon $end): array
    {
        $balances = [];
        $currentDate = $start->copy();

        while ($currentDate <= $end) {
            // Get balance at end of day
            $balance = $this->getBalanceAtDate($account, $currentDate);
            $balances[$currentDate->format('Y-m-d')] = $balance;
            $currentDate->addDay();
        }

        return $balances;
    }

    /**
     * Get account balance at a specific date
     */
    protected function getBalanceAtDate(Account $account, Carbon $date): float
    {
        // If date is today, return current balance
        if ($date->isToday()) {
            return (float) $account->current_balance;
        }

        // For past dates, calculate from ledger entries
        $balance = LedgerEntry::where('account_id', $account->id)
            ->whereHas('transaction', function ($query) use ($date) {
                $query->where('completed_at', '<=', $date->endOfDay())
                    ->where('status', 'completed');
            })
            ->orderBy('created_at', 'desc')
            ->value('balance_after');

        return $balance ?? (float) $account->initial_deposit ?? 0;
    }

    /**
     * Apply compounding to interest
     */
    protected function applyCompounding(float $interest, int $compoundDays): float
    {
        // Simple implementation - can be enhanced based on requirements
        return $interest * pow(1 + ($interest / $compoundDays), $compoundDays);
    }

    /**
     * Get calculation details for metadata
     */
    protected function getCalculationDetails(
        Account $account,
        InterestConfiguration $config,
        Carbon $periodStart,
        Carbon $periodEnd
    ): array {
        $dailyBalances = $this->getDailyBalances($account, $periodStart, $periodEnd);

        return [
            'daily_balances_count' => count($dailyBalances),
            'minimum_balance' => min($dailyBalances),
            'maximum_balance' => max($dailyBalances),
            'average_balance' => array_sum($dailyBalances) / count($dailyBalances),
            'interest_rate' => $config->interest_rate,
            'calculation_method' => $config->calculation_method,
            'period_days' => $periodStart->diffInDays($periodEnd) + 1,
        ];
    }

    /**
     * Generate unique interest reference
     */
    protected function generateInterestReference(Account $account, InterestConfiguration $config): string
    {
        $prefix = 'INT';
        $accountPart = str_pad($account->id, 8, '0', STR_PAD_LEFT);
        $configPart = str_pad($config->id, 4, '0', STR_PAD_LEFT);
        $datePart = now()->format('Ymd');
        $random = Str::upper(Str::random(4));

        return $prefix . $datePart . $accountPart . $configPart . $random;
    }

    /**
     * Get period start date based on frequency
     */
    protected function getPeriodStart(string $frequency, Carbon $postingDate): Carbon
    {
        switch ($frequency) {
            case 'daily':
                return $postingDate->copy()->subDay()->startOfDay();
            case 'weekly':
                return $postingDate->copy()->subWeek()->startOfWeek();
            case 'monthly':
                return $postingDate->copy()->subMonth()->startOfMonth();
            case 'quarterly':
                return $postingDate->copy()->subQuarter()->startOfQuarter();
            case 'yearly':
                return $postingDate->copy()->subYear()->startOfYear();
            default:
                return $postingDate->copy()->subMonth()->startOfMonth();
        }
    }

    /**
     * Get period end date based on frequency
     */
    protected function getPeriodEnd(string $frequency, Carbon $postingDate): Carbon
    {
        switch ($frequency) {
            case 'daily':
                return $postingDate->copy()->subDay()->endOfDay();
            case 'weekly':
                return $postingDate->copy()->subWeek()->endOfWeek();
            case 'monthly':
                return $postingDate->copy()->subMonth()->endOfMonth();
            case 'quarterly':
                return $postingDate->copy()->subQuarter()->endOfQuarter();
            case 'yearly':
                return $postingDate->copy()->subYear()->endOfYear();
            default:
                return $postingDate->copy()->subMonth()->endOfMonth();
        }
    }

    /**
     * Get interest summary for reporting
     */
    public function getInterestSummary(array $filters = []): array
    {
        $query = AccountInterest::query()
            ->with(['account', 'interestConfiguration']);

        if (!empty($filters['from_date'])) {
            $query->where('posting_date', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->where('posting_date', '<=', $filters['to_date']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['account_type_id'])) {
            $query->whereHas('account', function ($q) use ($filters) {
                $q->where('account_type_id', $filters['account_type_id']);
            });
        }

        $interests = $query->get();

        return [
            'total_records' => $interests->count(),
            'total_amount' => $interests->sum('amount'),
            'by_status' => $interests->groupBy('status')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('amount')
                ];
            }),
            'by_account_type' => $interests->groupBy('account.account_type_id')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('amount')
                ];
            }),
            'interests' => $interests
        ];
    }
}
