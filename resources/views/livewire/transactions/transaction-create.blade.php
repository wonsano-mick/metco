<div>
    <div class="max-w-6xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">
                            <i class="fas fa-university mr-2 text-blue-600"></i>
                            Banker Transaction Processing
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">
                            Process transactions for customers at the counter
                        </p>
                        <div class="flex items-center mt-2 text-sm text-gray-500">
                            <i class="fas fa-user-tie mr-1"></i>
                            <span class="font-medium">{{ ucwords(auth()->user()->role) . ':' }}</span>
                            <span
                                class="ml-1">{{ ucwords(auth()->user()->first_name . ' ' . auth()->user()->last_name) }}</span>
                            <span class="mx-2">•</span>
                            <i class="fas fa-building mr-1"></i>
                            <span class="font-medium">Branch:</span>
                            <span class="ml-1">{{ auth()->user()->branch->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <!-- Current Time -->
                        <div class="bg-white border border-gray-300 rounded-lg px-3 py-2">
                            <div class="text-sm font-medium text-gray-700">
                                <i class="far fa-clock mr-1"></i>
                                {{ now()->format('h:i A') }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ now()->format('F j, Y') }}
                            </div>
                        </div>

                        <!-- Progress Steps -->
                        <!-- Step Information Display -->
                        <div class="mt-4">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center">
                                            <span class="text-white font-bold">{{ $step }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-semibold text-blue-900">
                                            @switch($step)
                                                @case(1)
                                                    Step 1: Select Customer & Transaction Details
                                                @break

                                                @case(2)
                                                    Step 2: Transaction Initiator
                                                    ({{ $transactionInitiator === 'self' ? 'Account Holder' : 'Third Party' }})
                                                @break

                                                @case(3)
                                                    Step 3: Receipt & Confirmation
                                                @break

                                                @case(4)
                                                    Step 4: Final Review
                                                @break
                                            @endswitch
                                        </h3>
                                        <p class="text-sm text-blue-700">
                                            @switch($step)
                                                @case(1)
                                                    Select a customer and provide transaction details
                                                @break

                                                @case(2)
                                                    Verify who is initiating this transaction
                                                @break

                                                @case(3)
                                                    Set receipt options and finalize details
                                                @break

                                                @case(4)
                                                    Review all transaction details before submission
                                                @break
                                            @endswitch
                                        </p>
                                    </div>
                                </div>

                                <!-- Progress Indicator -->
                                <div class="mt-4">
                                    <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
                                        <span>Progress</span>
                                        <span>{{ $step }} of {{ $totalSteps }} steps</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                                            style="width: {{ ($step / $totalSteps) * 100 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Content -->
            <div class="p-6">
                @if ($showConfirmation && $transactionPreview)
                    <!-- Confirmation Step -->
                    <div class="space-y-6">
                        <!-- Transaction Summary -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
                                <i class="fas fa-receipt mr-2"></i>
                                Transaction Summary & Receipt Preview
                            </h3>
                            <div class="space-y-6">
                                <!-- Customer Information -->
                                <div class="bg-white rounded-lg border border-gray-200 p-4">
                                    <h4 class="font-medium text-gray-900 mb-3">Customer Information</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <p class="text-sm text-gray-600">Customer Name</p>
                                            <p class="font-medium">{{ $transactionPreview['customer']['name'] }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Customer Number</p>
                                            <p class="font-medium">{{ $transactionPreview['customer']['number'] }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">ID Number</p>
                                            <p class="font-medium">{{ $transactionPreview['customer']['id'] ?? 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Transaction Details -->
                                <div class="bg-white rounded-lg border border-gray-200 p-4">
                                    <h4 class="font-medium text-gray-900 mb-3">Transaction Details</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-gray-600">Transaction Type</p>
                                            <p class="font-medium">{{ $transactionPreview['type_display'] }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Transaction Purpose</p>
                                            <p class="font-medium">
                                                {{ ucfirst(str_replace('_', ' ', $transactionPreview['purpose'])) }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Amount</p>
                                            <p class="font-medium text-lg">
                                                {{ $transactionPreview['currency'] }}
                                                {{ $transactionPreview['amount'] }}
                                                @if ($transactionPreview['foreign_amount'])
                                                    <span class="text-sm text-gray-500">
                                                        (≈ {{ $transactionPreview['foreign_amount'] }}
                                                        {{ $transactionPreview['currency'] }})
                                                    </span>
                                                @endif
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Description</p>
                                            <p class="font-medium">{{ $transactionPreview['description'] }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Account Information -->
                                <div class="bg-white rounded-lg border border-gray-200 p-4">
                                    <h4 class="font-medium text-gray-900 mb-3">Account Information</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Source Account -->
                                        <div class="border-r border-gray-200 pr-6">
                                            <div class="flex items-center mb-2">
                                                <div class="bg-blue-100 p-2 rounded-lg">
                                                    <i class="fas fa-wallet text-blue-600"></i>
                                                </div>
                                                <div class="ml-3">
                                                    <p class="font-medium">From Account</p>
                                                    <p class="text-sm text-gray-600">
                                                        {{ $transactionPreview['source_account']['number'] }}</p>
                                                </div>
                                            </div>
                                            <div class="ml-11 space-y-1">
                                                <p class="text-sm text-gray-600">
                                                    Account Type: {{ $transactionPreview['source_account']['name'] }}
                                                </p>
                                                <div class="flex justify-between text-sm">
                                                    <span class="text-gray-600">Balance Before:</span>
                                                    <span
                                                        class="font-medium">{{ $transactionPreview['source_account']['balance_before'] }}</span>
                                                </div>
                                                <div class="flex justify-between text-sm">
                                                    <span class="text-gray-600">Balance After:</span>
                                                    <span
                                                        class="font-medium text-green-600">{{ $transactionPreview['source_account']['balance_after'] }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Destination Account/Beneficiary -->
                                        @if ($transactionPreview['destination_account'] || $transactionPreview['beneficiary'])
                                            <div>
                                                <div class="flex items-center mb-2">
                                                    <div class="bg-green-100 p-2 rounded-lg">
                                                        <i class="fas fa-user text-green-600"></i>
                                                    </div>
                                                    <div class="ml-3">
                                                        <p class="font-medium">
                                                            @if ($transactionPreview['destination_account'])
                                                                To Account
                                                            @else
                                                                Beneficiary
                                                            @endif
                                                        </p>
                                                        @if ($transactionPreview['destination_account'])
                                                            <p class="text-sm text-gray-600">
                                                                {{ $transactionPreview['destination_account']['number'] }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="ml-11 space-y-1">
                                                    @if ($transactionPreview['destination_account'])
                                                        <p class="text-sm text-gray-600">
                                                            Account Holder:
                                                            {{ $transactionPreview['destination_account']['customer'] }}
                                                        </p>
                                                        <p class="text-sm text-gray-600">
                                                            Account Type:
                                                            {{ $transactionPreview['destination_account']['name'] }}
                                                        </p>
                                                    @elseif($transactionPreview['beneficiary'])
                                                        <p class="text-sm text-gray-600">
                                                            Name: {{ $transactionPreview['beneficiary']['name'] }}
                                                        </p>
                                                        <p class="text-sm text-gray-600">
                                                            Account:
                                                            {{ $transactionPreview['beneficiary']['account'] }}
                                                        </p>
                                                        <p class="text-sm text-gray-600">
                                                            Bank: {{ $transactionPreview['beneficiary']['bank'] }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Cash Denominations (for cash transactions) -->
                                @if (in_array($transactionType, ['withdrawal', 'cash_deposit']) && $cashHandlingMethod === 'cash')
                                    <div class="bg-white rounded-lg border border-gray-200 p-4">
                                        <h4 class="font-medium text-gray-900 mb-3">Cash Denominations</h4>
                                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                                            @foreach ($cashDenominations as $denomination)
                                                @if ($denomination['count'] > 0)
                                                    <div
                                                        class="bg-gray-50 border border-gray-200 rounded-md p-3 text-center">
                                                        <div class="text-lg font-bold text-gray-900">
                                                            {{ number_format($denomination['denomination'], 2) }}
                                                        </div>
                                                        <div class="text-sm text-gray-600 mt-1">
                                                            × {{ $denomination['count'] }}
                                                        </div>
                                                        <div class="text-sm font-medium text-gray-900 mt-1">
                                                            {{ number_format($denomination['denomination'] * $denomination['count'], 2) }}
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                        <div class="mt-4 pt-4 border-t border-gray-200">
                                            <div class="flex justify-between items-center">
                                                <span class="text-lg font-semibold text-gray-900">Total Cash:</span>
                                                <span class="text-2xl font-bold text-blue-600">
                                                    {{ $currency }} {{ $this->getTotalCashCount() }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Verification Information -->
                                <div class="bg-white rounded-lg border border-gray-200 p-4">
                                    <h4 class="font-medium text-gray-900 mb-3">Verification & Processing</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-gray-600">Teller</p>
                                            <p class="font-medium">{{ $transactionPreview['teller'] }}</p>
                                        </div>
                                        @if ($transactionPreview['supervisor'])
                                            <div>
                                                <p class="text-sm text-gray-600">Supervisor Approval</p>
                                                <p class="font-medium text-green-600">
                                                    {{ $transactionPreview['supervisor'] }}</p>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="text-sm text-gray-600">Verification Method</p>
                                            <p class="font-medium capitalize">
                                                {{ $transactionPreview['verification']['method'] }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Customer Signature</p>
                                            <p
                                                class="font-medium {{ $transactionPreview['verification']['signature'] ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $transactionPreview['verification']['signature'] ? 'Verified' : 'Pending' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Receipt Options -->
                                <div class="bg-white rounded-lg border border-gray-200 p-4">
                                    <h4 class="font-medium text-gray-900 mb-3">Receipt Delivery</h4>
                                    <div class="flex flex-wrap gap-4">
                                        @if ($transactionPreview['receipt_options']['print'])
                                            <div class="flex items-center">
                                                <i class="fas fa-print text-blue-600 mr-2"></i>
                                                <span class="text-sm text-gray-700">Printed Receipt</span>
                                            </div>
                                        @endif
                                        @if ($transactionPreview['receipt_options']['email'])
                                            <div class="flex items-center">
                                                <i class="fas fa-envelope text-blue-600 mr-2"></i>
                                                <span class="text-sm text-gray-700">Email to:
                                                    {{ $customerEmail }}</span>
                                            </div>
                                        @endif
                                        @if ($transactionPreview['receipt_options']['sms'])
                                            <div class="flex items-center">
                                                <i class="fas fa-sms text-blue-600 mr-2"></i>
                                                <span class="text-sm text-gray-700">SMS to:
                                                    {{ $customerPhone }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Security & Authorization -->
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-shield-alt text-gray-600 mr-2"></i>
                                Final Authorization
                            </h3>

                            <!-- Supervisor Approval (if required) -->
                            @if ($supervisorApproval)
                                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                                    <h4 class="text-sm font-medium text-yellow-800 mb-2">Supervisor Approval Required
                                    </h4>
                                    <div class="space-y-3">
                                        <div>
                                            <label for="supervisorPassword"
                                                class="block text-sm font-medium text-gray-700">
                                                Supervisor Password <span class="text-red-500">*</span>
                                            </label>
                                            <div class="mt-1 relative">
                                                <input type="password" wire:model.live="supervisorPassword"
                                                    id="supervisorPassword"
                                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                                    placeholder="Enter supervisor password">
                                                @error('supervisorPassword')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <p class="mt-1 text-sm text-gray-500">
                                                This transaction requires supervisor approval. Please have the
                                                supervisor enter their password.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Final Confirmation -->
                            <div class="border-t border-gray-200 pt-6">
                                <div class="flex items-center">
                                    <input type="checkbox" id="finalConfirmation"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="finalConfirmation" class="ml-2 block text-sm text-gray-900">
                                        I confirm that all transaction details are correct, customer identity has been
                                        verified,
                                        and I have provided the correct amount to/from the customer.
                                    </label>
                                </div>
                                <p class="mt-4 text-sm text-red-600">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    Once confirmed, this transaction cannot be reversed without proper authorization.
                                </p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-between pt-6">
                            <button type="button" wire:click="cancelTransaction" wire:loading.attr="disabled"
                                class="px-6 py-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Back to Edit
                            </button>

                            <div class="flex space-x-3">
                                <button type="button" onclick="window.print()"
                                    class="px-6 py-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="fas fa-print mr-2"></i>
                                    Print Preview
                                </button>

                                <button type="button" wire:click="confirmTransaction" wire:loading.attr="disabled"
                                    wire:target="confirmTransaction"
                                    class="px-6 py-3 bg-green-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50">
                                    <span wire:loading.remove wire:target="confirmTransaction">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        Confirm & Complete Transaction
                                    </span>
                                    <span wire:loading wire:target="confirmTransaction">
                                        <i class="fas fa-spinner fa-spin mr-2"></i>
                                        Processing...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Multi-step Form -->
                    <form wire:submit.prevent="nextStep">
                        <!-- Step 1: Customer and Transaction Selection -->
                        @if ($step === 1)
                            <div class="space-y-6">
                                <!-- Customer Selection -->
                                <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                                    <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
                                        <i class="fas fa-user mr-2"></i>
                                        1. Select Customer
                                    </h3>
                                    <div>
                                        <label for="customerSearch" class="block text-sm font-medium text-gray-700">
                                            Search Customer <span class="text-red-500">*</span>
                                            <span class="text-xs text-gray-500 ml-2">Search by name, customer number,
                                                phone, email, or ID</span>
                                        </label>

                                        <!-- Search Input with Loading Indicator -->
                                        <div class="mt-1 relative">
                                            <div class="flex">
                                                <input type="text" wire:model.live.debounce.300ms="customerSearch"
                                                    wire:keydown.escape="clearCustomerSelection" id="customerSearch"
                                                    class="flex-1 block w-full pl-10 pr-10 py-3 text-base border-gray-300 rounded-l-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                    placeholder="Start typing to search customers..."
                                                    autocomplete="off"
                                                    @if ($customerId) disabled @endif>

                                                @if ($customerId)
                                                    <button type="button" wire:click="clearCustomerSelection"
                                                        class="inline-flex items-center px-4 py-3 border border-l-0 border-gray-300 bg-red-50 text-red-700 rounded-r-md hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500">
                                                        <i class="fas fa-times mr-2"></i>
                                                        Clear
                                                    </button>
                                                @else
                                                    <div
                                                        class="inline-flex items-center px-4 py-3 border border-l-0 border-gray-300 bg-gray-50 text-gray-500 rounded-r-md">
                                                        @if ($isSearching)
                                                            <i class="fas fa-spinner fa-spin mr-2"></i>
                                                            Searching...
                                                        @else
                                                            <i class="fas fa-search"></i>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Search Icon -->
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-search text-gray-400"></i>
                                            </div>

                                            <!-- Clear Search Button (when typing) -->
                                            @if ($customerSearch && !$customerId)
                                                <button type="button" wire:click="$set('customerSearch', '')"
                                                    class="absolute inset-y-0 right-12 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @endif

                                            @error('customerId')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <!-- Search Results Dropdown -->
                                        @if ($showSearchResults && !$customerId)
                                            <div
                                                class="mt-1 absolute z-50 w-100 bg-white shadow-lg max-h-96 overflow-y-auto rounded-lg border border-gray-300 search-results-container">
                                                <ul class="divide-y divide-gray-200">
                                                    @forelse($searchResults as $customer)
                                                        <li wire:key="customer-{{ $customer->id }}">
                                                            <button type="button"
                                                                wire:click="selectCustomer({{ $customer->id }})"
                                                                class="w-full text-left px-4 py-3 focus:outline-none focus:bg-blue-50">
                                                                <div class="flex items-center">
                                                                    <div class="flex-shrink-0">
                                                                        @if ($customer->profile_photo_url)
                                                                            <img class="h-10 w-10 rounded-full"
                                                                                src="{{ $customer->profile_photo_url }}"
                                                                                alt="{{ $customer->full_name }}">
                                                                        @else
                                                                            <div
                                                                                class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                                                <span
                                                                                    class="text-blue-600 font-medium">
                                                                                    {{ substr($customer->first_name, 0, 1) }}{{ substr($customer->last_name, 0, 1) }}
                                                                                </span>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                    <div class="ml-4 flex-1">
                                                                        <div class="flex justify-between items-start">
                                                                            <div>
                                                                                <div
                                                                                    class="text-sm font-medium text-gray-900">
                                                                                    {{ $customer->full_name }}</div>
                                                                                <div class="text-sm text-gray-500">
                                                                                    #{{ $customer->customer_number }}
                                                                                    @if ($customer->id_number)
                                                                                        • ID:
                                                                                        {{ $customer->id_number }}
                                                                                    @endif
                                                                                </div>
                                                                                <div
                                                                                    class="text-xs text-gray-400 mt-1">
                                                                                    {{ $customer->email }} |
                                                                                    {{ $customer->phone }}
                                                                                </div>
                                                                            </div>
                                                                            <div class="text-right">
                                                                                <span
                                                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                                                    <i class="fas fa-wallet mr-1"></i>
                                                                                    {{ $customer->accounts->count() }}
                                                                                    account(s)
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                        @if ($customer->accounts->count() > 0)
                                                                            <div
                                                                                class="mt-2 pt-2 border-t border-gray-100">
                                                                                <div
                                                                                    class="text-xs text-gray-600 font-medium mb-1">
                                                                                    Accounts:</div>
                                                                                <div class="flex flex-wrap gap-1">
                                                                                    @foreach ($customer->accounts->take(3) as $account)
                                                                                        <span
                                                                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                                                            {{ $account->account_number }}
                                                                                            ({{ $account->accountType->name ?? 'N/A' }})
                                                                                        </span>
                                                                                    @endforeach
                                                                                    @if ($customer->accounts->count() > 3)
                                                                                        <span
                                                                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                                                            +{{ $customer->accounts->count() - 3 }}
                                                                                            more
                                                                                        </span>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </button>
                                                        </li>
                                                    @empty
                                                        <li class="px-4 py-3 text-sm text-gray-500 text-center">
                                                            <i class="fas fa-user-slash mr-2"></i>
                                                            No customers found matching "{{ $customerSearch }}"
                                                        </li>
                                                    @endforelse
                                                </ul>
                                            </div>
                                        @endif

                                        <!-- Selected Customer Display -->
                                        @if ($selectedCustomer)
                                            <div
                                                class="mt-3 bg-white rounded-md border border-green-200 p-4 shadow-sm relative">
                                                <div class="flex items-start justify-between">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0">
                                                            @if ($selectedCustomer['profile_photo_url'])
                                                                <img class="h-12 w-12 rounded-full"
                                                                    src="{{ $selectedCustomer['profile_photo_url'] }}"
                                                                    alt="">
                                                            @else
                                                                <div
                                                                    class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                                                                    <span class="text-blue-600 font-medium text-lg">
                                                                        {{ substr($selectedCustomer['full_name'], 0, 1) }}{{ substr($selectedCustomer['full_name'], 0, 1) }}
                                                                    </span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="flex items-center">
                                                                <div class="text-lg font-medium text-gray-900">
                                                                    {{ $selectedCustomer['full_name'] }}</div>
                                                                <span
                                                                    class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                    <i class="fas fa-shield-alt mr-1"></i>
                                                                    KYC Verified
                                                                </span>
                                                            </div>
                                                            <div class="text-sm text-gray-600 mt-1">
                                                                Customer #{{ $selectedCustomer['customer_number'] }}
                                                                @if ($selectedCustomer['id_number'])
                                                                    • ID: {{ $selectedCustomer['id_number'] }}
                                                                @endif
                                                            </div>
                                                            <div class="text-xs text-gray-400 mt-1">
                                                                <i
                                                                    class="fas fa-envelope mr-1"></i>{{ $selectedCustomer['email'] }}
                                                                <i
                                                                    class="fas fa-phone ml-3 mr-1"></i>{{ $selectedCustomer['phone'] }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="button" wire:click="clearCustomerSelection"
                                                        class="text-gray-400 hover:text-red-600 transition-colors duration-150"
                                                        title="Clear customer selection">
                                                        <i class="fas fa-times text-lg"></i>
                                                    </button>
                                                </div>

                                                <!-- Customer Accounts Summary -->
                                                @if (!empty($selectedCustomer['accounts']) && count($selectedCustomer['accounts']) > 0)
                                                    <div class="mt-4 pt-4 border-t border-gray-200">
                                                        <div class="flex items-center justify-between mb-2">
                                                            <h4 class="text-sm font-medium text-gray-700">
                                                                <i class="fas fa-wallet mr-1"></i>
                                                                Customer Accounts
                                                                ({{ count($selectedCustomer['accounts']) }})
                                                            </h4>
                                                            <span class="text-xs text-gray-500">
                                                                Total Balance:
                                                                <span class="font-semibold">
                                                                    {{ number_format(array_sum(array_column($selectedCustomer['accounts'], 'current_balance')), 2) }}
                                                                    {{ $selectedCustomer['accounts'][0]['currency'] ?? 'USD' }}
                                                                </span>
                                                            </span>
                                                        </div>

                                                        <div
                                                            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                            @foreach ($customerAccounts as $account)
                                                                <div class="bg-gray-50 border border-gray-200 rounded-md p-3 hover:bg-blue-50 hover:border-blue-200 transition-colors duration-150 cursor-pointer"
                                                                    wire:click="$set('sourceAccountId', {{ $account->id }})"
                                                                    :class="{{ $sourceAccountId == $account->id ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-100' : '' }}">
                                                                    <div class="flex justify-between items-start">
                                                                        <div>
                                                                            <div
                                                                                class="text-sm font-medium text-gray-900">
                                                                                {{ $account->account_number }}
                                                                            </div>
                                                                            <div class="text-xs text-gray-600">
                                                                                {{ $account->accountType->name ?? 'N/A' }}
                                                                            </div>
                                                                        </div>
                                                                        @if ($sourceAccountId == $account->id)
                                                                            <i
                                                                                class="fas fa-check-circle text-green-500"></i>
                                                                        @endif
                                                                    </div>
                                                                    <div class="mt-2">
                                                                        <div class="text-xs text-gray-500">Current
                                                                            Balance</div>
                                                                        <div
                                                                            class="text-lg font-semibold {{ $account->current_balance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                                            {{ number_format($account->current_balance, 2) }}
                                                                            {{ $account->currency }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="mt-1 text-xs text-gray-500">
                                                                        Available:
                                                                        {{ number_format($account->available_balance, 2) }}
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>

                                                        <!-- Quick Stats -->
                                                        <div
                                                            class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                                                            <div class="bg-blue-50 p-2 rounded text-center">
                                                                <div class="text-blue-700 font-medium">
                                                                    {{ $customerAccounts->where('status', 'active')->count() }}
                                                                </div>
                                                                <div class="text-blue-600">Active</div>
                                                            </div>
                                                            <div class="bg-green-50 p-2 rounded text-center">
                                                                <div class="text-green-700 font-medium">
                                                                    {{ $customerAccounts->where('current_balance', '>', 0)->count() }}
                                                                </div>
                                                                <div class="text-green-600">Positive Balance</div>
                                                            </div>
                                                            <div class="bg-yellow-50 p-2 rounded text-center">
                                                                <div class="text-yellow-700 font-medium">
                                                                    {{ $customerAccounts->where('current_balance', '<', 0)->count() }}
                                                                </div>
                                                                <div class="text-yellow-600">Negative Balance</div>
                                                            </div>
                                                            <div class="bg-purple-50 p-2 rounded text-center">
                                                                <div class="text-purple-700 font-medium">
                                                                    {{ $beneficiaries->count() }}</div>
                                                                <div class="text-purple-600">Saved Beneficiaries
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div
                                                        class="mt-4 bg-yellow-50 border border-yellow-200 rounded-md p-4">
                                                        <div class="flex">
                                                            <div class="flex-shrink-0">
                                                                <i
                                                                    class="fas fa-exclamation-triangle text-yellow-500"></i>
                                                            </div>
                                                            <div class="ml-3">
                                                                <h4 class="text-sm font-medium text-yellow-800">No
                                                                    Active Accounts</h4>
                                                                <div class="mt-1 text-sm text-yellow-700">
                                                                    <p>This customer has no active accounts. Please:
                                                                    </p>
                                                                    <ul class="list-disc pl-5 mt-1 space-y-1">
                                                                        <li>Check if the customer has closed all
                                                                            accounts</li>
                                                                        <li>Verify if accounts are frozen or
                                                                            suspended</li>
                                                                        <li>Create a new account for the customer
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                                <div class="mt-3">
                                                                    <a href="{{ route('accounts.create', ['customer_id' => $customerId]) }}"
                                                                        class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                                        <i class="fas fa-plus-circle mr-1"></i>
                                                                        Create New Account
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        <!-- Validation Error Message -->
                                        @if (!$customerId && $step >= 2)
                                            <div class="mt-2 bg-red-50 border border-red-200 rounded-md p-3">
                                                <div class="flex">
                                                    <div class="flex-shrink-0">
                                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                                    </div>
                                                    <div class="ml-3">
                                                        <p class="text-sm text-red-700">
                                                            Please select a customer to continue with the transaction.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Transaction Type Selection -->
                                <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                                    <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
                                        <i class="fas fa-exchange-alt mr-2"></i>
                                        2. Transaction Details
                                    </h3>

                                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
                                        <button type="button" wire:click="$set('transactionType', 'transfer')"
                                            class="p-4 border rounded-lg flex flex-col items-center justify-center transition-all duration-200
                                                {{ $transactionType === 'transfer' ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' : 'border-gray-300 hover:border-blue-300 hover:bg-blue-50' }}">
                                            <div
                                                class="w-10 h-10 rounded-full flex items-center justify-center mb-2
                                                {{ $transactionType === 'transfer' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600' }}">
                                                <i class="fas fa-exchange-alt"></i>
                                            </div>
                                            <span class="text-sm font-medium">Transfer</span>
                                        </button>

                                        <button type="button" wire:click="$set('transactionType', 'withdrawal')"
                                            class="p-4 border rounded-lg flex flex-col items-center justify-center transition-all duration-200
                                                {{ $transactionType === 'withdrawal' ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' : 'border-gray-300 hover:border-blue-300 hover:bg-blue-50' }}">
                                            <div
                                                class="w-10 h-10 rounded-full flex items-center justify-center mb-2
                                                {{ $transactionType === 'withdrawal' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600' }}">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </div>
                                            <span class="text-sm font-medium">Withdrawal</span>
                                        </button>

                                        <button type="button" wire:click="$set('transactionType', 'cash_deposit')"
                                            class="p-4 border rounded-lg flex flex-col items-center justify-center transition-all duration-200
                                                {{ $transactionType === 'cash_deposit' ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' : 'border-gray-300 hover:border-blue-300 hover:bg-blue-50' }}">
                                            <div
                                                class="w-10 h-10 rounded-full flex items-center justify-center mb-2
                                                {{ $transactionType === 'cash_deposit' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600' }}">
                                                <i class="fas fa-money-check"></i>
                                            </div>
                                            <span class="text-sm font-medium">Cash Deposit</span>
                                        </button>

                                        <button type="button" wire:click="$set('transactionType', 'cheque_deposit')"
                                            class="p-4 border rounded-lg flex flex-col items-center justify-center transition-all duration-200
                                                {{ $transactionType === 'cheque_deposit' ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' : 'border-gray-300 hover:border-blue-300 hover:bg-blue-50' }}">
                                            <div
                                                class="w-10 h-10 rounded-full flex items-center justify-center mb-2
                                                {{ $transactionType === 'cheque_deposit' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600' }}">
                                                <i class="fas fa-file-invoice"></i>
                                            </div>
                                            <span class="text-sm font-medium">Cheque Deposit</span>
                                        </button>

                                        <button type="button" wire:click="$set('transactionType', 'bill_payment')"
                                            class="p-4 border rounded-lg flex flex-col items-center justify-center transition-all duration-200
                                                {{ $transactionType === 'bill_payment' ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' : 'border-gray-300 hover:border-blue-300 hover:bg-blue-50' }}">
                                            <div
                                                class="w-10 h-10 rounded-full flex items-center justify-center mb-2
                                                {{ $transactionType === 'bill_payment' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600' }}">
                                                <i class="fas fa-file-invoice-dollar"></i>
                                            </div>
                                            <span class="text-sm font-medium">Bill Payment</span>
                                        </button>

                                        <button type="button" wire:click="$set('transactionType', 'loan_payment')"
                                            class="p-4 border rounded-lg flex flex-col items-center justify-center transition-all duration-200
                                                {{ $transactionType === 'loan_payment' ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' : 'border-gray-300 hover:border-blue-300 hover:bg-blue-50' }}">
                                            <div
                                                class="w-10 h-10 rounded-full flex items-center justify-center mb-2
                                                {{ $transactionType === 'loan_payment' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600' }}">
                                                <i class="fas fa-hand-holding-usd"></i>
                                            </div>
                                            <span class="text-sm font-medium">Loan Payment</span>
                                        </button>

                                        <button type="button" wire:click="$set('transactionType', 'fee_collection')"
                                            class="p-4 border rounded-lg flex flex-col items-center justify-center transition-all duration-200
                                                {{ $transactionType === 'fee_collection' ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' : 'border-gray-300 hover:border-blue-300 hover:bg-blue-50' }}">
                                            <div
                                                class="w-10 h-10 rounded-full flex items-center justify-center mb-2
                                                {{ $transactionType === 'fee_collection' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600' }}">
                                                <i class="fas fa-credit-card"></i>
                                            </div>
                                            <span class="text-sm font-medium">Fee Collection</span>
                                        </button>

                                        <button type="button" wire:click="$set('transactionType', 'adjustment')"
                                            class="p-4 border rounded-lg flex flex-col items-center justify-center transition-all duration-200
                                                {{ $transactionType === 'adjustment' ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' : 'border-gray-300 hover:border-blue-300 hover:bg-blue-50' }}">
                                            <div
                                                class="w-10 h-10 rounded-full flex items-center justify-center mb-2
                                                {{ $transactionType === 'adjustment' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600' }}">
                                                <i class="fas fa-adjust"></i>
                                            </div>
                                            <span class="text-sm font-medium">Adjustment</span>
                                        </button>
                                    </div>
                                    @error('transactionType')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror

                                    <!-- Source Account Selection -->
                                    @if ($customerId && $customerAccounts->count() > 0)
                                        <div class="mt-4">
                                            <label for="sourceAccountId"
                                                class="block text-sm font-medium text-gray-700">
                                                Source Account <span class="text-red-500">*</span>
                                                <span class="text-xs text-gray-500 ml-2">Select the account to debit
                                                    from</span>
                                            </label>

                                            <div class="mt-2 space-y-3">
                                                @foreach ($customerAccounts as $account)
                                                    <div class="relative">
                                                        <input type="radio" wire:model.live="sourceAccountId"
                                                            value="{{ $account->id }}"
                                                            id="account_{{ $account->id }}" class="sr-only">
                                                        <label for="account_{{ $account->id }}"
                                                            class="cursor-pointer block p-4 border rounded-lg transition-all duration-200{{ $sourceAccountId == $account->id ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' : 'border-gray-300 hover:border-blue-300 hover:bg-blue-50' }}">
                                                            <div class="flex items-start justify-between">
                                                                <div class="flex-1">
                                                                    <div class="flex items-center">
                                                                        <div class="flex-shrink-0">
                                                                            <div
                                                                                class="h-10 w-10 rounded-full flex items-center justify-center {{ $sourceAccountId == $account->id ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600' }}">
                                                                                <i class="fas fa-wallet"></i>
                                                                            </div>
                                                                        </div>
                                                                        <div class="ml-4">
                                                                            <div
                                                                                class="text-sm font-medium text-gray-900">
                                                                                {{ $account->account_number }}
                                                                                <span
                                                                                    class="ml-2 text-xs font-normal text-gray-500">
                                                                                    {{ $account->accountType->name ?? 'N/A' }}
                                                                                </span>
                                                                            </div>
                                                                            <div class="mt-1 text-sm text-gray-600">
                                                                                {{ $selectedCustomer->full_name ?? 'Customer' }}
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div
                                                                        class="mt-4 grid grid-cols-2 md:grid-cols-3 gap-4">
                                                                        <div>
                                                                            <div class="text-xs text-gray-500">Current
                                                                                Balance</div>
                                                                            <div
                                                                                class="text-lg font-semibold {{ $account->current_balance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                                                {{ number_format($account->current_balance, 2) }}
                                                                                {{ $account->currency }}
                                                                            </div>
                                                                        </div>
                                                                        <div>
                                                                            <div class="text-xs text-gray-500">
                                                                                Available Balance</div>
                                                                            <div
                                                                                class="text-lg font-semibold {{ $account->available_balance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                                                {{ number_format($account->available_balance, 2) }}
                                                                                {{ $account->currency }}
                                                                            </div>
                                                                        </div>
                                                                        <div>
                                                                            <div class="text-xs text-gray-500">Status
                                                                            </div>
                                                                            <div class="mt-1">
                                                                                <span
                                                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $account->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $account->status === 'frozen' ? 'bg-red-100 text-red-800' : '' }}
                                                {{ $account->status === 'closed' ? 'bg-gray-100 text-gray-800' : '' }}">
                                                                                    {{ ucfirst($account->status) }}
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Account Details -->
                                                                    <div class="mt-3 pt-3 border-t border-gray-200">
                                                                        <div
                                                                            class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                                                                            <div class="text-gray-600">Opened:</div>
                                                                            <div class="text-gray-900">
                                                                                {{ $account->opened_at ? $account->opened_at->format('M d, Y') : 'N/A' }}
                                                                            </div>
                                                                            <div class="text-gray-600">Overdraft Limit:
                                                                            </div>
                                                                            <div class="text-gray-900">
                                                                                {{ number_format($account->overdraft_limit, 2) }}
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- Selection Indicator -->
                                                                <div class="ml-4 flex-shrink-0">
                                                                    @if ($sourceAccountId == $account->id)
                                                                        <div
                                                                            class="h-6 w-6 rounded-full bg-blue-600 flex items-center justify-center">
                                                                            <i
                                                                                class="fas fa-check text-white text-xs"></i>
                                                                        </div>
                                                                    @else
                                                                        <div
                                                                            class="h-6 w-6 rounded-full border-2 border-gray-300">
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>

                                            @error('sourceAccountId')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror

                                            <!-- Selected Account Details -->
                                            @if ($sourceAccountId)
                                                @php
                                                    $selectedAccount = $customerAccounts->firstWhere(
                                                        'id',
                                                        $sourceAccountId,
                                                    );
                                                @endphp
                                                @if ($selectedAccount)
                                                    <div class="mt-4 bg-white rounded-lg border border-blue-200 p-4">
                                                        <div class="flex items-center justify-between mb-3">
                                                            <h4 class="text-sm font-medium text-blue-900">
                                                                <i class="fas fa-info-circle mr-1"></i>
                                                                Selected Account Details
                                                            </h4>
                                                            <span class="text-xs text-gray-500">
                                                                Last updated:
                                                                {{ $selectedAccount->updated_at->diffForHumans() }}
                                                            </span>
                                                        </div>

                                                        <div
                                                            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                                            <div class="bg-blue-50 p-3 rounded">
                                                                <div class="text-xs text-blue-600">Available Balance
                                                                </div>
                                                                <div class="text-xl font-bold text-blue-700">
                                                                    {{ number_format($availableBalance, 2) }}
                                                                    {{ $currency }}
                                                                </div>
                                                            </div>

                                                            <div class="bg-gray-50 p-3 rounded">
                                                                <div class="text-xs text-gray-600">Current Balance
                                                                </div>
                                                                <div class="text-xl font-bold text-gray-700">
                                                                    {{ number_format($accountBalance, 2) }}
                                                                    {{ $currency }}
                                                                </div>
                                                            </div>

                                                            <div class="bg-green-50 p-3 rounded">
                                                                <div class="text-xs text-green-600">Ledger Balance
                                                                </div>
                                                                <div class="text-xl font-bold text-green-700">
                                                                    {{ number_format($selectedAccount->ledger_balance, 2) }}
                                                                    {{ $currency }}
                                                                </div>
                                                            </div>

                                                            <div class="bg-purple-50 p-3 rounded">
                                                                <div class="text-xs text-purple-600">Overdraft
                                                                    Available</div>
                                                                <div class="text-xl font-bold text-purple-700">
                                                                    {{ number_format($selectedAccount->overdraft_limit, 2) }}
                                                                    {{ $currency }}
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Transaction Limits -->
                                                        @if (!empty($limits))
                                                            <div class="mt-4 pt-4 border-t border-gray-200">
                                                                <h5 class="text-sm font-medium text-gray-700 mb-2">
                                                                    Transaction Limits:</h5>
                                                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                                                    @foreach ($limits as $period => $limit)
                                                                        <div
                                                                            class="bg-gray-50 p-2 rounded text-center">
                                                                            <div
                                                                                class="text-xs text-gray-500 uppercase">
                                                                                {{ $period }}</div>
                                                                            <div
                                                                                class="text-sm font-semibold text-gray-900">
                                                                                @if ($limit['max_amount'])
                                                                                    {{ number_format($limit['max_amount'], 2) }}
                                                                                @endif
                                                                                @if ($limit['max_count'])
                                                                                    <br>
                                                                                    <span
                                                                                        class="text-xs text-gray-600">
                                                                                        ({{ $limit['max_count'] }}
                                                                                        transactions)
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    @elseif($customerId)
                                        <div class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4">
                                            <div class="flex">
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-exclamation-triangle text-red-500"></i>
                                                </div>
                                                <div class="ml-3">
                                                    <h4 class="text-sm font-medium text-red-800">No Accounts Available
                                                    </h4>
                                                    <div class="mt-2 text-sm text-red-700">
                                                        <p>The selected customer has no active accounts. Please select
                                                            another customer or create an account for this customer.</p>
                                                    </div>
                                                    <div class="mt-3">
                                                        <a href="{{ route('accounts.create', ['customer_id' => $customerId]) }}"
                                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                            <i class="fas fa-plus-circle mr-2"></i>
                                                            Create New Account
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Amount -->
                                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="amount" class="block text-sm font-medium text-gray-700">
                                                Amount <span class="text-red-500">*</span>
                                            </label>
                                            <div class="mt-1 relative rounded-md shadow-sm">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 sm:text-sm">{{ $currency }}</span>
                                                </div>
                                                <input type="number" wire:model.live="amount" id="amount"
                                                    step="0.01" min="0.01"
                                                    class="professional-input block w-full pl-16 pr-4 py-3 text-base border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                    placeholder="0.00">
                                            </div>
                                            @error('amount')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror

                                            @if (
                                                $amount &&
                                                    $availableBalance &&
                                                    in_array($transactionType, ['withdrawal', 'transfer', 'bill_payment', 'loan_payment', 'fee_collection']))
                                                <div class="mt-2">
                                                    @if ($amount > $availableBalance)
                                                        <div class="flex items-center text-red-600">
                                                            <i class="fas fa-exclamation-circle mr-2"></i>
                                                            <span class="text-sm">Insufficient funds. Available:
                                                                {{ number_format($availableBalance, 2) }}</span>
                                                        </div>
                                                    @else
                                                        <div class="flex items-center text-green-600">
                                                            <i class="fas fa-check-circle mr-2"></i>
                                                            <span class="text-sm">Remaining after transaction:
                                                                {{ number_format($availableBalance - $amount, 2) }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>

                                        <div>
                                            <label for="transactionPurpose"
                                                class="block text-sm font-medium text-gray-700">
                                                Transaction Purpose <span class="text-red-500">*</span>
                                            </label>
                                            <select wire:model="transactionPurpose" id="transactionPurpose"
                                                class="professional-input block w-full pl-16 pr-4 py-3 text-base border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                <option value="">Select Purpose</option>
                                                @foreach ($transactionPurposes as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('transactionPurpose')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <div class="mt-4">
                                        <label for="description" class="block text-sm font-medium text-gray-700">
                                            Description <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" wire:model="description" id="description"
                                            class="professional-input block w-full pl-16 pr-4 py-3 text-base border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            placeholder="Enter transaction description...">
                                        @error('description')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Type-specific Fields -->
                                    @if (in_array($transactionType, ['withdrawal', 'cash_deposit']))
                                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label for="cashHandlingMethod"
                                                    class="block text-sm font-medium text-gray-700">
                                                    Method <span class="text-red-500">*</span>
                                                </label>
                                                <select wire:model="cashHandlingMethod" id="cashHandlingMethod"
                                                    class="professional-input block w-full pl-16 pr-4 py-3 text-base border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                    <option value="cash">Cash</option>
                                                    <option value="cheque">Cheque</option>
                                                </select>
                                                @error('cashHandlingMethod')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div>
                                                <label for="cashReferenceNumber"
                                                    class="block text-sm font-medium text-gray-700">
                                                    Reference Number <span class="text-red-500">*</span>
                                                </label>
                                                <input type="text" wire:model="cashReferenceNumber"
                                                    id="cashReferenceNumber"
                                                    class="professional-input block w-full pl-16 pr-4 py-3 text-base border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                    placeholder="e.g., Receipt #, Voucher #">
                                                @error('cashReferenceNumber')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                    @endif

                                    @if ($transactionType === 'cheque_deposit')
                                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label for="chequeNumber"
                                                    class="block text-sm font-medium text-gray-700">
                                                    Cheque Number <span class="text-red-500">*</span>
                                                </label>
                                                <input type="text" wire:model="chequeNumber" id="chequeNumber"
                                                    class="professional-input block w-full pl-16 pr-4 py-3 text-base border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                    placeholder="Cheque number">
                                                @error('chequeNumber')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div>
                                                <label for="drawerBank"
                                                    class="block text-sm font-medium text-gray-700">
                                                    Drawer Bank <span class="text-red-500">*</span>
                                                </label>
                                                <input type="text" wire:model="drawerBank" id="drawerBank"
                                                    class="professional-input block w-full pl-16 pr-4 py-3 text-base border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                    placeholder="Bank name">
                                                @error('drawerBank')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                    @endif

                                    @if ($transactionType === 'loan_payment')
                                        <div class="mt-4">
                                            <label for="loanAccountNumber"
                                                class="block text-sm font-medium text-gray-700">
                                                Loan Account Number <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" wire:model="loanAccountNumber"
                                                id="loanAccountNumber"
                                                class="professional-input block w-full pl-16 pr-4 py-3 text-base border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                placeholder="Loan account number">
                                            @error('loanAccountNumber')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    @endif

                                    @if ($transactionType === 'fee_collection')
                                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label for="feeType" class="block text-sm font-medium text-gray-700">
                                                    Fee Type <span class="text-red-500">*</span>
                                                </label>
                                                <select wire:model="feeType" id="feeType"
                                                    class="professional-input block w-full pl-16 pr-4 py-3 text-base border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                    <option value="">Select Fee Type</option>
                                                    @foreach ($feeTypes as $value => $label)
                                                        <option value="{{ $value }}">{{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('feeType')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div>
                                                <label for="feeDescription"
                                                    class="block text-sm font-medium text-gray-700">
                                                    Fee Description <span class="text-red-500">*</span>
                                                </label>
                                                <input type="text" wire:model="feeDescription" id="feeDescription"
                                                    class="professional-input block w-full pl-16 pr-4 py-3 text-base border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                    placeholder="Description of the fee">
                                                @error('feeDescription')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                    @endif

                                    @if ($transactionType === 'adjustment')
                                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label for="adjustmentType"
                                                    class="block text-sm font-medium text-gray-700">
                                                    Adjustment Type <span class="text-red-500">*</span>
                                                </label>
                                                <select wire:model="adjustmentType" id="adjustmentType"
                                                    class="professional-input block w-full pl-16 pr-4 py-3 text-base border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                    <option value="">Select Adjustment Type</option>
                                                    @foreach ($adjustmentTypes as $value => $label)
                                                        <option value="{{ $value }}">{{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('adjustmentType')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div>
                                                <label for="adjustmentReason"
                                                    class="block text-sm font-medium text-gray-700">
                                                    Adjustment Reason <span class="text-red-500">*</span>
                                                </label>
                                                <input type="text" wire:model="adjustmentReason"
                                                    id="adjustmentReason"
                                                    class="professional-input block w-full pl-16 pr-4 py-3 text-base border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                    placeholder="Reason for adjustment">
                                                @error('adjustmentReason')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                    @endif

                                    @if ($transactionType === 'bill_payment')
                                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label for="billType" class="block text-sm font-medium text-gray-700">
                                                    Bill Type <span class="text-red-500">*</span>
                                                </label>
                                                <select wire:model="billType" id="billType"
                                                    class="professional-input block w-full pl-16 pr-4 py-3 text-base border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                    <option value="">Select Bill Type</option>
                                                    @foreach ($billTypes as $value => $label)
                                                        <option value="{{ $value }}">{{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('billType')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div>
                                                <label for="billAccountNumber"
                                                    class="block text-sm font-medium text-gray-700">
                                                    Bill Account Number <span class="text-red-500">*</span>
                                                </label>
                                                <input type="text" wire:model="billAccountNumber"
                                                    id="billAccountNumber"
                                                    class="professional-input block w-full pl-16 pr-4 py-3 text-base border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                    placeholder="Account number with biller">
                                                @error('billAccountNumber')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Step 2: Transaction Initiator -->
                        @if ($step === 2)
                            <div class="space-y-6">
                                <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                                    <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
                                        <i class="fas fa-user-check mr-2"></i>
                                        2. Transaction Initiator
                                    </h3>
                                    <p class="text-sm text-gray-600 mb-4">
                                        Who is initiating this transaction? Please verify the identity of the person at
                                        the counter.
                                    </p>

                                    <div class="mb-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-3">
                                            Select Initiator Type <span class="text-red-500">*</span>
                                        </label>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <!-- Self/Account Holder Option -->
                                            <button type="button" wire:click="$set('transactionInitiator', 'self')"
                                                class="p-4 border rounded-lg flex flex-col items-center justify-center transition-all duration-200
                            {{ $transactionInitiator === 'self' ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' : 'border-gray-300 hover:border-blue-300 hover:bg-blue-50' }}">
                                                <div
                                                    class="w-12 h-12 rounded-full flex items-center justify-center mb-3
                                {{ $transactionInitiator === 'self' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600' }}">
                                                    <i class="fas fa-user text-xl"></i>
                                                </div>
                                                <span class="text-sm font-medium">Account Holder (Self)</span>
                                                <span class="text-xs text-gray-500 mt-1 text-center">
                                                    The customer themselves is initiating the transaction
                                                </span>
                                                @if ($transactionInitiator === 'self')
                                                    <div class="mt-2 flex items-center text-green-600">
                                                        <i class="fas fa-check-circle mr-1"></i>
                                                        <span class="text-xs">Selected</span>
                                                    </div>
                                                @endif
                                            </button>

                                            <!-- Third Party Option -->
                                            <button type="button"
                                                wire:click="$set('transactionInitiator', 'third_party')"
                                                class="p-4 border rounded-lg flex flex-col items-center justify-center transition-all duration-200
                            {{ $transactionInitiator === 'third_party' ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' : 'border-gray-300 hover:border-blue-300 hover:bg-blue-50' }}">
                                                <div
                                                    class="w-12 h-12 rounded-full flex items-center justify-center mb-3
                                {{ $transactionInitiator === 'third_party' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600' }}">
                                                    <i class="fas fa-users text-xl"></i>
                                                </div>
                                                <span class="text-sm font-medium">Third Party</span>
                                                <span class="text-xs text-gray-500 mt-1 text-center">
                                                    Someone else is transacting on behalf of the customer
                                                </span>
                                                @if ($transactionInitiator === 'third_party')
                                                    <div class="mt-2 flex items-center text-green-600">
                                                        <i class="fas fa-check-circle mr-1"></i>
                                                        <span class="text-xs">Selected</span>
                                                    </div>
                                                @endif
                                            </button>
                                        </div>
                                        @error('transactionInitiator')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Third Party Details (shown when third party is selected) -->
                                    @if ($transactionInitiator === 'third_party')
                                        <div class="mt-6 space-y-4">
                                            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                                                <div class="flex">
                                                    <div class="flex-shrink-0">
                                                        <i class="fas fa-exclamation-triangle text-yellow-500"></i>
                                                    </div>
                                                    <div class="ml-3">
                                                        <h4 class="text-sm font-medium text-yellow-800">Important
                                                            Notice</h4>
                                                        <div class="mt-2 text-sm text-yellow-700">
                                                            <p>For third-party transactions, you must:</p>
                                                            <ul class="list-disc pl-5 mt-1 space-y-1">
                                                                <li>Verify the third party's identity</li>
                                                                <li>Check authorization documents</li>
                                                                <li>Verify relationship with account holder</li>
                                                                <li>Obtain supervisor approval if required</li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="space-y-4">
                                                <!-- Third Party Information -->
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div>
                                                        <label for="thirdPartyName"
                                                            class="block text-sm font-medium text-gray-700">
                                                            Third Party Full Name <span class="text-red-500">*</span>
                                                        </label>
                                                        <input type="text" wire:model="thirdPartyName"
                                                            id="thirdPartyName"
                                                            class="professional-input block w-full pl-16 pr-4 py-3 text-base border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                            placeholder="Enter full name">
                                                        @error('thirdPartyName')
                                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                        @enderror
                                                    </div>

                                                    <div>
                                                        <label for="thirdPartyRelationship"
                                                            class="block text-sm font-medium text-gray-700">
                                                            Relationship to Account Holder <span
                                                                class="text-red-500">*</span>
                                                        </label>
                                                        <select wire:model="thirdPartyRelationship"
                                                            id="thirdPartyRelationship"
                                                            class="professional-input block w-full pl-16 pr-4 py-3 text-base border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                            <option value="">Select Relationship</option>
                                                            @foreach ($relationshipOptions as $value => $label)
                                                                <option value="{{ $value }}">
                                                                    {{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('thirdPartyRelationship')
                                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                    <div>
                                                        <label for="thirdPartyIdType"
                                                            class="block text-sm font-medium text-gray-700">
                                                            ID Type <span class="text-red-500">*</span>
                                                        </label>
                                                        <select wire:model="thirdPartyIdType" id="thirdPartyIdType"
                                                            class="professional-input block w-full pl-16 pr-4 py-3 text-base border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                            <option value="">Select ID Type</option>
                                                            @foreach ($idTypeOptions as $value => $label)
                                                                <option value="{{ $value }}">
                                                                    {{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('thirdPartyIdType')
                                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                        @enderror
                                                    </div>

                                                    <div>
                                                        <label for="thirdPartyIdNumber"
                                                            class="block text-sm font-medium text-gray-700">
                                                            ID Number <span class="text-red-500">*</span>
                                                        </label>
                                                        <input type="text" wire:model="thirdPartyIdNumber"
                                                            id="thirdPartyIdNumber"
                                                            class="professional-input block w-full pl-16 pr-4 py-3 text-base border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                            placeholder="ID Number">
                                                        @error('thirdPartyIdNumber')
                                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                        @enderror
                                                    </div>

                                                    <div>
                                                        <label for="thirdPartyPhone"
                                                            class="block text-sm font-medium text-gray-700">
                                                            Phone Number <span class="text-red-500">*</span>
                                                        </label>
                                                        <input type="text" wire:model="thirdPartyPhone"
                                                            id="thirdPartyPhone"
                                                            class="professional-input block w-full pl-16 pr-4 py-3 text-base border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                            placeholder="Phone number">
                                                        @error('thirdPartyPhone')
                                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <!-- Authorization -->
                                                <div class="space-y-3">
                                                    <div class="flex items-start">
                                                        <input type="checkbox" wire:model="thirdPartyAuthorization"
                                                            id="thirdPartyAuthorization"
                                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1">
                                                        <div class="ml-2">
                                                            <label for="thirdPartyAuthorization"
                                                                class="block text-sm font-medium text-gray-900">
                                                                Authorization Document Verified <span
                                                                    class="text-red-500">*</span>
                                                            </label>
                                                            <p class="text-sm text-gray-500">
                                                                I have verified the authorization document (power of
                                                                attorney, letter of authorization, etc.)
                                                            </p>
                                                        </div>
                                                    </div>
                                                    @error('thirdPartyAuthorization')
                                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                    @enderror

                                                    @if ($thirdPartyAuthorization)
                                                        <div>
                                                            <label for="authorizationDocument"
                                                                class="block text-sm font-medium text-gray-700">
                                                                Authorization Document Details <span
                                                                    class="text-red-500">*</span>
                                                            </label>
                                                            <input type="text" wire:model="authorizationDocument"
                                                                id="authorizationDocument"
                                                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                                                placeholder="e.g., Power of Attorney #12345, Letter of Authorization">
                                                            @error('authorizationDocument')
                                                                <p class="mt-1 text-sm text-red-600">{{ $message }}
                                                                </p>
                                                            @enderror
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- Current Account Holder Info -->
                                                <div class="mt-6 pt-6 border-t border-gray-200">
                                                    <h4 class="text-sm font-medium text-gray-900 mb-3">Account Holder
                                                        Information</h4>
                                                    <div class="bg-gray-50 rounded-md p-4">
                                                        <div class="flex items-center">
                                                            @if ($selectedCustomer && $selectedCustomer['profile_photo_url'])
                                                                <img class="h-10 w-10 rounded-full"
                                                                    src="{{ $selectedCustomer['profile_photo_url'] }}"
                                                                    alt="{{ $selectedCustomer['full_name'] }}">
                                                            @else
                                                                <div
                                                                    class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                                    <span class="text-blue-600 font-medium">
                                                                        {{ substr($selectedCustomer['full_name'] ?? 'C', 0, 1) }}
                                                                    </span>
                                                                </div>
                                                            @endif
                                                            <div class="ml-3">
                                                                <p class="text-sm font-medium text-gray-900">
                                                                    {{ $selectedCustomer['full_name'] ?? 'Customer' }}
                                                                </p>
                                                                <p class="text-sm text-gray-500">
                                                                    Customer
                                                                    #{{ $selectedCustomer['customer_number'] ?? 'N/A' }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                                                            <div>
                                                                <p class="text-gray-600">ID Number</p>
                                                                <p class="font-medium">
                                                                    {{ $selectedCustomer['id_number'] ?? 'N/A' }}</p>
                                                            </div>
                                                            <div>
                                                                <p class="text-gray-600">Phone</p>
                                                                <p class="font-medium">
                                                                    {{ $selectedCustomer['phone'] ?? 'N/A' }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <!-- Self Transaction Verification -->
                                        <div class="mt-6 space-y-4">
                                            <div class="bg-green-50 border border-green-200 rounded-md p-4">
                                                <div class="flex">
                                                    <div class="flex-shrink-0">
                                                        <i class="fas fa-check-circle text-green-500"></i>
                                                    </div>
                                                    <div class="ml-3">
                                                        <h4 class="text-sm font-medium text-green-800">Account Holder
                                                            Verified</h4>
                                                        <div class="mt-2 text-sm text-green-700">
                                                            <p>The account holder is personally initiating this
                                                                transaction.</p>
                                                            <p class="mt-1">Please verify the customer's identity
                                                                using their photo ID or biometric verification.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Customer Display -->
                                            @if ($selectedCustomer)
                                                <div class="bg-white border border-gray-200 rounded-md p-4">
                                                    <div class="flex items-center">
                                                        @if ($selectedCustomer['profile_photo_url'])
                                                            <img class="h-12 w-12 rounded-full"
                                                                src="{{ $selectedCustomer['profile_photo_url'] }}"
                                                                alt="{{ $selectedCustomer['full_name'] }}">
                                                        @else
                                                            <div
                                                                class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                                                                <span class="text-blue-600 font-medium text-lg">
                                                                    {{ substr($selectedCustomer['full_name'], 0, 1) }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                        <div class="ml-4">
                                                            <div class="flex items-center">
                                                                <div class="text-lg font-medium text-gray-900">
                                                                    {{ $selectedCustomer['full_name'] }}</div>
                                                                <span
                                                                    class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                    <i class="fas fa-user-check mr-1"></i>
                                                                    Present at Counter
                                                                </span>
                                                            </div>
                                                            <div class="text-sm text-gray-600 mt-1">
                                                                Customer #{{ $selectedCustomer['customer_number'] }}
                                                                @if ($selectedCustomer['id_number'])
                                                                    • ID: {{ $selectedCustomer['id_number'] }}
                                                                @endif
                                                            </div>
                                                            <div class="text-xs text-gray-400 mt-1">
                                                                <i
                                                                    class="fas fa-envelope mr-1"></i>{{ $selectedCustomer['email'] }}
                                                                <i
                                                                    class="fas fa-phone ml-3 mr-1"></i>{{ $selectedCustomer['phone'] }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Step 3: Verification & Receipt Options -->
                        @if ($step === 3)
                            <div class="space-y-6">
                                <!-- Customer Verification -->
                                <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                                    <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
                                        <i class="fas fa-shield-alt mr-2"></i>
                                        3. Customer Verification & Receipt Options
                                    </h3>

                                    <div class="space-y-4">
                                        <!-- Verification method selection -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                                Verification Method <span class="text-red-500">*</span>
                                            </label>
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                                <button type="button"
                                                    wire:click="$set('customerVerificationMethod', 'signature')"
                                                    class="p-3 border rounded-lg flex flex-col items-center justify-center transition-all duration-200
                                {{ $customerVerificationMethod === 'signature' ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' : 'border-gray-300 hover:border-blue-300 hover:bg-blue-50' }}">
                                                    <div
                                                        class="w-8 h-8 rounded-full flex items-center justify-center mb-1
                                {{ $customerVerificationMethod === 'signature' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600' }}">
                                                        <i class="fas fa-signature"></i>
                                                    </div>
                                                    <span class="text-sm font-medium">Signature</span>
                                                </button>

                                                <button type="button"
                                                    wire:click="$set('customerVerificationMethod', 'id')"
                                                    class="p-3 border rounded-lg flex flex-col items-center justify-center transition-all duration-200 {{ $customerVerificationMethod === 'id' ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' : 'border-gray-300 hover:border-blue-300 hover:bg-blue-50' }}">
                                                    <div
                                                        class="w-8 h-8 rounded-full flex items-center justify-center mb-1 {{ $customerVerificationMethod === 'id' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600' }}">
                                                        <i class="fas fa-id-card"></i>
                                                    </div>
                                                    <span class="text-sm font-medium">ID Check</span>
                                                </button>

                                                <button type="button"
                                                    wire:click="$set('customerVerificationMethod', 'biometric')"
                                                    class="p-3 border rounded-lg flex flex-col items-center justify-center transition-all duration-200 {{ $customerVerificationMethod === 'biometric' ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' : 'border-gray-300 hover:border-blue-300 hover:bg-blue-50' }}">
                                                    <div
                                                        class="w-8 h-8 rounded-full flex items-center justify-center mb-1 {{ $customerVerificationMethod === 'biometric' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600' }}">
                                                        <i class="fas fa-fingerprint"></i>
                                                    </div>
                                                    <span class="text-sm font-medium">Biometric</span>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Signature Verification -->
                                        <div class="flex items-center">
                                            <input type="checkbox" wire:model="customerSignature"
                                                id="customerSignature"
                                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <label for="customerSignature" class="ml-2 block text-sm text-gray-900">
                                                Customer signature verified on transaction slip
                                            </label>
                                        </div>

                                        <!-- ID Verification -->
                                        <div class="flex items-center">
                                            <input type="checkbox" wire:model="idVerified" id="idVerified"
                                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <label for="idVerified" class="ml-2 block text-sm text-gray-900">
                                                Customer ID verified and matches records
                                            </label>
                                        </div>

                                        @if ($customerVerificationMethod === 'id' && $idVerified)
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label for="idType"
                                                        class="block text-sm font-medium text-gray-700">
                                                        ID Type
                                                    </label>
                                                    <input type="text" wire:model="idType" id="idType"
                                                         class="professional-input block w-full pl-16 pr-4 py-3 text-base border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                        placeholder="e.g., Passport, Driver's License">
                                                </div>

                                                <div>
                                                    <label for="idNumber"
                                                        class="block text-sm font-medium text-gray-700">
                                                        ID Number
                                                    </label>
                                                    <input type="text" wire:model="idNumber" id="idNumber"
                                                         class="professional-input block w-full pl-16 pr-4 py-3 text-base border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                        placeholder="ID number">
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Supervisor Approval (now part of step 3) -->
                                <div class="bg-yellow-50 border border-yellow-100 rounded-lg p-4">
                                    <h3 class="text-lg font-semibold text-yellow-900 mb-4 flex items-center">
                                        <i class="fas fa-user-tie mr-2"></i>
                                        Supervisor Approval
                                    </h3>

                                    <div class="space-y-4">
                                        <div class="flex items-center">
                                            <input type="checkbox" wire:model.live="supervisorApproval"
                                                id="supervisorApproval"
                                                class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300 rounded">
                                            <label for="supervisorApproval" class="ml-2 block text-sm text-gray-900">
                                                This transaction requires supervisor approval
                                            </label>
                                        </div>

                                        @if ($supervisorApproval)
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label for="supervisorId"
                                                        class="block text-sm font-medium text-gray-700">
                                                        Select Supervisor <span class="text-red-500">*</span>
                                                    </label>
                                                    <select wire:model="supervisorId" id="supervisorId"
                                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                                        <option value="">Select Supervisor</option>
                                                        @foreach ($supervisors as $supervisor)
                                                            <option value="{{ $supervisor->id }}">
                                                                {{ $supervisor->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('supervisorId')
                                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Receipt Options (now part of step 3) -->
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                        <i class="fas fa-receipt mr-2"></i>
                                        Receipt & Confirmation
                                    </h3>

                                    <div class="space-y-4">
                                        <!-- Receipt Options -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Receipt Delivery Options
                                            </label>
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                                <div class="flex items-center">
                                                    <input type="checkbox" wire:model="printReceipt"
                                                        id="printReceipt"
                                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                    <label for="printReceipt"
                                                        class="ml-2 block text-sm text-gray-900">
                                                        Print Receipt
                                                    </label>
                                                </div>

                                                <div class="flex items-center">
                                                    <input type="checkbox" wire:model="emailReceipt"
                                                        id="emailReceipt"
                                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                    <label for="emailReceipt"
                                                        class="ml-2 block text-sm text-gray-900">
                                                        Email Receipt
                                                    </label>
                                                </div>

                                                <div class="flex items-center">
                                                    <input type="checkbox" wire:model="smsReceipt" id="smsReceipt"
                                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                    <label for="smsReceipt" class="ml-2 block text-sm text-gray-900">
                                                        SMS Receipt
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        @if ($emailReceipt)
                                            <div>
                                                <label for="customerEmail"
                                                    class="block text-sm font-medium text-gray-700">
                                                    Email Address <span class="text-red-500">*</span>
                                                </label>
                                                <input type="email" wire:model="customerEmail" id="customerEmail"
                                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                                    placeholder="customer@example.com">
                                                @error('customerEmail')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        @endif

                                        @if ($smsReceipt)
                                            <div>
                                                <label for="customerPhone"
                                                    class="block text-sm font-medium text-gray-700">
                                                    Phone Number <span class="text-red-500">*</span>
                                                </label>
                                                <input type="text" wire:model="customerPhone" id="customerPhone"
                                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                                    placeholder="+1234567890">
                                                @error('customerPhone')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        @endif

                                        <!-- Currency Conversion -->
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label for="currency" class="block text-sm font-medium text-gray-700">
                                                    Currency <span class="text-red-500">*</span>
                                                </label>
                                                <select wire:model="currency" id="currency"
                                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                                    <option value="GHS">GHS - Ghana Cedi</option>
                                                </select>
                                                @error('currency')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            @if ($currency !== 'GHS')
                                                <div>
                                                    <label for="exchangeRate"
                                                        class="block text-sm font-medium text-gray-700">
                                                        Exchange Rate
                                                    </label>
                                                    <input type="number" wire:model="exchangeRate" id="exchangeRate"
                                                        step="0.0001" min="0"
                                                        class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                                    @error('exchangeRate')
                                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                    @enderror
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">
                                                        Amount in {{ $currency }}
                                                    </label>
                                                    <div class="mt-1 p-2 bg-gray-100 rounded-md">
                                                        <p class="text-lg font-semibold text-gray-900">
                                                            @if (is_numeric($foreignAmount))
                                                                {{ number_format((float) $foreignAmount, 2) }}
                                                            @else
                                                                0.00
                                                            @endif
                                                            {{ $currency }}
                                                        </p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Navigation Buttons -->
                        <div class="flex justify-between pt-6 border-t border-gray-200 mt-8">
                            @if ($step > 1)
                                <button type="button" wire:click="previousStep" wire:loading.attr="disabled"
                                    class="px-6 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition-all duration-200">
                                    <i class="fas fa-arrow-left mr-2"></i>
                                    Previous Step
                                </button>
                            @else
                                <a href="{{ route('transactions.index') }}"
                                    class="px-6 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                    <i class="fas fa-times mr-2"></i>
                                    Cancel Transaction
                                </a>
                            @endif

                            <button type="button" wire:click="nextStep" wire:loading.attr="disabled"
                                class="px-6 py-3 bg-blue-600 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition-all duration-200">
                                @if ($step < $totalSteps)
                                    Continue to Next Step
                                    <i class="fas fa-arrow-right ml-2"></i>
                                @else
                                    Review Transaction
                                    <i class="fas fa-check-circle ml-2"></i>
                                @endif
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Processing Overlay -->
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
</div>
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Format amount input on blur
            const amountInput = document.getElementById('amount');
            if (amountInput) {
                amountInput.addEventListener('blur', function(e) {
                    let value = parseFloat(e.target.value);
                    if (!isNaN(value) && value > 0) {
                        e.target.value = value.toFixed(2);
                        // Trigger Livewire update
                        this.dispatchEvent(new Event('input', {
                            bubbles: true
                        }));
                    }
                });

                // Prevent invalid characters
                amountInput.addEventListener('keypress', function(e) {
                    const charCode = e.which ? e.which : e.keyCode;
                    const value = e.target.value;

                    // Allow: 0-9, ., backspace, delete, tab, escape, enter
                    if (charCode === 46) {
                        // Check if decimal point already exists
                        if (value.indexOf('.') !== -1) {
                            e.preventDefault();
                        }
                    } else if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                        e.preventDefault();
                    }
                });
            }

            // Add currency formatting to other numeric inputs
            const numericInputs = document.querySelectorAll('input[type="number"]');
            numericInputs.forEach(input => {
                input.addEventListener('blur', function(e) {
                    let value = parseFloat(e.target.value);
                    if (!isNaN(value) && value >= 0) {
                        e.target.value = value.toFixed(2);
                        this.dispatchEvent(new Event('input', {
                            bubbles: true
                        }));
                    }
                });
            });
        });

        // Livewire event listeners
        Livewire.on('close-search-results', () => {
            // Close search results when clicking outside
            document.addEventListener('click', function(event) {
                const searchContainer = document.getElementById('customerSearch');
                const resultsContainer = document.querySelector('.search-results-container');

                if (searchContainer && resultsContainer &&
                    !searchContainer.contains(event.target) &&
                    !resultsContainer.contains(event.target)) {
                    Livewire.dispatch('close-search-results');
                }
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Listen for step navigation
            Livewire.on('validation-failed', (message) => {
                // Show toast notification for validation errors
                showToast(message, 'error');
            });

            // Show toast notification function
            function showToast(message, type = 'info') {
                // You can implement your own toast notification here
                // For now, using a simple alert
                alert(message);
            }

            // Auto-scroll to top when step changes
            Livewire.on('step-changed', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });

            // Listen for nextStep event
            document.addEventListener('livewire:initialized', () => {
                @this.on('nextStep', () => {
                    // Optional: Add any custom logic when moving to next step
                    console.log('Moving to step:', @this.step);
                });
            });
        });
    </script>
@endpush
