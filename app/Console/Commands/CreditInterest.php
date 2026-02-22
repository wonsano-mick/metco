<?php

namespace App\Console\Commands;

use App\Models\Eloquent\Account;
use App\Services\Transaction\EnhancedTransactionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CreditInterest extends Command
{
    protected $signature = 'bank:credit-interest {type=deposit}';
    protected $description = 'Credit interest to customer accounts';

    public function handle(EnhancedTransactionService $transactionService)
    {
        $type = $this->argument('type');
        $this->info("Starting interest credit for $type accounts...");

        $accounts = Account::where('status', 'active')
            ->whereHas('accountType', function ($query) use ($type) {
                if ($type === 'deposit') {
                    $query->where('interest_rate', '>', 0)
                        ->where('type', 'savings');
                } else {
                    $query->where('interest_rate', '>', 0)
                        ->where('type', 'loan');
                }
            })
            ->get();

        $bar = $this->output->createProgressBar($accounts->count());
        $bar->start();

        $credited = 0;
        $failed = 0;

        foreach ($accounts as $account) {
            try {
                $dailyRate = $account->accountType->interest_rate / 365 / 100;
                $interestAmount = $account->current_balance * $dailyRate * 30; // Monthly interest

                if ($interestAmount > 0.01) { // Only credit if at least 1 cent
                    $transactionService->creditInterest($account, $interestAmount, $type);
                    $credited++;
                }
            } catch (\Exception $e) {
                $failed++;
                Log::error("Failed to credit interest for account {$account->account_number}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Interest credit completed. Credited: $credited, Failed: $failed");

        return Command::SUCCESS;
    }
}
