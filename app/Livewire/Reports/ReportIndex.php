<?php

namespace App\Livewire\Reports;

use App\Models\Eloquent\Account;
use App\Models\Eloquent\Branch;
use App\Models\Eloquent\Customer;
use App\Models\Eloquent\SystemAccount;
use App\Models\Eloquent\SystemLedgerEntry;
use App\Models\Eloquent\Transaction;
use App\Models\Eloquent\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ReportIndex extends Component
{
    use WithPagination;

    // Report type selection
    #[Url(history: true)]
    public $activeReport = 'dashboard';

    // Date range filters
    #[Url(history: true)]
    public $dateRange = 'this_month';
    public $startDate;
    public $endDate;

    // Account selection for statements
    public $searchAccount = '';
    public $selectedAccount = null;
    public $accounts = [];

    // Report specific filters
    public $selectedBranch = '';
    public $selectedUser = '';
    public $selectedCustomer = '';
    public $selectedTransactionType = '';
    public $selectedStatus = '';

    // Statistics and chart data
    public $dashboardStats = [];
    public $chartData = [];
    public $recentTransactions = [];
    public $topCustomers = [];
    public $branchPerformance = [];

    // Quick stats
    public $totalAccounts = 0;
    public $totalCustomers = 0;
    public $totalTransactions = 0;
    public $totalVolume = 0;
    public $activeUsers = 0;

    // User info
    public \App\Models\Eloquent\User $currentUser;
    public $tellerAccount = null;

    // Available report types
    public $reportTypes = [
        'dashboard' => 'Dashboard Overview',
        'account_statement' => 'Account Statement',
        'transaction_report' => 'Transaction Report',
        'customer_report' => 'Customer Report',
        'branch_report' => 'Branch Performance',
        'daily_summary' => 'Daily Summary',
        'monthly_summary' => 'Monthly Summary',
        'audit_trail' => 'Audit Trail',
        'revenue_report' => 'Revenue Report',
        'account_analysis' => 'Account Analysis',
    ];

    protected $listeners = [
        'refreshReports' => 'loadDashboardData',
        'accountSelected' => 'handleAccountSelected'
    ];

    public function mount()
    {
        /** @var User $user */
        $this->currentUser = Auth::user();

        if (!$this->currentUser) {
            abort(403, 'No authenticated user');
        }

        $this->setDateRange($this->dateRange);

        // Check authorization
        if (!Gate::allows('view reports')) {
            abort(403, 'Unauthorized access to reports.');
        }

        // Load teller account if user is teller
        if ($this->currentUser->isTeller()) {
            $this->loadTellerAccount();
        }

        // Load initial data
        $this->loadDashboardData();
        $this->loadQuickStats();
    }

    /**
     * Load teller account for current user
     */
    private function loadTellerAccount()
    {
        try {
            // Try to find by code pattern first
            $this->tellerAccount = SystemAccount::where('type', 'teller')
                ->where('code', 'TELLER-' . str_pad($this->currentUser->id, 5, '0', STR_PAD_LEFT))
                ->first();

            // If not found, try by metadata
            if (!$this->tellerAccount) {
                $this->tellerAccount = SystemAccount::where('type', 'teller')
                    ->where('metadata->user_id', $this->currentUser->id)
                    ->first();
            }
        } catch (\Exception $e) {
            Log::error('Error loading teller account: ' . $e->getMessage());
            $this->tellerAccount = null;
        }
    }

    public function updatedActiveReport($value)
    {
        $this->resetPage();

        if ($value === 'dashboard') {
            $this->loadDashboardData();
        } elseif ($value === 'account_statement') {
            $this->loadAccounts();
        }
    }

    public function updatedDateRange($value)
    {
        $this->setDateRange($value);
        $this->loadDashboardData();
    }

    public function updatedSearchAccount()
    {
        if (strlen($this->searchAccount) >= 2) {
            $this->searchAccounts();
        } else {
            $this->accounts = [];
        }
    }

    public function searchAccounts()
    {
        try {
            $user = $this->currentUser;

            $query = Account::with(['customer', 'accountType'])
                ->where(function ($q) {
                    $q->where('account_number', 'like', '%' . $this->searchAccount . '%')
                        ->orWhereHas('customer', function ($customerQuery) {
                            $customerQuery->where('first_name', 'like', '%' . $this->searchAccount . '%')
                                ->orWhere('last_name', 'like', '%' . $this->searchAccount . '%')
                                ->orWhere('company_name', 'like', '%' . $this->searchAccount . '%')
                                ->orWhere('customer_number', 'like', '%' . $this->searchAccount . '%');
                        });
                });

            // Apply branch restriction if not admin
            if (!$user->isAdmin() && $user->branch_id) {
                $query->whereHas('customer', function ($q) use ($user) {
                    $q->where('branch_id', $user->branch_id);
                });
            }

            // For tellers, only show accounts they have access to
            if ($user->isTeller()) {
                // Tellers can see all accounts in their branch
                $query->whereHas('customer', function ($q) use ($user) {
                    $q->where('branch_id', $user->branch_id);
                });
            }

            $this->accounts = $query->limit(10)->get();
        } catch (\Exception $e) {
            Log::error('Error searching accounts: ' . $e->getMessage());
            $this->accounts = [];
        }
    }

    public function selectAccount($accountId)
    {
        $this->selectedAccount = Account::with(['customer', 'accountType'])->find($accountId);
        $this->accounts = [];
        $this->searchAccount = $this->selectedAccount->account_number . ' - ' . $this->selectedAccount->customer->full_name;
    }

    public function clearSelectedAccount()
    {
        $this->selectedAccount = null;
        $this->searchAccount = '';
        $this->accounts = [];
    }

    public function generateStatement()
    {
        if (!$this->selectedAccount) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Please select an account first.'
            ]);
            return;
        }

        return redirect()->route('reports.accounts.statement', [
            'accountId' => $this->selectedAccount->id
        ]);
    }

    private function setDateRange($range)
    {
        $now = Carbon::now();

        switch ($range) {
            case 'today':
                $this->startDate = $now->format('Y-m-d');
                $this->endDate = $now->format('Y-m-d');
                break;

            case 'yesterday':
                $yesterday = $now->subDay();
                $this->startDate = $yesterday->format('Y-m-d');
                $this->endDate = $yesterday->format('Y-m-d');
                break;

            case 'this_week':
                $this->startDate = $now->startOfWeek()->format('Y-m-d');
                $this->endDate = $now->endOfWeek()->format('Y-m-d');
                break;

            case 'last_week':
                $lastWeek = $now->subWeek();
                $this->startDate = $lastWeek->startOfWeek()->format('Y-m-d');
                $this->endDate = $lastWeek->endOfWeek()->format('Y-m-d');
                break;

            case 'this_month':
                $this->startDate = $now->startOfMonth()->format('Y-m-d');
                $this->endDate = $now->endOfMonth()->format('Y-m-d');
                break;

            case 'last_month':
                $lastMonth = $now->subMonth();
                $this->startDate = $lastMonth->startOfMonth()->format('Y-m-d');
                $this->endDate = $lastMonth->endOfMonth()->format('Y-m-d');
                break;

            case 'this_quarter':
                $this->startDate = $now->startOfQuarter()->format('Y-m-d');
                $this->endDate = $now->endOfQuarter()->format('Y-m-d');
                break;

            case 'this_year':
                $this->startDate = $now->startOfYear()->format('Y-m-d');
                $this->endDate = $now->endOfYear()->format('Y-m-d');
                break;

            case 'last_year':
                $lastYear = $now->subYear();
                $this->startDate = $lastYear->startOfYear()->format('Y-m-d');
                $this->endDate = $lastYear->endOfYear()->format('Y-m-d');
                break;

            case 'custom':
                // Keep existing custom dates or set defaults
                if (!$this->startDate) {
                    $this->startDate = $now->startOfMonth()->format('Y-m-d');
                }
                if (!$this->endDate) {
                    $this->endDate = $now->format('Y-m-d');
                }
                break;

            default:
                $this->startDate = $now->startOfMonth()->format('Y-m-d');
                $this->endDate = $now->endOfMonth()->format('Y-m-d');
                break;
        }
    }

    private function loadQuickStats()
    {
        try {
            $user = $this->currentUser;

            // Base queries with branch restrictions
            $accountsQuery = Account::query();
            $customersQuery = Customer::query();
            $transactionsQuery = Transaction::whereIn('status', ['completed', 'posted']);

            // Apply branch restriction if not admin
            if (!$user->isAdmin() && $user->branch_id) {
                $customersQuery->where('branch_id', $user->branch_id);
                $accountsQuery->whereHas('customer', function ($q) use ($user) {
                    $q->where('branch_id', $user->branch_id);
                });
            }

            // For tellers, only count transactions they performed
            if ($user->isTeller()) {
                $transactionsQuery->where(function ($q) use ($user) {
                    $q->where('initiated_by', $user->id)
                        ->orWhere('completed_by', $user->id);
                });
            } elseif (!$user->isAdmin() && $user->branch_id) {
                // For branch managers, count transactions in their branch
                $transactionsQuery->whereHas('sourceAccount.customer', function ($q) use ($user) {
                    $q->where('branch_id', $user->branch_id);
                })->orWhereHas('destinationAccount.customer', function ($q) use ($user) {
                    $q->where('branch_id', $user->branch_id);
                });
            }

            $this->totalAccounts = $accountsQuery->count();
            $this->totalCustomers = $customersQuery->count();
            $this->totalTransactions = $transactionsQuery->count();
            $this->totalVolume = $transactionsQuery->sum('amount');
            $this->activeUsers = User::where('status', 'active')->count();
        } catch (\Exception $e) {
            Log::error('Error loading quick stats: ' . $e->getMessage());
        }
    }

    private function loadDashboardData()
    {
        try {
            $this->loadTransactionStats();
            $this->loadChartData();
            $this->loadRecentTransactions();
            $this->loadTopCustomers();
            $this->loadBranchPerformance();
        } catch (\Exception $e) {
            Log::error('Error loading dashboard data: ' . $e->getMessage());
        }
    }

    private function loadTransactionStats()
    {
        $startOfDay = Carbon::parse($this->startDate)->startOfDay();
        $endOfDay = Carbon::parse($this->endDate)->endOfDay();

        $user = $this->currentUser;

        $query = Transaction::whereBetween('initiated_at', [$startOfDay, $endOfDay])
            ->whereIn('status', ['completed', 'posted']);

        // Apply role-based restrictions
        if ($user->isTeller()) {
            // Tellers only see their own transactions
            $query->where(function ($q) use ($user) {
                $q->where('initiated_by', $user->id)
                    ->orWhere('completed_by', $user->id);
            });
        } elseif (!$user->isAdmin() && $user->branch_id) {
            // Branch managers see transactions in their branch
            $query->where(function ($q) use ($user) {
                $q->whereHas('sourceAccount.customer', function ($customerQuery) use ($user) {
                    $customerQuery->where('branch_id', $user->branch_id);
                })->orWhereHas('destinationAccount.customer', function ($customerQuery) use ($user) {
                    $customerQuery->where('branch_id', $user->branch_id);
                });
            });
        }

        $stats = [
            'total_transactions' => $query->count(),
            'total_volume' => $query->sum('amount'),
            'total_fees' => $query->sum('fee_amount'),
            'total_tax' => $query->sum('tax_amount'),
            'avg_transaction' => $query->avg('amount') ?? 0,
        ];

        // Get breakdown by type
        $byType = $query->select('type', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
            ->groupBy('type')
            ->get();

        $stats['by_type'] = [];
        foreach ($byType as $item) {
            $stats['by_type'][$item->type] = [
                'count' => $item->count,
                'total' => $item->total
            ];
        }

        // Get breakdown by status
        $byStatus = $query->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        $stats['by_status'] = [];
        foreach ($byStatus as $item) {
            $stats['by_status'][$item->status] = $item->count;
        }

        $this->dashboardStats = $stats;
    }

    private function loadChartData()
    {
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);
        $days = $startDate->diffInDays($endDate) + 1;

        $user = $this->currentUser;

        // Determine grouping based on date range
        if ($days <= 31) {
            // Daily grouping
            $groupBy = DB::raw("DATE(initiated_at) as date_group");
            $orderBy = 'date_group';
        } elseif ($days <= 90) {
            // Weekly grouping
            $groupBy = DB::raw("YEARWEEK(initiated_at) as date_group");
            $orderBy = 'date_group';
        } else {
            // Monthly grouping
            $groupBy = DB::raw("DATE_FORMAT(initiated_at, '%Y-%m') as date_group");
            $orderBy = 'date_group';
        }

        $query = Transaction::whereBetween('initiated_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->whereIn('status', ['completed', 'posted']);

        // Apply role-based restrictions
        if ($user->isTeller()) {
            $query->where(function ($q) use ($user) {
                $q->where('initiated_by', $user->id)
                    ->orWhere('completed_by', $user->id);
            });
        } elseif (!$user->isAdmin() && $user->branch_id) {
            $query->where(function ($q) use ($user) {
                $q->whereHas('sourceAccount.customer', function ($customerQuery) use ($user) {
                    $customerQuery->where('branch_id', $user->branch_id);
                })->orWhereHas('destinationAccount.customer', function ($customerQuery) use ($user) {
                    $customerQuery->where('branch_id', $user->branch_id);
                });
            });
        }

        $transactions = $query->select(
            $groupBy,
            DB::raw('SUM(CASE WHEN destination_account_id IS NOT NULL THEN amount ELSE 0 END) as credits'),
            DB::raw('SUM(CASE WHEN source_account_id IS NOT NULL THEN amount ELSE 0 END) as debits'),
            DB::raw('COUNT(*) as count')
        )
            ->groupBy('date_group')
            ->orderBy($orderBy)
            ->get();

        $labels = [];
        $credits = [];
        $debits = [];
        $counts = [];

        foreach ($transactions as $transaction) {
            if ($days <= 31) {
                $labels[] = Carbon::parse($transaction->date_group)->format('d M');
            } elseif ($days <= 90) {
                $year = substr($transaction->date_group, 0, 4);
                $week = substr($transaction->date_group, 4);
                $labels[] = "Week $week";
            } else {
                $labels[] = $transaction->date_group;
            }

            $credits[] = (float) ($transaction->credits ?? 0);
            $debits[] = (float) ($transaction->debits ?? 0);
            $counts[] = $transaction->count;
        }

        $this->chartData = [
            'labels' => $labels,
            'credits' => $credits,
            'debits' => $debits,
            'counts' => $counts,
        ];
    }

    private function loadRecentTransactions()
    {
        try {
            $user = $this->currentUser;

            $query = Transaction::with([
                'sourceAccount.customer',
                'destinationAccount.customer',
                'initiator'
            ])
                ->whereIn('status', ['completed', 'posted'])
                ->orderBy('initiated_at', 'desc')
                ->limit(10);

            // Apply role-based restrictions
            if ($user->isTeller()) {
                // Tellers only see their own transactions
                $query->where(function ($q) use ($user) {
                    $q->where('initiated_by', $user->id)
                        ->orWhere('completed_by', $user->id);
                });

                // Also include teller cash transactions
                if ($this->tellerAccount) {
                    $tellerTransactionIds = SystemLedgerEntry::where('system_account_id', $this->tellerAccount->id)
                        ->whereNotNull('transaction_id')
                        ->pluck('transaction_id');

                    if ($tellerTransactionIds->isNotEmpty()) {
                        $query->orWhereIn('id', $tellerTransactionIds);
                    }
                }
            } elseif (!$user->isAdmin() && $user->branch_id) {
                // Branch managers see transactions in their branch
                $query->where(function ($q) use ($user) {
                    $q->whereHas('sourceAccount.customer', function ($customerQuery) use ($user) {
                        $customerQuery->where('branch_id', $user->branch_id);
                    })->orWhereHas('destinationAccount.customer', function ($customerQuery) use ($user) {
                        $customerQuery->where('branch_id', $user->branch_id);
                    });
                });
            }

            $this->recentTransactions = $query->get();
        } catch (\Exception $e) {
            Log::error('Error loading recent transactions: ' . $e->getMessage());
            $this->recentTransactions = collect();
        }
    }

    private function loadTopCustomers()
    {
        try {
            $user = $this->currentUser;

            $query = Customer::withCount(['accounts' => function ($q) {
                $q->where('status', 'active');
            }])
                ->withSum('accounts as total_balance', 'current_balance')
                ->where('status', 'active')
                ->orderBy('total_balance', 'desc')
                ->limit(5);

            // Apply branch restriction
            if (!$user->isAdmin() && $user->branch_id) {
                $query->where('branch_id', $user->branch_id);
            }

            // For tellers, only show customers they've transacted with
            if ($user->isTeller()) {
                $customerIds = Transaction::where(function ($q) use ($user) {
                    $q->where('initiated_by', $user->id)
                        ->orWhere('completed_by', $user->id);
                })
                    ->where(function ($q) {
                        $q->whereNotNull('source_account_id')
                            ->orWhereNotNull('destination_account_id');
                    })
                    ->get()
                    ->map(function ($transaction) {
                        if ($transaction->sourceAccount) {
                            return $transaction->sourceAccount->customer_id;
                        }
                        if ($transaction->destinationAccount) {
                            return $transaction->destinationAccount->customer_id;
                        }
                        return null;
                    })
                    ->filter()
                    ->unique();

                if ($customerIds->isNotEmpty()) {
                    $query->whereIn('id', $customerIds);
                } else {
                    $query->whereRaw('1 = 0'); // No results
                }
            }

            $this->topCustomers = $query->get();
        } catch (\Exception $e) {
            Log::error('Error loading top customers: ' . $e->getMessage());
            $this->topCustomers = collect();
        }
    }

    /**
     * Load branch performance data
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

            // Fix: Use withCount on the relationship name that exists on Branch model
            $this->branchPerformance = Branch::withCount(['customers']) // This now works with the added relationship
                ->get()
                ->map(function ($branch) use ($startOfDay, $endOfDay) {
                    // Count active accounts
                    $branch->accounts_count = Account::whereHas('customer', function ($q) use ($branch) {
                        $q->where('branch_id', $branch->id);
                    })->where('status', 'active')->count();

                    // Sum account balances
                    $branch->accounts_sum_current_balance = Account::whereHas('customer', function ($q) use ($branch) {
                        $q->where('branch_id', $branch->id);
                    })->where('status', 'active')->sum('current_balance');

                    // Get transaction data
                    $transactions = Transaction::whereBetween('initiated_at', [$startOfDay, $endOfDay])
                        ->whereIn('status', ['completed', 'posted'])
                        ->where(function ($q) use ($branch) {
                            $q->whereHas('sourceAccount.customer', function ($customerQuery) use ($branch) {
                                $customerQuery->where('branch_id', $branch->id);
                            })->orWhereHas('destinationAccount.customer', function ($customerQuery) use ($branch) {
                                $customerQuery->where('branch_id', $branch->id);
                            });
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

    public function loadAccounts()
    {
        // This method will be called when switching to account_statement report
        // We don't need to load all accounts here, they're loaded via search
    }

    public function getTransactionReportProperty()
    {
        $startOfDay = Carbon::parse($this->startDate)->startOfDay();
        $endOfDay = Carbon::parse($this->endDate)->endOfDay();

        $user = $this->currentUser;

        $query = Transaction::with(['sourceAccount.customer', 'destinationAccount.customer', 'initiator'])
            ->whereBetween('initiated_at', [$startOfDay, $endOfDay])
            ->orderBy('initiated_at', 'desc');

        // Apply filters
        if ($this->selectedTransactionType) {
            $query->where('type', $this->selectedTransactionType);
        }

        if ($this->selectedStatus) {
            $query->where('status', $this->selectedStatus);
        }

        // Apply role-based restrictions
        if ($user->isTeller()) {
            $query->where(function ($q) use ($user) {
                $q->where('initiated_by', $user->id)
                    ->orWhere('completed_by', $user->id);
            });
        } elseif (!$user->isAdmin() && $user->branch_id) {
            $query->where(function ($q) use ($user) {
                $q->whereHas('sourceAccount.customer', function ($customerQuery) use ($user) {
                    $customerQuery->where('branch_id', $user->branch_id);
                })->orWhereHas('destinationAccount.customer', function ($customerQuery) use ($user) {
                    $customerQuery->where('branch_id', $user->branch_id);
                });
            });
        }

        return $query->paginate(15);
    }

    public function getCustomerReportProperty()
    {
        $user = $this->currentUser;

        $query = Customer::with(['branch', 'accounts' => function ($q) {
            $q->where('status', 'active');
        }])
            ->withCount(['accounts' => function ($q) {
                $q->where('status', 'active');
            }])
            ->withSum('accounts', 'current_balance');

        // Apply filters
        if ($this->selectedBranch) {
            $query->where('branch_id', $this->selectedBranch);
        }

        if ($this->selectedCustomer) {
            $query->where('id', $this->selectedCustomer);
        }

        // Apply branch restriction
        if (!$user->isAdmin() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        // For tellers, only show customers they've transacted with
        if ($user->isTeller()) {
            $customerIds = Transaction::where(function ($q) use ($user) {
                $q->where('initiated_by', $user->id)
                    ->orWhere('completed_by', $user->id);
            })
                ->where(function ($q) {
                    $q->whereNotNull('source_account_id')
                        ->orWhereNotNull('destination_account_id');
                })
                ->get()
                ->map(function ($transaction) {
                    if ($transaction->sourceAccount) {
                        return $transaction->sourceAccount->customer_id;
                    }
                    if ($transaction->destinationAccount) {
                        return $transaction->destinationAccount->customer_id;
                    }
                    return null;
                })
                ->filter()
                ->unique();

            if ($customerIds->isNotEmpty()) {
                $query->whereIn('id', $customerIds);
            } else {
                $query->whereRaw('1 = 0'); // No results
            }
        }

        return $query->paginate(15);
    }

    /**
     * Get Daily Summary Report Data
     */
    public function getDailySummaryProperty()
    {
        $startDate = Carbon::parse($this->startDate)->startOfDay();
        $endDate = Carbon::parse($this->endDate)->endOfDay();
        $user = $this->currentUser;

        // Get all days in the range
        $dates = [];
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $dates[] = $currentDate->format('Y-m-d');
            $currentDate->addDay();
        }

        $dailyData = [];

        foreach ($dates as $date) {
            $dayStart = Carbon::parse($date)->startOfDay();
            $dayEnd = Carbon::parse($date)->endOfDay();

            $query = Transaction::whereBetween('initiated_at', [$dayStart, $dayEnd])
                ->whereIn('status', ['completed', 'posted']);

            // Apply role-based restrictions
            if ($user->isTeller()) {
                $query->where(function ($q) use ($user) {
                    $q->where('initiated_by', $user->id)
                        ->orWhere('completed_by', $user->id);
                });
            } elseif (!$user->isAdmin() && $user->branch_id) {
                $query->where(function ($q) use ($user) {
                    $q->whereHas('sourceAccount.customer', function ($customerQuery) use ($user) {
                        $customerQuery->where('branch_id', $user->branch_id);
                    })->orWhereHas('destinationAccount.customer', function ($customerQuery) use ($user) {
                        $customerQuery->where('branch_id', $user->branch_id);
                    });
                });
            }

            $dailyData[] = [
                'date' => Carbon::parse($date)->format('D, M d, Y'),
                'raw_date' => $date,
                'transaction_count' => $query->count(),
                'total_volume' => $query->sum('amount'),
                'total_fees' => $query->sum('fee_amount'),
                'total_tax' => $query->sum('tax_amount'),
                'deposits' => (clone $query)->whereIn('type', ['deposit', 'cash_deposit', 'initial_deposit'])->sum('amount'),
                'withdrawals' => (clone $query)->whereIn('type', ['withdrawal', 'cash_withdrawal'])->sum('amount'),
                'transfers' => (clone $query)->whereIn('type', ['transfer', 'internal_transfer', 'external_transfer'])->sum('amount'),
                'unique_customers' => $this->getUniqueCustomersCount($query),
            ];
        }

        return collect($dailyData);
    }

    /**
     * Get Monthly Summary Report Data
     */
    public function getMonthlySummaryProperty()
    {
        $startDate = Carbon::parse($this->startDate)->startOfDay();
        $endDate = Carbon::parse($this->endDate)->endOfDay();
        $user = $this->currentUser;

        // Group by month
        $months = [];
        $currentDate = clone $startDate;

        while ($currentDate <= $endDate) {
            $monthKey = $currentDate->format('Y-m');
            $monthStart = (clone $currentDate)->startOfMonth();
            $monthEnd = (clone $currentDate)->endOfMonth();

            if (!isset($months[$monthKey])) {
                $query = Transaction::whereBetween('initiated_at', [$monthStart, $monthEnd])
                    ->whereIn('status', ['completed', 'posted']);

                // Apply role-based restrictions
                if ($user->isTeller()) {
                    $query->where(function ($q) use ($user) {
                        $q->where('initiated_by', $user->id)
                            ->orWhere('completed_by', $user->id);
                    });
                } elseif (!$user->isAdmin() && $user->branch_id) {
                    $query->where(function ($q) use ($user) {
                        $q->whereHas('sourceAccount.customer', function ($customerQuery) use ($user) {
                            $customerQuery->where('branch_id', $user->branch_id);
                        })->orWhereHas('destinationAccount.customer', function ($customerQuery) use ($user) {
                            $customerQuery->where('branch_id', $user->branch_id);
                        });
                    });
                }

                $months[$monthKey] = [
                    'month' => $currentDate->format('F Y'),
                    'year' => $currentDate->format('Y'),
                    'month_num' => $currentDate->format('m'),
                    'transaction_count' => $query->count(),
                    'total_volume' => $query->sum('amount'),
                    'total_fees' => $query->sum('fee_amount'),
                    'avg_transaction' => $query->avg('amount') ?? 0,
                    'deposits' => (clone $query)->whereIn('type', ['deposit', 'cash_deposit', 'initial_deposit'])->sum('amount'),
                    'withdrawals' => (clone $query)->whereIn('type', ['withdrawal', 'cash_withdrawal'])->sum('amount'),
                    'transfers' => (clone $query)->whereIn('type', ['transfer', 'internal_transfer', 'external_transfer'])->sum('amount'),
                    'fees_collected' => (clone $query)->where('type', 'like', '%fee%')->sum('amount'),
                    'unique_customers' => $this->getUniqueCustomersCount($query),
                    'peak_day' => $this->getPeakDay($monthStart, $monthEnd),
                    'peak_volume' => $this->getPeakVolume($monthStart, $monthEnd),
                ];
            }

            $currentDate->addMonth();
        }

        return collect(array_values($months));
    }

    /**
     * Get Audit Trail Data
     */
    public function getAuditTrailProperty()
    {
        $startDate = Carbon::parse($this->startDate)->startOfDay();
        $endDate = Carbon::parse($this->endDate)->endOfDay();

        $query = \App\Models\Eloquent\AuditLog::with(['user'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($this->selectedUser) {
            $query->where('user_id', $this->selectedUser);
        }

        if ($this->selectedTransactionType) {
            $query->where('action', $this->selectedTransactionType);
        }

        // Apply role-based restrictions
        $user = $this->currentUser;
        if ($user->isTeller()) {
            // Tellers only see their own audit logs
            $query->where('user_id', $user->id);
        } elseif (!$user->isAdmin() && $user->branch_id) {
            // Branch managers see logs from their branch
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            });
        }

        return $query->paginate(20);
    }

    /**
 * Get Account Analysis Data
 */
