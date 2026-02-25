<?php

namespace App\Console\Commands;

use App\Models\Eloquent\User;
use App\Services\Teller\TellerLimitService;
use Illuminate\Console\Command;

class SetTellerLimits extends Command
{
    protected $signature = 'teller:set-limits 
                            {--user= : Specific user ID}
                            {--role= : Role to update}
                            {--per-transaction= : Per-transaction limit}
                            {--daily= : Daily limit}';

    protected $description = 'Set teller limits for users';

    protected TellerLimitService $limitService;

    public function __construct(TellerLimitService $limitService)
    {
        parent::__construct();
        $this->limitService = $limitService;
    }

    public function handle()
    {
        $query = User::query();

        if ($this->option('user')) {
            $query->where('id', $this->option('user'));
        }

        if ($this->option('role')) {
            $query->where('role', $this->option('role'));
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->error('No users found.');
            return 1;
        }

        $perTransaction = $this->option('per-transaction');
        $daily = $this->option('daily');

        foreach ($users as $user) {
            if ($perTransaction) {
                $user->teller_limit = $perTransaction;
            }

            if ($daily) {
                $user->daily_teller_limit = $daily;
            }

            $user->save();

            $this->info("Updated limits for user: {$user->name} (ID: {$user->id})");
            $this->line("  Per-transaction: " . ($user->teller_limit ?? 'Not set'));
            $this->line("  Daily limit: " . ($user->daily_teller_limit ?? 'Not set'));
        }

        return 0;
    }
}
