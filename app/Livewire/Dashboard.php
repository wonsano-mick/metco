<?php

namespace App\Livewire;

use App\Models\Eloquent\Account;
use App\Models\Eloquent\Branch;
use App\Models\Eloquent\Customer;
use App\Models\Eloquent\Loan;
use App\Models\Eloquent\Transaction;
use App\Models\Eloquent\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    public $selectedPeriod = 'today';
    public $dateRange = [];
    public $stats = [];
    public $chartData = [];
    public $recentTransactions = [];
    public $pendingActions = [];
    public $role;
    public $quickStats = [];

    // Performance metrics
    public $performanceMetrics = [];
    public $topPerformers = [];
    public $dailyTrend = [];
    public $branchPerformance = [];

    protected $queryString = ['selectedPeriod'];

    public function mount()
    {
        $this->role = Auth::user()->role;

        // Redirect tellers to their dashboard
        if ($this->role === 'teller') {
            session()->flash('success', 'Welcome to your dashboard, ' . Auth::user()->first_name . '!');
            return redirect()->route('teller.dashboard');
        }

        $this->selectedPeriod = 'week'; 
        $this->setDateRange($this->selectedPeriod);
        $this->loadDashboardData();
    }

    public function setDateRange($period)
    {
        $now = now();

        switch ($period) {
            case 'today':
                $this->dateRange = [
                    'start' => $now->format('Y-m-d'),
                    'end' => $now->format('Y-m-d')
                ];
                break;
            case 'week':
                $this->dateRange = [
                    'start' => $now->startOfWeek()->format('Y-m-d'),
                    'end' => $now->endOfWeek()->format('Y-m-d')
                ];
                break;
            case 'month':
                $this->dateRange = [
                    'start' => $now->startOfMonth()->format('Y-m-d'),
                    'end' => $now->endOfMonth()->format('Y-m-d')
                ];
                break;
            case 'quarter':
                $this->dateRange = [
                    'start' => $now->startOfQuarter()->format('Y-m-d'),
                    'end' => $now->endOfQuarter()->format('Y-m-d')
                ];
                break;
            case 'year':
                $this->dateRange = [
                    'start' => $now->startOfYear()->format('Y-m-d'),
                    'end' => $now->endOfYear()->format('Y-m-d')
                ];
                break;
            default:
                $this->dateRange = [
                    'start' => $now->startOfMonth()->format('Y-m-d'),
                    'end' => $now->endOfMonth()->format('Y-m-d')
                ];
                break;
        }
    }

    public function loadDashboardData()
    {
        $this->loadStats();
        $this->loadQuickStats();
        $this->loadChartData();
        $this->loadRecentTransactions();
        $this->loadPendingActions();
        $this->loadPerformanceMetrics();
        $this->loadDailyTrend();
    }

    public function loadStats()
    {
        $user = Auth::user();
        if (!$user instanceof \App\Models\Eloquent\User) {
            return;
        }

        $branchId = $user->branch_id;
        $startDate = $this->dateRange['start'] ?? now()->startOfMonth();
        $endDate = $this->dateRange['end'] ?? now()->endOfMonth();

        // Base queries
        $accountsQuery = Account::query();
        $customersQuery = Customer::query();
        $transactionsQuery = Transaction::whereBetween('created_at', [$startDate, $endDate]);
        $loansQuery = Loan::query();

        // Apply branch filter for non-super-admin
        if ($user->role !== 'super-admin' && $branchId) {
            $customersQuery->where('branch_id', $branchId);
            $customerIds = Customer::where('branch_id', $branchId)->pluck('id');
            $accountsQuery->whereIn('customer_id', $customerIds);
            $loansQuery->whereIn('customer_id', $customerIds);
        }

        // Calculate previous period for comparison
        $previousStart = Carbon::parse($startDate)->subDays(Carbon::parse($startDate)->diffInDays($endDate))->format('Y-m-d');
        $previousEnd = Carbon::parse($startDate)->subDay()->format('Y-m-d');

        $previousTransactionsQuery = Transaction::whereBetween('created_at', [$previousStart, $previousEnd]);

        // Current period values
        $currentBalance = $accountsQuery->sum('current_balance');
        $previousBalance = $accountsQuery->where('created_at', '<', $startDate)->sum('current_balance');
        $balanceChange = $previousBalance > 0 ? (($currentBalance - $previousBalance) / $previousBalance) * 100 : 0;

        $currentCustomers = $customersQuery->count();
        $previousCustomers = $customersQuery->where('created_at', '<', $startDate)->count();
        $customerChange = $previousCustomers > 0 ? (($currentCustomers - $previousCustomers) / $previousCustomers) * 100 : 0;

        $currentTransactions = $transactionsQuery->count();
        $previousTransactions = $previousTransactionsQuery->count();
        $transactionChange = $previousTransactions > 0 ? (($currentTransactions - $previousTransactions) / $previousTransactions) * 100 : 0;

        $currentVolume = $transactionsQuery->sum('amount');
        $previousVolume = $previousTransactionsQuery->sum('amount');
        $volumeChange = $previousVolume > 0 ? (($currentVolume - $previousVolume) / $previousVolume) * 100 : 0;

        $this->stats = [
            'total_balance' => [
                'value' => 'GH₵ ' . number_format($currentBalance, 2),
                'change' => $balanceChange,
                'trend' => $balanceChange >= 0 ? 'up' : 'down',
                'icon' => 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z',
                'color' => 'blue',
                'bgColor' => 'bg-blue-50',
                'textColor' => 'text-blue-600',
                'label' => 'Total Balance'
            ],
            'total_customers' => [
                'value' => number_format($currentCustomers),
                'change' => $customerChange,
                'trend' => $customerChange >= 0 ? 'up' : 'down',
                'icon' => 'M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z',
                'color' => 'green',
                'bgColor' => 'bg-green-50',
                'textColor' => 'text-green-600',
                'label' => 'Total Customers'
            ],
            'transactions' => [
                'value' => number_format($currentTransactions),
                'change' => $transactionChange,
                'trend' => $transactionChange >= 0 ? 'up' : 'down',
                'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
                'color' => 'purple',
                'bgColor' => 'bg-purple-50',
                'textColor' => 'text-purple-600',
                'label' => 'Transactions'
            ],
            'transaction_volume' => [
                'value' => 'GH₵ ' . number_format($currentVolume, 2),
                'change' => $volumeChange,
                'trend' => $volumeChange >= 0 ? 'up' : 'down',
                'icon' => 'M9 8h6m-6 4h6m-6 4h6M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z',
                'color' => 'amber',
                'bgColor' => 'bg-amber-50',
                'textColor' => 'text-amber-600',
                'label' => 'Transaction Volume'
            ],
        ];

        // Add role-specific stats
        if ($user->role === 'manager') {
            $pendingLoans = $loansQuery->where('status', 'pending')->count();
            $this->stats['pending_loans'] = [
                'value' => number_format($pendingLoans),
                'change' => 0,
                'trend' => 'neutral',
                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                'color' => 'red',
                'bgColor' => 'bg-red-50',
                'textColor' => 'text-red-600',
                'label' => 'Pending Loans'
            ];
        }

        if ($user->role === 'super-admin') {
            $this->stats['total_branches'] = [
                'value' => number_format(\App\Models\Eloquent\Branch::count()),
                'change' => 0,
                'trend' => 'neutral',
                'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                'color' => 'pink',
                'bgColor' => 'bg-pink-50',
                'textColor' => 'text-pink-600',
                'label' => 'Total Branches'
            ];
        }
    }

    public function loadQuickStats()
    {
        $user = Auth::user();
        $branchId = $user->branch_id;
        $today = now()->format('Y-m-d');

        // Today's transactions
        $todayQuery = Transaction::whereDate('created_at', $today);

        if ($user->role !== 'super-admin' && $branchId) {
            $customerIds = Customer::where('branch_id', $branchId)->pluck('id');
            $todayQuery->where(function ($q) use ($customerIds) {
                $q->whereHas('sourceAccount', fn($q) => $q->whereIn('customer_id', $customerIds))
                    ->orWhereHas('destinationAccount', fn($q) => $q->whereIn('customer_id', $customerIds));
            });
        }

        $this->quickStats = [
            'today_transactions' => $todayQuery->count(),
            'today_volume' => $todayQuery->sum('amount'),
            'active_accounts' => Account::where('status', 'active')->count(),
            'pending_kyc' => Customer::where('kyc_status', 'pending')
                ->when($branchId && $user->role !== 'super-admin', fn($q) => $q->where('branch_id', $branchId))
                ->count(),
        ];
    }

   public function loadChartData()
{
    $user = Auth::user();
    
    // Parse dates from dateRange
    $startDate = Carbon::parse($this->dateRange['start'])->startOfDay();
    $endDate = Carbon::parse($this->dateRange['end'])->endOfDay();
    $days = $startDate->diffInDays($endDate) + 1;

    // Get daily transaction counts for the actual date range
    $query = Transaction::whereBetween('created_at', [$startDate, $endDate])
        ->whereIn('status', ['completed', 'posted']);

    // Apply branch filter
    if ($user->role !== 'super-admin' && $user->branch_id) {
        $customerIds = Customer::where('branch_id', $user->branch_id)->pluck('id');
        $query->where(function($q) use ($customerIds) {
            $q->whereHas('sourceAccount', fn($q) => $q->whereIn('customer_id', $customerIds))
              ->orWhereHas('destinationAccount', fn($q) => $q->whereIn('customer_id', $customerIds));
        });
    }

    // Get daily transaction counts
    $dailyTransactions = (clone $query)
        ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
        ->groupBy('date')
        ->orderBy('date')
        ->get()
        ->keyBy('date');

    // Generate labels and data for the actual date range
    $labels = [];
    $transactionData = [];
    $balanceData = [];

    // Loop through each day in the date range (not fixed 30 days)
    for ($i = 0; $i < $days; $i++) {
        $date = $startDate->copy()->addDays($i);
        $dateStr = $date->format('Y-m-d');
        
        // Format label based on range length
        if ($days <= 7) {
            $labels[] = $date->format('D'); // Mon, Tue, etc.
        } elseif ($days <= 31) {
            $labels[] = $date->format('d M'); // 01 Feb
        } elseif ($days <= 90) {
            // For quarter view, show week numbers or month-day
            $labels[] = $date->format('d M');
        } else {
            // For year view, show month
            $labels[] = $date->format('M');
        }
        
        // Get transaction count for this day
        $transactionData[] = $dailyTransactions[$dateStr]->count ?? 0;
        
        // Get balance up to this day
        $balanceQuery = Account::whereDate('created_at', '<=', $date);
        
        if ($user->role !== 'super-admin' && $user->branch_id) {
            $customerIds = Customer::where('branch_id', $user->branch_id)->pluck('id');
            $balanceQuery->whereIn('customer_id', $customerIds);
        }
        
        $balanceData[] = $balanceQuery->sum('current_balance');
    }

    $this->chartData = [
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'Transactions',
                'data' => $transactionData,
                'borderColor' => '#3B82F6',
                'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                'tension' => 0.4,
                'fill' => true,
                'yAxisID' => 'y-transactions',
            ],
            [
                'label' => 'Total Balance (GH₵)',
                'data' => $balanceData,
                'borderColor' => '#10B981',
                'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                'tension' => 0.4,
                'fill' => true,
                'yAxisID' => 'y-balance',
            ]
        ]
    ];
    
    // Dispatch event to update chart
    $this->dispatch('chartUpdated');
}

    public function loadDailyTrend()
    {
        $user = Auth::user();
        $days = 7;
        $trend = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $query = Transaction::whereDate('created_at', $date);

            if ($user->role !== 'super-admin' && $user->branch_id) {
                $customerIds = Customer::where('branch_id', $user->branch_id)->pluck('id');
                $query->where(function ($q) use ($customerIds) {
                    $q->whereHas('sourceAccount', fn($q) => $q->whereIn('customer_id', $customerIds))
                        ->orWhereHas('destinationAccount', fn($q) => $q->whereIn('customer_id', $customerIds));
                });
            }

            $credits = (clone $query)->whereIn('type', ['deposit', 'cash_deposit', 'initial_deposit', 'transfer_in'])->sum('amount');
            $debits = (clone $query)->whereIn('type', ['withdrawal', 'cash_withdrawal', 'transfer_out'])->sum('amount');

            $trend[] = [
                'day' => $date->format('D'),
                'date' => $date->format('M d'),
                'credits' => $credits,
                'debits' => $debits,
                'net' => $credits - $debits,
            ];
        }

        $this->dailyTrend = $trend;
    }

    public function loadPerformanceMetrics()
{
    $user = Auth::user();
    $branchId = $user->branch_id;

    if ($user->role === 'manager') {
        // Get all tellers in the branch
        $tellers = User::where('role', 'teller')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->get();
        
        // Manually calculate transaction counts for each teller
        $tellerQuery = $tellers->map(function($teller) {
            $startDate = $this->dateRange['start'] ?? now()->startOfMonth();
            $endDate = $this->dateRange['end'] ?? now()->endOfMonth();
            
            // Count both initiated and completed transactions
            $transactionCount = Transaction::whereBetween('created_at', [$startDate, $endDate])
                ->where(function($q) use ($teller) {
                    $q->where('initiated_by', $teller->id)
                      ->orWhere('completed_by', $teller->id);
                })
                ->count();
            
            $teller->transaction_count = $transactionCount;
            return $teller;
        });

        $totalTellerTransactions = $tellerQuery->sum('transaction_count');
        $avgPerTeller = $tellerQuery->count() > 0 ? $totalTellerTransactions / $tellerQuery->count() : 0;

        $this->performanceMetrics = [
            'teller_count' => $tellerQuery->count(),
            'total_teller_transactions' => $totalTellerTransactions,
            'avg_per_teller' => round($avgPerTeller, 1),
            'top_tellers' => $tellerQuery->sortByDesc('transaction_count')->take(3)->values(),
        ];
    }

    if ($user->role === 'super-admin') {
        // Branch performance - same as above
        $branches = \App\Models\Eloquent\Branch::withCount(['customers'])
            ->get()
            ->map(function($branch) {
                $customerIds = $branch->customers()->pluck('id');
                
                $branch->accounts_count = Account::whereIn('customer_id', $customerIds)->count();
                
                $branch->transaction_volume = Transaction::whereBetween('created_at', [
                        $this->dateRange['start'] ?? now()->startOfMonth(), 
                        $this->dateRange['end'] ?? now()->endOfMonth()
                    ])
                    ->where(function($q) use ($customerIds) {
                        $q->whereHas('sourceAccount', fn($q) => $q->whereIn('customer_id', $customerIds))
                          ->orWhereHas('destinationAccount', fn($q) => $q->whereIn('customer_id', $customerIds));
                    })
                    ->sum('amount');
                
                return $branch;
            });

        $this->performanceMetrics = [
            'total_branches' => $branches->count(),
            'total_customers' => $branches->sum('customers_count'),
            'total_accounts' => $branches->sum('accounts_count'),
            'total_volume' => $branches->sum('transaction_volume'),
            'top_branches' => $branches->sortByDesc('transaction_volume')->take(3)->values(),
        ];
    }
}
/**
 * Load branch performance data - FIXED
 */