public function getAccountAnalysisProperty()
{
    $user = $this->currentUser;
    
    $query = Account::with(['customer', 'accountType']);
     
    // Apply branch restriction
    if (!$user->isAdmin() && $user->branch_id) {
        $query->whereHas('customer', function ($q) use ($user) {
            $q->where('branch_id', $user->branch_id);
        });
    }
    
    // For tellers, only show accounts they've transacted with
    if ($user->isTeller()) {
        $accountIds = Transaction::where(function ($q) use ($user) {
                $q->where('initiated_by', $user->id)
                  ->orWhere('completed_by', $user->id);
            })
            ->where(function ($q) {
                $q->whereNotNull('source_account_id')
                  ->orWhereNotNull('destination_account_id');
            })
            ->get()
            ->flatMap(function ($transaction) {
                $ids = [];
                if ($transaction->source_account_id) {
                    $ids[] = $transaction->source_account_id;
                }
                if ($transaction->destination_account_id) {
                    $ids[] = $transaction->destination_account_id;
                }
                return $ids;
            })
            ->unique()
            ->values()
            ->toArray();
        
        if (!empty($accountIds)) {
            $query->whereIn('id', $accountIds);
        } else {
            $query->whereRaw('1 = 0'); // No results
        }
    }
    
    // Get accounts with pagination
    $accounts = $query->paginate(20);
    
    // Use the accessors we defined in the Account model
    foreach ($accounts as $account) {
        // These will use the accessors we just added
        $account->transactions_count = $account->transactions_count;
        $account->transactions_total = $account->transactions_total;
        
        // Get last activity
        $lastTransaction = Transaction::where(function ($q) use ($account) {
                $q->where('source_account_id', $account->id)
                  ->orWhere('destination_account_id', $account->id);
            })
            ->whereIn('status', ['completed', 'posted'])
            ->orderBy('initiated_at', 'desc')
            ->first();
        
        $account->last_activity = $lastTransaction ? $lastTransaction->initiated_at : null;
    }
    
    // Calculate additional metrics
    $totalAccounts = Account::count();
    $activeAccounts = Account::where('status', 'active')->count();
    
    // Get dormant accounts (no transactions in last 3 months)
    $threeMonthsAgo = now()->subMonths(3);
    $activeAccountIds = Account::where('status', 'active')->pluck('id');
    
    $accountsWithTransactions = Transaction::where(function ($q) use ($activeAccountIds) {
            $q->whereIn('source_account_id', $activeAccountIds)
              ->orWhereIn('destination_account_id', $activeAccountIds);
        })
        ->where('initiated_at', '>=', $threeMonthsAgo)
        ->whereIn('status', ['completed', 'posted'])
        ->get()
        ->flatMap(function ($transaction) {
            $ids = [];
            if ($transaction->source_account_id) {
                $ids[] = $transaction->source_account_id;
            }
            if ($transaction->destination_account_id) {
                $ids[] = $transaction->destination_account_id;
            }
            return $ids;
        })
        ->unique()
        ->toArray();
    
    $dormantAccounts = $activeAccountIds->diff($accountsWithTransactions)->count();
    
    // Balance ranges
    $balanceRanges = [
        '0-1000' => Account::where('current_balance', '>=', 0)->where('current_balance', '<', 1000)->count(),
        '1000-10000' => Account::where('current_balance', '>=', 1000)->where('current_balance', '<', 10000)->count(),
        '10000-50000' => Account::where('current_balance', '>=', 10000)->where('current_balance', '<', 50000)->count(),
        '50000-100000' => Account::where('current_balance', '>=', 50000)->where('current_balance', '<', 100000)->count(),
        '100000+' => Account::where('current_balance', '>=', 100000)->count(),
    ];
    
    return [
        'accounts' => $accounts,
        'summary' => [
            'total_accounts' => $totalAccounts,
            'active_accounts' => $activeAccounts,
            'dormant_accounts' => $dormantAccounts,
            'inactive_rate' => $totalAccounts > 0 ? round(($dormantAccounts / $totalAccounts) * 100, 2) : 0,
            'avg_balance' => Account::avg('current_balance'),
            'total_balance' => Account::sum('current_balance'),
            'balance_ranges' => $balanceRanges,
        ]
    ];
}

    /**
     * Get Revenue Report Data
     */
    public function getRevenueReportProperty()
    {
        $startDate = Carbon::parse($this->startDate)->startOfDay();
        $endDate = Carbon::parse($this->endDate)->endOfDay();
        $user = $this->currentUser;

        // Base query for fee transactions
        $query = Transaction::whereBetween('initiated_at', [$startDate, $endDate])
            ->where(function ($q) {
                $q->where('type', 'like', '%fee%')
                    ->orWhere('type', 'interest')
                    ->orWhere('type', 'charge')
                    ->orWhere('type', 'commission');
            })
            ->whereIn('status', ['completed', 'posted']);

        // Apply role-based restrictions
        if ($user->isTeller()) {
            $query->where(function ($q) use ($user) {
                $q->where('initiated_by', $user->id)
                    ->orWhere('completed_by', $user->id);
            });
        } elseif (!$user->isAdmin() && $user->branch_id) {
            $query->where(function ($q) use ($user) {
                $q->whereHas('sourceAccount.customer', function ($customerQuery) use ($user) {
                    $customerQuery->where('branch_id', $user->branch_id);
                })->orWhereHas('destinationAccount.customer', function ($customerQuery) use ($user) {
                    $customerQuery->where('branch_id', $user->branch_id);
                });
            });
        }

        // Revenue by category
        $byCategory = (clone $query)
            ->select('type', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
            ->groupBy('type')
            ->get();

        // Revenue by day
        $byDay = (clone $query)
            ->select(DB::raw('DATE(initiated_at) as date'), DB::raw('sum(amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Revenue by branch (admin only)
        $byBranch = [];
        if ($user->isAdmin()) {
            $byBranch = Branch::withCount(['customers'])
                ->get()
                ->map(function ($branch) use ($startDate, $endDate) {
                    $revenue = Transaction::whereBetween('initiated_at', [$startDate, $endDate])
                        ->where(function ($q) {
                            $q->where('type', 'like', '%fee%')
                                ->orWhere('type', 'interest')
                                ->orWhere('type', 'charge')
                                ->orWhere('type', 'commission');
                        })
                        ->whereIn('status', ['completed', 'posted'])
                        ->where(function ($q) use ($branch) {
                            $q->whereHas('sourceAccount.customer', function ($customerQuery) use ($branch) {
                                $customerQuery->where('branch_id', $branch->id);
                            })->orWhereHas('destinationAccount.customer', function ($customerQuery) use ($branch) {
                                $customerQuery->where('branch_id', $branch->id);
                            });
                        })
                        ->sum('amount');

                    $branch->revenue = $revenue;
                    return $branch;
                });
        }

        return [
            'summary' => [
                'total_revenue' => $query->sum('amount'),
                'total_transactions' => $query->count(),
                'avg_revenue_per_transaction' => $query->avg('amount') ?? 0,
                'daily_avg' => $byDay->avg('total') ?? 0,
                'projected_monthly' => $this->calculateProjectedRevenue($byDay, $startDate, $endDate),
            ],
            'by_category' => $byCategory,
            'by_day' => $byDay,
            'by_branch' => $byBranch,
        ];
    }

    /**
     * Helper: Get unique customers count from transaction query
     */
    private function getUniqueCustomersCount($query)
    {
        $customerIds = (clone $query)->get()
            ->flatMap(function ($transaction) {
                $customers = [];
                if ($transaction->sourceAccount && $transaction->sourceAccount->customer) {
                    $customers[] = $transaction->sourceAccount->customer_id;
                }
                if ($transaction->destinationAccount && $transaction->destinationAccount->customer) {
                    $customers[] = $transaction->destinationAccount->customer_id;
                }
                return $customers;
            })
            ->unique();

        return $customerIds->count();
    }

    /**
     * Helper: Get peak day transaction count
     */
    private function getPeakDay($monthStart, $monthEnd)
    {
        $peak = Transaction::whereBetween('initiated_at', [$monthStart, $monthEnd])
            ->whereIn('status', ['completed', 'posted'])
            ->select(DB::raw('DATE(initiated_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('count', 'desc')
            ->first();

        return $peak ? Carbon::parse($peak->date)->format('M d') . ' (' . $peak->count . ')' : 'N/A';
    }

    /**
     * Helper: Get peak day transaction volume
     */
    private function getPeakVolume($monthStart, $monthEnd)
    {
        $peak = Transaction::whereBetween('initiated_at', [$monthStart, $monthEnd])
            ->whereIn('status', ['completed', 'posted'])
            ->select(DB::raw('DATE(initiated_at) as date'), DB::raw('sum(amount) as volume'))
            ->groupBy('date')
            ->orderBy('volume', 'desc')
            ->first();

        return $peak ? Carbon::parse($peak->date)->format('M d') . ' (GHS ' . number_format($peak->volume, 2) . ')' : 'N/A';
    }

    /**
     * Helper: Calculate projected monthly revenue
     */
    private function calculateProjectedRevenue($byDay, $startDate, $endDate)
    {
        if ($byDay->isEmpty()) {
            return 0;
        }

        $daysInRange = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $dailyAvg = $byDay->avg('total');
        $daysInMonth = Carbon::now()->daysInMonth;

        return $dailyAvg * $daysInMonth;
    }

    public function exportReport($format = 'pdf')
    {
        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Export feature coming soon!'
        ]);
    }

    #[Layout('layouts.main')]
    public function render()
    {
        return view('livewire.reports.report-index', [
            'branches' => Branch::all(),
            'users' => User::where('status', 'active')->get(),
            'customers' => Customer::where('status', 'active')->limit(100)->get(),
            'transactionTypes' => Transaction::distinct('type')->pluck('type'),
            'reportData' => $this->getReportData(),
            'isTeller' => $this->currentUser->isTeller(),
            'isAdmin' => $this->currentUser->isAdmin(),
            'isBranchManager' => $this->currentUser->isBranchManager(),
        ]);
    }

    private function getReportData()
    {
        switch ($this->activeReport) {
            case 'transaction_report':
                return $this->transaction_report;
            case 'customer_report':
                return $this->customer_report;
            default:
                return null;
        }
    }
}
