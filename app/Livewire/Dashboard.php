<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Eloquent\Loan;
use Livewire\Attributes\Layout;
use App\Models\Eloquent\Account;
use App\Models\Eloquent\Customer;
use App\Models\Eloquent\Transaction;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    use WithPagination;

    public $selectedPeriod = 'today';
    public $selectedView = 'overview';
    public $search = '';
    public $dateRange = [];
    public $stats = [];
    public $chartData = [];
    public $recentTransactions = [];
    public $pendingActions = [];
    public $role;

    protected $queryString = ['selectedView', 'selectedPeriod', 'search'];

    public function mount()
    {
        $this->role = Auth::user()->role;
        if ($this->role === 'teller') {
            session()->flash('success', 'Welcome to your dashboard, ' . Auth::user()->first_name . '! Here you can manage your daily transactions');
            return redirect()->route('teller.dashboard');
        }
        $this->dateRange = [
            'start' => now()->startOfMonth()->format('Y-m-d'),
            'end' => now()->endOfMonth()->format('Y-m-d')
        ];
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        $this->loadStats();
        $this->loadChartData();
        $this->loadRecentTransactions();
        $this->loadPendingActions();
    }

    public function loadStats()
    {
        $user = Auth::user();
        if (! $user instanceof \App\Models\Eloquent\User) {
            return;
        }
        $branchId = $user->branch_id;

        // Base queries
        $accountsQuery = Account::query();
        $customersQuery = Customer::query();
        $transactionsQuery = Transaction::query();
        $loansQuery = Loan::query();

        // Apply branch filter for non-super-admin
        if ($user->role !== 'super-admin' && $branchId) {
            $customersQuery->where('branch_id', $branchId);
            $customerIds = Customer::where('branch_id', $branchId)->pluck('id');
            $accountsQuery->whereIn('customer_id', $customerIds);
            $loansQuery->whereIn('customer_id', $customerIds);
        }

        // Period filter
        $this->applyPeriodFilter($transactionsQuery);

        // Calculate stats
        $this->stats = [
            'total_balance' => [
                'value' => 'GH₵'.number_format($accountsQuery->sum('current_balance'), 2),
                'change' => '+12.5%',
                'icon' => 'M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6z M8 4v2 M16 4v2 M4 9h16 M4 15h16 M12 9v8 M9 12h6 M16 18h2 M6 18h2',
                'color' => 'green',
                'label' => 'Total Balance'
            ],
            'total_customers' => [
                'value' => $customersQuery->count(),
                'change' => '+8.2%',
                'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                'color' => 'blue',
                'label' => 'Total Customers'
            ],
            'transactions_today' => [
                'value' => $transactionsQuery->whereDate('created_at', today())->count(),
                'change' => '+15.3%',
                'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
                'color' => 'purple',
                'label' => 'Today\'s Transactions'
            ],
            'pending_loans' => [
                'value' => $loansQuery->where('status', 'pending')->count(),
                'change' => '-3.1%',
                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z M12 6v2',
                'color' => 'yellow',
                'label' => 'Pending Loans'
            ],
            'active_accounts' => [
                'value' => $accountsQuery->where('status', 'active')->count(),
                'change' => '+5.7%',
                'icon' => 'M5 13l4 4L19 7 M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2',
                'color' => 'indigo',
                'label' => 'Active Accounts'
            ],
            'kyc_pending' => [
                'value' => Customer::where('kyc_status', 'pending')->when(
                    $branchId && $user->role !== 'super-admin',
                    fn($q) => $q->where('branch_id', $branchId)
                )->count(),
                'change' => '+22.4%',
                'icon' => 'M9 12h6m-6 4h6m2-10h2a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V8a2 2 0 012-2h2m2-2h4a1 1 0 010 2h-4a1 1 0 010-2z M12 8v4',
                'color' => 'red',
                'label' => 'KYC Pending'
            ]
        ];

        // Role-specific stats
        if ($user->role === 'manager') {
            $this->stats['branch_performance'] = [
                'value' => 'GH₵' . number_format($transactionsQuery->sum('amount'), 2),
                'change' => '+18.2%',
                'icon' => 'M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z M17 21v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4 M14 3v4a2 2 0 002 2h4 M9 11h6 M9 15h4',
                'color' => 'teal',
                'label' => 'Branch Performance'
            ];
        }

        if ($user->role === 'super-admin') {
            $this->stats['total_branches'] = [
                'value' => \App\Models\Eloquent\Branch::count(),
                'change' => '+2.3%',
                'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                'color' => 'pink',
                'label' => 'Total Branches'
            ];
        }
    }

    public function loadChartData()
    {
        $user = Auth::user();
        if (! $user instanceof \App\Models\Eloquent\User) {
            return;
        }
        $days = 30;

        // Initialize data arrays
        $dates = [];
        $transactionData = [];
        $accountData = [];
        $balanceData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('M d');
            $dates[] = $date;

            $query = Transaction::whereDate('created_at', now()->subDays($i));
            $accountQuery = Account::query();
            $customerQuery = Customer::query();

            // Apply branch filter
            if ($user->role !== 'super-admin' && $user->branch_id) {
                $customerIds = Customer::where('branch_id', $user->branch_id)->pluck('id');
                $query->whereHas('sourceAccount', function ($q) use ($customerIds) {
                    $q->whereIn('customer_id', $customerIds);
                })->orWhereHas('destinationAccount', function ($q) use ($customerIds) {
                    $q->whereIn('customer_id', $customerIds);
                });
                $accountQuery->whereIn('customer_id', $customerIds);
                $customerQuery->where('branch_id', $user->branch_id);
            }

            $transactionData[] = $query->count();
            $accountData[] = $customerQuery->whereDate('created_at', '<=', now()->subDays($i))->count();
            $balanceData[] = $accountQuery->sum('current_balance') / 1000; // Scale down for chart
        }

        $this->chartData = [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Transactions',
                    'data' => $transactionData,
                    'borderColor' => '#4F46E5',
                    'backgroundColor' => 'rgba(79, 70, 229, 0.1)',
                    'tension' => 0.4
                ],
                [
                    'label' => 'Total Customers',
                    'data' => $accountData,
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'tension' => 0.4
                ],
                [
                    'label' => 'Total Balance (in thousands)',
                    'data' => $balanceData,
                    'borderColor' => '#F59E0B',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'tension' => 0.4
                ]
            ]
        ];
    }

    public function loadRecentTransactions()
    {
        $query = Transaction::with(['sourceAccount.customer', 'destinationAccount.customer'])
            ->orderBy('created_at', 'desc')
            ->limit(10);

        $user = Auth::user();
        if (! $user instanceof \App\Models\Eloquent\User) {
            return;
        }

        if ($user->role !== 'super-admin' && $user->branch_id) {
            $customerIds = Customer::where('branch_id', $user->branch_id)->pluck('id');
            $query->where(function ($q) use ($customerIds) {
                $q->whereHas('sourceAccount', function ($q2) use ($customerIds) {
                    $q2->whereIn('customer_id', $customerIds);
                })->orWhereHas('destinationAccount', function ($q2) use ($customerIds) {
                    $q2->whereIn('customer_id', $customerIds);
                });
            });
        }

        $this->recentTransactions = $query->get();
    }

    public function loadPendingActions()
    {
        $user = Auth::user();
        if (! $user instanceof \App\Models\Eloquent\User) {
            return;
        }
        $this->pendingActions = [];

        // Pending KYC approvals
        $kycQuery = Customer::where('kyc_status', 'pending');
        if ($user->role !== 'super-admin' && $user->branch_id) {
            $kycQuery->where('branch_id', $user->branch_id);
        }

        $pendingKyc = $kycQuery->count();
        if ($pendingKyc > 0) {
            $this->pendingActions[] = [
                'type' => 'kyc',
                'count' => $pendingKyc,
                'message' => "$pendingKyc KYC applications pending review",
                'route' => 'customers.index',
                'icon' => 'shield-exclamation',
                'color' => 'warning'
            ];
        }

        // Pending loans
        $loanQuery = Loan::where('status', 'pending');
        if ($user->role !== 'super-admin' && $user->branch_id) {
            $customerIds = Customer::where('branch_id', $user->branch_id)->pluck('id');
            $loanQuery->whereIn('customer_id', $customerIds);
        }

        $pendingLoans = $loanQuery->count();
        if ($pendingLoans > 0) {
            $this->pendingActions[] = [
                'type' => 'loan',
                'count' => $pendingLoans,
                'message' => "$pendingLoans loan applications pending approval",
                'route' => 'loans.index',
                'icon' => 'document-text',
                'color' => 'info'
            ];
        }

        // Pending transactions (for tellers and managers)
        if (in_array($user->role, ['teller', 'manager'])) {
            $pendingTransactions = Transaction::where('status', 'pending')
                ->whereDate('created_at', today())
                ->count();

            if ($pendingTransactions > 0) {
                $this->pendingActions[] = [
                    'type' => 'transaction',
                    'count' => $pendingTransactions,
                    'message' => "$pendingTransactions transactions pending processing",
                    'route' => 'transactions.index',
                    'icon' => 'clock',
                    'color' => 'secondary'
                ];
            }
        }
    }

    public function updatedSelectedPeriod()
    {
        $this->loadDashboardData();
    }

    public function updatedDateRange()
    {
        $this->loadDashboardData();
    }

    private function applyPeriodFilter($query)
    {
        switch ($this->selectedPeriod) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                break;
            case 'year':
                $query->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]);
                break;
        }
    }

    #[Layout('layouts.main')]
    public function render()
    {
        return view('livewire.dashboard', [
            'role' => $this->role,
            'stats' => $this->stats,
            'chartData' => $this->chartData,
            'recentTransactions' => $this->recentTransactions,
            'pendingActions' => $this->pendingActions
        ]);
    }
}
