<div>
    <div class="max-w-7xl mx-auto py-6 shadow-lg sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg">
            <!-- Header -->
            <div class="p-6 border-b shadow-md border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Transaction History</h2>
                        <p class="text-sm text-gray-600 mt-1">View and manage all financial transactions</p>
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
                        <button wire:click="exportTransactions"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-file-excel mr-2 text-green-600"></i>
                            Export
                        </button>
                        @if ($canCreate)
                            <button onclick="window.location.href='{{ route('transactions.create') }}'"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-exchange-alt mr-2"></i>
                                New Transaction
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Total Transactions Card -->
                    <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-12 w-12 rounded-lg bg-blue-100 flex items-center justify-center">
                                        <i class="fas fa-exchange-alt text-blue-600"></i>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Total Transactions
                                        </dt>
                                        <dd class="text-2xl font-semibold text-gray-900">
                                            {{ number_format($stats['total']) }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Completed Transactions Card -->
                    <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-12 w-12 rounded-lg bg-green-100 flex items-center justify-center">
                                        <i class="fas fa-check-circle text-green-600"></i>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Completed
                                        </dt>
                                        <dd class="text-2xl font-semibold text-gray-900">
                                            {{ number_format($stats['completed']) }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3">
                            <div class="text-sm">
                                <button type="button" wire:click="$set('status', 'completed')"
                                    class="font-medium text-blue-700 hover:text-blue-900">
                                    View completed
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Transactions Card -->
                    <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-12 w-12 rounded-lg bg-yellow-100 flex items-center justify-center">
                                        <i class="fas fa-clock text-yellow-600"></i>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Pending
                                        </dt>
                                        <dd class="text-2xl font-semibold text-gray-900">
                                            {{ number_format($stats['pending']) }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3">
                            <div class="text-sm">
                                <button type="button" wire:click="$set('status', 'pending')"
                                    class="font-medium text-blue-700 hover:text-blue-900">
                                    View pending
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Failed Transactions Card -->
                    <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-12 w-12 rounded-lg bg-red-100 flex items-center justify-center">
                                        <i class="fas fa-times-circle text-red-600"></i>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Failed
                                        </dt>
                                        <dd class="text-2xl font-semibold text-gray-900">
                                            {{ number_format($stats['failed']) }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3">
                            <div class="text-sm">
                                <button type="button" wire:click="$set('status', 'failed')"
                                    class="font-medium text-blue-700 hover:text-blue-900">
                                    View failed
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters Panel -->
                @if ($showFilters)
                    <div class="mt-6 transition-all duration-300 ease-in-out">
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Filter Transactions</h3>
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
                                            placeholder="Search by reference or description..."
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

                                <!-- Account Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Account</label>
                                    <select wire:model.live="account_id"
                                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">All Accounts</option>
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account->id }}">
                                                {{ $account->account_number }} - {{ $account->accountType->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Type Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                                    <select wire:model.live="type"
                                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">All Types</option>
                                        @foreach ($transactionTypes as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Status Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <select wire:model.live="status"
                                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">All Statuses</option>
                                        @foreach ($statuses as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Date Range -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <input type="date" wire:model.live="start_date"
                                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <p class="text-xs text-gray-500 mt-1">Start date</p>
                                        </div>
                                        <div>
                                            <input type="date" wire:model.live="end_date"
                                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <p class="text-xs text-gray-500 mt-1">End date</p>
                                        </div>
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
                                            <button wire:click="$set('search', '')"
                                                class="ml-1 text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </span>
                                    @endif
                                    @if ($account_id)
                                        @php
                                            $account = $accounts->firstWhere('id', $account_id);
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Account: {{ $account ? $account->account_number : 'N/A' }}
                                            <button wire:click="$set('account_id', '')"
                                                class="ml-1 text-green-600 hover:text-green-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </span>
                                    @endif
                                    @if ($type)
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            Type: {{ $transactionTypes[$type] ?? $type }}
                                            <button wire:click="$set('type', '')"
                                                class="ml-1 text-purple-600 hover:text-purple-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </span>
                                    @endif
                                    @if ($status)
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            Status: {{ $statuses[$status] ?? $status }}
                                            <button wire:click="$set('status', '')"
                                                class="ml-1 text-orange-600 hover:text-orange-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </span>
                                    @endif
                                    @if ($start_date)
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            From: {{ $start_date }}
                                            <button wire:click="$set('start_date', '')"
                                                class="ml-1 text-indigo-600 hover:text-indigo-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </span>
                                    @endif
                                    @if ($end_date)
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            To: {{ $end_date }}
                                            <button wire:click="$set('end_date', '')"
                                                class="ml-1 text-indigo-600 hover:text-indigo-800">
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

            <!-- Transactions Table -->
            <div class="overflow-x-auto">
                <!-- Results Summary -->
                <div class="mt-4 px-6 flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        @if ($transactions && $transactions->total() > 0)
                            Showing {{ $transactions->firstItem() }} to {{ $transactions->lastItem() }} of
                            {{ $transactions->total() }} transactions
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

                @if ($transactions && $transactions->count())
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Transaction
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Accounts
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Amount
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date & Time
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($transactions as $transaction)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                @php
                                                    $icon = match ($transaction->type) {
                                                        'transfer' => 'exchange-alt',
                                                        'withdrawal' => 'money-bill-wave',
                                                        'deposit' => 'money-check',
                                                        'reversal' => 'undo',
                                                        default => 'exchange-alt',
                                                    };

                                                    $color = match ($transaction->type) {
                                                        'transfer' => 'text-blue-600 bg-blue-100',
                                                        'withdrawal' => 'text-red-600 bg-red-100',
                                                        'deposit' => 'text-green-600 bg-green-100',
                                                        'reversal' => 'text-yellow-600 bg-yellow-100',
                                                        default => 'text-gray-600 bg-gray-100',
                                                    };
                                                @endphp
                                                <div
                                                    class="h-10 w-10 rounded-lg {{ $color }} flex items-center justify-center">
                                                    <i class="fas fa-{{ $icon }}"></i>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ ucfirst($transaction->type) }}
                                                </div>
                                                <div class="text-sm text-gray-500 font-mono">
                                                    #{{ $transaction->transaction_reference }}
                                                </div>
                                                @if ($transaction->description)
                                                    <div class="text-xs text-gray-400 truncate max-w-xs">
                                                        {{ $transaction->description }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="space-y-1">
                                            @foreach ($transaction->ledgerEntries as $entry)
                                                {{-- <div class="flex items-center text-sm"> --}}
                                                    <div
                                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                                        {{ $entry->entry_type === 'credit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                        {{ strtoupper($entry->entry_type) }}
                                                    </div>
                                                    <div class="ml-2 font-mono text-gray-500  text-sm">
                                                        {{ $entry->account->account_number }}
                                                    </div>
                                                    <div class="ml-2 text-gray-500 text-xs">
                                                        ({{ $entry->account->accountType->name }})
                                                    </div>
                                                {{-- </div> --}}
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ number_format($transaction->amount, 2) }} {{ $transaction->currency }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            @php
                                                $totalEntries = $transaction->ledgerEntries->count();
                                                $credits = $transaction->ledgerEntries
                                                    ->where('entry_type', 'credit')
                                                    ->sum('amount');
                                                $debits = $transaction->ledgerEntries
                                                    ->where('entry_type', 'debit')
                                                    ->sum('amount');
                                            @endphp
                                            {{-- Balance: {{ number_format($credits - $debits, 2) }} --}}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $transaction->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $transaction->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $transaction->status === 'reversed' ? 'bg-gray-100 text-gray-800' : '' }}">
                                            <i class="fas fa-circle mr-1 text-xs"></i>
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                        @if ($transaction->metadata && array_key_exists('failure_reason', $transaction->metadata))
                                            <div class="text-xs text-red-600 mt-1">
                                                {{ $transaction->metadata['failure_reason'] }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $transaction->initiated_at->format('M d, Y') }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $transaction->initiated_at->format('h:i A') }}
                                            @if ($transaction->completed_at)
                                                <br>
                                                <span class="text-green-600">
                                                    Completed: {{ $transaction->completed_at->format('h:i A') }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            @if ($viewTransaction)
                                                <a href="{{ route('transactions.show', $transaction->id) }}"
                                                    class="text-blue-600 hover:text-blue-900 transition-colors duration-150 p-1 rounded hover:bg-blue-50"
                                                    title="View Transaction">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endif
                                            @if ($reverseTransaction && $transaction->isCompleted() && !$transaction->isReversed())
                                                <button wire:click="confirmReverse({{ $transaction->id }})"
                                                    class="text-yellow-600 hover:text-yellow-900 transition-colors duration-150 p-1 rounded hover:bg-yellow-50"
                                                    title="Reverse Transaction">
                                                    <i class="fas fa-undo"></i>
                                                </button> 
                                            @endif

                                            @if ($exportReceipt)
                                                <button wire:click="exportReceipt({{ $transaction->id }})"
                                                    class="text-purple-600 hover:text-purple-900 transition-colors duration-150 p-1 rounded hover:bg-purple-50"
                                                    title="Download Receipt">
                                                    <i class="fas fa-receipt"></i>
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
                            <i class="fas fa-exchange-alt text-4xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900">No transactions found</h3>
                        <p class="text-gray-500 mt-1">
                            @if (!$transactions)
                                Unable to load transactions. Please check your permissions.
                            @else
                                @if ($hasActiveFilters)
                                    Try adjusting your search or filters
                                @else
                                    No transactions recorded yet.
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
            @if ($transactions && $transactions->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Reverse Confirmation Modal -->
    @if ($showReverseModal && $transactionToReverse)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Reverse Transaction
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Are you sure you want to reverse transaction
                                        <strong>#{{ $transactionToReverse->transaction_reference }}</strong>?
                                    </p>
                                    <div class="mt-4 bg-gray-50 p-4 rounded-md">
                                        <h4 class="text-sm font-medium text-gray-700 mb-2">Transaction Details:</h4>
                                        <div class="grid grid-cols-2 gap-2 text-sm">
                                            <div class="text-gray-600">Amount:</div>
                                            <div class="text-gray-900 font-medium">
                                                {{ number_format($transactionToReverse->amount, 2) }}
                                                {{ $transactionToReverse->currency }}
                                            </div>
                                            <div class="text-gray-600">Type:</div>
                                            <div class="text-gray-900">{{ ucfirst($transactionToReverse->type) }}
                                            </div>
                                            <div class="text-gray-600">Date:</div>
                                            <div class="text-gray-900">
                                                {{ $transactionToReverse->initiated_at->format('M d, Y H:i') }}</div>
                                            @if ($transactionToReverse->description)
                                                <div class="text-gray-600 col-span-2">Description:</div>
                                                <div class="text-gray-900 col-span-2">
                                                    {{ $transactionToReverse->description }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <label for="reverse_reason" class="block text-sm font-medium text-gray-700">
                                            Reason for Reversal <span class="text-gray-500">(Optional)</span>
                                        </label>
                                        <textarea wire:model="reverseReason" id="reverse_reason" rows="3"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Enter reason for reversing this transaction..."></textarea>
                                        @error('reverseReason')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <p class="text-sm text-red-500 mt-2">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        This action cannot be undone and will create a reversal transaction.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="reverseTransaction" wire:loading.attr="disabled"
                            wire:target="reverseTransaction"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="reverseTransaction">
                                Reverse Transaction
                            </span>
                            <span wire:loading wire:target="reverseTransaction">
                                <i class="fas fa-spinner fa-spin mr-2"></i> Processing...
                            </span>
                        </button>
                        <button type="button" wire:click="closeReverseModal" wire:loading.attr="disabled"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
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
        // View transaction details
        function viewTransaction(id) {
            Livewire.dispatch('open-transaction-modal', {
                transactionId: id
            });
        }

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

            // Esc to close modals
            if (e.key === 'Escape') {
                Livewire.dispatch('close-transaction-modal');
            }
        });

        // Date range validation
        document.addEventListener('livewire:init', () => {
            Livewire.on('validateDateRange', () => {
                const startDate = document.querySelector('input[name="start_date"]');
                const endDate = document.querySelector('input[name="end_date"]');

                if (startDate.value && endDate.value && startDate.value > endDate.value) {
                    alert('End date must be after start date');
                    endDate.value = '';
                    endDate.dispatchEvent(new Event('input'));
                }
            });
        });
    </script>
@endpush
