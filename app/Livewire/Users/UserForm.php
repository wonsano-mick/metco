<?php

namespace App\Livewire\Users;

use App\Enums\Role;
use Livewire\Component;
use App\Models\Eloquent\User;
use App\Models\Eloquent\Branch;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserForm extends Component
{
    public $mode = 'create'; // 'create' or 'edit'
    public $user = null;
    public $userId = null;

    // Form fields
    public $first_name = '';
    public $last_name = '';
    public $email = '';
    public $username = '';
    public $role = '';
    public $branch_id = '';
    public $employee_id = '';
    public $department = '';
    public $position = '';
    public $hire_date = '';
    public $date_of_birth = '';
    public $address = '';
    public $city = '';
    public $state = '';
    public $zip_code = '';
    public $country = '';

    // Password fields (create mode only)
    public $password = '';
    public $password_confirmation = '';

    public $branches = [];
    public $availableRoles = [];

    protected function rules()
    {
        $rules = [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email',
            'username' => 'string|max:20',
            'role' => 'required|string|in:' . implode(',', array_column(Role::cases(), 'value')),
        ];

        if ($this->mode === 'create') {
            $rules['email'] = 'required|email|unique:users,email';
            // $rules['employee_id'] = 'nullable|string|max:50|unique:users,employee_id';
            $rules['password'] = ['required', 'confirmed', Password::min(8)];
            // ->letters()
            // ->mixedCase()
            // ->numbers()];
        } elseif ($this->user) {
            $rules['email'] = 'required|email|unique:users,email,' . $this->user->id;
            // $rules['employee_id'] = 'nullable|string|max:50|unique:users,employee_id,' . $this->user->id;
        }

        return $rules;
    }

    public function mount($user = null)
    {
        $currentUser = Auth::user();
        if (! $currentUser instanceof \App\Models\Eloquent\User) {
            return;
        }

        // Authorization check - only admins can create/edit users
        if (!$currentUser->isAdmin()) {
            abort(403, 'You do not have permission to manage users.');
        }

        if ($user) {
            $this->mode = 'edit';
            $this->user = User::findOrFail($user);
            $this->userId = $this->user->id;
            $this->fillForm($this->user);
        }

        // Load branches
        $this->branches = Branch::when(!$currentUser->isAdmin(), function ($query) use ($currentUser) {
            $query->where('id', $currentUser->branch_id);
        })->get();

        // Set available roles based on current user's role
        $this->availableRoles = $this->getAvailableRoles($currentUser->role);

        // Set default branch for branch managers
        if ($currentUser->isBranchManager()) {
            $this->branch_id = $currentUser->branch_id;
        }
    }

    public function save()
    {
        $this->validate();

        $currentUser = Auth::user();

        // Validate role assignment - only allow roles that the current user can assign
        if (!$this->canAssignRole($currentUser->role, $this->role)) {
            $this->showToast('You are not authorized to assign this role.', 'error');
            $this->addError('role', 'You are not authorized to assign this role.');
            return;
        }

        $data = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'username' => $this->username,
            'role' => $this->role,
            'status' => 'active',
            'branch_id' => Auth::user()->branch_id,
        ];

        try {
            if ($this->mode === 'create') {
                $data['password'] = Hash::make($this->password);

                // Generate username if not provided
                if (empty($this->username)) {
                    $data['username'] = $this->generateUsername($this->first_name, $this->last_name);
                }

                $user = User::create($data);

                // Ensure role exists before assigning (for Spatie)
                $this->ensureRoleExists($user->role);

                // Assign role via Spatie if needed
                if ($user->role && method_exists($user, 'assignRole')) {
                    $user->assignRole($user->role);
                }

                session()->flash('success', 'User created successfully.');

                // Redirect to customer show page
                return redirect()->route('users.index');

            } else {
                // Prevent changing own role
                if ($this->user->id === $currentUser->id && $this->role !== $this->user->role) {
                    $this->showToast('You cannot change your own role.', 'error');
                    $this->addError('role', 'You cannot change your own role.');
                    return;
                }

                // Ensure role exists before updating (for Spatie)
                $this->ensureRoleExists($this->role);

                $this->user->update($data);

                // Sync Spatie role
                if ($this->role !== $this->user->role && method_exists($this->user, 'syncRoles')) {
                    $this->user->syncRoles([$this->role]);
                }

                session()->flash('success', 'User details updated successfully');
                // Redirect to customer show page
                return redirect()->route('users.index');
                
            }
        } catch (\Exception $e) {
            // Handle any unexpected errors
            Log::error('Error saving user: ' . $e->getMessage());
            session()->flash('error', 'An error occurred while saving the user. Please try again.' );
            return redirect()->route('users.index');

            // If it's a role-related error, provide more specific message
            if (str_contains($e->getMessage(), 'role named')) {
                session()->flash('info', 'Role assignment failed. Please ensure the role exists in the system.');
                return redirect()->route('users.index');
            }

            return;
        }
    }

    private function fillForm(User $user)
    {
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->email = $user->email;
        $this->username = $user->username ?? '';
        $this->role = $user->role;
    }

    private function getAvailableRoles(string $currentUserRole): array
    {
        // Define role hierarchy
        $hierarchy = [
            'super-admin' => array_column(Role::cases(), 'value'),
            'admin' => array_column(Role::cases(), 'value'),
            'manager' => ['teller', 'accountant', 'supervisor','customer'],
            'teller' => [],
            'accountant' => [],
            'auditor' => [],
            'customer' => [],
        ];

        return $hierarchy[$currentUserRole] ?? [];
    }

    private function canAssignRole(string $assignerRole, string $targetRole): bool
    {
        $availableRoles = $this->getAvailableRoles($assignerRole);
        return in_array($targetRole, $availableRoles);
    }

    private function generateUsername(string $firstName, string $lastName): string
    {
        $baseUsername = strtolower($firstName . '.' . $lastName);
        $username = $baseUsername;
        $counter = 1;

        // Remove special characters and spaces
        $username = preg_replace('/[^a-z0-9.]/', '', $username);

        // Check if username exists and add number if needed
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }

    // Ensure role exists in Spatie roles table
    private function ensureRoleExists($roleName)
    {
        // Skip if not using Spatie or role is empty
        if (empty($roleName) || !class_exists('\Spatie\Permission\Models\Role')) {
            return true;
        }

        try {
            $role = \Spatie\Permission\Models\Role::where('name', $roleName)->first();

            if (!$role) {
                // Create the role if it doesn't exist
                \Spatie\Permission\Models\Role::create([
                    'name' => $roleName,
                    'guard_name' => 'web'
                ]);
                Log::info("Created missing role: {$roleName}");
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to ensure role {$roleName} exists: " . $e->getMessage());
            return false;
        }
    }

    #[Layout('layouts.main')]
    public function render()
    {
        return view('livewire.users.user-form');
    }
}