private function loadBranchPerformance()
{
    if (!$this->currentUser->isAdmin()) {
        // Non-admins only see their branch
        return;
    }

    try {
        $startOfDay = Carbon::parse($this->startDate)->startOfDay();
        $endOfDay = Carbon::parse($this->endDate)->endOfDay();

        // Get branches with customer count
        $this->branchPerformance = Branch::withCount(['customers'])
            ->get()
            ->map(function ($branch) use ($startOfDay, $endOfDay) {
                // Count active accounts through customers using the relationship
                $branch->accounts_count = $branch->accounts()
                    ->where('status', 'active')
                    ->count();

                // Sum account balances through customers
                $branch->accounts_sum_current_balance = $branch->accounts()
                    ->where('status', 'active')
                    ->sum('current_balance');

                // Get customer IDs for transaction queries
                $customerIds = $branch->customers()->pluck('id');

                // Get transaction data
                $transactions = Transaction::whereBetween('initiated_at', [$startOfDay, $endOfDay])
                    ->whereIn('status', ['completed', 'posted'])
                    ->where(function ($q) use ($customerIds) {
                        $q->whereHas('sourceAccount', fn($q) => $q->whereIn('customer_id', $customerIds))
                          ->orWhereHas('destinationAccount', fn($q) => $q->whereIn('customer_id', $customerIds));
                    })
                    ->select(
                        DB::raw('COUNT(*) as transaction_count'),
                        DB::raw('SUM(amount) as transaction_volume')
                    )
                    ->first();

                $branch->transaction_count = $transactions->transaction_count ?? 0;
                $branch->transaction_volume = $transactions->transaction_volume ?? 0;

                return $branch;
            });
    } catch (\Exception $e) {
        Log::error('Error loading branch performance: ' . $e->getMessage());
        $this->branchPerformance = collect();
    }
}

    public function loadRecentTransactions()
{
    $query = Transaction::with([
            'sourceAccount.customer', 
            'destinationAccount.customer', 
            'initiator'
        ])
        ->whereIn('status', ['completed', 'posted'])
        ->orderBy('created_at', 'desc')
        ->limit(10);

    $user = Auth::user();

    if ($user->role !== 'super-admin' && $user->branch_id) {
        $customerIds = Customer::where('branch_id', $user->branch_id)->pluck('id');
        $query->where(function ($q) use ($customerIds) {
            $q->whereHas('sourceAccount', fn($q2) => $q2->whereIn('customer_id', $customerIds))
              ->orWhereHas('destinationAccount', fn($q2) => $q2->whereIn('customer_id', $customerIds));
        });
    }

    $this->recentTransactions = $query->get();
}

    public function loadPendingActions()
    {
        $user = Auth::user();
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
                'message' => "$pendingKyc KYC " . ($pendingKyc === 1 ? 'application' : 'applications') . " pending review",
                'route' => 'customers.index',
                'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                'color' => 'yellow',
                'bgColor' => 'bg-yellow-50',
                'textColor' => 'text-yellow-700',
                'borderColor' => 'border-yellow-200',
                'iconBg' => 'bg-yellow-100',
                'iconColor' => 'text-yellow-600',
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
                'message' => "$pendingLoans loan " . ($pendingLoans === 1 ? 'application' : 'applications') . " pending approval",
                'route' => 'loans.index',
                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                'color' => 'blue',
                'bgColor' => 'bg-blue-50',
                'textColor' => 'text-blue-700',
                'borderColor' => 'border-blue-200',
                'iconBg' => 'bg-blue-100',
                'iconColor' => 'text-blue-600',
            ];
        }
    }

    public function updatedSelectedPeriod($value)
    {
        $this->setDateRange($value);
        $this->loadDashboardData();
        $this->dispatch('chartUpdated');
    }

    public function updatedDateRange()
    {
        $this->loadDashboardData();
    }

    #[Layout('layouts.main')]
    public function render()
    {
        return view('livewire.dashboard', [
            'role' => $this->role,
            'stats' => $this->stats,
            'quickStats' => $this->quickStats,
            'chartData' => $this->chartData,
            'recentTransactions' => $this->recentTransactions,
            'pendingActions' => $this->pendingActions,
            'performanceMetrics' => $this->performanceMetrics,
            'dailyTrend' => $this->dailyTrend,
        ]);
    }
}
