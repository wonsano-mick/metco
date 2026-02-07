<div>
    <div class="max-w-7xl mx-auto py-6 shadow-lg sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg">
            <!-- Header -->
            <div class="p-6 border-b shadow-md border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Account Management</h2>
                        <p class="text-sm text-gray-600 mt-1">Manage customer accounts, balances, and status</p>
                        <div class="flex items-center space-x-4 mt-2">
                            <span class="text-sm text-gray-600">
                                <i class="fas fa-wallet mr-1"></i> Total Balance:
                                <span class="font-bold text-green-600">
                                    {{ number_format($totalBalance, 2) }}
                                </span>
                            </span>
                            <span class="text-sm text-gray-600">
                                <i class="fas fa-check-circle mr-1"></i> Active Accounts:
                                <span class="font-bold text-blue-600">{{ $activeAccountsCount }}</span>
                            </span>
                            <span class="text-sm text-gray-600">
                                <i class="fas fa-database mr-1"></i> Total:
                                <span class="font-bold text-gray-700">{{ $accounts->total() }}</span>
                            </span>
                        </div>
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

                        <!-- Export Button -->
                        <div class="relative inline-block text-left">
                            <button wire:click="exportAccounts"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-file-excel mr-2 text-green-600"></i>
                                Export
                            </button>
                        </div>

                        @if ($canCreate)
                            <a href="{{ route('accounts.create') }}"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-plus mr-2"></i>
                                Create Account
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Filters Panel -->
                @if ($showFilters)
                    <div class="mt-6 transition-all duration-300 ease-in-out">
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Filter Accounts</h3>
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
                                            placeholder="Search by account number, customer name..."
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

                                <!-- Account Type Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Type</label>
                                    <div class="relative">
                                        <select wire:model.live="accountType"
                                            class="pr-8 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">All Types</option>
                                            @foreach ($accountTypes as $type)
                                                <option value="{{ $type->id }}">
                                                    {{ $type->name }}
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
                                            @foreach ($statuses as $statusEnum)
                                                <option value="{{ $statusEnum->value }}">
                                                    {{ $statusEnum->label() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Currency Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                                    <div class="relative">
                                        <select wire:model.live="currency"
                                            class="pr-8 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">All Currencies</option>
                                            @foreach ($currencies as $curr)
                                                <option value="{{ $curr }}">{{ $curr }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Customer Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                                    <div class="relative">
                                        <select wire:model.live="customerId"
                                            class="pr-8 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">All Customers</option>
                                            @foreach ($customers as $customer)
                                                <option value="{{ $customer->id }}">
                                                    {{ $customer->full_name }} ({{ $customer->accounts_count }}
                                                    accounts)
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Balance Range -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Min Balance</label>
                                    <div class="relative">
                                        <input type="number" wire:model.live.debounce.500ms="balanceMin"
                                            placeholder="Min balance"
                                            class="pr-8 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                            step="0.01">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Balance</label>
                                    <div class="relative">
                                        <input type="number" wire:model.live.debounce.500ms="balanceMax"
                                            placeholder="Max balance"
                                            class="pr-8 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                            step="0.01">
                                    </div>
                                </div>

                                <!-- Branch Filter (for admin) -->
                                @if (auth()->user()->can('view all branches'))
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
                                    @if ($accountType)
                                        @php
                                            $selectedType = $accountTypes->firstWhere('id', $accountType);
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            Type: {{ $selectedType->name ?? 'N/A' }}
                                            <button wire:click="resetFilters"
                                                class="ml-1 text-purple-600 hover:text-purple-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </span>
                                    @endif
                                    @if ($status)
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Status:
                                            {{ \App\Enums\AccountStatus::tryFrom($status)?->label() ?? ucfirst($status) }}
                                            <button wire:click="resetFilters"
                                                class="ml-1 text-green-600 hover:text-green-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </span>
                                    @endif
                                    @if ($currency)
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Currency: {{ $currency }}
                                            <button wire:click="resetFilters"
                                                class="ml-1 text-yellow-600 hover:text-yellow-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </span>
                                    @endif
                                    @if ($balanceMin)
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            Min: {{ number_format($balanceMin, 2) }}
                                            <button wire:click="resetFilters"
                                                class="ml-1 text-indigo-600 hover:text-indigo-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </span>
                                    @endif
                                    @if ($balanceMax)
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-pink-100 text-pink-800">
                                            Max: {{ number_format($balanceMax, 2) }}
                                            <button wire:click="resetFilters"
                                                class="ml-1 text-pink-600 hover:text-pink-800">
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

            <!-- Accounts Table -->
            <div class="overflow-x-auto">
                <!-- Results Summary -->
                <div class="mt-4 px-6 flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        @if ($accounts && $accounts->total() > 0)
                            Showing {{ $accounts->firstItem() }} to {{ $accounts->lastItem() }} of
                            {{ $accounts->total() }}
                            accounts
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
                            <option value="20">20</option>
                            <option value="50">50</option>
                        </select>
                        <span class="text-sm text-gray-600">per page</span>
                    </div>
                </div>
                @if ($accounts && $accounts->count())
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Account Details
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Customer
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Balances
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Created
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($accounts as $account)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div
                                                    class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold">
                                                    <i class="fas fa-wallet"></i>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $account->account_number }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $account->accountType->name ?? 'N/A' }}
                                                </div>
                                                <div class="text-xs text-gray-400">
                                                    {{ strtoupper($account->currency) }}
                                                    @if ($account->accountType && isset($account->accountType->interest_rate))
                                                        • {{ number_format($account->accountType->interest_rate, 2) }}%
                                                        interest
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $account->customer->full_name ?? 'N/A' }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $account->customer->email ?? 'N/A' }}
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            ID: {{ $account->customer->id ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-900">
                                            {{ number_format($account->current_balance, 2) }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Available: {{ number_format($account->available_balance, 2) }}
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            Ledger: {{ number_format($account->ledger_balance, 2) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusEnum = \App\Enums\AccountStatus::tryFrom($account->status);
                                        @endphp
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusEnum?->badgeClass() ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $statusEnum?->label() ?? ucfirst($account->status) }}
                                        </span>
                                        <div class="text-xs text-gray-500 mt-1">
                                            @if ($account->opened_at)
                                                Opened: {{ $account->opened_at->format('M d, Y') }}
                                            @endif
                                            @if ($account->closed_at)
                                                <br>Closed: {{ $account->closed_at->format('M d, Y') }}
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $account->created_at->format('M d, Y') }}
                                        <div class="text-xs text-gray-400">
                                            {{ $account->created_at->diffForHumans() }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('accounts.transactions', $account->id) }}"
                                                class="text-purple-600 hover:text-purple-900"
                                                title="View Transactions">
                                                <i class="fas fa-exchange-alt"></i>
                                            </a>
                                            <a href="{{ route('accounts.show', $account->id) }}"
                                                class="text-blue-600 hover:text-blue-900" title="View Account">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            @if ($canEdit)
                                                <a href="{{ route('accounts.edit', $account) }}"
                                                    class="text-green-600 hover:text-green-900" title="Edit Account">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif

                                            @if ($canFreeze)
                                                <button wire:click="confirmFreeze({{ $account->id }})"
                                                    class="{{ $account->status === 'frozen' ? 'text-green-600 hover:text-green-900' : 'text-yellow-600 hover:text-yellow-900' }}"
                                                    title="{{ $account->status === 'frozen' ? 'Unfreeze Account' : 'Freeze Account' }}">
                                                    @if ($account->status === 'frozen')
                                                        <i class="fas fa-unlock"></i>
                                                    @else
                                                        <i class="fas fa-lock"></i>
                                                    @endif
                                                </button>
                                            @endif

                                            @if ($canDelete && $account->status !== 'closed')
                                                <button wire:click="confirmDelete({{ $account->id }})"
                                                    class="text-red-600 hover:text-red-900" title="Close Account">
                                                    <i class="fas fa-times-circle"></i>
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
                            <i class="fas fa-wallet text-4xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900">No accounts found</h3>
                        <p class="text-gray-500 mt-1">
                            @if (!$accounts)
                                Unable to load accounts. Please check your permissions.
                            @else
                                @if ($hasActiveFilters)
                                    Try adjusting your search or filters
                                @else
                                    No accounts in the system yet.
                                    @if ($canCreate)
                                        <a href="{{ route('accounts.create') }}"
                                            class="text-blue-600 hover:text-blue-800 ml-1">
                                            Create the first account
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
            @if ($accounts && $accounts->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $accounts->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Delete/Close Confirmation Modal -->
    @if ($showDeleteModal && $accountToDelete)
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
                                <i class="fas fa-times-circle text-red-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Close Account
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Are you sure you want to close account
                                        <strong>{{ $accountToDelete->account_number }}</strong> for customer
                                        <strong>{{ $accountToDelete->user->full_name ?? 'Unknown' }}</strong>?
                                    </p>
                                    <div class="mt-3 p-3 bg-yellow-50 rounded-md">
                                        <p class="text-sm text-yellow-700">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            This action will:
                                        </p>
                                        <ul class="text-sm text-yellow-600 mt-1 list-disc list-inside">
                                            <li>Change account status to "Closed"</li>
                                            <li>Record closing date and time</li>
                                            <li>Prevent further transactions</li>
                                            <li>Balance must be zero to close</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="deleteAccount"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Close Account
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

    <!-- Freeze/Unfreeze Modal -->
    @if ($showFreezeModal && $accountToFreeze)
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
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full {{ $accountToFreeze->status === 'frozen' ? 'bg-green-100' : 'bg-yellow-100' }} sm:mx-0 sm:h-10 sm:w-10">
                                @if ($accountToFreeze->status === 'frozen')
                                    <i class="fas fa-unlock text-green-600"></i>
                                @else
                                    <i class="fas fa-lock text-yellow-600"></i>
                                @endif
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    {{ $accountToFreeze->status === 'frozen' ? 'Unfreeze' : 'Freeze' }} Account
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Are you sure you want to
                                        <strong>{{ $accountToFreeze->status === 'frozen' ? 'unfreeze' : 'freeze' }}</strong>
                                        account <strong>{{ $accountToFreeze->account_number }}</strong>?
                                    </p>
                                    <div
                                        class="mt-3 p-3 {{ $accountToFreeze->status === 'frozen' ? 'bg-green-50' : 'bg-yellow-50' }} rounded-md">
                                        <p
                                            class="text-sm {{ $accountToFreeze->status === 'frozen' ? 'text-green-700' : 'text-yellow-700' }}">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            {{ $accountToFreeze->status === 'frozen' ? 'Unfreezing will:' : 'Freezing will:' }}
                                        </p>
                                        <ul
                                            class="text-sm {{ $accountToFreeze->status === 'frozen' ? 'text-green-600' : 'text-yellow-600' }} mt-1 list-disc list-inside">
                                            @if ($accountToFreeze->status === 'frozen')
                                                <li>Allow all transactions</li>
                                                <li>Change status to "Active"</li>
                                                <li>Remove transaction restrictions</li>
                                            @else
                                                <li>Block all debit transactions</li>
                                                <li>Allow only credit transactions</li>
                                                <li>Change status to "Frozen"</li>
                                                <li>Customer will be notified</li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="toggleFreeze"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 {{ $accountToFreeze->status === 'frozen' ? 'bg-green-600 hover:bg-green-700' : 'bg-yellow-600 hover:bg-yellow-700' }} text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $accountToFreeze->status === 'frozen' ? 'focus:ring-green-500' : 'focus:ring-yellow-500' }} sm:ml-3 sm:w-auto sm:text-sm">
                            {{ $accountToFreeze->status === 'frozen' ? 'Unfreeze Account' : 'Freeze Account' }}
                        </button>
                        <button type="button" wire:click="closeFreezeModal"
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
                        el.scrollHeight;
                        el.style.height = '0';
                    }
                });
            });

        });

        // Format currency input
        document.addEventListener('DOMContentLoaded', function() {
            const balanceInputs = document.querySelectorAll('input[type="number"][wire\\:model*="balance"]');
            balanceInputs.forEach(input => {
                input.addEventListener('blur', function(e) {
                    if (this.value) {
                        this.value = parseFloat(this.value).toFixed(2);
                    }
                });
            });
        });
    </script>
@endpush
