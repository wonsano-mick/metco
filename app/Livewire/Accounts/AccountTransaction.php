<?php

namespace App\Livewire\Accounts;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Eloquent\Account;
use App\Models\Eloquent\LedgerEntry;
use Illuminate\Support\Facades\Gate;

class AccountTransaction extends Component
{
    use WithPagination;

    public Account $account;
    public $search = '';
    public $entryType = '';
    public $status = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $amountMin = '';
    public $amountMax = '';
    public $perPage = 10;
    public $viewType = 'list'; // 'list' or 'details'
    public $showFilters = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'entryType' => ['except' => ''],
        'status' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'amountMin' => ['except' => ''],
        'amountMax' => ['except' => ''],
        'perPage' => ['except' => 10],
        'viewType' => ['except' => 'list'],
        'showFilters' => ['except' => false],
    ];

    public function mount(Account $account)
    {
        $this->account = Account::with(['customer', 'accountType'])
            ->findOrFail($account->id);
        // Authorization check
        if (!Gate::allows('view transactions', $this->account)) {
            abort(403, 'Unauthorized access.');
        }
    }

    public function updating($property, $value)
    {
        if (in_array($property, ['search', 'entryType', 'status', 'dateFrom', 'dateTo', 'amountMin', 'amountMax'])) {
            $this->resetPage();
        }
    }

    public function resetFilters()
    {
        $this->reset(['search', 'entryType', 'status', 'dateFrom', 'dateTo', 'amountMin', 'amountMax', 'showFilters']);
        $this->resetPage();
    }

    public function getLedgerEntriesProperty()
    {
        $query = LedgerEntry::query()
            ->with([
                'transaction' => function ($q) {
                    $q->with(['sourceAccount.customer', 'destinationAccount.customer', 'initiator']);
                },
                'account.customer'
            ])
            ->where('account_id', $this->account->id)
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('transaction', function ($q2) {
                    $q2->where('transaction_reference', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                })
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->entryType) {
            $query->where('entry_type', $this->entryType);
        }

        if ($this->status) {
            $query->whereHas('transaction', function ($q) {
                $q->where('status', $this->status);
            });
        }

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        if ($this->amountMin !== '') {
            $query->where('amount', '>=', (float) $this->amountMin);
        }

        if ($this->amountMax !== '') {
            $query->where('amount', '<=', (float) $this->amountMax);
        }

        return $query->paginate($this->perPage);
    }

    public function getEntryTypesProperty()
    {
        return ['credit', 'debit'];
    }

    public function getTransactionStatusesProperty()
    {
        return LedgerEntry::whereHas('transaction')
            ->with(['transaction' => function ($q) {
                $q->select('status')->distinct();
            }])
            ->where('account_id', $this->account->id)
            ->get()
            ->pluck('transaction.status')
            ->filter()
            ->unique()
            ->values();
    }

    public function getHasActiveFiltersProperty()
    {
        return $this->search || $this->entryType || $this->status || $this->dateFrom ||
            $this->dateTo || $this->amountMin !== '' || $this->amountMax !== '';
    }

    public function getTransactionSummaryProperty()
    {
        $allEntries = LedgerEntry::where('account_id', $this->account->id)
            ->with('transaction')
            ->get();

        $totalCredits = $allEntries->where('entry_type', 'credit')
            ->where('is_reversed', false)
            ->sum('amount');

        $totalDebits = $allEntries->where('entry_type', 'debit')
            ->where('is_reversed', false)
            ->sum('amount');

        $completedEntries = $allEntries->filter(function ($entry) {
            return $entry->transaction && $entry->transaction->status === 'completed';
        });

        $reversedEntries = $allEntries->where('is_reversed', true);

        return [
            'total_count' => $allEntries->count(),
            'completed_count' => $completedEntries->count(),
            'credit_count' => $allEntries->where('entry_type', 'credit')->count(),
            'debit_count' => $allEntries->where('entry_type', 'debit')->count(),
            'total_credits' => (float) $totalCredits,
            'total_debits' => (float) $totalDebits,
            'net_flow' => (float) ($totalCredits - $totalDebits),
            'reversed_count' => $reversedEntries->count(),
        ];
    }

    public function toggleView()
    {
        $this->viewType = $this->viewType === 'list' ? 'details' : 'list';
    }

    public function exportTransactions()
    {
        // Export functionality
        // You can implement CSV/Excel export here
        $this->dispatch(
            'showToast',
            message: 'Export feature will be implemented soon.',
            type: 'info'
        );
    }

    public function back(){
       return redirect()->route('accounts.index');
    }

    #[Layout('layouts.main')]
    public function render()
    {
        return view('livewire.accounts.account-transaction', [
            'canCreate' => Gate::allows('create accounts'),
            'ledgerEntries' => $this->ledgerEntries,
            'entryTypes' => $this->entryTypes,
            'transactionStatuses' => $this->transactionStatuses,
            'transactionSummary' => $this->transactionSummary,
            'hasActiveFilters' => $this->hasActiveFilters,
            'showFilters' => $this->showFilters,
            'viewType' => $this->viewType, 
            'account' => $this->account,
        ]);
    }
}