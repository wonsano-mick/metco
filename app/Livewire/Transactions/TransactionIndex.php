<?php

namespace App\Livewire\Transactions;

use App\Exports\TransactionsExport;
use App\Models\Eloquent\Account;
use App\Models\Eloquent\SystemAccount;
use App\Models\Eloquent\Transaction;
use App\Services\Transaction\TransactionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class TransactionIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $account_id = '';
    public $type = '';
    public $status = '';
    public $start_date = '';
    public $end_date = '';
    public $perPage = 20;
    public $showFilters = false;

    // Add these properties for reverse modal
    public $showReverseModal = false;
    public $transactionToReverse = null;
    public $reverseReason = '';

    public $accounts = [];
    public $systemAccounts = []; // Add system accounts
    public $transactionTypes = [
        'transfer' => 'Transfer',
        'withdrawal' => 'Withdrawal',
        'deposit' => 'Deposit',
        'reversal' => 'Reversal',
        'teller_topup' => 'Teller Topup',
        'initial_deposit' => 'Initial Deposit',
    ];
 
    public $statuses = [
        'pending' => 'Pending',
        'completed' => 'Completed',
        'failed' => 'Failed',
        'reversed' => 'Reversed',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'account_id' => ['except' => ''],
        'type' => ['except' => ''],
        'status' => ['except' => ''],
        'start_date' => ['except' => ''],
        'end_date' => ['except' => ''],
        'perPage' => ['except' => 20],
        'showFilters' => ['except' => false],
    ];

    /**
     * Load accounts based on user role
     */
    private function loadAccounts()
    {
        $user = Auth::user();
        if (! $user instanceof \App\Models\Eloquent\User) {
            $this->accounts = [];
            $this->systemAccounts = [];
            return;
        }

        // Load regular accounts
        if ($user->hasRole('super-admin') || $user->hasRole('manager')) {
            // Admins can see all active accounts
            $this->accounts = Account::with(['accountType', 'customer'])
                ->active()
                ->orderBy('account_number')
                ->get();
        } elseif ($user->isManager() || $user->isTeller()) {
            // Managers and tellers can see accounts in their branch
            $this->accounts = Account::with(['accountType', 'customer'])
                ->whereHas('customer', function ($query) use ($user) {
                    $query->where('branch_id', $user->branch_id);
                })
                ->active()
                ->orderBy('account_number')
                ->get();
        } else {
            // Regular users can only see their own accounts
            $this->accounts = Account::with(['accountType', 'customer'])
                ->where('customer_id', $user->customer_id)
                ->active()
                ->orderBy('account_number')
                ->get();
        }

        // Load system accounts for filtering (if needed)
        if ($user->hasRole('super-admin') || $user->hasRole('manager')) {
            // FIXED: Removed the non-existent 'systemAccountType' relationship
            // Just load the system accounts without any relationship
            $this->systemAccounts = SystemAccount::where('is_active', true)
                ->orderBy('code')
                ->get();
                
            // If you need to load a relationship, check what's actually available
            // For example, if there's a 'type' relationship, you could use:
            // $this->systemAccounts = SystemAccount::with('type')
            //     ->where('is_active', true)
            //     ->orderBy('code')
            //     ->get();
        }
    }

    public function mount()
    {
        $user = Auth::user();
        if ($user instanceof \App\Models\Eloquent\User) {
            Log::info('User in mount:', [
                'id' => $user->id,
                'role' => $user->role,
                'isTeller' => $user->isTeller(),
                'isManager' => $user->isManager(),
                'branch_id' => $user->branch_id,
                'hasRole_super-admin' => $user->hasRole('super-admin'),
                'hasRole_admin' => $user->hasRole('admin')
            ]);
        }

        $this->loadAccounts();

        // Check for active filters
        if ($this->search || $this->account_id || $this->type || $this->status || $this->start_date || $this->end_date) {
            $this->showFilters = true;
        }
    }

    /**
     * Export transactions
     */
    public function exportTransactions()
    {
        try {
            // Check permission
            if (!Gate::allows('export transaction reports')) {
                session()->flash('error', 'You are not authorized to export transactions.');
                return redirect()->route('transactions.index');
            }

            // Get filtered transactions without pagination
            $transactions = $this->getExportData();

            if ($transactions->isEmpty()) {
                session()->flash('error', 'No transaction to export.');
                return redirect()->route('transactions.index');
            }

            // Prepare filter information
            $filters = $this->prepareFilterInfo();

            // Generate filename with timestamp
            $filename = 'transactions_export_' . now()->format('Y_m_d_His') . '.xlsx';

            session()->flash('success', 'Transactions exported successfully. Check your downloads folder');
            // Dispatch download event
            return Excel::download(new TransactionsExport($transactions, $filters), $filename);
        } catch (\Exception $e) {
            Log::error('Transaction export error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            session()->flash('error', 'Failed to export. ' . $e->getMessage());
            return redirect()->route('transactions.index');
        }
    }

    /**
     * Get transactions for export (without pagination)
     */
    private function getExportData()
    {
        $user = Auth::user();
        if (! $user instanceof \App\Models\Eloquent\User) {
            return collect();
        }

        $query = Transaction::with([
            'ledgerEntries.account.customer',
            'initiator',
            'completer',
            'approver',
            'canceller',
            'sourceAccount.customer',
            'destinationAccount.customer'
        ]);

        // Only try to load systemLedgerEntries if the relationship exists
        if (method_exists(Transaction::class, 'systemLedgerEntries')) {
            $query->with('systemLedgerEntries.systemAccount');
        }

        // Apply role-based filtering
        $this->applyRoleBasedFiltering($query, $user);

        // Apply filters
        if ($this->account_id) {
            $query->whereHas('ledgerEntries', function ($q) {
                $q->where('account_id', $this->account_id);
            });
        }

        if ($this->type) {
            $query->where('type', $this->type);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('transaction_reference', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhere('notes', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->start_date) {
            $query->where('initiated_at', '>=', $this->start_date);
        }

        if ($this->end_date) {
            $query->where('initiated_at', '<=', $this->end_date . ' 23:59:59');
        }

        return $query->orderBy('initiated_at', 'desc')->get();
    }

    /**
     * Prepare filter information for export
     */
    private function prepareFilterInfo(): array
    {
        $filters = [];

        if ($this->search) $filters['search'] = $this->search;
        if ($this->account_id) $filters['account'] = $this->account_id;
        if ($this->status) $filters['status'] = $this->status;
        if ($this->type) $filters['type'] = $this->type;
        if ($this->start_date) $filters['start_date'] = $this->start_date;
        if ($this->end_date) $filters['end_date'] = $this->end_date;

        return $filters;
    }

    /**
     * Confirm transaction reversal
     */
    public function confirmReverse($transactionId)
    {
        $query = Transaction::with(['ledgerEntries.account']);

        // Only load systemLedgerEntries if the relationship exists
        if (method_exists(Transaction::class, 'systemLedgerEntries')) {
            $query->with('systemLedgerEntries.systemAccount');
        }

        $this->transactionToReverse = $query->findOrFail($transactionId);

        // Check if transaction can be reversed
        if (!$this->transactionToReverse->isCompleted()) {
            session()->flash('error', 'Only completed transactions can be reversed.');
            return redirect()->route('transactions.index');
        }

        if ($this->transactionToReverse->isReversed()) {
            session()->flash('error', 'Transaction already reversed');
            return redirect()->route('transactions.index');
        }

        // Check permissions (only admin can reverse)
        if (!Gate::allows('reverse transactions')) {
            session()->flash('error', 'Unauthorized to reverse transactions');
            return redirect()->route('transactions.index');
        }

        $this->showReverseModal = true;
    }

    public function closeReverseModal()
    {
        $this->showReverseModal = false;
        $this->transactionToReverse = null;
        $this->reverseReason = '';
    }

    public function reverseTransaction()
    {
        $this->validate([
            'reverseReason' => 'nullable|string|max:500',
        ]);

        try {
            $transactionService = app(TransactionService::class);
            $reversal = $transactionService->reverse(
                $this->transactionToReverse->id,
                $this->reverseReason
            );
            session()->flash('success', 'Transaction reversed successfully');
            // Close modal and reset
            $this->closeReverseModal();
            return redirect()->route('transactions.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to reverse transaction: ' . $e->getMessage());
            return redirect()->route('transactions.index');
        }
    }

    /**
     * View transaction details
     */
    public function viewTransaction($transactionId)
    {
        $query = Transaction::with([
            'ledgerEntries.account',
            'initiator',
            'sourceAccount',
            'destinationAccount'
        ]);

        // Only load systemLedgerEntries if the relationship exists
        if (method_exists(Transaction::class, 'systemLedgerEntries')) {
            $query->with('systemLedgerEntries.systemAccount');
        }

        $transaction = $query->findOrFail($transactionId);

        // Check permissions
        $user = Auth::user();
        if (! $user instanceof \App\Models\Eloquent\User) {
            return;
        }

        if (!Gate::allows('view transactions')) {
            $userAccountIds = Account::where('customer_id', $user->customer_id)
                ->pluck('id')
                ->toArray();

            $transactionAccountIds = $transaction->ledgerEntries->pluck('account_id')->toArray();

            if (!array_intersect($userAccountIds, $transactionAccountIds)) {
                session()->flash('error', 'Unauthorized to view this transaction');
                return redirect()->route('transactions.index');
            }
        }

        // Dispatch event to show modal with transaction details
        $this->dispatch('open-transaction-modal', [
            'transaction' => $transaction,
            'canReverse' => $user->hasRole('admin') && $transaction->isCompleted() && !$transaction->isReversed(),
        ]);
    }

    public function exportReceipt($transactionId)
    {
        $transaction = Transaction::findOrFail($transactionId);

        // Generate receipt
        session()->flash('success', 'Receipt downloaded for transaction #' . $transaction->transaction_reference);
        return redirect()->route('transactions.index');
    }

    // Add clear search method
    public function clearSearch()
    {
        $this->search = '';
        $this->resetPage();
    }

    // Filter updating methods
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingAccountId()
    {
        $this->resetPage();
    }

    public function updatingType()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingStartDate()
    {
        $this->resetPage();
    }

    public function updatingEndDate()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function resetFilters()
    {
        $this->reset(['search', 'account_id', 'type', 'status', 'start_date', 'end_date']);
        $this->resetPage();
        $this->showFilters = false;

        $this->dispatch(
            'showToast',
            message: 'Filters cleared successfully.',
            type: 'info'
        );
    }

    /**
     * Apply role-based filtering to the query
     */
    private function applyRoleBasedFiltering($query, $user)
    {
        // Super-admin and admin can see all transactions
        if ($user->hasRole('super-admin') || $user->hasRole('admin')) {
            Log::info('Admin user - no filtering applied');
            return;
        }

        // For managers: show ALL transactions from their branch
        if ($user->isManager()) {
            Log::info('Manager filtering - branch_id: ' . $user->branch_id);

            $query->where(function ($q) use ($user) {
                // Transactions initiated by ANY user in the manager's branch
                $q->whereHas('initiator', function ($subQ) use ($user) {
                    $subQ->where('branch_id', $user->branch_id);
                })
                    // Or transactions completed by ANY user in the manager's branch
                    ->orWhereHas('completer', function ($subQ) use ($user) {
                        $subQ->where('branch_id', $user->branch_id);
                    })
                    // Or transactions involving ANY customer accounts from the manager's branch
                    ->orWhereHas('ledgerEntries.account.customer', function ($subQ) use ($user) {
                        $subQ->where('branch_id', $user->branch_id);
                    });

                // Only add systemLedgerEntries filtering if the relationship exists
                if (method_exists(Transaction::class, 'systemLedgerEntries')) {
                    $q->orWhereHas('systemLedgerEntries', function ($subQ) use ($user) {
                        $subQ->whereHas('systemAccount', function ($systemQ) use ($user) {
                            // Check if branch_id exists on system_accounts table
                            if (Schema::hasColumn('system_accounts', 'branch_id')) {
                                $systemQ->where('branch_id', $user->branch_id);
                            }
                        });
                    });
                }
            });
            return;
        }

        // For tellers: show ONLY their own transactions
        if ($user->isTeller()) {
            Log::info('Teller filtering - user_id: ' . $user->id . ', branch_id: ' . $user->branch_id);

            $query->where(function ($q) use ($user) {
                // ONLY transactions initiated by this specific teller
                $q->where('initiated_by', $user->id)
                    // OR transactions completed by this specific teller
                    ->orWhere('completed_by', $user->id);
            });
            return;
        }

        // For regular users (customers): only show their own transactions
        Log::info('Customer filtering - customer_id: ' . $user->customer_id);

        $userAccountIds = Account::where('customer_id', $user->customer_id)
            ->pluck('id')
            ->toArray();

        $query->whereHas('ledgerEntries', function ($q) use ($userAccountIds) {
            $q->whereIn('account_id', $userAccountIds);
        });
    }

    public function getTransactionsProperty()
    {
        $user = Auth::user();
        if (! $user instanceof \App\Models\Eloquent\User) {
            return null;
        }

        // Debug: Log user information
        Log::info('User accessing transactions:', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'is_teller' => $user->isTeller(),
            'is_manager' => $user->isManager(),
            'branch_id' => $user->branch_id,
            'customer_id' => $user->customer_id
        ]);

        $query = Transaction::with([
            'ledgerEntries.account',
            'initiator',
            'completer'
        ]);

        // Only load systemLedgerEntries if the relationship exists
        if (method_exists(Transaction::class, 'systemLedgerEntries')) {
            $query->with('systemLedgerEntries.systemAccount');
        }

        // Apply role-based filtering
        $this->applyRoleBasedFiltering($query, $user);

        // Debug: Log the SQL query for tellers
        if ($user->isTeller()) {
            $sql = $query->toSql();
            $bindings = $query->getBindings();
            Log::info('Teller query:', ['sql' => $sql, 'bindings' => $bindings]);
        }

        // Apply filters
        if ($this->account_id) {
            $query->whereHas('ledgerEntries', function ($q) {
                $q->where('account_id', $this->account_id);
            });
        }

        if ($this->type) {
            $query->where('type', $this->type);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('transaction_reference', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhere('notes', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->start_date) {
            $query->where('initiated_at', '>=', $this->start_date);
        }

        if ($this->end_date) {
            $query->where('initiated_at', '<=', $this->end_date . ' 23:59:59');
        }

        $results = $query->orderBy('initiated_at', 'desc')->paginate($this->perPage);

        // Debug: Log the count for tellers
        if ($user->isTeller()) {
            Log::info('Teller results count:', ['count' => $results->total()]);
        }

        return $results;
    }

    public function getHasActiveFiltersProperty()
    {
        return $this->search || $this->account_id || $this->type || $this->status || $this->start_date || $this->end_date;
    }

    public function getActiveFiltersCountProperty()
    {
        $count = 0;
        if ($this->search) $count++;
        if ($this->account_id) $count++;
        if ($this->type) $count++;
        if ($this->status) $count++;
        if ($this->start_date) $count++;
        if ($this->end_date) $count++;
        return $count;
    }

    public function getStatsProperty()
    {
        $user = Auth::user();
        if (! $user instanceof \App\Models\Eloquent\User) {
            return [
                'total' => 0,
                'completed' => 0,
                'pending' => 0,
                'failed' => 0,
            ];
        }

        $query = Transaction::query();

        // Apply role-based filtering
        $this->applyRoleBasedFiltering($query, $user);

        // Apply the same filters as the main query
        if ($this->account_id) {
            $query->whereHas('ledgerEntries', function ($q) {
                $q->where('account_id', $this->account_id);
            });
        }

        if ($this->type) {
            $query->where('type', $this->type);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('transaction_reference', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhere('notes', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->start_date) {
            $query->where('initiated_at', '>=', $this->start_date);
        }

        if ($this->end_date) {
            $query->where('initiated_at', '<=', $this->end_date . ' 23:59:59');
        }

        return [
            'total' => $query->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'failed' => (clone $query)->where('status', 'failed')->count(),
        ];
    }

    #[Layout('layouts.main')]
    public function render()
    {
        return view('livewire.transactions.transaction-index', [
            'transactions' => $this->transactions,
            'stats' => $this->stats,
            'hasActiveFilters' => $this->hasActiveFilters,
            'activeFiltersCount' => $this->activeFiltersCount,
            'showReverseModal' => $this->showReverseModal,
            'transactionToReverse' => $this->transactionToReverse,
            'canCreate' => Gate::allows('create transactions'),
            'exportReceipt' => Gate::allows('export transaction reports'),
            'reverseTransaction' => Gate::allows('reverse transactions'),
            'viewTransaction' => Gate::allows('view transactions'),
        ]);
    }
}