<div>
    <div class="max-w-7xl mx-auto py-6 shadow-lg sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg">
            <!-- Header -->
            <div class="p-6 border-b shadow-md border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Customer Management</h2>
                        <p class="text-sm text-gray-600 mt-1">Manage bank customers and their information</p>
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
                            <a href="{{ route('customers.create') }}"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-user-plus mr-2"></i>
                                Add Customer
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Total Customers Card -->
                    <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-12 w-12 rounded-lg bg-blue-100 flex items-center justify-center">
                                        <i class="fas fa-users text-blue-600"></i>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Total Customers
                                        </dt>
                                        <dd class="text-2xl font-semibold text-gray-900">
                                            {{ number_format($stats['total']) }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3">
                            <div class="text-sm">
                                <button type="button" wire:click="$set('status', '')"
                                    class="font-medium text-blue-700 hover:text-blue-900">
                                    View all
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Active Customers Card -->
                    <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-12 w-12 rounded-lg bg-green-100 flex items-center justify-center">
                                        <i class="fas fa-user-check text-green-600"></i>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Active
                                        </dt>
                                        <dd class="text-2xl font-semibold text-gray-900">
                                            {{ number_format($stats['active']) }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3">
                            <div class="text-sm">
                                <button type="button" wire:click="$set('status', 'active')"
                                    class="font-medium text-blue-700 hover:text-blue-900">
                                    View active
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- KYC Verified Card -->
                    <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-12 w-12 rounded-lg bg-purple-100 flex items-center justify-center">
                                        <i class="fas fa-shield-alt text-purple-600"></i>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            KYC Verified
                                        </dt>
                                        <dd class="text-2xl font-semibold text-gray-900">
                                            {{ number_format($stats['verified']) }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3">
                            <div class="text-sm">
                                <button type="button" wire:click="$set('kyc_status', 'verified')"
                                    class="font-medium text-blue-700 hover:text-blue-900">
                                    View verified
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- With Accounts Card -->
                    <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-12 w-12 rounded-lg bg-orange-100 flex items-center justify-center">
                                        <i class="fas fa-wallet text-orange-600"></i>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            With Accounts
                                        </dt>
                                        <dd class="text-2xl font-semibold text-gray-900">
                                            {{ number_format($stats['with_accounts']) }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <!-- With Accounts Card -->
                        <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                            <div class="bg-gray-50 px-5 py-3">
                                <div class="flex justify-between items-center">
                                    @if ($has_accounts === true)
                                        <div class="flex items-center">
                                            <span class="text-sm text-green-600 font-medium">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Showing customers with accounts
                                            </span>
                                        </div>
                                        <button wire:click="$set('has_accounts', null)"
                                            class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Clear Filter
                                        </button>
                                    @else
                                        <button type="button" wire:click="filterWithAccounts"
                                            class="font-medium text-blue-700 hover:text-blue-900">
                                            View with accounts
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters Panel -->
                @if ($showFilters)
                    <div class="mt-6 transition-all duration-300 ease-in-out">
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Filter Customers</h3>
                                @if ($hasActiveFilters)
                                    <button wire:click="resetFilters"
                                        class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        Clear All Filters
                                    </button>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-4">
                                <!-- Search -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                    <div class="relative">
                                        <input type="text" wire:model.live.debounce.300ms="search"
                                            placeholder="Search by name, customer #, phone, email, ID..."
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
                                    <p class="mt-1 text-xs text-gray-500">
                                        Search by: name, customer number, phone, email, or ID
                                    </p>
                                </div>

                                <!-- Status Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <div class="relative">
                                        <select wire:model.live="status"
                                            class="pr-8 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">All Statuses</option>
                                            <option value="active">Active</option>
                                            <option value="pending">Pending</option>
                                            <option value="suspended">Suspended</option>
                                            <option value="closed">Closed</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- KYC Status Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">KYC Status</label>
                                    <div class="relative">
                                        <select wire:model.live="kyc_status"
                                            class="pr-8 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">All KYC Status</option>
                                            <option value="pending">Pending</option>
                                            <option value="verified">Verified</option>
                                            <option value="rejected">Rejected</option>
                                            <option value="expired">Expired</option>
                                        </select>
                                    </div>
                                </div>
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
                                    @if ($kyc_status)
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            KYC: {{ ucfirst($kyc_status) }}
                                            <button wire:click="resetFilters"
                                                class="ml-1 text-orange-600 hover:text-orange-800">
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

            <!-- Customers Table -->
            <div class="overflow-x-auto">
                <!-- Results Summary -->
                <div class="mt-4 px-6 flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        @if ($customers && $customers->total() > 0)
                            Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }} of
                            {{ $customers->total() }}
                            customers
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
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-sm text-gray-600">per page</span>
                    </div>
                </div>
                @if ($customers && $customers->count())
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Customer
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Contact Info
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
                                    Registered
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Accounts
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($customers as $customer)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                @if ($customer->profile_photo_url)
                                                    <img class="h-10 w-10 rounded-full object-cover"
                                                        src="{{ $customer->profile_photo_url }}"
                                                        alt="{{ $customer->full_name }}">
                                                @else
                                                    <div
                                                        class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                        <span
                                                            class="text-blue-600 font-medium">{{ substr($customer->first_name, 0, 1) }}{{ substr($customer->last_name, 0, 1) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $customer->full_name }}
                                                </div>
                                                <div class="text-sm text-gray-500 font-mono">
                                                    #{{ $customer->customer_number }}
                                                </div>
                                                @if ($customer->id_number)
                                                    <div class="text-xs text-gray-400">
                                                        ID: {{ $customer->id_number }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="space-y-1">
                                            <div class="text-sm text-gray-900">{{ $customer->email }}</div>
                                            <div class="text-sm text-gray-500">{{ $customer->phone }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $customer->branch->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="space-y-1">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $customer->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $customer->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $customer->status === 'suspended' ? 'bg-red-100 text-red-800' : '' }}
                                                {{ $customer->status === 'closed' ? 'bg-gray-100 text-gray-800' : '' }}">
                                                {{ ucfirst($customer->status) }}
                                            </span>
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $customer->kyc_status === 'verified' ? 'bg-purple-100 text-purple-800' : '' }}
                                                {{ $customer->kyc_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $customer->kyc_status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                                                {{ $customer->kyc_status === 'expired' ? 'bg-orange-100 text-orange-800' : '' }}">
                                                <i class="fas fa-shield-alt mr-1"></i>
                                                {{ ucfirst($customer->kyc_status) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if ($customer->registered_at)
                                            {{ $customer->registered_at->format('M d, Y') }}
                                            <div class="text-xs text-gray-400">
                                                {{ $customer->registered_at->diffForHumans() }}
                                            </div>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $customer->accounts->count() }} account(s)
                                        </div>
                                        @if ($customer->accounts->count() > 0)
                                            <div class="text-xs text-gray-500">
                                                Total:
                                                {{ number_format($customer->accounts->sum('current_balance'), 2) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('customers.show', $customer->id) }}"
                                                class="text-blue-600 hover:text-blue-900 transition-colors duration-150 p-1 rounded hover:bg-blue-50"
                                                title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            @if ($canEdit)
                                                <a href="{{ route('customers.edit', $customer->id) }}"
                                                    class="text-green-600 hover:text-green-900 transition-colors duration-150 p-1 rounded hover:bg-green-50"
                                                    title="Edit Customer">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif

                                            @can('create accounts')
                                                @if ($customer->kyc_status === 'verified')
                                                    <a href="{{ route('accounts.create', ['customer_id' => $customer->id]) }}"
                                                        class="text-purple-600 hover:text-purple-900 transition-colors duration-150 p-1 rounded hover:bg-purple-50"
                                                        title="Create Account">
                                                        <i class="fas fa-plus-circle"></i>
                                                    </a>
                                                @endif
                                            @endcan

                                            @if ($canDelete && $customer->accounts->count() === 0)
                                                <button wire:click="confirmDelete({{ $customer->id }})"
                                                    class="text-red-600 hover:text-red-900 transition-colors duration-150 p-1 rounded hover:bg-red-50"
                                                    title="Delete Customer">
                                                    <i class="fas fa-trash"></i>
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
                        <h3 class="text-lg font-medium text-gray-900">No customers found</h3>
                        <p class="text-gray-500 mt-1">
                            @if (!$customers)
                                Unable to load customers. Please check your permissions.
                            @else
                                @if ($hasActiveFilters)
                                    Try adjusting your search or filters
                                @else
                                    No customers in the system yet.
                                    @if ($canCreate)
                                        <a href="{{ route('customers.create') }}"
                                            class="text-blue-600 hover:text-blue-800 ml-1">
                                            Add the first customer
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
            @if ($customers && $customers->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $customers->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    @if ($showDeleteModal && $customerToDelete)
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
                                    Delete Customer
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Are you sure you want to delete customer
                                        <strong>{{ $customerToDelete->full_name }}</strong>
                                        ({{ $customerToDelete->customer_number }})?
                                    </p>
                                    <p class="text-sm text-red-500 mt-2">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        This action cannot be undone.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="deleteCustomer"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Delete Customer
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

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + K to focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                const searchInput = document.querySelector(
                    'input[wire\\:model\\.live\\.debounce\\.300ms="search"]');
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }
        });
    </script>
@endpush
