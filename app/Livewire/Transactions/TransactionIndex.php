<?php

namespace App\Livewire\Transactions;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Eloquent\Account;
use App\Exports\TransactionsExport;
use Illuminate\Support\Facades\Log;
use App\Models\Eloquent\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\Transaction\TransactionService;

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
    public $transactionTypes = [
        'transfer' => 'Transfer',
        'withdrawal' => 'Withdrawal',
        'deposit' => 'Deposit',
        'reversal' => 'Reversal',
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

    // Add the loadAccounts method
    private function loadAccounts()
    {
        $user = Auth::user();
        if (! $user instanceof \App\Models\Eloquent\User) {
            return;
        }

        if (!$user) {
            $this->accounts = [];
            return;
        }

        if (Gate::allows('create transactions')) {
            // Admins can see all active accounts
            $this->accounts = Account::with(['accountType', 'customer'])
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
    }

    public function mount()
    {
        $this->loadAccounts();

        // Check for active filters
        if ($this->search || $this->account_id || $this->type || $this->status || $this->start_date || $this->end_date) {
            $this->showFilters = true;
        }
    }

    private function showToast($message, $type = 'success')
    {
        $this->dispatch('showToast', message: $message, type: $type);
    }
    
    // Add export functionality
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

            session()->flash('error', 'Failed to export.'.$e->getMessage());
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

        // Filter by user's accounts (for non-admin users)
        if (!$user->hasRole('super-admin')) {
            $userAccountIds = Account::where('customer_id', Auth::user()->customer_id)
                ->pluck('id')
                ->toArray();

            $query->whereHas('ledgerEntries', function ($q) use ($userAccountIds) {
                $q->whereIn('account_id', $userAccountIds);
            });
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
        if ($this->type) $filters['account'] = $this->type;
        if ($this->end_date) $filters['end_date'] = $this->end_date;

        return $filters;
    }

    // Add these methods for reverse functionality
    public function confirmReverse($transactionId)
    {
        $this->transactionToReverse = Transaction::with(['ledgerEntries.account'])
            ->findOrFail($transactionId);

        // Check if transaction can be reversed
        if (!$this->transactionToReverse->isCompleted()) {
 
            session()->flash('error', 'Only completed transactions can be reserved.');
            return redirect()->route('transactions.index');
        }

        if ($this->transactionToReverse->isReversed()) {

            session()->flash('error', 'Transaction already reversed');
            return redirect()->route('transactions.index');
        }

        // Check permissions (only admin can reverse)
        if (!Gate::allows('reverse transactions')) {

            session()->flash('error', 'Unauthorized to reserve transactions');
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

            session()->flash('error', 'Failed to reverse transaction'.$e->getMessage());
            return redirect()->route('transactions.index');
        }
    }

    // view transaction method
    public function viewTransaction($transactionId)
    {
        $transaction = Transaction::with(['ledgerEntries.account', 'initiator', 'sourceAccount', 'destinationAccount'])
            ->findOrFail($transactionId);

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

    public function getTransactionsProperty()
    {
        $user = Auth::user();
        if (! $user instanceof \App\Models\Eloquent\User) {
            return;
        }
        $query = Transaction::with(['ledgerEntries.account', 'initiator']);

        // Filter by user's accounts (for non-admin users)
        if (!$user->hasRole('super-admin')) {
            $userAccountIds = Account::where('customer_id', Auth::user()->customer_id)
                ->pluck('id')
                ->toArray();

            $query->whereHas('ledgerEntries', function ($q) use ($userAccountIds) {
                $q->whereIn('account_id', $userAccountIds);
            });
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

        return $query->orderBy('initiated_at', 'desc')->paginate($this->perPage);
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
            return;
        }

        if (Gate::allows('view transactions')) {
            $query = Transaction::query();
        } else {
            $userAccountIds = Account::where('customer_id', $user->customer_id)
                ->pluck('id')
                ->toArray();

            $query = Transaction::whereHas('ledgerEntries', function ($q) use ($userAccountIds) {
                $q->whereIn('account_id', $userAccountIds);
            });
        }

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

        if ($this->start_date) {
            $query->where('initiated_at', '>=', $this->start_date);
        }

        if ($this->end_date) {
            $query->where('initiated_at', '<=', $this->end_date . ' 23:59:59');
        }

        return [
            'total' => $query->count(),
            'completed' => $query->clone()->where('status', 'completed')->count(),
            'pending' => $query->clone()->where('status', 'pending')->count(),
            'failed' => $query->clone()->where('status', 'failed')->count(),
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
 