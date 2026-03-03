<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Charge monthly maintenance fees on the 1st of each month at 2 AM
        $schedule->command('bank:charge-monthly-fees')
            ->monthlyOn(1, '02:00')
            ->withoutOverlapping()
            ->sendOutputTo(storage_path('logs/monthly-fees.log'));

        // Credit interest on savings accounts on the last day of each month at 3 AM
        $schedule->command('bank:credit-interest deposit')
            ->monthlyOn(now()->endOfMonth()->day, '03:00')
            ->withoutOverlapping()
            ->sendOutputTo(storage_path('logs/interest-credit.log'));

        // Charge interest on loans daily at 4 AM
        $schedule->command('bank:credit-interest loan')
            ->dailyAt('04:00')
            ->withoutOverlapping()
            ->sendOutputTo(storage_path('logs/loan-interest.log'));

        // Process fees daily at 2 AM
        $schedule->command('banking:process-automated-fees --type=fees')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/fee-processing.log'));

        // Process interest daily at 3 AM
        $schedule->command('banking:process-automated-fees --type=interest')
            ->dailyAt('03:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/interest-processing.log'));

        // Month-end processing on the 1st of each month at 1 AM
        $schedule->command('banking:process-automated-fees --type=all')
            ->monthlyOn(1, '01:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/month-end-processing.log'));
    }
}
