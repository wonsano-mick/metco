<?php

namespace App\Livewire\Users;

use App\Enums\Role;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Eloquent\User;
use App\Models\Eloquent\Branch;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

class UserIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $role = '';
    public $status = '';
    public $branchId = '';
    public $perPage = 5;

    // Add showFilters property
    public $showFilters = false;

    // For delete confirmation modal
    public $showDeleteModal = false;
    public $userToDelete = null;

    public $roles = [];
    public $branches = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'role' => ['except' => ''],
        'status' => ['except' => ''],
        'branchId' => ['except' => ''],
        'perPage' => ['except' => 20],
        'showFilters' => ['except' => false],
    ];

    public function mount()
    {
        $user = Auth::user();
        if (!$user instanceof \App\Models\Eloquent\User) {
            abort(403, 'Unauthorized access.');
        }

        if (!$user->isAdmin()) {
            abort(403, 'Admin access required.');
        }

        // Get roles for dropdown
        $roles = Role::cases();
        $this->roles = array_column($roles, 'value');

        // Get branches
        $this->branches = Branch::orderBy('name')->get();

        // Check if any filters are active to show filter panel
        if ($this->search || $this->role || $this->status || $this->branchId) {
            $this->showFilters = true;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRole()
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

    // Toggle filters visibility
    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    // Show delete confirmation modal
    public function confirmDelete($id)
    {
        $this->userToDelete = User::findOrFail($id);

        // Check if user is trying to delete themselves
        if ($this->userToDelete->id === Auth::id()) {
            session()->flash('error', 'You cannot delete your own account.');
            return redirect()->route('users.index');
        }

        // Check if user is already deleted
        if ($this->userToDelete->status === 'deleted') {
            session()->flash('success', 'This user is already deleted.');
            return redirect()->route('users.index');
        }

        $this->showDeleteModal = true;
    }

    // Close delete modal
    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->userToDelete = null;
    }

    // Delete user after confirmation (soft delete by changing status)
    public function deleteUser()
    {
        if ($this->userToDelete) {
            // Check if user is trying to delete themselves
            if ($this->userToDelete->id === Auth::id()) {
                session()->flash('success', 'You cannot delete your own account');
                $this->closeDeleteModal();
                return redirect()->route('users.index');
            }

            // Check if user is already deleted
            if ($this->userToDelete->status === 'deleted') {
                session()->flash('success', 'This user is already deleted.');
                $this->closeDeleteModal();
                return redirect()->route('users.index');
            }

            $userName = $this->userToDelete->full_name;

            // Soft delete by changing status to 'deleted'
            $this->userToDelete->update(['status' => 'deleted']);
            session()->flash('success', 'User '.$userName.' has been deleted');
            $this->closeDeleteModal();
            $this->resetPage();
            return redirect()->route('users.index');
        }
    }

    public function toggleStatus($userId)
    {
        $user = User::findOrFail($userId);

        // Toggle between active/suspended
        if ($user->status === 'active') {
            $user->update(['status' => 'suspended']);
            session()->flash('success', 'User suspended successfully.');
            return redirect()->route('users.index');
        } else {
            $user->update(['status' => 'active']);
            session()->flash('success', 'User activated successfully.');
            return redirect()->route('users.index');
        }

        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'role', 'status', 'branchId']);
        $this->resetPage();

        // Hide filters after clearing
        $this->showFilters = false;

        session()->flash('info', 'Filter cleared successfully.');
        return redirect()->route('users.index');
    }

    // Clear individual filters
    public function clearSearch()
    {
        $this->search = '';
        $this->resetPage();
    }

    public function clearRole()
    {
        $this->role = '';
        $this->resetPage();
    }

    public function clearStatus()
    {
        $this->status = '';
        $this->resetPage();
    }

    public function clearBranch()
    {
        $this->branchId = '';
        $this->resetPage();
    }

    public function getUsersProperty()
    {
        $query = User::query();

        $currentUser = Auth::user();

        if (!$currentUser instanceof \App\Models\Eloquent\User) {
            return User::whereRaw('1 = 0')->paginate($this->perPage);
        }

        $isAdmin = $currentUser->isAdmin();

        // Apply search filters
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('last_name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('username', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->role) {
            $query->where('role', $this->role);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->branchId && $isAdmin) {
            $query->where('branch_id', $this->branchId);
        }

        return $query
            ->with(['branch'])
            ->latest()
            ->paginate($this->perPage);
    }

    // Computed property to check if any filters are active
    public function getHasActiveFiltersProperty()
    {
        return $this->search || $this->role || $this->status || $this->branchId;
    }

    // Computed property to count active filters
    public function getActiveFiltersCountProperty()
    {
        $count = 0;
        if ($this->search) $count++;
        if ($this->role) $count++;
        if ($this->status) $count++;
        if ($this->branchId) $count++;
        return $count;
    }

    #[Layout('layouts.main')]
    public function render()
    {
        $user = Auth::user();
        if (! $user instanceof \App\Models\Eloquent\User) {
            return;
        }

        return view('livewire.users.user-index', [
            'users' => $this->users,
            'canCreate' => $user->isAdmin(),
            'canEdit' => $user->isAdmin(),
            'canDelete' => $user->isAdmin(),
            'hasActiveFilters' => $this->hasActiveFilters,
            'activeFiltersCount' => $this->activeFiltersCount,
        ]);
    }
}
