<div>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Account Management</h2>
                        <p class="text-sm text-gray-600 mt-1">Manage customer accounts, balances, and status</p>
                        <div class="flex flex-wrap items-center gap-4 mt-3">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-green-500"></div>
                                <span class="text-sm text-gray-600">
                                    Total Balance:
                                    <span class="font-bold text-green-700 ml-1">
                                        {{ number_format($totalBalance, 2) }}
                                    </span>
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                <span class="text-sm text-gray-600">
                                    Active Accounts:
                                    <span class="font-bold text-blue-700 ml-1">{{ $activeAccountsCount }}</span>
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-gray-500"></div>
                                <span class="text-sm text-gray-600">
                                    Total:
                                    <span class="font-bold text-gray-700 ml-1">{{ $accounts->total() }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <!-- Filter Toggle Button -->
                        <button wire:click="toggleFilters"
                            class="inline-flex items-center px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                            <i class="fas fa-filter mr-2 text-gray-500"></i>
                            Filters
                            @if ($hasActiveFilters)
                                <span class="ml-2 inline-flex items-center justify-center h-5 w-5 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold">
                                    {{ $activeFiltersCount }}
                                </span>
                            @endif
                        </button>

                        <!-- Export Button -->
                        <button wire:click="exportAccounts"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                            <i class="fas fa-file-excel mr-2 text-green-600"></i>
                            <span wire:loading.remove>Export</span>
                            <span wire:loading>
                                <i class="fas fa-spinner fa-spin mr-2"></i>Exporting...
                            </span>
                        </button>

                        @if ($canCreate)
                            <a href="{{ route('accounts.create') }}"
                                class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                <i class="fas fa-plus mr-2"></i>
                                Create Account
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Filters Panel -->
                @if ($showFilters)
                    <div class="mt-6 transition-all duration-300 ease-in-out">
                        <div class="bg-gray-50/80 rounded-xl p-5 border border-gray-200 backdrop-blur-sm">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    <i class="fas fa-sliders-h mr-2 text-blue-600"></i>
                                    Filter Accounts
                                </h3>
                                @if ($hasActiveFilters)
                                    <button wire:click="resetFilters"
                                        class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors duration-200">
                                        <i class="fas fa-times-circle mr-2"></i>
                                        Clear All Filters
                                    </button>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                <!-- Search -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">
                                        <i class="fas fa-search mr-1 text-gray-400"></i>
                                        Search
                                    </label>
                                    <div class="relative">
                                        <input type="text" 
                                               wire:model.live.debounce.300ms="search"
                                               placeholder="Account number, customer name..."
                                               class="pl-10 pr-10 w-full border border-gray-300 rounded-lg shadow-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-search text-gray-400"></i>
                                        </div>
                                        @if ($search)
                                            <button wire:click="clearSearch"
                                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors duration-200">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <!-- Account Type Filter -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">
                                        <i class="fas fa-wallet mr-1 text-gray-400"></i>
                                        Account Type
                                    </label>
                                    <select wire:model.live="accountType"
                                            class="w-full border border-gray-300 rounded-lg shadow-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                                        <option value="">All Types</option>
                                        @foreach ($accountTypes as $type)
                                            <option value="{{ $type->id }}">
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Status Filter -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">
                                        <i class="fas fa-circle mr-1 text-gray-400"></i>
                                        Status
                                    </label>
                                    <select wire:model.live="status"
                                            class="w-full border border-gray-300 rounded-lg shadow-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                                        <option value="">All Status</option>
                                        @foreach ($statuses as $statusEnum)
                                            <option value="{{ $statusEnum->value }}">
                                                {{ $statusEnum->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Customer Filter -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">
                                        <i class="fas fa-user mr-1 text-gray-400"></i>
                                        Customer
                                    </label>
                                    <select wire:model.live="customerId"
                                            class="w-full border border-gray-300 rounded-lg shadow-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                                        <option value="">All Customers</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}">
                                                {{ $customer->full_name }}
                                                @if($customer->accounts_count > 0)
                                                    ({{ $customer->accounts_count }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Advanced Filters -->
                            <div x-data="{ showAdvanced: false }" class="mt-6">
                                <button @click="showAdvanced = !showAdvanced"
                                        class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors duration-200">
                                    <i class="fas fa-chevron-down mr-2 transition-transform duration-200" :class="{ 'rotate-180': showAdvanced }"></i>
                                    Advanced Filters
                                </button>

                                <div x-show="showAdvanced" x-collapse class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <!-- Currency Filter -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Currency
                                        </label>
                                        <select wire:model.live="currency"
                                                class="w-full border border-gray-300 rounded-lg shadow-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                                            <option value="">All Currencies</option>
                                            @foreach ($currencies as $curr)
                                                <option value="{{ $curr }}">{{ $curr }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Balance Range -->
                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Min Balance
                                        </label>
                                        <div class="relative">
                                            <input type="number" 
                                                   wire:model.live.debounce.500ms="balanceMin"
                                                   placeholder="0.00"
                                                   class="w-full border border-gray-300 rounded-lg shadow-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                                   step="0.01">
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Max Balance
                                        </label>
                                        <div class="relative">
                                            <input type="number" 
                                                   wire:model.live.debounce.500ms="balanceMax"
                                                   placeholder="1000000.00"
                                                   class="w-full border border-gray-300 rounded-lg shadow-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                                   step="0.01">
                                        </div>
                                    </div>

                                    <!-- Branch Filter (for admin) -->
                                    @if (auth()->user()->can('view all branches') && $branches->count() > 0)
                                        <div class="space-y-2">
                                            <label class="block text-sm font-medium text-gray-700">
                                                Branch
                                            </label>
                                            <select wire:model.live="branchId"
                                                    class="w-full border border-gray-300 rounded-lg shadow-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                                                <option value="">All Branches</option>
                                                @foreach ($branches as $branch)
                                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Active Filters Badges -->
                            @if ($hasActiveFilters)
                                <div class="mt-6 pt-4 border-t border-gray-200">
                                    <div class="flex items-center gap-2 mb-3">
                                        <i class="fas fa-filter text-gray-400"></i>
                                        <span class="text-sm font-medium text-gray-700">Active Filters:</span>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        @if ($search)
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                                Search: "{{ $search }}"
                                                <button wire:click="clearSearch" class="ml-1.5 text-blue-600 hover:text-blue-800">
                                                    <i class="fas fa-times text-xs"></i>
                                                </button>
                                            </span>
                                        @endif
                                        @if ($accountType)
                                            @php
                                                $selectedType = $accountTypes->firstWhere('id', $accountType);
                                            @endphp
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 border border-purple-200">
                                                Type: {{ $selectedType->name ?? 'N/A' }}
                                                <button wire:click="$set('accountType', '')" class="ml-1.5 text-purple-600 hover:text-purple-800">
                                                    <i class="fas fa-times text-xs"></i>
                                                </button>
                                            </span>
                                        @endif
                                        @if ($status)
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                                Status: {{ \App\Enums\AccountStatus::tryFrom($status)?->label() ?? ucfirst($status) }}
                                                <button wire:click="$set('status', '')" class="ml-1.5 text-green-600 hover:text-green-800">
                                                    <i class="fas fa-times text-xs"></i>
                                                </button>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Accounts Table -->
            <div class="overflow-x-auto">
                <!-- Results Summary -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="text-sm text-gray-600">
                            @if ($accounts->total() > 0)
                                Showing <span class="font-semibold">{{ $accounts->firstItem() }}</span> to 
                                <span class="font-semibold">{{ $accounts->lastItem() }}</span> of 
                                <span class="font-semibold">{{ $accounts->total() }}</span> accounts
                                @if ($hasActiveFilters)
                                    <span class="ml-2 text-blue-600 font-medium">(filtered)</span>
                                @endif
                            @endif
                        </div>

                        <!-- Items per page -->
                        <div class="flex items-center space-x-3">
                            <span class="text-sm text-gray-600">Show:</span>
                            <select wire:model.live="perPage"
                                    class="border border-gray-300 w-20 rounded-lg shadow-sm py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                            </select>
                            <span class="text-sm text-gray-600">per page</span>
                        </div>
                    </div>
                </div>

                <!-- Accounts Table -->
                @if ($accounts->count())
                    <div class="overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Account Details
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Customer
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Balances
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Created
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($accounts as $account)
                                    <tr class="hover:bg-gray-50/80 transition-colors duration-150">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-12 w-12">
                                                    <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white shadow-sm">
                                                        <i class="fas fa-wallet text-lg"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-semibold text-gray-900">
                                                        {{ $account->account_number }}
                                                    </div>
                                                    <div class="text-sm text-gray-600 mt-0.5">
                                                        {{ $account->accountType->name ?? 'N/A' }}
                                                    </div>
                                                    <div class="text-xs text-gray-400 mt-0.5">
                                                        {{ strtoupper($account->currency) }}
                                                        @if ($account->accountType && $account->accountType->interest_rate)
                                                            • {{ number_format($account->accountType->interest_rate, 2) }}% interest
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $account->customer->full_name ?? 'N/A' }}
                                            </div>
                                            <div class="text-sm text-gray-600 mt-0.5">
                                                {{ $account->customer->email ?? 'N/A' }}
                                            </div>
                                            <div class="text-xs text-gray-400 mt-0.5">
                                                ID: {{ $account->customer->customer_number ?? $account->customer->id ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-lg font-bold text-gray-900">
                                                {{ number_format($account->current_balance, 2) }}
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                <span class="inline-flex items-center">
                                                    <i class="fas fa-check-circle text-green-500 mr-1 text-xs"></i>
                                                    Available: {{ number_format($account->available_balance, 2) }}
                                                </span>
                                            </div>
                                            <div class="text-xs text-gray-400 mt-0.5">
                                                Ledger: {{ number_format($account->ledger_balance, 2) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $statusEnum = \App\Enums\AccountStatus::tryFrom($account->status);
                                                $badgeClasses = [
                                                    'active' => 'bg-green-100 text-green-800 border border-green-200',
                                                    'frozen' => 'bg-yellow-100 text-yellow-800 border border-yellow-200',
                                                    'closed' => 'bg-red-100 text-red-800 border border-red-200',
                                                    'dormant' => 'bg-gray-100 text-gray-800 border border-gray-200',
                                                ];
                                                $badgeClass = $badgeClasses[$account->status] ?? 'bg-gray-100 text-gray-800';
                                            @endphp
                                            <span class="px-3 py-1.5 inline-flex text-xs leading-4 font-semibold rounded-full {{ $badgeClass }}">
                                                {{ $statusEnum?->label() ?? ucfirst($account->status) }}
                                            </span>
                                            @if ($account->opened_at)
                                                <div class="text-xs text-gray-500 mt-2">
                                                    Opened: {{ $account->opened_at->format('M d, Y') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">
                                                {{ $account->created_at->format('M d, Y') }}
                                            </div>
                                            <div class="text-xs text-gray-400">
                                                {{ $account->created_at->diffForHumans() }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('accounts.transactions', $account->id) }}"
                                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-purple-600 hover:bg-purple-50 transition-colors duration-200"
                                                   title="View Transactions">
                                                    <i class="fas fa-exchange-alt"></i>
                                                </a>
                                                <a href="{{ route('accounts.show', $account->id) }}"
                                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors duration-200"
                                                   title="View Account">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                @if ($canEdit)
                                                    <a href="{{ route('accounts.edit', $account) }}"
                                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-green-600 hover:bg-green-50 transition-colors duration-200"
                                                       title="Edit Account">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif

                                                @if ($canFreeze && $account->status !== 'closed')
                                                    <button wire:click="confirmFreeze({{ $account->id }})"
                                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg {{ $account->status === 'frozen' ? 'text-green-600 hover:bg-green-50' : 'text-yellow-600 hover:bg-yellow-50' }} transition-colors duration-200"
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
                                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-600 hover:bg-red-50 transition-colors duration-200"
                                                            title="Close Account">
                                                        <i class="fas fa-times-circle"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="text-center py-16 px-4">
                        <div class="mx-auto w-24 h-24 rounded-full bg-gray-100 flex items-center justify-center mb-6">
                            <i class="fas fa-wallet text-3xl text-gray-400"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">
                            @if ($hasActiveFilters)
                                No accounts match your filters
                            @else
                                No accounts found
                            @endif
                        </h3>
                        <p class="text-gray-500 mb-6 max-w-md mx-auto">
                            @if ($hasActiveFilters)
                                Try adjusting your search criteria or clear filters to see all accounts.
                            @else
                                Get started by creating a new customer account.
                            @endif
                        </p>
                        <div class="flex flex-col sm:flex-row gap-3 justify-center">
                            @if ($hasActiveFilters)
                                <button wire:click="resetFilters"
                                        class="inline-flex items-center px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                    <i class="fas fa-times-circle mr-2"></i>
                                    Clear All Filters
                                </button>
                            @endif
                            @if ($canCreate)
                                <a href="{{ route('accounts.create') }}"
                                   class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                    <i class="fas fa-plus mr-2"></i>
                                    Create New Account
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Pagination -->
            @if ($accounts->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $accounts->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modals -->
    <!-- Delete/Close Confirmation Modal -->
    @if ($showDeleteModal && $accountToDelete)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-exclamation-triangle text-red-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">
                                    Close Account
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-600">
                                        Are you sure you want to close account <strong>{{ $accountToDelete->account_number }}</strong>?
                                    </p>
                                    <div class="mt-4 p-4 bg-yellow-50 rounded-lg">
                                        <p class="text-sm text-yellow-800 font-medium">
                                            <i class="fas fa-exclamation-circle mr-2"></i>
                                            Important Notes:
                                        </p>
                                        <ul class="text-sm text-yellow-700 mt-2 space-y-1 list-disc list-inside">
                                            <li>Account status will be changed to "Closed"</li>
                                            <li>No further transactions will be allowed</li>
                                            <li>This action can be reversed by contacting support</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-2xl">
                        <button type="button" 
                                wire:click="deleteAccount"
                                class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2.5 bg-gradient-to-r from-red-600 to-red-700 text-base font-medium text-white hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-all duration-200">
                            Close Account
                        </button>
                        <button type="button" 
                                wire:click="closeDeleteModal"
                                class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Freeze/Unfreeze Modal -->
    @if ($showFreezeModal && $accountToFreeze)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full {{ $accountToFreeze->status === 'frozen' ? 'bg-green-100' : 'bg-yellow-100' }} sm:mx-0 sm:h-10 sm:w-10">
                                @if ($accountToFreeze->status === 'frozen')
                                    <i class="fas fa-unlock text-green-600"></i>
                                @else
                                    <i class="fas fa-lock text-yellow-600"></i>
                                @endif
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">
                                    {{ $accountToFreeze->status === 'frozen' ? 'Unfreeze' : 'Freeze' }} Account
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-600">
                                        Are you sure you want to <strong>{{ $accountToFreeze->status === 'frozen' ? 'unfreeze' : 'freeze' }}</strong> account <strong>{{ $accountToFreeze->account_number }}</strong>?
                                    </p>
                                    <div class="mt-4 p-4 {{ $accountToFreeze->status === 'frozen' ? 'bg-green-50' : 'bg-yellow-50' }} rounded-lg">
                                        <p class="text-sm {{ $accountToFreeze->status === 'frozen' ? 'text-green-800' : 'text-yellow-800' }} font-medium">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            {{ $accountToFreeze->status === 'frozen' ? 'Unfreezing will:' : 'Freezing will:' }}
                                        </p>
                                        <ul class="text-sm {{ $accountToFreeze->status === 'frozen' ? 'text-green-700' : 'text-yellow-700' }} mt-2 space-y-1 list-disc list-inside">
                                            @if ($accountToFreeze->status === 'frozen')
                                                <li>Allow all transactions immediately</li>
                                                <li>Change status to "Active"</li>
                                                <li>Remove all transaction restrictions</li>
                                            @else
                                                <li>Block all debit transactions</li>
                                                <li>Allow only credit transactions</li>
                                                <li>Change status to "Frozen"</li>
                                                <li>Customer will be notified automatically</li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-2xl">
                        <button type="button" 
                                wire:click="toggleFreeze"
                                class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2.5 {{ $accountToFreeze->status === 'frozen' ? 'bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800' : 'bg-gradient-to-r from-yellow-600 to-yellow-700 hover:from-yellow-700 hover:to-yellow-800' }} text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $accountToFreeze->status === 'frozen' ? 'focus:ring-green-500' : 'focus:ring-yellow-500' }} sm:ml-3 sm:w-auto sm:text-sm transition-all duration-200">
                            {{ $accountToFreeze->status === 'frozen' ? 'Unfreeze Account' : 'Freeze Account' }}
                        </button>
                        <button type="button" 
                                wire:click="closeFreezeModal"
                                class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
    [x-cloak] { display: none !important; }
    .rotate-180 { transform: rotate(180deg); }
</style>
@endpush

@push('scripts')
<script>
    // Initialize Alpine.js components
    document.addEventListener('alpine:init', () => {
        Alpine.data('accountFilters', () => ({
            showAdvanced: false,
            init() {
                // Any initialization logic for filters
            }
        }));
    });

    // Format currency inputs on blur
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