<div>
    <div class="max-w-7xl mx-auto py-6 shadow-lg sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg">
            <!-- Header -->
            <div class="p-6 border-b shadow-md border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <div class="flex items-center space-x-3">
                            <button wire:click="back" 
                                class="text-gray-500 hover:text-gray-700">
                                <i class="fas fa-arrow-left"></i>
                            </button>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">Transaction History</h2>
                                <div class="flex items-center space-x-4 mt-1">
                                    <div class="text-sm text-gray-600">
                                        <i class="fas fa-wallet mr-1"></i>
                                        Account: 
                                        <span class="font-semibold">{{ $account->account_number }}</span>
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        <i class="fas fa-user mr-1"></i>
                                        Customer: 
                                        <span class="font-semibold">{{ $account->customer->full_name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        <i class="fas fa-money-bill-wave mr-1"></i>
                                        Balance: 
                                        <span class="font-semibold text-green-600">
                                            {{ number_format($account->current_balance, 2) }} {{ strtoupper($account->currency) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Transaction Summary -->
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-gray-50 p-3 rounded-lg border">
                                <div class="text-sm text-gray-600">Total Entries</div>
                                <div class="text-lg font-bold text-gray-800">{{ number_format($transactionSummary['total_count']) }}</div>
                            </div>
                            <div class="bg-green-50 p-3 rounded-lg border border-green-100">
                                <div class="text-sm text-green-600">Credits</div>
                                <div class="text-lg font-bold text-green-700">
                                    +{{ number_format($transactionSummary['total_credits'], 2) }}
                                </div>
                                <div class="text-xs text-green-600 mt-1">
                                    {{ number_format($transactionSummary['credit_count']) }} entries
                                </div>
                            </div>
                            <div class="bg-red-50 p-3 rounded-lg border border-red-100">
                                <div class="text-sm text-red-600">Debits</div>
                                <div class="text-lg font-bold text-red-700">
                                    -{{ number_format($transactionSummary['total_debits'], 2) }}
                                </div>
                                <div class="text-xs text-red-600 mt-1">
                                    {{ number_format($transactionSummary['debit_count']) }} entries
                                </div>
                            </div>
                            <div class="bg-blue-50 p-3 rounded-lg border border-blue-100">
                                <div class="text-sm text-blue-600">Net Flow</div>
                                <div class="text-lg font-bold {{ $transactionSummary['net_flow'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                                    {{ $transactionSummary['net_flow'] >= 0 ? '+' : '' }}{{ number_format($transactionSummary['net_flow'], 2) }}
                                </div>
                                <div class="text-xs text-blue-600 mt-1">
                                    {{ strtoupper($account->currency) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex space-x-3">
                        <!-- View Toggle -->
                        <button wire:click="toggleView"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-{{ $viewType === 'list' ? 'th-list' : 'list' }} mr-2"></i>
                            {{ $viewType === 'list' ? 'Detailed View' : 'List View' }}
                        </button>
                        
                        <!-- Filter Toggle -->
                        <button wire:click="$toggle('showFilters')"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-filter mr-2"></i>
                            Filters
                            @if ($hasActiveFilters)
                                <span class="ml-2 inline-flex items-center justify-center h-5 w-5 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold">
                                    <i class="fas fa-check"></i>
                                </span>
                            @endif
                        </button>
                        
                        <!-- Export Button -->
                        <button wire:click="exportTransactions"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-file-export mr-2"></i>
                            Export
                        </button>
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
                                            placeholder="Search by reference, description..."
                                            class="pl-10 pr-8 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-search text-gray-400"></i>
                                        </div>
                                        @if ($search)
                                            <button wire:click="$set('search', '')"
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Entry Type Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Entry Type</label>
                                    <div class="relative">
                                        <select wire:model.live="entryType"
                                            class="pr-8 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">All Types</option>
                                            <option value="credit">Credit</option>
                                            <option value="debit">Debit</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Status Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Status</label>
                                    <div class="relative">
                                        <select wire:model.live="status"
                                            class="pr-8 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">All Status</option>
                                            @foreach ($transactionStatuses as $statusValue)
                                                <option value="{{ $statusValue }}">
                                                    {{ ucfirst($statusValue) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Date Range -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                                    <input type="date" wire:model.live="dateFrom"
                                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                                    <input type="date" wire:model.live="dateTo"
                                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                
                                <!-- Amount Range -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Min Amount</label>
                                    <input type="number" wire:model.live.debounce.500ms="amountMin"
                                        placeholder="Min amount"
                                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        step="0.01">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Amount</label>
                                    <input type="number" wire:model.live.debounce.500ms="amountMax"
                                        placeholder="Max amount"
                                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        step="0.01">
                                </div>
                            </div>
                            
                            <!-- Active Filters Badges -->
                            @if ($hasActiveFilters)
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @if ($search)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Search: "{{ $search }}"
                                            <button wire:click="$set('search', '')" class="ml-1 text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </span>
                                    @endif
                                    @if ($entryType)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            Type: {{ ucfirst($entryType) }}
                                            <button wire:click="$set('entryType', '')" class="ml-1 text-purple-600 hover:text-purple-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </span>
                                    @endif
                                    @if ($status)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Status: {{ ucfirst($status) }}
                                            <button wire:click="$set('status', '')" class="ml-1 text-green-600 hover:text-green-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </span>
                                    @endif
                                    @if ($dateFrom)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            From: {{ $dateFrom }}
                                            <button wire:click="$set('dateFrom', '')" class="ml-1 text-yellow-600 hover:text-yellow-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </span>
                                    @endif
                                    @if ($dateTo)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            To: {{ $dateTo }}
                                            <button wire:click="$set('dateTo', '')" class="ml-1 text-indigo-600 hover:text-indigo-800">
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
            
            <!-- Transactions Content -->
            <div class="overflow-x-auto">
                <!-- Results Summary -->
                <div class="mt-4 px-6 flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        @if ($ledgerEntries->total() > 0)
                            Showing {{ $ledgerEntries->firstItem() }} to {{ $ledgerEntries->lastItem() }} of
                            {{ $ledgerEntries->total() }} ledger entries
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
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-sm text-gray-600">per page</span>
                    </div>
                </div>
                
                @if ($ledgerEntries->count())
                    <!-- List View -->
                    @if ($viewType === 'list')
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date & Reference
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Description
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Type
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Amount
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Balance
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($ledgerEntries as $entry)
                                    @php
                                        $transaction = $entry->transaction;
                                        $isCredit = $entry->entry_type === 'credit';
                                        $isReversed = $entry->is_reversed;
                                        $counterparty = null;
                                        
                                        if ($transaction) {
                                            $isDebitEntry = $entry->entry_type === 'debit';
                                            $counterparty = $isDebitEntry ? $transaction->destinationAccount : $transaction->sourceAccount;
                                        }
                                    @endphp
                                    <tr class="hover:bg-gray-50 {{ $isReversed ? 'bg-gray-50 opacity-75' : '' }}">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $entry->created_at->format('M d, Y') }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $entry->created_at->format('H:i') }}
                                            </div>
                                            @if ($transaction && $transaction->transaction_reference)
                                                <div class="text-xs text-gray-400 mt-1">
                                                    {{ $transaction->transaction_reference }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $entry->description ?: ($transaction ? $transaction->description : 'Ledger Entry') }}
                                            </div>
                                            @if ($counterparty && $counterparty->customer)
                                                <div class="text-xs text-gray-500">
                                                    {{ $isCredit ? 'From: ' : 'To: ' }}
                                                    {{ $counterparty->customer->full_name }}
                                                    ({{ $counterparty->account_number }})
                                                </div>
                                            @endif
                                            @if ($isReversed)
                                                <div class="text-xs text-red-500 mt-1">
                                                    <i class="fas fa-undo mr-1"></i>Reversed
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center space-x-2">
                                                <span class="px-2 py-1 text-xs rounded-full {{ $isCredit ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ ucfirst($entry->entry_type) }}
                                                </span>
                                                @if ($transaction && $transaction->type)
                                                    <span class="text-sm text-gray-600">
                                                        {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if ($entry->category)
                                                <div class="text-xs text-gray-400 mt-1">
                                                    {{ $entry->category }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-bold {{ $isCredit ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $isCredit ? '+' : '-' }}{{ number_format($entry->amount, 2) }}
                                                <span class="text-xs">{{ strtoupper($entry->currency ?: $account->currency) }}</span>
                                            </div>
                                            @if ($transaction && $transaction->fee_amount > 0)
                                                <div class="text-xs text-gray-500">
                                                    Fee: {{ number_format($transaction->fee_amount, 2) }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="space-y-1">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ number_format($entry->balance_after, 2) }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    Before: {{ number_format($entry->balance_before, 2) }}
                                                </div>
                                                @if ($entry->available_balance_after != $entry->balance_after)
                                                    <div class="text-xs text-blue-500">
                                                        Available: {{ number_format($entry->available_balance_after, 2) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if ($transaction)
                                                @php
                                                    $statusColors = [
                                                        'completed' => 'bg-green-100 text-green-800',
                                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                                        'failed' => 'bg-red-100 text-red-800',
                                                        'cancelled' => 'bg-gray-100 text-gray-800',
                                                        'reversed' => 'bg-purple-100 text-purple-800',
                                                    ];
                                                    $statusColor = $statusColors[$transaction->status] ?? 'bg-gray-100 text-gray-800';
                                                @endphp
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColor }}">
                                                    {{ ucfirst($transaction->status) }}
                                                </span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    N/A
                                                </span>
                                            @endif
                                            @if ($isReversed)
                                                <div class="text-xs text-red-400 mt-1">
                                                    {{ $entry->reversed_at?->format('M d') ?? 'Reversed' }}
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    
                    <!-- Detailed View -->
                    @else
                        <div class="p-6 space-y-4">
                            @foreach ($ledgerEntries as $entry)
                                @php
                                    $transaction = $entry->transaction;
                                    $isCredit = $entry->entry_type === 'credit';
                                    $isReversed = $entry->is_reversed;
                                    $counterparty = null;
                                    
                                    if ($transaction) {
                                        $isDebitEntry = $entry->entry_type === 'debit';
                                        $counterparty = $isDebitEntry ? $transaction->destinationAccount : $transaction->sourceAccount;
                                    }
                                @endphp
                                
                                <div class="border border-gray-200 rounded-lg overflow-hidden hover:border-gray-300 transition-colors {{ $isReversed ? 'border-red-200 bg-red-50' : '' }}">
                                    <div class="bg-gray-50 px-6 py-3 border-b border-gray-200 flex justify-between items-center">
                                        <div class="flex items-center space-x-4">
                                            <div class="h-8 w-8 rounded-full {{ $isCredit ? 'bg-green-100' : 'bg-red-100' }} flex items-center justify-center">
                                                <i class="fas {{ $isCredit ? 'fa-arrow-down' : 'fa-arrow-up' }} text-sm {{ $isCredit ? 'text-green-600' : 'text-red-600' }}"></i>
                                            </div>
                                            <div>
                                                <span class="text-sm font-medium text-gray-900">
                                                    {{ $isCredit ? 'Credit' : 'Debit' }} Entry
                                                </span>
                                                <div class="text-xs text-gray-500">
                                                    {{ $entry->created_at->format('M d, Y H:i') }}
                                                    @if ($transaction && $transaction->transaction_reference)
                                                        • {{ $transaction->transaction_reference }}
                                                    @endif
                                                    @if ($isReversed)
                                                        • <span class="text-red-500">Reversed</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-bold {{ $isCredit ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $isCredit ? '+' : '-' }}{{ number_format($entry->amount, 2) }}
                                                <span class="text-sm">{{ strtoupper($entry->currency ?: $account->currency) }}</span>
                                            </div>
                                            @if ($isReversed)
                                                <div class="text-xs text-red-500">
                                                    <i class="fas fa-undo mr-1"></i>Reversed Entry
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                        <!-- Entry Details -->
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-700 mb-2">Entry Details</h4>
                                            <div class="space-y-2">
                                                <div class="flex justify-between">
                                                    <span class="text-sm text-gray-500">Type:</span>
                                                    <span class="text-sm font-medium {{ $isCredit ? 'text-green-600' : 'text-red-600' }}">
                                                        {{ ucfirst($entry->entry_type) }}
                                                    </span>
                                                </div>
                                                @if ($entry->category)
                                                <div class="flex justify-between">
                                                    <span class="text-sm text-gray-500">Category:</span>
                                                    <span class="text-sm font-medium text-gray-900">
                                                        {{ $entry->category }}
                                                    </span>
                                                </div>
                                                @endif
                                                @if ($transaction)
                                                <div class="flex justify-between">
                                                    <span class="text-sm text-gray-500">Transaction Type:</span>
                                                    <span class="text-sm font-medium text-gray-900">
                                                        {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
                                                    </span>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Balance Information -->
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-700 mb-2">Balance Information</h4>
                                            <div class="space-y-2">
                                                <div class="flex justify-between">
                                                    <span class="text-sm text-gray-500">Balance Before:</span>
                                                    <span class="text-sm font-medium text-gray-900">
                                                        {{ number_format($entry->balance_before, 2) }}
                                                    </span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-sm text-gray-500">Amount:</span>
                                                    <span class="text-sm font-medium {{ $isCredit ? 'text-green-600' : 'text-red-600' }}">
                                                        {{ $isCredit ? '+' : '-' }}{{ number_format($entry->amount, 2) }}
                                                    </span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-sm text-gray-500">Balance After:</span>
                                                    <span class="text-sm font-bold text-gray-900">
                                                        {{ number_format($entry->balance_after, 2) }}
                                                    </span>
                                                </div>
                                                @if ($entry->available_balance_after != $entry->balance_after)
                                                <div class="flex justify-between">
                                                    <span class="text-sm text-gray-500">Available Balance:</span>
                                                    <span class="text-sm text-blue-600">
                                                        {{ number_format($entry->available_balance_after, 2) }}
                                                    </span>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Transaction Status -->
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-700 mb-2">Transaction Status</h4>
                                            @if ($transaction)
                                            <div class="space-y-2">
                                                <div class="flex justify-between">
                                                    <span class="text-sm text-gray-500">Status:</span>
                                                    @php
                                                        $statusColors = [
                                                            'completed' => 'text-green-600',
                                                            'pending' => 'text-yellow-600',
                                                            'failed' => 'text-red-600',
                                                            'cancelled' => 'text-gray-600',
                                                        ];
                                                    @endphp
                                                    <span class="text-sm font-medium {{ $statusColors[$transaction->status] ?? 'text-gray-600' }}">
                                                        {{ ucfirst($transaction->status) }}
                                                    </span>
                                                </div>
                                                @if ($transaction->completed_at)
                                                <div class="flex justify-between">
                                                    <span class="text-sm text-gray-500">Completed:</span>
                                                    <span class="text-sm text-gray-900">
                                                        {{ $transaction->completed_at->format('M d, Y H:i') }}
                                                    </span>
                                                </div>
                                                @endif
                                                @if ($transaction->initiator)
                                                <div class="flex justify-between">
                                                    <span class="text-sm text-gray-500">Initiated By:</span>
                                                    <span class="text-sm text-gray-900">
                                                        {{ $transaction->initiator->name ?? 'System' }}
                                                    </span>
                                                </div>
                                                @endif
                                            </div>
                                            @else
                                            <div class="text-sm text-gray-500 italic">No linked transaction</div>
                                            @endif
                                        </div>
                                        
                                        <!-- Counterparty Information -->
                                        @if ($counterparty)
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-700 mb-2">
                                                {{ $isCredit ? 'Sender' : 'Recipient' }}
                                            </h4>
                                            <div class="space-y-2">
                                                <div class="flex justify-between">
                                                    <span class="text-sm text-gray-500">Account:</span>
                                                    <span class="text-sm font-medium text-gray-900">
                                                        {{ $counterparty->account_number }}
                                                    </span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-sm text-gray-500">Customer:</span>
                                                    <span class="text-sm font-medium text-gray-900">
                                                        {{ $counterparty->customer->full_name ?? 'External' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        
                                        <!-- Description -->
                                        @if ($entry->description || ($transaction && $transaction->description))
                                        <div class="md:col-span-2 lg:col-span-3">
                                            <h4 class="text-sm font-medium text-gray-700 mb-2">Description</h4>
                                            <p class="text-sm text-gray-600">
                                                {{ $entry->description ?: ($transaction ? $transaction->description : 'No description') }}
                                            </p>
                                            @if ($transaction && $transaction->notes)
                                            <div class="mt-2 p-3 bg-gray-50 rounded">
                                                <div class="text-xs text-gray-500 mb-1">Notes:</div>
                                                <p class="text-sm text-gray-700">{{ $transaction->notes }}</p>
                                            </div>
                                            @endif
                                        </div>
                                        @endif
                                        
                                        <!-- Additional Metadata -->
                                        @if ($entry->metadata && count(json_decode($entry->metadata, true) ?: []))
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-700 mb-2">Additional Info</h4>
                                            <div class="space-y-1">
                                                @php
                                                    $metadata = json_decode($entry->metadata, true) ?: [];
                                                @endphp
                                                @foreach ($metadata as $key => $value)
                                                    @if (!is_array($value))
                                                        <div class="flex justify-between">
                                                            <span class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                                            <span class="text-xs text-gray-900">{{ $value }}</span>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    
                                    @if ($isReversed && $entry->reversed_at)
                                    <div class="bg-red-50 px-6 py-3 border-t border-red-200">
                                        <div class="flex items-center text-red-600">
                                            <i class="fas fa-undo mr-2"></i>
                                            <span class="text-sm">
                                                This entry was reversed on {{ $entry->reversed_at->format('M d, Y H:i') }}
                                            </span>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="text-center py-12">
                        <div class="text-gray-400 mb-4">
                            <i class="fas fa-exchange-alt text-4xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900">No ledger entries found</h3>
                        <p class="text-gray-500 mt-1">
                            @if ($hasActiveFilters)
                                Try adjusting your search or filters
                            @else
                                No ledger entries recorded for this account.
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
            @if ($ledgerEntries->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $ledgerEntries->links() }}
                </div>
            @endif
        </div>
    </div>
</div>