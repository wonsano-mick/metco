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

    private function loadBranchPerformance()
    {
        if (!$this->currentUser->isAdmin()) {
            // Non-admins only see their branch
            return;
        }

        try {
            $startOfDay = Carbon::parse($this->startDate)->startOfDay();
            $endOfDay = Carbon::parse($this->endDate)->endOfDay();

            $this->branchPerformance = Branch::withCount(['customers'])
                ->withCount(['accounts' => function ($q) {
                    $q->where('status', 'active');
                }])
                ->withSum(['accounts' => function ($q) {
                    $q->where('status', 'active');
                }], 'current_balance')
                ->get()
                ->map(function ($branch) use ($startOfDay, $endOfDay) {
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
