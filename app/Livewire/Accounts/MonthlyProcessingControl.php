<?php

namespace App\Livewire\Accounts;

use App\Models\Eloquent\Account;
use App\Models\Eloquent\AccountType;
use App\Models\Eloquent\MonthlyAccountProcessing;
use App\Services\Transaction\EnhancedTransactionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class MonthlyProcessingControl extends Component
{
    public $processingMonth;
    public $accountTypeId = '';
    public $isProcessing = false;
    public $processingLog = [];

    // Processing statistics
    public $processedCount = 0;
    public $failedCount = 0;
    public $totalFeesCharged = 0;
    public $totalInterestCredited = 0;

    protected $rules = [
        'processingMonth' => 'required|date',
        'accountTypeId' => 'nullable|exists:account_types,id'
    ];

    public function mount()
    {
        $this->processingMonth = now()->startOfMonth()->format('Y-m');
    }

    public function triggerProcessing()
    {
        $this->validate();

        $this->isProcessing = true;
        $this->processingLog = [];
        $this->processedCount = 0;
        $this->failedCount = 0;
        $this->totalFeesCharged = 0;
        $this->totalInterestCredited = 0;

        try {
            $month = Carbon::parse($this->processingMonth . '-01')->startOfMonth();

            $this->logMessage("Starting monthly processing for {$month->format('F Y')}");

            // Process accounts synchronously
            $this->processAccounts($month);

            $this->logMessage("Processing completed successfully!");
            $this->logSummary();
        } catch (\Exception $e) {
            $this->logMessage("Error: " . $e->getMessage(), 'error');
        }

        $this->isProcessing = false;
    }

    protected function processAccounts(Carbon $processingMonth)
    {
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
        $alreadyProcessed = MonthlyAccountProcessing::where('processing_month', $processingMonth)
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
            return;
        }

        // Process accounts one by one (synchronously)
        $accounts = $query->get();
        $transactionService = app(EnhancedTransactionService::class);

        foreach ($accounts as $account) {
            $this->processAccount($account, $processingMonth, $transactionService);
        }
    }

    protected function processAccount($account, Carbon $processingMonth, EnhancedTransactionService $transactionService)
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
                            $processingMonth
                        );
                        $feeTransactionId = $feeTransaction->id;
                        $this->totalFeesCharged += $monthlyFee;
                        $this->logMessage("  - Charged monthly fee: " . number_format($monthlyFee, 2));
                    } else {
                        $this->logMessage("  - Insufficient funds for monthly fee: " . number_format($monthlyFee, 2) . " (Available: " . number_format($account->available_balance, 2) . ")", 'warning');
                    }
                } catch (\Exception $e) {
                    $this->logMessage("  - Failed to charge monthly fee: " . $e->getMessage(), 'error');
                    throw $e;
                }
            }

            // Apply monthly interest if applicable
            if ($monthlyInterest > 0.01) { // Only credit if interest is at least 1 cent
                try {
                    $interestTransaction = $transactionService->creditMonthlyInterest(
                        $account,
                        $monthlyInterest,
                        $processingMonth
                    );
                    $interestTransactionId = $interestTransaction->id;
                    $this->totalInterestCredited += $monthlyInterest;
                    $this->logMessage("  - Credited interest: " . number_format($monthlyInterest, 2));
                } catch (\Exception $e) {
                    $this->logMessage("  - Failed to credit interest: " . $e->getMessage(), 'error');
                    throw $e;
                }
            }

            // Refresh account to get updated balance
            $account->refresh();
            $balanceAfter = $account->current_balance;

            // Create processing record
            MonthlyAccountProcessing::create([
                'account_id' => $account->id,
                'processing_month' => $processingMonth,
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
            $this->logMessage("  ✓ Completed - Net change: {$changeSymbol}" . number_format($netChange, 2));
        } catch (\Exception $e) {
            DB::rollBack();
            $this->failedCount++;
            $this->logMessage("  ✗ Failed for account {$account->account_number}: " . $e->getMessage(), 'error');
        }
    }

    protected function logSummary()
    {
        $this->logMessage("=== PROCESSING SUMMARY ===");
        $this->logMessage("Accounts processed successfully: {$this->processedCount}");
        $this->logMessage("Accounts failed: {$this->failedCount}");
        $this->logMessage("Total fees charged: " . number_format($this->totalFeesCharged, 2));
        $this->logMessage("Total interest credited: " . number_format($this->totalInterestCredited, 2));

        $netImpact = $this->totalInterestCredited - $this->totalFeesCharged;
        $this->logMessage("Net impact: " . number_format($netImpact, 2));
    }

    protected function logMessage($message, $type = 'info')
    {
        $this->processingLog[] = [
            'time' => now()->format('H:i:s'),
            'message' => $message,
            'type' => $type
        ];

        // Force Livewire to update the UI
        $this->dispatch('log-updated');
    }

    #[Layout('layouts.main')]
    public function render()
    {
        return view('livewire.accounts.monthly-processing-control', [
            'accountTypes' => AccountType::active()->orderBy('name')->get()
        ]);
    }
}
