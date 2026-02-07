<?php

namespace App\Livewire\Loans;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Eloquent\Customer;
use App\Models\Eloquent\Loan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class LoanIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $customer_id = '';
    public $loan_type = '';
    public $status = '';
    public $loan_officer_id = '';
    public $start_date = '';
    public $end_date = '';
    public $perPage = 20;
    public $showFilters = false;

    public $customers = [];
    public $loanTypes = [
        'personal' => 'Personal Loan',
        'mortgage' => 'Mortgage',
        'funeral' => 'Funeral Loan',
        'business' => 'Business Loan',
        'auto' => 'Auto Loan',
        'education' => 'Education Loan',
        'agriculture' => 'Agriculture Loan',
        'emergency' => 'Emergency Loan',
    ];

    public $statuses = [
        'draft' => 'Draft',
        'pending' => 'Pending',
        'under_review' => 'Under Review',
        'committee_review' => 'Committee Review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'disbursed' => 'Disbursed',
        'active' => 'Active',
        'completed' => 'Completed',
        'defaulted' => 'Defaulted',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'customer_id' => ['except' => ''],
        'loan_type' => ['except' => ''],
        'status' => ['except' => ''],
        'loan_officer_id' => ['except' => ''],
        'start_date' => ['except' => ''],
        'end_date' => ['except' => ''],
        'perPage' => ['except' => 20],
        'showFilters' => ['except' => false],
    ];

    public function mount()
    {
        $this->loadCustomers();

        if (
            $this->search || $this->customer_id || $this->loan_type || $this->status ||
            $this->loan_officer_id || $this->start_date || $this->end_date
        ) {
            $this->showFilters = true;
        }
    }

    private function loadCustomers()
    {
        $user = Auth::user();

        if (Gate::allows('view all loans')) {
            $this->customers = Customer::active()
                ->orderBy('first_name')
                ->get(['id', 'customer_number', 'first_name', 'last_name']);
        } else {
            $this->customers = Customer::where('relationship_manager_id', $user->id)
                ->active()
                ->orderBy('first_name')
                ->get(['id', 'customer_number', 'first_name', 'last_name']);
        }
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function resetFilters()
    {
        $this->reset(['search', 'customer_id', 'loan_type', 'status', 'loan_officer_id', 'start_date', 'end_date']);
        $this->resetPage();
        $this->showFilters = false;

        $this->dispatch(
            'showToast',
            message: 'Filters cleared successfully.',
            type: 'info'
        );
    }

    public function getLoansProperty()
    {
        $user = Auth::user();
        $query = Loan::with(['customer', 'loanOfficer', 'account']);

        // Filter by permissions
        if (!Gate::allows('view all loans')) {
            $query->where(function ($q) use ($user) {
                $q->where('loan_officer_id', $user->id)
                    ->orWhere('customer_id', $user->customer_id ?? null);
            });
        }

        // Apply filters
        if ($this->customer_id) {
            $query->where('customer_id', $this->customer_id);
        }

        if ($this->loan_type) {
            $query->where('loan_type', $this->loan_type);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->loan_officer_id) {
            $query->where('loan_officer_id', $this->loan_officer_id);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('loan_number', 'like', '%' . $this->search . '%')
                    ->orWhere('purpose', 'like', '%' . $this->search . '%')
                    ->orWhereHas('customer', function ($q) {
                        $q->where('first_name', 'like', '%' . $this->search . '%')
                            ->orWhere('last_name', 'like', '%' . $this->search . '%')
                            ->orWhere('customer_number', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->start_date) {
            $query->where('application_date', '>=', $this->start_date);
        }

        if ($this->end_date) {
            $query->where('application_date', '<=', $this->end_date . ' 23:59:59');
        }

        return $query->orderBy('application_date', 'desc')->paginate($this->perPage);
    }

    public function getStatsProperty()
    {
        $user = Auth::user();
        $query = Loan::query();

        if (!Gate::allows('view all loans')) {
            $query->where(function ($q) use ($user) {
                $q->where('loan_officer_id', $user->id)
                    ->orWhere('customer_id', $user->customer_id ?? null);
            });
        }

        // Apply same filters as main query
        if ($this->customer_id) {
            $query->where('customer_id', $this->customer_id);
        }
        if ($this->loan_type) {
            $query->where('loan_type', $this->loan_type);
        }
        if ($this->loan_officer_id) {
            $query->where('loan_officer_id', $this->loan_officer_id);
        }
        if ($this->start_date) {
            $query->where('application_date', '>=', $this->start_date);
        }
        if ($this->end_date) {
            $query->where('application_date', '<=', $this->end_date . ' 23:59:59');
        }

        return [
            'total' => $query->count(),
            'pending' => $query->clone()->where('status', 'pending')->count(),
            'approved' => $query->clone()->where('status', 'approved')->count(),
            'active' => $query->clone()->where('status', 'active')->count(),
            'overdue' => $query->clone()->where('status', 'active')
                ->where('next_payment_date', '<', now())
                ->count(),
        ];
    }

    public function getHasActiveFiltersProperty()
    {
        return $this->search || $this->customer_id || $this->loan_type ||
            $this->status || $this->loan_officer_id || $this->start_date || $this->end_date;
    }

    public function getActiveFiltersCountProperty()
    {
        $count = 0;
        if ($this->search) $count++;
        if ($this->customer_id) $count++;
        if ($this->loan_type) $count++;
        if ($this->status) $count++;
        if ($this->loan_officer_id) $count++;
        if ($this->start_date) $count++;
        if ($this->end_date) $count++;
        return $count;
    }

    // Filter updating methods
    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingCustomerId()
    {
        $this->resetPage();
    }
    public function updatingLoanType()
    {
        $this->resetPage();
    }
    public function updatingStatus()
    {
        $this->resetPage();
    }
    public function updatingLoanOfficerId()
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

    #[Layout('layouts.main')]
    public function render()
    {
        return view('livewire.loans.loan-index', [
            'loans' => $this->loans,
            'stats' => $this->stats,
            'hasActiveFilters' => $this->hasActiveFilters,
            'activeFiltersCount' => $this->activeFiltersCount,
            'canCreate' => Gate::allows('create loans'),
        ]);
    }
}
