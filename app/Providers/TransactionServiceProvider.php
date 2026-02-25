<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Teller\TellerLimitService;
use App\Services\Transaction\TellerTransactionService;
use App\Services\Transaction\EnhancedTransactionService;
use Illuminate\Support\Facades\Auth;

class TransactionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register TellerLimitService as a singleton
        $this->app->singleton(TellerLimitService::class, function ($app) {
            return new TellerLimitService();
        });

        // Register EnhancedTransactionService with its dependencies
        $this->app->singleton(EnhancedTransactionService::class, function ($app) {
            // Based on the EnhancedTransactionService, it might need:
            // - User ID
            // - Branch ID
            // - Other dependencies
            
            // Get the authenticated user
            $user = Auth::user();
            
            // Create instance with required parameters
            // Adjust this based on the actual EnhancedTransactionService constructor
            return new EnhancedTransactionService(
                // Pass any required parameters here
                // For example:
                // $user->id,
                // $user->branch_id
            );
        });

        // Register TellerTransactionService with its dependencies
        $this->app->singleton(TellerTransactionService::class, function ($app) {
            return new TellerTransactionService(
                $app->make(TellerLimitService::class)
                // Note: TellerTransactionService will handle calling parent constructor
            );
        });
    }

    public function boot(): void
    {
        //
    }
}