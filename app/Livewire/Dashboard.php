<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Eloquent\Loan;
use App\Models\Eloquent\User;
use Livewire\Attributes\Layout;
use App\Models\Eloquent\Account;
use App\Models\Eloquent\Customer;
use Illuminate\Support\Facades\DB;
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
                'value' => number_format($accountsQuery->sum('current_balance'), 2),
                'change' => '+12.5%',
                'icon' => 'currency-dollar',
                'color' => 'green',
                'label' => 'Total Balance'
            ],
            'total_customers' => [
                'value' => $customersQuery->count(),
                'change' => '+8.2%',
                'icon' => 'users',
                'color' => 'blue',
                'label' => 'Total Customers'
            ],
            'transactions_today' => [
                'value' => $transactionsQuery->whereDate('created_at', today())->count(),
                'change' => '+15.3%',
                'icon' => 'arrow-right-left',
                'color' => 'purple',
                'label' => 'Today\'s Transactions'
            ],
            'pending_loans' => [
                'value' => $loansQuery->where('status', 'pending')->count(),
                'change' => '-3.1%',
                'icon' => 'document-text',
                'color' => 'yellow',
                'label' => 'Pending Loans'
            ],
            'active_accounts' => [
                'value' => $accountsQuery->where('status', 'active')->count(),
                'change' => '+5.7%',
                'icon' => 'credit-card',
                'color' => 'indigo',
                'label' => 'Active Accounts'
            ],
            'kyc_pending' => [
                'value' => Customer::where('kyc_status', 'pending')->when(
                    $branchId && $user->role !== 'super-admin',
                    fn($q) => $q->where('branch_id', $branchId)
                )->count(),
                'change' => '+22.4%',
                'icon' => 'shield-check',
                'color' => 'red',
                'label' => 'KYC Pending'
            ]
        ];

        // Role-specific stats
        if ($user->role === 'manager') {
            $this->stats['branch_performance'] = [
                'value' => '₦' . number_format($transactionsQuery->sum('amount'), 2),
                'change' => '+18.2%',
                'icon' => 'chart-bar',
                'color' => 'teal',
                'label' => 'Branch Performance'
            ];
        }

        if ($user->role === 'super-admin') {
            $this->stats['total_branches'] = [
                'value' => \App\Models\Eloquent\Branch::count(),
                'change' => '+2.3%',
                'icon' => 'building-office',
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
