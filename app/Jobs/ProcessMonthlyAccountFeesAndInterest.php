<?php

namespace App\Jobs;

use App\Models\Eloquent\Account;
use App\Models\Eloquent\AccountType;
use App\Models\Eloquent\MonthlyAccountProcessing;
use App\Services\Transaction\EnhancedTransactionService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessMonthlyAccountFeesAndInterest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Carbon $processingMonth;
    protected ?int $accountTypeId;
    protected array $processingLog = [];
    protected int $processedCount = 0;
    protected int $failedCount = 0;
    protected float $totalFeesCharged = 0;
    protected float $totalInterestCredited = 0;

    public $timeout = 3600; // 1 hour timeout for large batches
    public $tries = 3; // Retry up to 3 times

    /**
     * Create a new job instance.
     */
    public function __construct(Carbon $processingMonth, ?int $accountTypeId = null)
    {
        $this->processingMonth = $processingMonth->copy()->startOfMonth();
        $this->accountTypeId = $accountTypeId;
    }

    /**
     * Execute the job.
     */
    public function handle(EnhancedTransactionService $transactionService): void
    {
        $this->logMessage("Starting monthly processing for {$this->processingMonth->format('F Y')}");

        try {
            // Build query for accounts to process
            $query = Account::query()
                ->where('status', 'active')
                ->whereHas('accountType', function ($q) {
                    $q->where('is_active', true);
                })
                ->with(['accountType', 'customer']);

            // Filter by account type if specified
            if ($this->accountTypeId) {
                $query->where('account_type_id', $this->accountTypeId);
                $accountType = AccountType::find($this->accountTypeId);
                $this->logMessage("Filtering by account type: {$accountType?->name}");
            }

            // Exclude accounts already processed for this month
            $alreadyProcessed = MonthlyAccountProcessing::where('processing_month', $this->processingMonth)
                ->pluck('account_id')
                ->toArray();

            if (!empty($alreadyProcessed)) {
                $query->whereNotIn('id', $alreadyProcessed);
                $this->logMessage("Skipping " . count($alreadyProcessed) . " accounts already processed for this month");
            }

            $totalAccounts = $query->count();
            $this->logMessage("Found {$totalAccounts} accounts to process");

            if ($totalAccounts === 0) {
                $this->logMessage("No accounts to process");
                $this->writeSummaryLog();
                return;
            }

            // Process accounts in chunks to avoid memory issues
            $query->chunk(100, function ($accounts) use ($transactionService) {
                foreach ($accounts as $account) {
                    $this->processAccount($account, $transactionService);
                }
            });

            $this->logMessage("Processing completed successfully!");
            $this->writeSummaryLog();
        } catch (\Exception $e) {
            $this->logMessage("Critical error: " . $e->getMessage(), 'error');
            Log::error('Monthly processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'processing_month' => $this->processingMonth->format('Y-m')
            ]);
            throw $e;
        }
    }

    /**
     * Process a single account
     */
    protected function processAccount(Account $account, EnhancedTransactionService $transactionService): void
    {
        DB::beginTransaction();

        try {
            $this->logMessage("Processing account: {$account->account_number} ({$account->customer?->full_name})");

            $balanceBefore = $account->current_balance;
            $monthlyFee = $account->accountType?->monthly_fee ?? 0;
            $monthlyInterest = 0;
            $feeTransactionId = null;
            $interestTransactionId = null;

            // Calculate monthly interest if applicable
            if ($account->accountType && $account->accountType->interest_rate > 0) {
                $annualRate = $account->accountType->interest_rate / 100;
                $monthlyRate = $annualRate / 12;
                $monthlyInterest = $balanceBefore * $monthlyRate;

                // Round to 2 decimal places
                $monthlyInterest = round($monthlyInterest, 2);
            }

            // Apply monthly fee if applicable
            if ($monthlyFee > 0) {
                try {
                    // Check if account has sufficient balance
                    if ($account->available_balance >= $monthlyFee) {
                        $feeTransaction = $transactionService->chargeMonthlyFee(
                            $account,
                            $monthlyFee,
                            $this->processingMonth
                        );
                        $feeTransactionId = $feeTransaction->id;
                        $this->totalFeesCharged += $monthlyFee;
                        $this->logMessage("  - Charged monthly fee: {$monthlyFee}");
                    } else {
                        $this->logMessage("  - Insufficient funds for monthly fee: {$monthlyFee} (Available: {$account->available_balance})", 'warning');
                        // TODO: Handle insufficient funds - maybe mark account for review or apply negative balance
                    }
                } catch (\Exception $e) {
                    $this->logMessage("  - Failed to charge monthly fee: " . $e->getMessage(), 'error');
                    throw $e; // Re-throw to trigger transaction rollback
                }
            }

            // Apply monthly interest if applicable
            if ($monthlyInterest > 0) {
                try {
                    $interestTransaction = $transactionService->creditMonthlyInterest(
                        $account,
                        $monthlyInterest,
                        $this->processingMonth
                    );
                    $interestTransactionId = $interestTransaction->id;
                    $this->totalInterestCredited += $monthlyInterest;
                    $this->logMessage("  - Credited interest: {$monthlyInterest}");
                } catch (\Exception $e) {
                    $this->logMessage("  - Failed to credit interest: " . $e->getMessage(), 'error');
                    throw $e; // Re-throw to trigger transaction rollback
                }
            }

            // Refresh account to get updated balance
            $account->refresh();
            $balanceAfter = $account->current_balance;

            // Create processing record
            MonthlyAccountProcessing::create([
                'account_id' => $account->id,
                'processing_month' => $this->processingMonth,
                'balance_before' => $balanceBefore,
                'monthly_fee_applied' => $monthlyFee,
                'interest_earned' => $monthlyInterest,
                'balance_after' => $balanceAfter,
                'fee_transaction_id' => $feeTransactionId,
                'interest_transaction_id' => $interestTransactionId,
                'processed_at' => now(),
            ]);

            DB::commit();
            $this->processedCount++;

            $netChange = $monthlyInterest - $monthlyFee;
            $changeSymbol = $netChange >= 0 ? '+' : '';
            $this->logMessage("  ✓ Completed - Net change: {$changeSymbol}{$netChange}");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->failedCount++;
            $this->logMessage("  ✗ Failed for account {$account->account_number}: " . $e->getMessage(), 'error');

            Log::error('Failed to process monthly account', [
                'account_id' => $account->id,
                'account_number' => $account->account_number,
                'processing_month' => $this->processingMonth->format('Y-m'),
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Log a message to both the job log and the application log
     */
    protected function logMessage(string $message, string $type = 'info'): void
    {
        $timestamp = now()->format('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}";

        $this->processingLog[] = [
            'time' => now()->format('H:i:s'),
            'message' => $message,
            'type' => $type
        ];

        // Log to file
        if ($type === 'error') {
            Log::channel('monthly-processing')->error($logMessage);
        } else {
            Log::channel('monthly-processing')->info($logMessage);
        }

        // Also log to main log for debugging
        Log::info('MonthlyProcessing: ' . $message);
    }

    /**
     * Write summary log at the end of processing
     */
    protected function writeSummaryLog(): void
    {
        $summary = [
            'processing_month' => $this->processingMonth->format('Y-m'),
            'processed_count' => $this->processedCount,
            'failed_count' => $this->failedCount,
            'total_fees_charged' => $this->totalFeesCharged,
            'total_interest_credited' => $this->totalInterestCredited,
            'net_impact' => $this->totalInterestCredited - $this->totalFeesCharged
        ];

        $this->logMessage("=== PROCESSING SUMMARY ===");
        $this->logMessage("Accounts processed successfully: {$this->processedCount}");
        $this->logMessage("Accounts failed: {$this->failedCount}");
        $this->logMessage("Total fees charged: " . number_format($this->totalFeesCharged, 2));
        $this->logMessage("Total interest credited: " . number_format($this->totalInterestCredited, 2));
        $this->logMessage("Net impact: " . number_format($summary['net_impact'], 2));

        // Log summary to database or for monitoring
        Log::channel('monthly-processing')->info('Monthly processing summary', $summary);
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel('monthly-processing')->error('Monthly processing job failed completely', [
            'error' => $exception->getMessage(),
            'processing_month' => $this->processingMonth->format('Y-m'),
            'account_type_id' => $this->accountTypeId
        ]);
    }
}
