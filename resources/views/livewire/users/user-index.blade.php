<div>
    <div class="max-w-7xl mx-auto py-6 shadow-lg sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg">
            <!-- Header -->
            <div class="p-6 border-b shadow-md border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">User Management</h2>
                        <p class="text-sm text-gray-600 mt-1">Manage system users and their permissions</p>
                    </div>
                    <div class="flex space-x-3">
                        <!-- Filter Toggle Button -->
                        <button wire:click="toggleFilters"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-filter mr-2"></i>
                            Filters
                            @if ($hasActiveFilters)
                                <span
                                    class="ml-2 inline-flex items-center justify-center h-5 w-5 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold">
                                    {{ $activeFiltersCount }}
                                </span>
                            @endif
                        </button>

                        @if ($canCreate)
                            <a href="{{ route('users.create') }}"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-plus mr-2"></i>
                                Create User
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Filters Panel (Simplified - No Alpine Collapse) -->
                @if ($showFilters)
                    <div class="mt-6 transition-all duration-300 ease-in-out">
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Filter Users</h3>
                                @if ($hasActiveFilters)
                                    <button wire:click="resetFilters"
                                        class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        Clear All Filters
                                    </button>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <!-- Search -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                    <div class="relative">
                                        <input type="text" wire:model.live.debounce.300ms="search"
                                            placeholder="Search by name, email, username..."
                                            class="pl-10 pr-8 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-search text-gray-400"></i>
                                        </div>
                                        @if ($search)
                                            <button wire:click="clearSearch"
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <!-- Role Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                                    <div class="relative">
                                        <select wire:model.live="role"
                                            class="pr-8 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">All Roles</option>
                                            @foreach ($roles as $roleValue)
                                                <option value="{{ $roleValue }}">
                                                    {{ \App\Enums\Role::tryFrom($roleValue)?->label() ?? ucfirst($roleValue) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Status Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <div class="relative">
                                        <select wire:model.live="status"
                                            class="pr-8 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">All Status</option>
                                            <option value="active">Active</option>
                                            <option value="suspended">Suspended</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Branch Filter -->
                                @if (auth()->user()->isAdmin())
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                                        <div class="relative">
                                            <select wire:model.live="branchId"
                                                class="pr-8 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                                <option value="">All Branches</option>
                                                @foreach ($branches as $branch)
                                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Active Filters Badges -->
                            @if ($hasActiveFilters)
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @if ($search)
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Search: "{{ $search }}"
                                            <button wire:click="resetFilters"
                                                class="ml-1 text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </span>
                                    @endif
                                    @if ($role)
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            Role: {{ \App\Enums\Role::tryFrom($role)?->label() ?? ucfirst($role) }}
                                            <button wire:click="resetFilters"
                                                class="ml-1 text-purple-600 hover:text-purple-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </span>
                                    @endif
                                    @if ($status)
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Status: {{ ucfirst($status) }}
                                            <button wire:click="resetFilters"
                                                class="ml-1 text-green-600 hover:text-green-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </span>
                                    @endif
                                    @if ($branchId)
                                        @php
                                            $selectedBranch = $branches->firstWhere('id', $branchId);
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Branch: {{ $selectedBranch->name ?? 'N/A' }}
                                            <button wire:click="resetFilters"
                                                class="ml-1 text-yellow-600 hover:text-yellow-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Users Table -->
            <div class="overflow-x-auto">
                <!-- Results Summary -->
                <div class="mt-4 flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        @if ($users && $users->total() > 0)
                            Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }}
                            users
                            @if ($hasActiveFilters)
                                <span class="font-medium">(filtered)</span>
                            @endif
                        @endif
                    </div>

                    <!-- Items per page -->
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-600">Show:</span>
                        <select wire:model.live="perPage"
                            class="border border-gray-300 rounded-md shadow-sm py-1 px-2 text-sm w-20 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="20">15</option>
                            <option value="50">20</option>
                        </select>
                        <span class="text-sm text-gray-600">per page</span>
                    </div>
                </div>
                @if ($users && $users->count())
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    User
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Role
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Branch
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Last Login
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($users as $user)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div
                                                    class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold">
                                                    {{ $user->initials }}
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $user->full_name }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $user->email }}
                                                </div>
                                                @if ($user->username)
                                                    <div class="text-xs text-gray-400">
                                                        @: {{ $user->username }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @switch($user->role)
                                                @case('admin') bg-red-100 text-red-800 @break
                                                @case('manager') bg-purple-100 text-purple-800 @break
                                                @case('teller') bg-blue-100 text-blue-800 @break
                                                @case('accountant') bg-green-100 text-green-800 @break
                                                @case('auditor') bg-yellow-100 text-yellow-800 @break
                                                @case('supervisor') bg-yellow-100 text-yellow-800 @break
                                                @default bg-gray-100 text-gray-800
                                            @endswitch">
                                            {{ \App\Enums\Role::tryFrom($user->role)?->label() ?? ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $user->branch->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @switch($user->status)
                                                @case('active') bg-green-100 text-green-800 @break
                                                @case('suspended') bg-red-100 text-red-800 @break
                                                @case('inactive') bg-gray-100 text-gray-800 @break
                                                @default bg-gray-100 text-gray-800
                                            @endswitch">
                                            {{ ucfirst($user->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if ($user->last_login_at)
                                            {{ $user->last_login_at->diffForHumans() }}
                                        @else
                                            Never
                                        @endif
                                    </td>
                                    {{-- <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('users.show', $user->id) }}"
                                                class="text-blue-600 hover:text-blue-900" title="View User">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            @if ($canEdit)
                                                <a href="{{ route('users.edit', $user) }}"
                                                    class="text-green-600 hover:text-green-900" title="Edit User">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <button wire:click="toggleStatus({{ $user->id }})"
                                                    class="{{ $user->status === 'active' ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900' }}"
                                                    title="{{ $user->status === 'active' ? 'Suspend User' : 'Activate User' }}">
                                                    @if ($user->status === 'active')
                                                        <i class="fas fa-pause"></i>
                                                    @else
                                                        <i class="fas fa-play"></i>
                                                    @endif
                                                </button>
                                            @endif

                                            @if ($canDelete && $user->id !== auth()->id())
                                                <button wire:click="confirmDelete({{ $user->id }})"
                                                    class="text-red-600 hover:text-red-900" title="Delete User">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td> --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('users.show', $user->id) }}"
                                                class="text-blue-600 hover:text-blue-900">
                                                View
                                            </a>
                                            
                                            @if ($canEdit)
                                                <a href="{{ route('users.edit', $user) }}"
                                                    class="text-green-600 hover:text-green-900">
                                                    Edit
                                                </a>
                                                
                                                <button wire:click="toggleStatus({{ $user->id }})"
                                                    class="{{ $user->status === 'active' ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900' }}">
                                                    {{ $user->status === 'active' ? 'Suspend' : 'Activate' }}
                                                </button>
                                            @endif
                                            
                                            @if ($canDelete && $user->id !== auth()->id())
                                                <button wire:click="confirmDelete({{ $user->id }})"
                                                    class="text-red-600 hover:text-red-900">
                                                    Delete
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="text-center py-12">
                        <div class="text-gray-400 mb-4">
                            <i class="fas fa-users text-4xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900">No users found</h3>
                        <p class="text-gray-500 mt-1">
                            @if (!$users)
                                Unable to load users. Please check your permissions.
                            @else
                                @if ($hasActiveFilters)
                                    Try adjusting your search or filters
                                @else
                                    No users in the system yet.
                                    @if ($canCreate)
                                        <a href="{{ route('users.create') }}"
                                            class="text-blue-600 hover:text-blue-800 ml-1">
                                            Create the first user
                                        </a>
                                    @endif
                                @endif
                            @endif
                        </p>
                        @if ($hasActiveFilters)
                            <button wire:click="resetFilters"
                                class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-times-circle mr-2"></i>
                                Clear All Filters
                            </button>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Pagination -->
            @if ($users && $users->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    @if ($showDeleteModal && $userToDelete)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                <!-- Modal panel -->
                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.346 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Delete User
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Are you sure you want to delete
                                        <strong>{{ $userToDelete->full_name }}</strong>? This action cannot be
                                        undone.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="deleteUser"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Delete
                        </button>
                        <button type="button" wire:click="closeDeleteModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
    <script>
        // Add Alpine.js collapse plugin
        document.addEventListener('alpine:init', () => {
            Alpine.directive('collapse', (el) => {
                let duration = 300;

                el.style.transition = `height ${duration}ms ease`;
                el.style.height = '0';
                el.style.overflow = 'hidden';

                Alpine.effect(() => {
                    if (Alpine.evaluate(el, 'show')) {
                        el.style.height = el.scrollHeight + 'px';
                        setTimeout(() => {
                            el.style.height = 'auto';
                        }, duration);
                    } else {
                        el.style.height = el.scrollHeight + 'px';
                        // Force reflow
                        el.scrollHeight;
                        el.style.height = '0';
                    }
                });
            });
        });
    </script>
@endpush
