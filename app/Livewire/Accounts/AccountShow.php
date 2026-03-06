<?php

namespace App\Livewire\Accounts;

use App\Models\Eloquent\Account;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

class AccountShow extends Component
{
    public Account $account;
    public $monthlyProcessings;
    public $statistics;

    public function mount(Account $account)
    {
        // Authorization check
        if (!Gate::allows('view accounts')) {
            abort(403, 'Unauthorized access.');
        }

        // Load the relationships
        $this->account = $account->load([
            'customer',
            'accountType',
            'monthlyProcessings' => function ($query) {
                $query->latest('processing_month')->limit(12);
            }
        ]);

        $this->monthlyProcessings = $this->account->monthlyProcessings;
        $this->calculateStatistics();
    }

    protected function calculateStatistics()
    {
        $processings = $this->account->monthlyProcessings;

        $this->statistics = [
            'total_fees_paid' => $processings->sum('monthly_fee_applied'),
            'total_interest_earned' => $processings->sum('interest_earned'),
            'avg_monthly_balance' => $processings->avg('balance_after') ?? $this->account->current_balance,
            'months_processed' => $processings->count(),
            'last_processing' => $processings->first(),
        ];
    }


    public function getAccountTypeIcon()
    {
        $typeName = strtolower($this->account->accountType?->name ?? '');

        if (str_contains($typeName, 'savings')) {
            return 'fa-piggy-bank';
        } elseif (str_contains($typeName, 'current') || str_contains($typeName, 'checking')) {
            return 'fa-wallet';
        } elseif (str_contains($typeName, 'business') || str_contains($typeName, 'corporate')) {
            return 'fa-building';
        } elseif (str_contains($typeName, 'joint')) {
            return 'fa-users';
        } else {
            return 'fa-credit-card';
        }
    }

    public function getStatusColor()
    {
        return match ($this->account->status) {
            'active' => 'green',
            'dormant' => 'yellow',
            'frozen' => 'red',
            'closed' => 'gray',
            default => 'blue'
        };
    }

    #[Layout('layouts.main')]
    public function render()
    {
        return view('livewire.accounts.account-show');
    }
}
