<?php

namespace App\Livewire\Accounts;

use Livewire\Component;
use App\Enums\AccountStatus;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Eloquent\Account;
use App\Models\Eloquent\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Eloquent\AccountType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Exports\AccountsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Pagination\LengthAwarePaginator;

class AccountIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $accountType = '';
    public $status = '';
    public $branchId = '';
    public $currency = '';
    public $balanceMin = '';
    public $balanceMax = '';
    public $customerId = '';
    public $perPage = 10;

    public $showFilters = false;
    public $showDeleteModal = false;
    public $accountToDelete = null;
    public $showFreezeModal = false;
    public $accountToFreeze = null;

    public $accountTypes = [];
    public $branches = [];
    public $currencies = ['GHS'];
    public $customers = [];
    public $statuses = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'accountType' => ['except' => ''],
        'status' => ['except' => ''],
        'branchId' => ['except' => ''],
        'currency' => ['except' => ''],
        'balanceMin' => ['except' => ''],
        'balanceMax' => ['except' => ''],
        'customerId' => ['except' => ''],
        'perPage' => ['except' => 10],
        'showFilters' => ['except' => false],
    ];

    public function mount()
    {
        $user = Auth::user();
        if (! $user instanceof \App\Models\Eloquent\User) {
            return;
        }

        if (!$user) {
            abort(403, 'Unauthorized access.');
        }

        // Authorization check
        if (!Gate::allows('view accounts')) {
            abort(403, 'Unauthorized access.');
        }

        // Load account types
        try {
            $this->accountTypes = AccountType::orderBy('name')->get();
        } catch (\Exception $e) {
            $this->accountTypes = collect();
            Log::error('Error loading account types: ' . $e->getMessage());
        }

        // Load branches if user has access
        if ($user->can('view all branches')) {
            $this->branches = \App\Models\Eloquent\Branch::orderBy('name')->get();
        } elseif ($user->branch_id) {
            $this->branches = \App\Models\Eloquent\Branch::where('id', $user->branch_id)->get();
        } else {
            $this->branches = collect();
        }

        // Load statuses from enum
        $this->statuses = AccountStatus::cases();

        // Load customers for filter
        try {
            $this->customers = Customer::whereHas('accounts')
                ->withCount('accounts')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();
        } catch (\Exception $e) {
            $this->customers = collect();
            Log::error('Error loading customers: ' . $e->getMessage());
        }

        // Show filters if any are active
        if ($this->hasActiveFilters) {
            $this->showFilters = true;
        }
    }

    public function updating($property, $value)
    {
        if (in_array($property, ['search', 'accountType', 'status', 'branchId', 'currency', 'balanceMin', 'balanceMax', 'customerId'])) {
            $this->resetPage();
        }
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function resetFilters()
    {
        $this->reset(['search', 'accountType', 'status', 'branchId', 'currency', 'balanceMin', 'balanceMax', 'customerId']);
        $this->resetPage();
        $this->showFilters = false;
        session()->flash('info', 'Filters cleared successfully.');
        return redirect()->route('accounts.index');
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        try {
            $account = Account::with(['customer', 'accountType'])->find($id);

            if (!$account) {
                session()->flash('error', 'Account not found.');
                return redirect()->route('accounts.index');
            }

            if (!Gate::allows('delete accounts', $account)) {
                session()->flash('error', 'You are not authorized to delete this account.');
                return redirect()->route('accounts.index');
            }

            if ($account->status === 'closed') {
                session()->flash('error', 'This account is already closed.');
                return redirect()->route('accounts.index');
            }

            $this->accountToDelete = $account;
            $this->showDeleteModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
            return redirect()->route('accounts.index');
        }
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->accountToDelete = null;
    }

    public function deleteAccount()
    {
        if (!$this->accountToDelete) {
            session()->flash('error', 'Account not found.');
            $this->closeDeleteModal();
            return redirect()->route('accounts.index');
        }

        try {
            $accountNumber = $this->accountToDelete->account_number;

            // Close the account instead of deleting
            $this->accountToDelete->update([
                'status' => 'closed',
                'closed_at' => now()
            ]);

            // Log activity
            activity()
                ->causedBy(Auth::user())
                ->performedOn($this->accountToDelete)
                ->log("Account {$accountNumber} closed");

            session()->flash('success', 'Account ' . $accountNumber . ' has been closed successfully.');
            return redirect()->route('accounts.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to close account: ' . $e->getMessage());
            return redirect()->route('accounts.index');
        }

        $this->closeDeleteModal();
    }

    public function confirmFreeze($id)
    {
        try {
            $account = Account::with(['customer', 'accountType'])->find($id);

            if (!$account) {
                session()->flash('error', 'Account not found.');
                return redirect()->route('accounts.index');
            }

            if (!Gate::allows('freeze accounts', $account)) {
                session()->flash('error', 'You are not authorized to freeze/unfreeze this account.');
                return redirect()->route('accounts.index');
            }

            $this->accountToFreeze = $account;
            $this->showFreezeModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function closeFreezeModal()
    {
        $this->showFreezeModal = false;
        $this->accountToFreeze = null;
    }

    public function toggleFreeze()
    {
        if (!$this->accountToFreeze) {
            session()->flash('error', 'Account not found.');
            $this->closeFreezeModal();
            return;
        }

        try {
            $newStatus = $this->accountToFreeze->status === 'frozen' ? 'active' : 'frozen';
            $action = $newStatus === 'frozen' ? 'frozen' : 'unfrozen';
            $accountNumber = $this->accountToFreeze->account_number;

            $this->accountToFreeze->update(['status' => $newStatus]);

            // Log activity
            activity()
                ->causedBy(Auth::user())
                ->performedOn($this->accountToFreeze)
                ->log("Account {$accountNumber} {$action}");

            session()->flash('success', 'Account ' . $accountNumber . ' has been ' . $action . ' successfully.');
            return redirect()->route('accounts.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating account: ' . $e->getMessage());
        }

        $this->closeFreezeModal();
    }

    public function exportAccounts()
    {
        try {
            $accounts = $this->getFilteredAccountsQuery()->get();

            if ($accounts->isEmpty()) {
                session()->flash('info', 'No accounts to export.');
                return;
            }

            $filename = 'accounts_export_' . now()->format('Y_m_d_His') . '.xlsx';

            return Excel::download(new AccountsExport($accounts), $filename);
        } catch (\Exception $e) {
            Log::error('Export error: ' . $e->getMessage());
            session()->flash('error', 'Failed to export accounts: ' . $e->getMessage());
        }
    }

    private function getFilteredAccountsQuery()
    {
        $user = Auth::user();
        if (! $user instanceof \App\Models\Eloquent\User) {
            return;
        }
        $query = Account::query()->with(['customer', 'accountType']);

        // Apply branch filter for non-admin users
        if (!$user->can('view all branches') && $user->branch_id) {
            $query->whereHas('customer', function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            });
        }

        // Search filter
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('account_number', 'like', $searchTerm)
                    ->orWhereHas('customer', function ($q2) use ($searchTerm) {
                        $q2->where('first_name', 'like', $searchTerm)
                            ->orWhere('last_name', 'like', $searchTerm)
                            ->orWhere('email', 'like', $searchTerm)
                            ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', $searchTerm);
                    });
            });
        }

        if ($this->accountType) {
            $query->where('account_type_id', $this->accountType);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->currency) {
            $query->where('currency', $this->currency);
        }

        if ($this->balanceMin !== '') {
            $query->where('current_balance', '>=', (float) $this->balanceMin);
        }

        if ($this->balanceMax !== '') {
            $query->where('current_balance', '<=', (float) $this->balanceMax);
        }

        if ($this->customerId) {
            $query->where('customer_id', $this->customerId);
        }

        if ($this->branchId && $user->can('view all branches')) {
            $query->whereHas('customer', function ($q) {
                $q->where('branch_id', $this->branchId);
            });
        }

        return $query;
    }

    // Computed properties
    public function getAccountsProperty()
    {
        try {
            $query = $this->getFilteredAccountsQuery();
            return $query->orderBy('created_at', 'desc')->paginate($this->perPage);
        } catch (\Exception $e) {
            Log::error('Error fetching accounts: ' . $e->getMessage());
            return new LengthAwarePaginator([], 0, $this->perPage, 1);
        }
    }

    public function getHasActiveFiltersProperty()
    {
        return $this->search || $this->accountType || $this->status || $this->branchId ||
            $this->currency || $this->balanceMin !== '' || $this->balanceMax !== '' || $this->customerId;
    }

    public function getActiveFiltersCountProperty()
    {
        $count = 0;
        if ($this->search) $count++;
        if ($this->accountType) $count++;
        if ($this->status) $count++;
        if ($this->branchId) $count++;
        if ($this->currency) $count++;
        if ($this->balanceMin !== '') $count++;
        if ($this->balanceMax !== '') $count++;
        if ($this->customerId) $count++;
        return $count;
    }

    public function getTotalBalanceProperty()
    {
        try {
            return $this->accounts->sum('current_balance');
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getActiveAccountsCountProperty()
    {
        try {
            return $this->accounts->where('status', 'active')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    #[Layout('layouts.main')]
    public function render()
    {
        return view('livewire.accounts.account-index', [
            'accounts' => $this->accounts,
            'canCreate' => Gate::allows('create accounts'),
            'canEdit' => Gate::allows('update accounts'),
            'canDelete' => Gate::allows('delete accounts'),
            'canFreeze' => Gate::allows('freeze accounts'),
            'hasActiveFilters' => $this->hasActiveFilters,
            'activeFiltersCount' => $this->activeFiltersCount,
            'totalBalance' => $this->totalBalance,
            'activeAccountsCount' => $this->activeAccountsCount,
        ]);
    }
}
