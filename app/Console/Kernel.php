<?php

namespace App\Console;

use App\Jobs\ProcessMonthlyAccountFeesAndInterest;
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

        // Run monthly fee and interest processing on the 1st of each month at 1:00 AM
        $schedule->job(new ProcessMonthlyAccountFeesAndInterest())
            ->monthlyOn(1, '01:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/monthly-processing.log'));

        // You can also add a daily check for new accounts that might have been missed
        $schedule->call(function () {
            $lastMonth = now()->subMonth()->startOfMonth();
            ProcessMonthlyAccountFeesAndInterest::dispatch($lastMonth);
        })->dailyAt('02:00');
    }
}
