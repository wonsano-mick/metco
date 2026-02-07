<div>
    @if ($loading)
        <!-- Loading State -->
        <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center">
            <div class="text-center">
                <div class="inline-block animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-blue-500 mb-4">
                </div>
                <h3 class="text-lg font-medium text-gray-900">Loading Transaction Details</h3>
                <p class="mt-2 text-gray-600">Please wait while we fetch the transaction information...</p>
            </div>
        </div>
    @elseif($transaction && is_object($transaction))
        <!-- Main Content -->
        <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
            <!-- Header -->
            <div class="bg-white shadow-sm border-b border-gray-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center">
                                <button onclick="history.back()" class="mr-4 text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-arrow-left text-xl"></i>
                                </button>
                                <div>
                                    <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                                        Transaction Details
                                    </h1>
                                    <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
                                        <div class="mt-2 flex items-center text-sm text-gray-500">
                                            <i class="far fa-clock mr-1.5"></i>
                                            Created
                                            {{ optional($transaction->created_at)->format('M d, Y h:i A') ?? 'N/A' }}
                                        </div>
                                        <div class="mt-2 flex items-center text-sm text-gray-500">
                                            <i class="fas fa-hashtag mr-1.5"></i>
                                            {{ $transaction->transaction_reference ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                            <button onclick="printReceiptPreview()"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-print mr-2"></i>
                                Print
                            </button>

                            <button wire:click="printReceipt"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-receipt mr-2"></i>
                                Receipt
                            </button>

                            @if (($transaction->status ?? '') === 'completed' && ($transaction->type ?? '') !== 'reversal')
                                <button wire:click="showReverseConfirmation"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <i class="fas fa-undo mr-2"></i>
                                    Reverse
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Transaction Summary Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8 border border-gray-200">
                    <div class="p-8">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <!-- Left Column -->
                            <div class="lg:col-span-2">
                                <div class="flex items-start justify-between mb-6">
                                    <div>
                                        <h2 class="text-2xl font-bold text-gray-900">
                                            {{ $transactionTypes[$transaction->type] ?? ucfirst(str_replace('_', ' ', $transaction->type)) }}
                                        </h2>
                                        <p class="mt-1 text-gray-600">{{ $transaction->description }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusConfig[$transaction->status]['color'] ?? 'bg-gray-100 text-gray-800' }}">
                                            <i
                                                class="{{ $statusConfig[$transaction->status]['icon'] ?? 'fas fa-circle' }} mr-1.5"></i>
                                            {{ $statusConfig[$transaction->status]['badge'] ?? ucfirst($transaction->status) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Amount Display -->
                                <div class="mb-8">
                                    <div class="text-5xl font-bold text-gray-900 mb-2">
                                        {{ $transaction->currency }} {{ number_format($transaction->amount, 2) }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Transaction Amount
                                    </div>
                                </div>

                                <!-- Transaction Info Grid -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-4">
                                        <div>
                                            <div class="text-sm font-medium text-gray-500">Reference Number</div>
                                            <div class="mt-1 text-lg font-mono font-semibold text-gray-900">
                                                {{ $transaction->transaction_reference }}</div>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-500">Transaction Date</div>
                                            <div class="mt-1 text-lg font-semibold text-gray-900">
                                                {{ optional($transaction->created_at)->format('F d, Y h:i A') ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="space-y-4">
                                        <div>
                                            <div class="text-sm font-medium text-gray-500">Completed Date</div>
                                            <div class="mt-1 text-lg font-semibold text-gray-900">
                                                {{ $transaction->completed_at ? $transaction->completed_at->format('F d, Y h:i A') : 'Pending' }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-500">Initiated By</div>
                                            <div class="mt-1 flex items-center">
                                                @if ($transaction->initiator)
                                                    <div
                                                        class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                        <span class="text-blue-600 font-medium text-sm">
                                                            {{ substr($transaction->initiator->first_name, 0, 1) }}{{ substr($transaction->initiator->last_name, 0, 1) }}
                                                        </span>
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-lg font-semibold text-gray-900">
                                                            {{ $transaction->initiator->name }}</div>
                                                        <div class="text-sm text-gray-500">
                                                            {{ $transaction->initiator->email }}</div>
                                                    </div>
                                                @else
                                                    <div class="text-lg font-semibold text-gray-900">System</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column - Quick Stats -->
                            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Transaction Overview</h3>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600">Type</span>
                                        <span
                                            class="font-semibold text-gray-900">{{ $transactionTypes[$transaction->type] ?? ucfirst(str_replace('_', ' ', $transaction->type)) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600">Status</span>
                                        <span
                                            class="font-semibold {{ $transaction->status === 'completed' ? 'text-green-600' : 'text-yellow-600' }}">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600">Currency</span>
                                        <span class="font-semibold text-gray-900">{{ $transaction->currency }}</span>
                                    </div>
                                    @if ($transaction->metadata && isset($transaction->metadata['purpose']))
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-600">Purpose</span>
                                            <span
                                                class="font-semibold text-gray-900">{{ ucfirst($transaction->metadata['purpose']) }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8">
                            <button wire:click="$set('activeTab', 'overview')"
                                class="@if ($activeTab === 'overview') border-blue-500 text-blue-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                <i class="fas fa-info-circle mr-2"></i>
                                Overview
                            </button>

                            <button wire:click="$set('activeTab', 'parties')"
                                class="@if ($activeTab === 'parties') border-blue-500 text-blue-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                <i class="fas fa-users mr-2"></i>
                                Parties Involved
                            </button>

                            <button wire:click="$set('activeTab', 'ledger')"
                                class="@if ($activeTab === 'ledger') border-blue-500 text-blue-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                <i class="fas fa-book mr-2"></i>
                                Ledger Entries
                            </button>

                            @if (count($auditLogs) > 0)
                                <button wire:click="$set('activeTab', 'audit')"
                                    class="@if ($activeTab === 'audit') border-blue-500 text-blue-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                    <i class="fas fa-history mr-2"></i>
                                    Audit Trail
                                </button>
                            @endif
                        </nav>
                    </div>
                </div>

                <!-- Tab Content -->
                <div>
                    <!-- Overview Tab -->
                    @if ($activeTab === 'overview')
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <!-- Timeline -->
                            <div class="lg:col-span-2">
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Transaction Timeline</h3>
                                    <div class="space-y-6">
                                        @foreach ($this->getTransactionTimeline() as $event)
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0">
                                                    <div
                                                        class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center">
                                                        <i class="{{ $event['icon'] }} {{ $event['color'] }}"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-4 flex-1">
                                                    <div class="flex items-center justify-between">
                                                        <h4 class="text-sm font-semibold text-gray-900">
                                                            {{ $event['status'] }}</h4>
                                                        <span
                                                            class="text-sm text-gray-500">{{ $event['time']->format('h:i A') }}</span>
                                                    </div>
                                                    <p class="mt-1 text-sm text-gray-600">{{ $event['description'] }}
                                                    </p>
                                                    <p class="mt-1 text-xs text-gray-400">
                                                        {{ $event['time']->format('F d, Y') }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div>
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                                    <div class="space-y-3">
                                        <button wire:click="downloadReceipt"
                                            class="w-full inline-flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                            <i class="fas fa-download mr-3"></i>
                                            Download Receipt
                                        </button>

                                        <button wire:click="emailReceipt"
                                            class="w-full inline-flex items-center justify-center px-4 py-3 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                            <i class="fas fa-envelope mr-3"></i>
                                            Email Receipt
                                        </button>

                                        @if (!$transaction->verified_at)
                                            <button wire:click="verifyTransaction"
                                                class="w-full inline-flex items-center justify-center px-4 py-3 border border-transparent rounded-lg text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
                                                <i class="fas fa-check mr-3"></i>
                                                Verify Transaction
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <!-- Metadata -->
                                @if ($transaction->metadata && is_array($transaction->metadata) && count($transaction->metadata) > 0)
                                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Information
                                        </h3>
                                        <div class="space-y-3">
                                            @foreach ($transaction->metadata as $key => $value)
                                                @if (is_string($value) && !in_array($key, ['initiator', 'ip_address', 'account', 'from_account', 'to_account']))
                                                    <div>
                                                        <div
                                                            class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            {{ str_replace('_', ' ', $key) }}</div>
                                                        <div class="mt-1 text-sm font-medium text-gray-900">
                                                            {{ $value }}</div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Parties Involved Tab -->
                    @if ($activeTab === 'parties')
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Source Account -->
                            @if ($sourceAccount)
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                    <div class="flex items-center mb-6">
                                        <div class="flex-shrink-0">
                                            <div
                                                class="h-12 w-12 rounded-lg bg-gradient-to-r from-red-500 to-pink-500 flex items-center justify-center">
                                                <i class="fas fa-wallet text-white"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <h3 class="text-lg font-semibold text-gray-900">Source Account</h3>
                                            <p class="text-sm text-gray-600">Debited Account</p>
                                        </div>
                                    </div>

                                    <div class="space-y-4">
                                        <div>
                                            <div class="text-sm font-medium text-gray-500">Account Number</div>
                                            <div class="mt-1 text-lg font-mono font-semibold text-gray-900">
                                                {{ $sourceAccount->account_number }}</div>
                                        </div>

                                        @if ($customer)
                                            <div>
                                                <div class="text-sm font-medium text-gray-500">Account Holder</div>
                                                <div class="mt-1 text-lg font-semibold text-gray-900">
                                                    {{ $customer->full_name }}</div>
                                                <div class="mt-1 text-sm text-gray-600">Customer
                                                    #{{ $customer->customer_number }}</div>
                                            </div>
                                        @endif

                                        <div>
                                            <div class="text-sm font-medium text-gray-500">Account Type</div>
                                            <div class="mt-1 text-lg font-semibold text-gray-900">
                                                {{ $sourceAccount->accountType->name ?? 'N/A' }}</div>
                                        </div>

                                        <div>
                                            <div class="text-sm font-medium text-gray-500">Current Balance</div>
                                            <div
                                                class="mt-1 text-2xl font-bold {{ $sourceAccount->current_balance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $sourceAccount->currency }}
                                                {{ number_format($sourceAccount->current_balance, 2) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Destination Account -->
                            @if ($destinationAccount)
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                    <div class="flex items-center mb-6">
                                        <div class="flex-shrink-0">
                                            <div
                                                class="h-12 w-12 rounded-lg bg-gradient-to-r from-green-500 to-emerald-500 flex items-center justify-center">
                                                <i class="fas fa-user text-white"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <h3 class="text-lg font-semibold text-gray-900">Destination Account</h3>
                                            <p class="text-sm text-gray-600">Credited Account</p>
                                        </div>
                                    </div>

                                    <div class="space-y-4">
                                        <div>
                                            <div class="text-sm font-medium text-gray-500">Account Number</div>
                                            <div class="mt-1 text-lg font-mono font-semibold text-gray-900">
                                                {{ $destinationAccount->account_number }}</div>
                                        </div>

                                        @if ($destinationAccount->customer)
                                            <div>
                                                <div class="text-sm font-medium text-gray-500">Account Holder</div>
                                                <div class="mt-1 text-lg font-semibold text-gray-900">
                                                    {{ $destinationAccount->customer->full_name }}</div>
                                                <div class="mt-1 text-sm text-gray-600">Customer
                                                    #{{ $destinationAccount->customer->customer_number }}</div>
                                            </div>
                                        @endif

                                        <div>
                                            <div class="text-sm font-medium text-gray-500">Account Type</div>
                                            <div class="mt-1 text-lg font-semibold text-gray-900">
                                                {{ $destinationAccount->accountType->name ?? 'N/A' }}</div>
                                        </div>

                                        <div>
                                            <div class="text-sm font-medium text-gray-500">Current Balance</div>
                                            <div
                                                class="mt-1 text-2xl font-bold {{ $destinationAccount->current_balance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $destinationAccount->currency }}
                                                {{ number_format($destinationAccount->current_balance, 2) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Beneficiary -->
                            @if ($beneficiary)
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 lg:col-span-2">
                                    <div class="flex items-center mb-6">
                                        <div class="flex-shrink-0">
                                            <div
                                                class="h-12 w-12 rounded-lg bg-gradient-to-r from-blue-500 to-indigo-500 flex items-center justify-center">
                                                <i class="fas fa-user-tie text-white"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <h3 class="text-lg font-semibold text-gray-900">Beneficiary</h3>
                                            <p class="text-sm text-gray-600">External Recipient</p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <div>
                                            <div class="text-sm font-medium text-gray-500">Beneficiary Name</div>
                                            <div class="mt-1 text-lg font-semibold text-gray-900">
                                                {{ $beneficiary->full_name }}</div>
                                        </div>

                                        <div>
                                            <div class="text-sm font-medium text-gray-500">Account Number</div>
                                            <div class="mt-1 text-lg font-mono font-semibold text-gray-900">
                                                {{ $beneficiary->account_number }}</div>
                                        </div>

                                        <div>
                                            <div class="text-sm font-medium text-gray-500">Bank</div>
                                            <div class="mt-1 text-lg font-semibold text-gray-900">
                                                {{ $beneficiary->bank_name }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Ledger Entries Tab -->
                    @if ($activeTab === 'ledger')
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                                <h3 class="text-lg font-semibold text-gray-900">Double-Entry Ledger</h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    Complete accounting entries for this transaction
                                    @if ($this->ledgerEntries?->isNotEmpty())
                                        <span class="text-blue-600 font-medium">({{ $this->ledgerEntries->count() }}
                                            entries
                                            found)</span>
                                    @endif
                                </p>
                            </div>

                            <div class="divide-y divide-gray-200">
                                @forelse ($this->ledgerEntries as $entry)
                                    <div class="px-6 py-5 hover:bg-gray-50 transition-colors duration-150">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0">
                                                    <div
                                                        class="h-12 w-12 rounded-full flex items-center justify-center 
                                        {{ $entry->entry_type === 'debit' ? 'bg-gradient-to-r from-red-100 to-pink-100' : 'bg-gradient-to-r from-green-100 to-emerald-100' }}">
                                                        <i
                                                            class="{{ $entry->entry_type === 'debit' ? 'fas fa-arrow-up text-red-600' : 'fas fa-arrow-down text-green-600' }} text-lg"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="flex items-center">
                                                        <div class="text-lg font-semibold text-gray-900">
                                                            {{ $entry->account->account_number ?? 'N/A' }}
                                                        </div>
                                                        <span
                                                            class="ml-3 inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold 
                                            {{ $entry->entry_type === 'debit' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                                            {{ strtoupper($entry->entry_type) }}
                                                        </span>
                                                    </div>
                                                    <div class="mt-1 text-sm text-gray-600">
                                                        @if ($entry->account)
                                                            {{ $entry->account->accountType->name ?? 'N/A' }}
                                                            @if ($entry->account->customer)
                                                                • {{ $entry->account->customer->full_name }}
                                                            @endif
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="text-right">
                                                <div
                                                    class="text-2xl font-bold {{ $entry->entry_type === 'debit' ? 'text-red-600' : 'text-green-600' }}">
                                                    {{ $entry->currency }} {{ number_format($entry->amount, 2) }}
                                                </div>
                                                <div class="mt-1 text-sm text-gray-600">
                                                    Balance After: {{ number_format($entry->balance_after, 2) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-6 py-12 text-center">
                                        <div
                                            class="mx-auto h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                                            <i class="fas fa-book text-2xl text-gray-400"></i>
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Ledger Entries</h3>
                                        <p class="text-gray-600 mb-4">No ledger entries found for this transaction.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endif
                    <!-- Audit Trail Tab -->
                    @if ($activeTab === 'audit')
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                                <h3 class="text-lg font-semibold text-gray-900">Audit Trail</h3>
                                <p class="mt-1 text-sm text-gray-600">Complete history of actions performed on this
                                    transaction</p>
                            </div>

                            <div class="divide-y divide-gray-200">
                                @forelse($auditLogs as $log)
                                    <div class="px-6 py-5">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center">
                                                    <span
                                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ isset($log['action']) && $log['action'] === 'transaction_completed' ? 'bg-green-100 text-green-800' : (isset($log['action']) && $log['action'] === 'transaction_failed' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                                                        {{ isset($log['action']) ? str_replace('_', ' ', $log['action']) : 'Unknown Action' }}
                                                    </span>
                                                    <span class="ml-3 text-sm text-gray-500">
                                                        {{ isset($log['created_at']) ? \Carbon\Carbon::parse($log['created_at'])->diffForHumans() : 'N/A' }}
                                                    </span>
                                                </div>

                                                @if (isset($log['user']) && $log['user'])
                                                    <div class="mt-3 flex items-center">
                                                        <div
                                                            class="flex-shrink-0 h-8 w-8 rounded-full bg-gradient-to-r from-blue-100 to-indigo-100 flex items-center justify-center">
                                                            <span class="text-blue-600 font-medium text-sm">
                                                                {{ substr($log['user']['first_name'] ?? '', 0, 1) }}{{ substr($log['user']['last_name'] ?? '', 0, 1) }}
                                                            </span>
                                                        </div>
                                                        <div class="ml-3">
                                                            <div class="text-sm font-semibold text-gray-900">
                                                                {{ $log['user']['name'] ?? 'Unknown User' }}</div>
                                                            <div class="text-xs text-gray-500">
                                                                {{ $log['user']['email'] ?? '' }}</div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="text-right">
                                                <div class="text-xs text-gray-500">
                                                    IP: {{ $log['ip_address'] ?? 'N/A' }}
                                                </div>
                                                @if (isset($log['user_agent']) && $log['user_agent'])
                                                    <div class="mt-1 text-xs text-gray-500">
                                                        {{ Str::limit($log['user_agent'], 40) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-6 py-12 text-center">
                                        <div
                                            class="mx-auto h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                                            <i class="fas fa-history text-2xl text-gray-400"></i>
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Audit Logs</h3>
                                        <p class="text-gray-600">No audit logs found for this transaction.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endif
                </div>
            </main>
        </div>

        <!-- Receipt Modal -->
        @if ($showReceiptModal)
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
                aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div
                        class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                        <div class="bg-white px-8 pt-8 pb-6 sm:p-10">
                            <div class="flex justify-between items-start mb-8">
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900" id="modal-title">
                                        Transaction Receipt
                                    </h3>
                                    <p class="mt-1 text-gray-600">
                                        {{ $transaction->transaction_reference }}
                                    </p>
                                </div>
                                <button wire:click="$set('showReceiptModal', false)"
                                    class="text-gray-400 hover:text-gray-500 transition-colors duration-150">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                            </div>

                            <!-- Receipt Content -->
                            <div
                                class="border-2 border-gray-200 rounded-xl p-8 bg-gradient-to-br from-gray-50 to-white">
                                <!-- Add receipt content here -->
                                <div class="text-center mb-8">
                                    <div
                                        class="inline-block p-4 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 mb-4 shadow-lg">
                                        <i class="fas fa-university text-white text-4xl"></i>
                                    </div>
                                    <h2 class="text-2xl font-bold text-gray-900">Transaction Receipt</h2>
                                    <p class="text-gray-600">Official Bank Confirmation</p>
                                </div>

                                <div class="space-y-6">
                                    <!-- Receipt details would go here -->
                                    <div class="text-center py-8">
                                        <i class="fas fa-receipt text-4xl text-gray-300 mb-4"></i>
                                        <p class="text-gray-600">Receipt preview would appear here</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-8 py-6 sm:px-10 sm:flex sm:flex-row-reverse">
                            <button wire:click="downloadReceipt"
                                class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-base font-medium text-white hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                <i class="fas fa-download mr-2"></i>
                                Download PDF
                            </button>
                            <button wire:click="$set('showReceiptModal', false)"
                                class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-6 py-3 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Reverse Transaction Modal -->
        @if ($showReverseModal)
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
                                            <h4 class="text-sm font-medium text-gray-700 mb-2">Transaction Details:
                                            </h4>
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
                                                    {{ $transactionToReverse->initiated_at->format('M d, Y H:i') }}
                                                </div>
                                                @if ($transactionToReverse->description)
                                                    <div class="text-gray-600 col-span-2">Description:</div>
                                                    <div class="text-gray-900 col-span-2">
                                                        {{ $transactionToReverse->description }}</div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <label for="reverse_reason"
                                                class="block text-sm font-medium text-gray-700">
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

        <!-- Loading Overlay -->
        @if ($isProcessing)
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
                aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                    <div
                        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <i class="fas fa-spinner fa-spin text-blue-600"></i>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                        Processing Transaction
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            Please wait while we process your transaction. This may take a few moments.
                                        </p>
                                        <div class="mt-4">
                                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                <div class="bg-blue-600 h-2.5 rounded-full animate-pulse"
                                                    style="width: 80%"></div>
                                            </div>
                                        </div>
                                        <p class="mt-2 text-xs text-gray-500">
                                            Updating ledger, sending receipts, and recording audit trail...
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @else
        <!-- No Transaction Selected State -->
        <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center px-4">
            <div class="max-w-md w-full text-center">
                <div
                    class="mx-auto h-24 w-24 rounded-full bg-gradient-to-r from-blue-100 to-indigo-100 flex items-center justify-center mb-6 shadow-lg">
                    <i class="fas fa-exchange-alt text-3xl text-blue-600"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">No Transaction Selected</h2>
                <p class="text-gray-600 mb-8">
                    Please select a transaction from the list to view detailed information.
                </p>
                <div class="space-y-3">
                    <a href="{{ route('transactions.index') }}"
                        class="inline-flex items-center justify-center w-full px-6 py-3 border border-transparent rounded-xl text-base font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Transactions
                    </a>
                    <a href="{{ route('transactions.create') }}"
                        class="inline-flex items-center justify-center w-full px-6 py-3 border border-gray-300 rounded-xl text-base font-medium text-gray-700 bg-white hover:bg-gray-50 shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-plus mr-2"></i>
                        Create New Transaction
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Receipt Modal -->
    @if ($showReceiptModal && $transaction)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full">
                    <div class="bg-white px-8 pt-8 pb-6 sm:p-10">
                        <div class="flex justify-between items-start mb-8">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900" id="modal-title">
                                    Transaction Receipt
                                </h3>
                                <p class="mt-1 text-gray-600">
                                    Reference: {{ $transaction->transaction_reference }}
                                </p>
                            </div>
                            <div class="flex space-x-3">
                                <button onclick="printReceiptPreview()"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-150">
                                    <i class="fas fa-print mr-2"></i>
                                    Print
                                </button>
                                <button wire:click="$set('showReceiptModal', false)"
                                    class="text-gray-400 hover:text-gray-500 transition-colors duration-150">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Receipt Preview -->
                        <div class="border-2 border-gray-200 rounded-xl p-8 bg-gradient-to-br from-gray-50 to-white">
                            <!-- Receipt Header -->
                            <div class="flex justify-between items-center mb-8 pb-6 border-b border-gray-200">
                                <div>
                                    <div class="text-3xl font-bold text-blue-600 mb-2">
                                        {{ 'METCO Banking System' }}</div>
                                    <div class="text-gray-600">P. O. Box 2, Goaso - Ahafo Region</div>
                                    <div class="text-gray-600">Phone: +233 554389606 | Email: info.metco@gmail.com
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-gray-900">OFFICIAL RECEIPT</div>
                                    <div class="text-gray-600 mt-2">{{ now()->format('M d, Y') }}</div>
                                </div>
                            </div>

                            <!-- Status Banner -->
                            <div
                                class="mb-8 p-6 rounded-xl bg-gradient-to-r {{ $transaction->status === 'completed' ? 'from-green-500 to-emerald-500' : ($transaction->status === 'pending' ? 'from-yellow-500 to-amber-500' : 'from-red-500 to-pink-500') }} text-white">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="text-sm opacity-90 mb-1">Transaction Amount</div>
                                        <div class="text-4xl font-bold">{{ $transaction->currency }}
                                            {{ number_format($transaction->amount, 2) }}</div>
                                    </div>
                                    <div
                                        class="bg-white bg-opacity-20 px-4 py-2 rounded-full text-sm font-semibold uppercase">
                                        {{ ucfirst($transaction->status) }}
                                    </div>
                                </div>
                            </div>

                            <!-- Transaction Details -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                                <div class="space-y-4">
                                    <div>
                                        <div class="text-sm text-gray-500">Reference Number</div>
                                        <div class="text-lg font-mono font-semibold">
                                            {{ $transaction->transaction_reference }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-500">Transaction Type</div>
                                        <div class="text-lg font-semibold">{{ ucfirst($transaction->type) }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-500">Date & Time</div>
                                        <div class="text-lg font-semibold">
                                            {{ $transaction->created_at->format('F d, Y h:i A') }}</div>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <div class="text-sm text-gray-500">Description</div>
                                        <div class="text-lg font-semibold">{{ $transaction->description }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-500">Teller Name</div>
                                        <div class="text-lg font-semibold">
                                            {{ $transaction->initiator->first_name.' '.$transaction->initiator->last_name ?? 'System' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-500">Status</div>
                                        <div
                                            class="text-lg font-semibold {{ $transaction->status === 'completed' ? 'text-green-600' : 'text-yellow-600' }}">
                                            {{ ucfirst($transaction->status) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Account Details -->
                            @if ($sourceAccount || $destinationAccount)
                                <div class="mb-8">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Account Details</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        @if ($sourceAccount)
                                            <div class="border border-gray-200 rounded-lg p-4">
                                                <div class="flex items-center mb-3">
                                                    <div
                                                        class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center mr-3">
                                                        <i class="fas fa-arrow-up text-red-600"></i>
                                                    </div>
                                                    <div>
                                                        <div class="font-semibold text-gray-900">Debited Account</div>
                                                        <div class="text-sm text-gray-600">Source</div>
                                                    </div>
                                                </div>
                                                <div class="font-mono text-lg font-semibold mb-2">
                                                    {{ $sourceAccount->account_number }}</div>
                                                <div class="text-gray-700">
                                                    {{ $sourceAccount->customer->full_name ?? 'N/A' }}</div>
                                                <div class="text-sm text-gray-600 mt-2">Balance:
                                                    {{ number_format($sourceAccount->current_balance, 2) }}
                                                    {{ $sourceAccount->currency }}</div>
                                            </div>
                                        @endif

                                        @if ($destinationAccount)
                                            <div class="border border-gray-200 rounded-lg p-4">
                                                <div class="flex items-center mb-3">
                                                    <div
                                                        class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center mr-3">
                                                        <i class="fas fa-arrow-down text-green-600"></i>
                                                    </div>
                                                    <div>
                                                        <div class="font-semibold text-gray-900">Credited Account</div>
                                                        <div class="text-sm text-gray-600">Destination</div>
                                                    </div>
                                                </div>
                                                <div class="font-mono text-lg font-semibold mb-2">
                                                    {{ $destinationAccount->account_number }}</div>
                                                <div class="text-gray-700">
                                                    {{ $destinationAccount->customer->full_name ?? 'N/A' }}</div>
                                                <div class="text-sm text-gray-600 mt-2">Balance:
                                                    {{ number_format($destinationAccount->current_balance, 2) }}
                                                    {{ $destinationAccount->currency }}</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Amount Breakdown -->
                            <div class="mb-8">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4">Amount Breakdown</h4>
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <span class="text-gray-600">Amount</span>
                                        <span class="font-semibold">{{ $transaction->currency }}
                                            {{ number_format($transaction->amount, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <span class="text-gray-600">Amount in words</span>
                                        <span class="font-semibold">{{ App\Helpers\MoneyConverter::numberToWords($transaction->amount) }}</span>
                                    </div>
                                    @if ($transaction->fee_amount)
                                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                            <span class="text-gray-600">Fee Amount</span>
                                            <span
                                                class="text-red-600 font-semibold">-{{ number_format($transaction->fee_amount, 2) }}</span>
                                        </div>
                                        
                                    @endif
                                    @if ($transaction->tax_amount)
                                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                            <span class="text-gray-600">Tax Amount</span>
                                            <span
                                                class="text-red-600 font-semibold">-{{ number_format($transaction->tax_amount, 2) }}</span>
                                        </div>
                                    @endif
                                    <div
                                        class="flex justify-between items-center py-2 border-t border-gray-200 mt-4 pt-4">
                                        <span class="text-lg font-semibold text-gray-900">Net Amount</span>
                                        <span class="text-2xl font-bold text-blue-600">{{ $transaction->currency }}
                                            {{ number_format($transaction->net_amount ?? $transaction->amount, 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Security Features -->
                            <div class="bg-gray-50 rounded-lg p-6 mb-8">
                                <div class="flex items-center mb-4">
                                    <i class="fas fa-shield-alt text-blue-500 mr-3"></i>
                                    <div class="font-semibold text-gray-900">Security Features</div>
                                </div>
                                <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                                    <div>• Unique Reference: {{ $transaction->transaction_reference }}</div>
                                    <div>• Generated: {{ now()->format('Y-m-d H:i:s') }}</div>
                                    <div>• Document ID: {{ Str::random(16) }}</div>
                                    <div>• Digital Signature Verified</div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="border-t border-gray-200 pt-6 text-center text-gray-600 text-sm">
                                <div class="mb-2">This is an official document. Please retain for your records.</div>
                                <div>© {{ date('Y') }} {{ 'METCO Banking System' }}. All rights
                                    reserved.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="bg-gray-50 px-8 py-6 sm:px-10 sm:flex sm:flex-row-reverse">
                        <button wire:click="downloadReceipt" wire:loading.attr="disabled"
                            class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-base font-medium text-white hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-download mr-2"></i>
                            @if ($receiptLoading)
                                <i class="fas fa-spinner fa-spin mr-2"></i> Generating...
                            @else
                                Download PDF
                            @endif
                        </button>

                        <button wire:click="emailReceipt" wire:loading.attr="disabled"
                            class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-6 py-3 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-envelope mr-2"></i>
                            @if ($emailReceiptLoading)
                                <i class="fas fa-spinner fa-spin mr-2"></i> Sending...
                            @else
                                Email Receipt
                            @endif
                        </button>

                        <button wire:click="$set('showReceiptModal', false)"
                            class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-6 py-3 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@push('scripts')
    <script>
        // Smooth scroll for tabs
        document.addEventListener('livewire:load', function() {
            Livewire.on('tabChanged', () => {
                const element = document.querySelector('[data-tab-content]');
                if (element) {
                    element.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
@endpush
@push('scripts')
<script>
    // Print receipt preview
    function printReceiptPreview() {
        const receiptContent = document.querySelector('[data-receipt-content]');
        const printWindow = window.open('', '_blank');
        
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Receipt - {{ $transaction->transaction_reference ?? 'N/A' }}</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    .receipt { max-width: 800px; margin: 0 auto; }
                    .header { text-align: center; margin-bottom: 30px; }
                    .amount { font-size: 24px; font-weight: bold; text-align: center; margin: 20px 0; }
                    .details { margin: 20px 0; }
                    .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; }
                </style>
            </head>
            <body>
                <div class="receipt">
                    <div class="header">
                        <h2>{{ 'METCO Banking System' }}</h2>
                        <h3>Transaction Receipt</h3>
                        <p>Reference: {{ $transaction->transaction_reference ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="details">
                        <p><strong>Amount:</strong> {{ $transaction->currency ?? '' }} {{ number_format($transaction->amount ?? 0, 2) }}</p>
                        <p><strong>Amount in words:{{ ' '.App\Helpers\MoneyConverter::numberToWords($transaction->amount) }}</p>
                        <p><strong>Type:</strong> {{ ucfirst($transaction->type ?? '') }}</p>
                        <p><strong>Date:</strong> {{ $transaction->created_at ? $transaction->created_at->format('F d, Y h:i A') : 'N/A' }}</p>
                        <p><strong>Status:</strong> {{ ucfirst($transaction->status ?? '') }}</p>
                        <p><strong>Description:</strong> {{ $transaction->description ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="footer">
                        <p>Generated on {{ now()->format('F d, Y \a\t h:i A') }}</p>
                        <p>This is an official document. Please retain for your records.</p>
                    </div>
                </div>
            </body>
            </html>
        `);
        
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 500);
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + P to print receipt when modal is open
        if ((e.ctrlKey || e.metaKey) && e.key === 'p' && @json($showReceiptModal)) {
            e.preventDefault();
            printReceiptPreview();
        }
        
        // Escape to close modal
        if (e.key === 'Escape' && @json($showReceiptModal)) {
            @this.set('showReceiptModal', false);
        }
    });

    // Livewire hooks for receipt actions
    document.addEventListener('livewire:initialized', () => {
        // Show loading state for receipt actions
        Livewire.on('receipt-processing', () => {
            // Add loading overlay
            const modal = document.querySelector('[aria-modal="true"]');
            if (modal) {
                const loadingDiv = document.createElement('div');
                loadingDiv.className = 'fixed inset-0 bg-white bg-opacity-90 flex items-center justify-center z-50';
                loadingDiv.innerHTML = `
                    <div class="text-center">
                        <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500 mb-4"></div>
                        <div class="text-lg font-medium text-gray-900">Processing Receipt...</div>
                        <div class="text-gray-600">Please wait</div>
                    </div>
                `;
                modal.appendChild(loadingDiv);
            }
        });
    });
</script>
@endpush
