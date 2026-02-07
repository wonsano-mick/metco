<?php

namespace App\Livewire\Customers;

use Livewire\Component;
use App\Models\Eloquent\Customer;
use App\Models\Eloquent\Branch;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\WithPagination;

class CustomerIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $branch_id = '';
    public $kyc_status = '';
    public $perPage = 20;
    public $showFilters = false;

    // Add this property for accounts filter
    public $has_accounts = null;

    // For delete confirmation modal
    public $showDeleteModal = false;
    public $customerToDelete = null;

    public $branches = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'branch_id' => ['except' => ''],
        'kyc_status' => ['except' => ''],
        'perPage' => ['except' => 20],
        'showFilters' => ['except' => false],
        'has_accounts' => ['except' => null], // Add this
    ];

    public function mount()
    {
        if (!Gate::allows('view customers')) {
            abort(403, 'Unauthorized access.');
        }

        $this->loadBranches();

        // Check if any filters are active to show filter panel
        if ($this->search || $this->status || $this->branch_id || $this->kyc_status || $this->has_accounts !== null) {
            $this->showFilters = true;
        }
    }
 
    // Add this method for filtering customers with accounts
    public function filterWithAccounts()
    {
        $this->has_accounts = true;
        // $this->showFilters = true;
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingBranchId()
    {
        $this->resetPage();
    }

    public function updatingKycStatus()
    {
        $this->resetPage();
    }

    // Add this method
    public function updatingHasAccounts()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    // Toggle filters visibility
    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    protected function loadBranches()
    {
        $user = Auth::user();
        if (!$user instanceof \App\Models\Eloquent\User) {
            return;
        }

        if ($user->can('view all branches')) {
            $this->branches = Branch::orderBy('name')->get();
        } else {
            $this->branches = Branch::where('id', $user->branch_id)->get();
        }
    }

    // Show delete confirmation modal
    public function confirmDelete($id)
    {
        $this->customerToDelete = Customer::findOrFail($id);

        // Check if customer has accounts
        if ($this->customerToDelete->accounts()->count() > 0) {
            session()->flash('error', 'Cannot delete customer with existing accounts');
            return redirect()->route('customers.index');
        }

        $this->showDeleteModal = true;
    }

    // Close delete modal
    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->customerToDelete = null;
    }

    // Delete customer after confirmation
    public function deleteCustomer()
    {
        if ($this->customerToDelete) {
            // Check if customer has accounts
            if ($this->customerToDelete->accounts()->count() > 0) {
                $this->closeDeleteModal();
                session()->flash('error', 'Cannot delete customer with existing accounts');
                return redirect()->route('customers.index');
            }

            $customerName = $this->customerToDelete->full_name;

            try {
                $this->customerToDelete->delete();
                session()->flash('success', $customerName.' has been deleted');
                return redirect()->route('customers.index');
            } catch (\Exception $e) {
                session()->flash('error', 'Failed to delete customer: '.$e->getMessage());
                return redirect()->route('customers.index');
            }

            $this->closeDeleteModal();
            $this->resetPage();
        }
    }

    public function resetFilters()
    {
        $this->reset(['search', 'status', 'branch_id', 'kyc_status', 'has_accounts']);
        $this->resetPage();

        // Hide filters after clearing
        $this->showFilters = false;

        session()->flash('info', 'Filters cleared successfully');
        return redirect()->route('customers.index');
    }

    public function getCustomersProperty()
    {
        $query = Customer::query();

        $user = Auth::user();
        if (!$user instanceof \App\Models\Eloquent\User) {
            return Customer::whereRaw('1 = 0')->paginate($this->perPage);
        }

        // Filter by branch if user doesn't have all-branch access
        if (!$user->can('view all branches') && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($this->branch_id) {
            $query->where('branch_id', $this->branch_id);
        }

        // Apply search filters
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('customer_number', 'like', '%' . $this->search . '%')
                    ->orWhere('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('last_name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%')
                    ->orWhere('id_number', 'like', '%' . $this->search . '%');
            });
        }

        // Apply status filter
        if ($this->status) {
            $query->where('status', $this->status);
        }

        // Apply KYC status filter
        if ($this->kyc_status) {
            $query->where('kyc_status', $this->kyc_status);
        }

        // Apply has accounts filter
        if ($this->has_accounts === true) {
            $query->has('accounts');
        } elseif ($this->has_accounts === false) {
            $query->doesntHave('accounts');
        }

        return $query
            ->with(['branch', 'accounts'])
            ->latest('registered_at')
            ->paginate($this->perPage);
    }

    // Computed property to check if any filters are active
    public function getHasActiveFiltersProperty()
    {
        return $this->search || $this->status || $this->branch_id || $this->kyc_status || $this->has_accounts !== null;
    }

    // Computed property to count active filters
    public function getActiveFiltersCountProperty()
    {
        $count = 0;
        if ($this->search) $count++;
        if ($this->status) $count++;
        if ($this->branch_id) $count++;
        if ($this->kyc_status) $count++;
        if ($this->has_accounts !== null) $count++;
        return $count;
    }

    public function getStatsProperty()
    {
        return [
            'total' => Customer::count(),
            'active' => Customer::where('status', 'active')->count(),
            'verified' => Customer::where('kyc_status', 'verified')->count(),
            'with_accounts' => Customer::has('accounts')->count(),
        ];
    }

    #[Layout('layouts.main')]
    public function render()
    {
        $user = Auth::user();
        if (! $user instanceof \App\Models\Eloquent\User) {
            return;
        }

        return view('livewire.customers.customer-index', [
            'customers' => $this->customers,
            'stats' => $this->stats,
            'canCreate' => $user->can('create customers'),
            'canEdit' => $user->can('update customers'),
            'canDelete' => $user->can('delete customers'),
            'hasActiveFilters' => $this->hasActiveFilters,
            'activeFiltersCount' => $this->activeFiltersCount,
        ]);
    }
}
