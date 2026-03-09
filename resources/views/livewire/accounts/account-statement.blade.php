<div>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">
                            <i class="fas fa-file-invoice mr-2 text-blue-600"></i>
                            Account Statement
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">
                            View transaction history and download statements
                        </p>
                    </div>
                    <div class="flex space-x-2">
                        <!-- Filter Toggle Button -->
                        <button wire:click="$toggle('showFilters')"
                            class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <i class="fas fa-sliders-h mr-2"></i>
                            <span>{{ $showFilters ? 'Hide' : 'Show' }} Filters</span>
                        </button>
                        {{-- Export Button --}}
                        <button wire:click="$set('showExportModal', true)"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <i class="fas fa-download mr-2"></i>
                            Export Statement
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <!-- Account Details -->
            <div class="bg-white rounded-lg shadow-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                        <i class="fas fa-university text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Account Number</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $account->account_number }}</p>
                        <p class="text-xs text-gray-500">{{ $account->accountType->name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Customer Name -->
            <div class="bg-white rounded-lg shadow-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                        <i class="fas fa-user text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Account Holder</p>
                        <p class="text-lg font-semibold text-gray-900">{{ ucwords($customer->full_name) }}</p>
                        <p class="text-xs text-gray-500">ID: {{ $customer->customer_number }}</p>
                    </div>
                </div>
            </div>

            <!-- Currency -->
            <div class="bg-white rounded-lg shadow-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                        <i class="fas fa-money-bill-wave text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Currency</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $account->currency }}</p>
                        <p class="text-xs text-gray-500">Current Balance</p>
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="bg-white rounded-lg shadow-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                        <i class="fas fa-circle text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Status</p>
                        <p class="text-lg font-semibold">
                            <span
                                class="px-2 py-1 text-xs font-medium rounded-full
                                @if ($account->status === 'active') bg-green-100 text-green-800
                                @elseif($account->status === 'frozen') bg-yellow-100 text-yellow-800
                                @elseif($account->status === 'closed') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($account->status) }}
                            </span>
                        </p>
                        <p class="text-xs text-gray-500">As of {{ now()->format('d M Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Card - Only shown when showFilters is true -->
        @if ($showFilters)
            <div class="bg-white rounded-lg shadow-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800">
                            <i class="fas fa-filter mr-2 text-blue-600"></i>
                            Filter Transactions
                        </h3>
                        <button wire:click="$toggle('showFilters')" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Date Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt mr-1 text-gray-400"></i>
                                Date Range
                            </label>
                            <select wire:model.live="dateRange"
                                class="w-full pl-10 pr-10 py-3 text-base border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="today">Today</option>
                                <option value="this_week">This Week</option>
                                <option value="this_month">This Month</option>
                                <option value="last_month">Last Month</option>
                                <option value="this_quarter">This Quarter</option>
                                <option value="this_year">This Year</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>

                        <!-- Start Date (shown only for custom range) -->
                        @if ($dateRange === 'custom')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                <input type="date" wire:model.live="startDate"
                                    class="w-full pl-10 pr-10 py-3 text-base border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                                <input type="date" wire:model.live="endDate"
                                    class="w-full pl-10 pr-10 py-3 text-base border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                        @endif

                        <!-- Transaction Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-exchange-alt mr-1 text-gray-400"></i>
                                Transaction Type
                            </label>
                            <select wire:model.live="transactionType"
                                class="w-full pl-10 pr-10 py-3 text-base border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="all">All Types</option>
                                <option value="deposit">Deposits</option>
                                <option value="withdrawal">Withdrawals</option>
                                <option value="transfer">Transfers</option>
                                <option value="fee">Fees</option>
                                <option value="interest">Interest</option>
                            </select>
                        </div>

                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-search mr-1 text-gray-400"></i>
                                Search
                            </label>
                            <input type="text" wire:model.live.debounce.300ms="search"
                                placeholder="Reference or description..."
                                class="w-full pl-10 pr-10 py-3 text-base border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <!-- Min Amount -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Min Amount
                                ({{ $account->currency }})</label>
                            <input type="number" wire:model.live.debounce.500ms="minAmount" step="0.01"
                                min="0" placeholder="0.00"
                                class="w-full pl-10 pr-10 py-3 text-base border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <!-- Max Amount -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Max Amount
                                ({{ $account->currency }})</label>
                            <input type="number" wire:model.live.debounce.500ms="maxAmount" step="0.01"
                                min="0" placeholder="0.00"
                                class="w-full pl-10 pr-10 py-3 text-base border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-end space-x-2">
                            <button wire:click="applyFilters"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <i class="fas fa-filter mr-2"></i>
                                Apply Filters
                            </button>
                            <button wire:click="resetFilters"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                <i class="fas fa-undo mr-2"></i>
                                Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif


        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-lg p-4">
                <p class="text-sm font-medium text-gray-500">Opening Balance</p>
                <p class="text-xl font-bold text-gray-900">{{ $account->currency }}
                    {{ number_format($openingBalance, 2) }}</p>
                <p class="text-xs text-gray-500">As of {{ Carbon\Carbon::parse($startDate)->format('d M Y') }}</p>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-4">
                <p class="text-sm font-medium text-gray-500">Total Credits</p>
                <p class="text-xl font-bold text-green-600">{{ $account->currency }}
                    {{ number_format($totalCredits, 2) }}</p>
                <p class="text-xs text-gray-500">{{ $transactionCount }} transactions</p>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-4">
                <p class="text-sm font-medium text-gray-500">Total Debits</p>
                <p class="text-xl font-bold text-red-600">{{ $account->currency }}
                    {{ number_format($totalDebits, 2) }}</p>
                <p class="text-xs text-gray-500">{{ $transactionCount }} transactions</p>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-4">
                <p class="text-sm font-medium text-gray-500">Net Change</p>
                @php $netChange = $totalCredits - $totalDebits; @endphp
                <p class="text-xl font-bold {{ $netChange >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $account->currency }} {{ number_format($netChange, 2) }}
                </p>
                <p class="text-xs text-gray-500">During period</p>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-4">
                <p class="text-sm font-medium text-gray-500">Closing Balance</p>
                <p class="text-xl font-bold text-gray-900">{{ $account->currency }}
                    {{ number_format($closingBalance, 2) }}</p>
                <p class="text-xs text-gray-500">As of {{ Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white rounded-lg shadow-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-list mr-2 text-blue-600"></i>
                    Transaction History
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Reference
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Description
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Debit (Out)
                            </th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Credit (In)
                            </th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Balance
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            $runningBalance = $closingBalance;
                        @endphp

                        @forelse($transactions as $transaction)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $transaction->initiated_at->format('d M Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">
                                    {{ $transaction->transaction_reference }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $transaction->description }}
                                    @if ($transaction->notes)
                                        <br>
                                        <span class="text-xs text-gray-500">{{ $transaction->notes }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 py-1 text-xs font-medium rounded-full @if ($transaction->type == 'deposit' || $transaction->type == 'cash_deposit') bg-green-100 text-green-800 @elseif($transaction->type == 'withdrawal' || $transaction->type == 'cash_withdrawal') bg-red-100 text-red-800 @elseif($transaction->type == 'transfer') bg-blue-100 text-blue-800 @elseif($transaction->type == 'fee') bg-yellow-100 text-yellow-800 @elseif($transaction->type == 'interest') bg-purple-100 text-purple-800 @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-red-600">
                                    @if ($transaction->source_account_id == $account->id)
                                        {{ $account->currency }} {{ number_format($transaction->amount, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-green-600">
                                    @if ($transaction->destination_account_id == $account->id)
                                        {{ $account->currency }} {{ number_format($transaction->amount, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                                    {{ $account->currency }}
                                    {{ number_format($runningBalances[$transaction->id] ?? 0, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 py-1 text-xs font-medium rounded-full @if ($transaction->status === 'completed') bg-green-100 text-green-800 @elseif($transaction->status === 'pending') bg-yellow-100 text-yellow-800 @elseif($transaction->status === 'failed') bg-red-100 text-red-800 @elseif($transaction->status === 'reversed') bg-gray-100 text-gray-800 @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-receipt text-5xl mb-4 text-gray-300"></i>
                                    <p class="text-lg font-medium text-gray-600">No transactions found</p>
                                    <p class="text-sm text-gray-400 mt-1">
                                        No transactions match your selected filters
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($transactions->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>

        <!-- Export Modal -->
        @if ($showExportModal)
            <div class="fixed inset-0 overflow-y-auto z-50" aria-labelledby="modal-title" role="dialog"
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
                                    class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <i class="fas fa-download text-blue-600"></i>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                        Export Account Statement
                                    </h3>
                                    <div class="mt-4">
                                        <p class="text-sm text-gray-500 mb-4">
                                            Choose export format for the statement period:
                                            <br>
                                            <span class="font-medium">
                                                {{ Carbon\Carbon::parse($startDate)->format('d M Y') }} -
                                                {{ Carbon\Carbon::parse($endDate)->format('d M Y') }}
                                            </span>
                                        </p>

                                        <div class="space-y-3">
                                            <label class="flex items-center">
                                                <input type="radio" wire:model="exportFormat" value="pdf"
                                                    class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                                <span class="ml-3">
                                                    <span class="block text-sm font-medium text-gray-700">PDF
                                                        Document</span>
                                                    <span class="block text-xs text-gray-500">Best for printing and
                                                        sharing</span>
                                                </span>
                                            </label>

                                            <label class="flex items-center">
                                                <input type="radio" wire:model="exportFormat" value="csv"
                                                    class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                                <span class="ml-3">
                                                    <span class="block text-sm font-medium text-gray-700">CSV
                                                        File</span>
                                                    <span class="block text-xs text-gray-500">Compatible with Excel and
                                                        other spreadsheet apps</span>
                                                </span>
                                            </label>

                                            <label class="flex items-center opacity-50">
                                                <input type="radio" wire:model="exportFormat" value="excel"
                                                    disabled
                                                    class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                                <span class="ml-3">
                                                    <span class="block text-sm font-medium text-gray-700">Excel File
                                                        (Coming Soon)</span>
                                                    <span class="block text-xs text-gray-500">Native Excel
                                                        format</span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" wire:click="exportStatement" wire:loading.attr="disabled"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                <span wire:loading.remove wire:target="exportStatement">
                                    <i class="fas fa-download mr-2"></i>
                                    Export
                                </span>
                                <span wire:loading wire:target="exportStatement">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>
                                    Exporting...
                                </span>
                            </button>
                            <button type="button" wire:click="$set('showExportModal', false)"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Loading Indicator -->
        <div wire:loading class="fixed bottom-4 right-4 bg-blue-600 text-white px-4 py-2 rounded-lg shadow-lg">
            <i class="fas fa-spinner fa-spin mr-2"></i>
            Loading...
        </div>
    </div>
</div>
