<?php

namespace App\Livewire\Teller;

use App\Enums\Role;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Eloquent\User;
use App\Models\Eloquent\Branch;
use App\Models\Eloquent\SystemAccount;
use App\Services\Transaction\EnhancedTransactionService;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

class TellerIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $branchId = '';
    public $status = '';
    public $perPage = 10;

    // Filter visibility
    public $showFilters = false;

    // Modal properties
    public $showCashModal = false;
    public $selectedTeller = null;
    public $transactionType = ''; // 'topup' or 'withdraw'
    public $amount = '';
    public $reference = '';

    // Lists for filters
    public $branches = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'branchId' => ['except' => ''],
        'status' => ['except' => ''],
        'perPage' => ['except' => 10],
        'showFilters' => ['except' => false],
    ];

    protected $rules = [
        'amount' => 'required|numeric|min:1',
        'reference' => 'required|string|max:50',
    ];

    public function mount()
    {
        /** @var \App\Models\Eloquent\User $user */
        $user = Auth::user();

        // Check if user has manager or super-admin access
        if (!$user || (!$user->isManager() && !$user->isAdmin())) {
            abort(403, 'Unauthorized access. Manager or Admin privileges required.');
        }

        // Get branches for filter
        $this->branches = Branch::orderBy('name')->get();

        // Check if any filters are active
        if ($this->search || $this->branchId || $this->status) {
            $this->showFilters = true;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingBranchId()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function resetFilters()
    {
        $this->reset(['search', 'branchId', 'status']);
        $this->resetPage();
        $this->showFilters = false;

        session()->flash('info', 'Filters cleared successfully.');
        return redirect()->route('tellers.index');
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->resetPage();
    }

    public function openTopUpModal($tellerId)
    {
        $this->selectedTeller = User::findOrFail($tellerId);

        // Verify the user is actually a teller
        if (!$this->selectedTeller->isTeller()) {
            session()->flash('error', 'Selected user is not a teller.');
            return redirect()->route('tellers.index');
        }

        $this->transactionType = 'topup';
        $this->reset(['amount', 'reference']);
        $this->showCashModal = true;
    }

    public function openWithdrawModal($tellerId)
    {
        $this->selectedTeller = User::findOrFail($tellerId);

        // Verify the user is actually a teller
        if (!$this->selectedTeller->isTeller()) {
            session()->flash('error', 'Selected user is not a teller.');
            return redirect()->route('tellers.index');
        }

        $this->transactionType = 'withdraw';
        $this->reset(['amount', 'reference']);
        $this->showCashModal = true;
    }

    public function closeModal()
    {
        $this->showCashModal = false;
        $this->selectedTeller = null;
        $this->transactionType = '';
        $this->reset(['amount', 'reference']);
    }

    public function processCashTransaction(EnhancedTransactionService $transactionService)
    {
        // dd($this->all());
        $this->validate();

        try {
            if ($this->transactionType === 'topup') {
                $transactionService->topUpTellerForUser(
                    $this->selectedTeller->id,
                    $this->amount,
                    $this->reference
                );

                $message = 'Cash topped up successfully for ' . $this->selectedTeller->full_name;
            } else {
                $transactionService->withdrawTellerCashForUser(
                    $this->selectedTeller->id,
                    $this->amount,
                    $this->reference
                );

                $message = 'Cash withdrawn successfully from ' . $this->selectedTeller->full_name;
            }

            $this->closeModal();

            session()->flash('success', $message);
            return redirect()->route('tellers.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Error processing cash transaction: ' . $e->getMessage());
            return redirect()->route('tellers.index');
        }
    }

    public function getTellerCashBalance($tellerId)
    {
        // Find the teller's cash account
        $tellerAccount = SystemAccount::where('type', SystemAccount::TYPE_TELLER)
            ->where('code', 'TELLER-' . str_pad($tellerId, 5, '0', STR_PAD_LEFT))
            ->first();

        return $tellerAccount ? $tellerAccount->balance : 0;
    }

    public function getHasActiveFiltersProperty()
    {
        return $this->search || $this->branchId || $this->status;
    }

    public function getActiveFiltersCountProperty()
    {
        $count = 0;
        if ($this->search) $count++;
        if ($this->branchId) $count++;
        if ($this->status) $count++;
        return $count;
    }

    public function getTellersProperty()
    {
        $query = User::query();

        // Only get tellers
        $query->where('role', Role::TELLER->value);

        // Apply filters
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('last_name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('username', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->branchId) {
            $query->where('branch_id', $this->branchId);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        // For managers, only show tellers from their branch
        /** @var \App\Models\Eloquent\User $currentUser */
        $currentUser = Auth::user();

        if ($currentUser) {
            if ($currentUser->isManager() && !$currentUser->isAdmin()) {
                $query->where('branch_id', $currentUser->branch_id);
            }
        }

        return $query->with(['branch'])
            ->latest()
            ->paginate($this->perPage);
    }

    #[Layout('layouts.main')]
    public function render()
    {
        return view('livewire.teller.teller-index', [
            'tellers' => $this->tellers,
            'hasActiveFilters' => $this->hasActiveFilters,
            'activeFiltersCount' => $this->activeFiltersCount,
        ]);
    }
}
