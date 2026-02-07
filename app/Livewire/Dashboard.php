<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Eloquent\Account;
use Illuminate\Support\Facades\Log;
use App\Models\Eloquent\LedgerEntry;
use App\Models\Eloquent\Transaction;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    public $loading = true;
    public $message = 'Loading your banking dashboard...';
    public $error = null;

    // Dashboard data
    public $accounts = [];
    public $recentTransactions = [];
    public $totalBalance = 0;
    public $monthlyIncome = 0;
    public $monthlyExpenses = 0;

    // User info
    public $userName = '';
    public $userEmail = '';

    public function mount()
    {
        // Log::info('Dashboard mounted', [
        //     'user_id' => auth()->id(),
        //     'user_email' => auth()->user()->email ?? 'none',
        //     'url' => request()->url()
        // ]);

        $this->loading = true;

        try {
            $user = Auth::user();

            if (!$user) {
                throw new \Exception('Please log in to view the dashboard.');
            }

            $this->userName = $user->name;
            $this->userEmail = $user->email;

            // Load dashboard data
            $this->loadDashboardData($user);

            $this->loading = false;
            $this->message = 'Welcome to your banking dashboard!';
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->loading = false;
            $this->message = 'Error loading dashboard';
        }
    }

    protected function loadDashboardData($user)
    {
        // Load accounts from database
        $this->accounts = Account::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('accountType')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($account) {
                return [
                    'id' => $account->id,
                    'account_number' => $account->account_number,
                    'account_type' => [
                        'name' => $account->accountType->name ?? 'Unknown',
                        'code' => $account->accountType->code ?? 'N/A',
                    ],
                    'current_balance' => (float) $account->current_balance,
                    'available_balance' => (float) $account->available_balance,
                    'ledger_balance' => (float) $account->ledger_balance,
                    'status' => $account->status,
                    'currency' => $account->currency,
                    'opened_at' => $account->opened_at?->format('Y-m-d H:i:s'),
                ];
            })
            ->toArray();

        // Calculate total balance
        $this->totalBalance = collect($this->accounts)->sum('current_balance');

        // Load recent transactions
        $this->recentTransactions = LedgerEntry::whereHas('account', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with(['transaction', 'account'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($ledgerEntry) {
                return [
                    'id' => $ledgerEntry->transaction->id ?? 'unknown',
                    'type' => $ledgerEntry->entry_type,
                    'amount' => (float) $ledgerEntry->amount,
                    'currency' => $ledgerEntry->currency,
                    'description' => $ledgerEntry->transaction->description ?? 'Transaction',
                    'created_at' => $ledgerEntry->created_at->format('Y-m-d H:i:s'),
                    'status' => $ledgerEntry->transaction->status ?? 'completed',
                ];
            })
            ->toArray();

        // Calculate monthly income/expenses (simplified for now)
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $monthlyLedgerEntries = LedgerEntry::whereHas('account', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->get();

        $this->monthlyIncome = $monthlyLedgerEntries
            ->where('entry_type', 'credit')
            ->sum('amount');

        $this->monthlyExpenses = $monthlyLedgerEntries
            ->where('entry_type', 'debit')
            ->sum('amount');
    }

    public function refreshData()
    {
        $this->loading = true;
        $this->message = 'Refreshing dashboard data...';

        try {
            $user = Auth::user();
            $this->loadDashboardData($user);

            $this->loading = false;
            $this->message = 'Dashboard refreshed at ' . now()->format('H:i:s');
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->loading = false;
        }
    }

    #[Layout('layouts.main')]
    public function render()
    {
        return view('livewire.dashboard');
    }
}
