<?php

namespace App\Console\Commands;

use App\Models\Eloquent\Account;
use App\Services\Transaction\EnhancedTransactionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ChargeMonthlyFees extends Command
{
    protected $signature = 'bank:charge-monthly-fees';
    protected $description = 'Charge monthly maintenance fees to customer accounts';

    public function handle(EnhancedTransactionService $transactionService)
    {
        $this->info('Starting monthly fee collection...');

        $accounts = Account::where('status', 'active')
            ->whereHas('accountType', function ($query) {
                $query->where('monthly_fee', '>', 0);
            })
            ->get();

        $bar = $this->output->createProgressBar($accounts->count());
        $bar->start();

        $charged = 0;
        $failed = 0;

        foreach ($accounts as $account) {
            try {
                $feeAmount = $account->accountType->monthly_fee ?? 10; // Default $10 if not set

                $transactionService->chargeMonthlyMaintenanceFee($account, $feeAmount);

                $charged++;
            } catch (\Exception $e) {
                $failed++;
                Log::error('Failed to charge monthly fee for account ' . $account->account_number . ': ' . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Monthly fee collection completed. Charged: $charged, Failed: $failed");

        return Command::SUCCESS;
    }
}
