<div>
    <div class="max-w-6xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">
                            <i class="fas fa-hand-holding-usd mr-2 text-blue-600"></i>
                            Loan Application Processing
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">
                            Complete loan application for customers
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
                                                    Step 1: Customer & Loan Details
                                                @break

                                                @case(2)
                                                    Step 2: Collateral & Guarantors
                                                @break

                                                @case(3)
                                                    Step 3: Documentation & Review
                                                @break

                                                @case(4)
                                                    Step 4: Final Review & Submission
                                                @break
                                            @endswitch
                                        </h3>
                                        <p class="text-sm text-blue-700">
                                            @switch($step)
                                                @case(1)
                                                    Select customer and enter loan details
                                                @break

                                                @case(2)
                                                    Add collateral information and guarantors
                                                @break

                                                @case(3)
                                                    Upload documents and review details
                                                @break

                                                @case(4)
                                                    Final review and submit application
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
                @if ($showConfirmation && $loanPreview)
                    <!-- Confirmation Step -->
                    <div class="space-y-6">
                        <!-- Loan Summary -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
                                <i class="fas fa-file-contract mr-2"></i>
                                Loan Application Summary
                            </h3>
                            <div class="space-y-6">
                                <!-- Customer Information -->
                                <div class="bg-white rounded-lg border border-gray-200 p-4">
                                    <h4 class="font-medium text-gray-900 mb-3">Customer Information</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <p class="text-sm text-gray-600">Customer Name</p>
                                            <p class="font-medium">{{ $loanPreview['customer']['full_name'] }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Customer Number</p>
                                            <p class="font-medium">{{ $loanPreview['customer']['customer_number'] }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Monthly Income</p>
                                            <p class="font-medium">
                                                {{ number_format($loanPreview['customer']['monthly_income'], 2) }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Loan Details -->
                                <div class="bg-white rounded-lg border border-gray-200 p-4">
                                    <h4 class="font-medium text-gray-900 mb-3">Loan Details</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        <div>
                                            <p class="text-sm text-gray-600">Loan Type</p>
                                            <p class="font-medium capitalize">
                                                {{ $loanPreview['loan_details']['type'] }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Purpose</p>
                                            <p class="font-medium">{{ $loanPreview['loan_details']['purpose'] }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Amount</p>
                                            <p class="font-medium text-lg">{{ $loanPreview['loan_details']['amount'] }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Term</p>
                                            <p class="font-medium">{{ $loanPreview['loan_details']['term'] }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Interest Rate</p>
                                            <p class="font-medium">{{ $loanPreview['loan_details']['interest_rate'] }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Interest Type</p>
                                            <p class="font-medium">{{ $loanPreview['loan_details']['interest_type'] }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Financial Summary -->
                                <div class="bg-white rounded-lg border border-gray-200 p-4">
                                    <h4 class="font-medium text-gray-900 mb-3">Financial Summary</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                        <div class="bg-green-50 p-3 rounded">
                                            <p class="text-xs text-green-600">Monthly Payment</p>
                                            <p class="text-xl font-bold text-green-700">
                                                {{ $loanPreview['financials']['monthly_payment'] }}</p>
                                        </div>
                                        <div class="bg-blue-50 p-3 rounded">
                                            <p class="text-xs text-blue-600">Total Interest</p>
                                            <p class="text-xl font-bold text-blue-700">
                                                {{ $loanPreview['financials']['total_interest'] }}</p>
                                        </div>
                                        <div class="bg-purple-50 p-3 rounded">
                                            <p class="text-xs text-purple-600">Total Amount</p>
                                            <p class="text-xl font-bold text-purple-700">
                                                {{ $loanPreview['financials']['total_amount'] }}</p>
                                        </div>
                                        <div class="bg-yellow-50 p-3 rounded">
                                            <p class="text-xs text-yellow-600">Total Fees</p>
                                            <p class="text-xl font-bold text-yellow-700">
                                                @php
                                                    $totalFees =
                                                        (float) str_replace(
                                                            ',',
                                                            '',
                                                            $loanPreview['financials']['processing_fee'],
                                                        ) +
                                                        (float) str_replace(
                                                            ',',
                                                            '',
                                                            $loanPreview['financials']['insurance_fee'],
                                                        );
                                                @endphp
                                                {{ number_format($totalFees, 2) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Collateral & Guarantors -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Collateral -->
                                    <div class="bg-white rounded-lg border border-gray-200 p-4">
                                        <h4 class="font-medium text-gray-900 mb-3">Collateral</h4>
                                        <div class="space-y-2">
                                            <div>
                                                <p class="text-sm text-gray-600">Value</p>
                                                <p class="font-medium">{{ $loanPreview['collateral']['value'] }}</p>
                                            </div>
                                            @if (!empty($loanPreview['collateral']['details']))
                                                <div>
                                                    <p class="text-sm text-gray-600">Details</p>
                                                    <p class="font-medium text-sm">
                                                        {{ implode(', ', $loanPreview['collateral']['details']) }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Guarantors -->
                                    <div class="bg-white rounded-lg border border-gray-200 p-4">
                                        <h4 class="font-medium text-gray-900 mb-3">Guarantors</h4>
                                        @if (!empty($loanPreview['guarantors']))
                                            <div class="space-y-2">
                                                @foreach ($loanPreview['guarantors'] as $guarantor)
                                                    <div class="border-b border-gray-100 pb-2 last:border-0">
                                                        <p class="font-medium text-sm">{{ $guarantor['name'] }}</p>
                                                        <p class="text-xs text-gray-500">
                                                            {{ $guarantor['relationship'] }} •
                                                            {{ $guarantor['phone'] }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-sm text-gray-500 italic">No guarantors added</p>
                                        @endif
                                    </div>
                                </div>

                                <!-- Dates -->
                                <div class="bg-white rounded-lg border border-gray-200 p-4">
                                    <h4 class="font-medium text-gray-900 mb-3">Timeline</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-gray-600">Application Date</p>
                                            <p class="font-medium">
                                                {{ date('F j, Y', strtotime($loanPreview['dates']['application_date'])) }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Proposed Start Date</p>
                                            <p class="font-medium">
                                                {{ date('F j, Y', strtotime($loanPreview['dates']['start_date'])) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Final Authorization -->
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-shield-alt text-gray-600 mr-2"></i>
                                Final Authorization
                            </h3>

                            <div class="space-y-4">
                                <!-- Compliance Check -->
                                <div class="flex items-start">
                                    <input type="checkbox" id="complianceCheck"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1">
                                    <div class="ml-2">
                                        <label for="complianceCheck" class="block text-sm font-medium text-gray-900">
                                            Compliance Check
                                        </label>
                                        <p class="text-sm text-gray-500">
                                            I confirm that this loan application complies with all bank policies and
                                            regulatory requirements.
                                        </p>
                                    </div>
                                </div>

                                <!-- Risk Acknowledgment -->
                                <div class="flex items-start">
                                    <input type="checkbox" id="riskAcknowledgment"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1">
                                    <div class="ml-2">
                                        <label for="riskAcknowledgment"
                                            class="block text-sm font-medium text-gray-900">
                                            Risk Acknowledgment
                                        </label>
                                        <p class="text-sm text-gray-500">
                                            I acknowledge the risk level of this loan and confirm proper assessment has
                                            been conducted.
                                        </p>
                                    </div>
                                </div>

                                <!-- Customer Verification -->
                                <div class="flex items-start">
                                    <input type="checkbox" id="customerVerification"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1">
                                    <div class="ml-2">
                                        <label for="customerVerification"
                                            class="block text-sm font-medium text-gray-900">
                                            Customer Verification
                                        </label>
                                        <p class="text-sm text-gray-500">
                                            I verify that all customer information is accurate and documents have been
                                            validated.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <p class="text-sm text-red-600">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    Once submitted, this application will be sent for committee review and cannot be
                                    edited.
                                </p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-between pt-6">
                            <button type="button" wire:click="previousStep" wire:loading.attr="disabled"
                                class="px-6 py-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Back to Edit
                            </button>

                            <div class="flex space-x-3">
                                <button type="button" onclick="window.print()"
                                    class="px-6 py-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="fas fa-print mr-2"></i>
                                    Print Summary
                                </button>

                                <button type="button" wire:click="submitApplication" wire:loading.attr="disabled"
                                    wire:target="submitApplication"
                                    class="px-6 py-3 bg-green-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50">
                                    <span wire:loading.remove wire:target="submitApplication">
                                        <i class="fas fa-paper-plane mr-2"></i>
                                        Submit Application
                                    </span>
                                    <span wire:loading wire:target="submitApplication">
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
                        <!-- Step 1: Customer and Loan Details -->
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
                                                phone, or email</span>
                                        </label>

                                        <!-- Search Input with Loading Indicator -->
                                        <div class="mt-1 relative">
                                            <div class="flex">
                                                <input type="text" wire:model.live.debounce.300ms="customerSearch"
                                                    wire:keydown.escape="clearSearch" id="customerSearch"
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
                                                <button type="button" wire:click="clearSearch"
                                                    class="absolute inset-y-0 right-12 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @endif

                                            @error('customerId')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <!-- Search Results Dropdown -->
                                        @if ($showSearchResults && !empty($searchResults) && !$customerId)
                                            <div class="mt-1 absolute z-50 w-100 bg-white shadow-lg max-h-96 overflow-y-auto rounded-lg border border-gray-300"
                                                style="position: absolute; z-index: 9999;">
                                                <ul class="divide-y divide-gray-200">
                                                    @foreach ($searchResults as $customer)
                                                        <li wire:key="customer-{{ $customer['id'] }}">
                                                            <button type="button"
                                                                wire:click="selectCustomer({{ $customer['id'] }})"
                                                                class="w-full text-left px-4 py-3 focus:outline-none focus:bg-blue-50 hover:bg-blue-50">
                                                                <div class="flex items-center">
                                                                    <div class="flex-shrink-0">
                                                                        @if (!empty($customer['profile_photo_url']))
                                                                            <img class="h-10 w-10 rounded-full"
                                                                                src="{{ $customer['profile_photo_url'] }}"
                                                                                alt="{{ $customer['full_name'] }}">
                                                                        @else
                                                                            <div
                                                                                class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                                                <span
                                                                                    class="text-blue-600 font-medium">
                                                                                    {{ substr($customer['first_name'] ?? '', 0, 1) }}{{ substr($customer['last_name'] ?? '', 0, 1) }}
                                                                                </span>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                    <div class="ml-4 flex-1">
                                                                        <div class="flex justify-between items-start">
                                                                            <div>
                                                                                <div
                                                                                    class="text-sm font-medium text-gray-900">
                                                                                    {{ $customer['full_name'] ?? 'N/A' }}
                                                                                </div>
                                                                                <div class="text-sm text-gray-500">
                                                                                    #{{ $customer['customer_number'] ?? 'N/A' }}
                                                                                </div>
                                                                                <div
                                                                                    class="text-xs text-gray-400 mt-1">
                                                                                    {{ $customer['email'] ?? '' }} |
                                                                                    {{ $customer['phone'] ?? '' }}
                                                                                </div>
                                                                            </div>
                                                                            <div class="text-right">
                                                                                <span
                                                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                                                    <i
                                                                                        class="fas fa-money-bill-wave mr-1"></i>
                                                                                    {{ number_format($customer['monthly_income'] ?? 0, 2) }}
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </button>
                                                        </li>
                                                    @endforeach
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
                                                            @if (!empty($selectedCustomer['profile_photo_url']))
                                                                <img class="h-12 w-12 rounded-full"
                                                                    src="{{ $selectedCustomer['profile_photo_url'] }}"
                                                                    alt="{{ $selectedCustomer['full_name'] }}">
                                                            @else
                                                                <div
                                                                    class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                                                                    <span class="text-blue-600 font-medium text-lg">
                                                                        {{ substr($selectedCustomer['first_name'] ?? '', 0, 1) }}{{ substr($selectedCustomer['last_name'] ?? '', 0, 1) }}
                                                                    </span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="flex items-center">
                                                                <div class="text-lg font-medium text-gray-900">
                                                                    {{ $selectedCustomer['full_name'] }}
                                                                </div>
                                                                <span
                                                                    class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                    <i class="fas fa-shield-alt mr-1"></i>
                                                                    KYC Verified
                                                                </span>
                                                            </div>
                                                            <div class="text-sm text-gray-600 mt-1">
                                                                Customer #{{ $selectedCustomer['customer_number'] }}
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

                                                <!-- Customer Financial Info -->
                                                <div class="mt-4 pt-4 border-t border-gray-200">
                                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                                        <div class="bg-blue-50 p-2 rounded text-center">
                                                            <div class="text-blue-700 font-medium">
                                                                {{ number_format($selectedCustomer['monthly_income'], 2) }}
                                                            </div>
                                                            <div class="text-blue-600">Monthly Income</div>
                                                        </div>
                                                        @if (!empty($selectedCustomer['accounts']))
                                                            <div class="bg-green-50 p-2 rounded text-center">
                                                                <div class="text-green-700 font-medium">
                                                                    {{ count($selectedCustomer['accounts']) }}
                                                                </div>
                                                                <div class="text-green-600">Accounts</div>
                                                            </div>
                                                            <div class="bg-purple-50 p-2 rounded text-center">
                                                                <div class="text-purple-700 font-medium">
                                                                    @php
                                                                        $totalBalance = array_sum(
                                                                            array_column(
                                                                                $selectedCustomer['accounts'],
                                                                                'current_balance',
                                                                            ),
                                                                        );
                                                                    @endphp
                                                                    {{ number_format($totalBalance, 2) }}
                                                                </div>
                                                                <div class="text-purple-600">Total Balance</div>
                                                            </div>
                                                        @endif
                                                        <div class="bg-yellow-50 p-2 rounded text-center">
                                                            <div class="text-yellow-700 font-medium">
                                                                {{ ($selectedCustomer['age'] ?? 'N/A') . ($selectedCustomer['age'] ? ' years' : '') }}
                                                            </div>
                                                            <div class="text-yellow-600">Age</div>
                                                        </div>
                                                    </div>
                                                </div>
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
                                                            Please select a customer to continue with the loan
                                                            application.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Loan Type Selection -->
                                <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                                    <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
                                        <i class="fas fa-list-alt mr-2"></i>
                                        2. Loan Details
                                    </h3>

                                    <!-- Loan Type Selection -->
                                    <div class="mb-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-3">
                                            Loan Type <span class="text-red-500">*</span>
                                        </label>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                            @foreach (['personal' => ['icon' => 'user', 'color' => 'blue', 'label' => 'Personal'], 'mortgage' => ['icon' => 'home', 'color' => 'green', 'label' => 'Mortgage'], 'funeral' => ['icon' => 'cross', 'color' => 'gray', 'label' => 'Funeral'], 'business' => ['icon' => 'briefcase', 'color' => 'purple', 'label' => 'Business'], 'auto' => ['icon' => 'car', 'color' => 'red', 'label' => 'Auto'], 'education' => ['icon' => 'graduation-cap', 'color' => 'indigo', 'label' => 'Education'], 'agriculture' => ['icon' => 'tractor', 'color' => 'yellow', 'label' => 'Agriculture'], 'emergency' => ['icon' => 'ambulance', 'color' => 'pink', 'label' => 'Emergency']] as $type => $data)
                                                <button type="button"
                                                    wire:click="$set('loanType', '{{ $type }}')"
                                                    class="p-4 border rounded-lg flex flex-col items-center justify-center transition-all duration-200
                                                    {{ $loanType === $type ? 'border-' . $data['color'] . '-500 bg-' . $data['color'] . '-50 ring-2 ring-' . $data['color'] . '-200' : 'border-gray-300 hover:border-' . $data['color'] . '-300 hover:bg-' . $data['color'] . '-50' }}">
                                                    <div
                                                        class="w-10 h-10 rounded-full flex items-center justify-center mb-2
                                                        {{ $loanType === $type ? 'bg-' . $data['color'] . '-100 text-' . $data['color'] . '-600' : 'bg-gray-100 text-gray-600' }}">
                                                        <i class="fas fa-{{ $data['icon'] }}"></i>
                                                    </div>
                                                    <span class="text-sm font-medium">{{ $data['label'] }}</span>
                                                    @if ($loanType === $type)
                                                        <div
                                                            class="mt-1 flex items-center text-{{ $data['color'] }}-600">
                                                            <i class="fas fa-check-circle mr-1 text-xs"></i>
                                                            <span class="text-xs">Selected</span>
                                                        </div>
                                                    @endif
                                                </button>
                                            @endforeach
                                        </div>
                                        @error('loanType')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Loan Details Form -->
                                    <div class="space-y-4">
                                        <!-- Purpose -->
                                        <div>
                                            <label for="purpose" class="block text-sm font-medium text-gray-700">
                                                Loan Purpose <span class="text-red-500">*</span>
                                            </label>
                                            <textarea wire:model="purpose" id="purpose" rows="3"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                placeholder="Describe the purpose of this loan..."></textarea>
                                            @error('purpose')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <!-- Amount -->
                                            <div>
                                                <label for="amount" class="block text-sm font-medium text-gray-700">
                                                    Loan Amount (GHS) <span class="text-red-500">*</span>
                                                </label>
                                                <div class="mt-1 relative rounded-md shadow-sm">
                                                    <div
                                                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <span class="text-gray-500 sm:text-sm">GHS</span>
                                                    </div>
                                                    <input type="number" wire:model.live="amount" id="amount"
                                                        step="100" min="100" max="1000000"
                                                        class="block w-full pl-12 pr-12 py-3 text-base border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                        placeholder="0.00">
                                                </div>
                                                @error('amount')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <!-- Term -->
                                            <div>
                                                <label for="termMonths"
                                                    class="block text-sm font-medium text-gray-700">
                                                    Term (Months) <span class="text-red-500">*</span>
                                                </label>
                                                <div class="mt-1">
                                                    <input type="number" wire:model.live="termMonths"
                                                        id="termMonths" min="1" max="360"
                                                        class="block w-full pl-3 pr-3 py-3 text-base border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                        placeholder="12">
                                                </div>
                                                @error('termMonths')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <!-- Interest Rate -->
                                            <div>
                                                <label for="interestRate"
                                                    class="block text-sm font-medium text-gray-700">
                                                    Interest Rate (%) <span class="text-red-500">*</span>
                                                </label>
                                                <div class="mt-1 relative rounded-md shadow-sm">
                                                    <input type="number" wire:model.live="interestRate"
                                                        id="interestRate" step="0.1" min="1"
                                                        max="50"
                                                        class="block w-full pl-3 pr-12 py-3 text-base border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                        placeholder="15.0">
                                                    <div
                                                        class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                        <span class="text-gray-500 sm:text-sm">%</span>
                                                    </div>
                                                </div>
                                                @error('interestRate')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <!-- Interest Type -->
                                            <div>
                                                <label for="interestType"
                                                    class="block text-sm font-medium text-gray-700">
                                                    Interest Type <span class="text-red-500">*</span>
                                                </label>
                                                <select wire:model="interestType" id="interestType"
                                                    class="mt-1 block w-full pl-3 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-lg">
                                                    <option value="reducing">Reducing Balance</option>
                                                    <option value="flat">Flat Rate</option>
                                                    <option value="fixed">Fixed Rate</option>
                                                </select>
                                                @error('interestType')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <!-- Repayment Frequency -->
                                            <div>
                                                <label for="repaymentFrequency"
                                                    class="block text-sm font-medium text-gray-700">
                                                    Repayment Frequency <span class="text-red-500">*</span>
                                                </label>
                                                <select wire:model="repaymentFrequency" id="repaymentFrequency"
                                                    class="mt-1 block w-full pl-3 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-lg">
                                                    <option value="monthly">Monthly</option>
                                                    <option value="biweekly">Bi-Weekly</option>
                                                    <option value="weekly">Weekly</option>
                                                    <option value="quarterly">Quarterly</option>
                                                </select>
                                                @error('repaymentFrequency')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Payment Calculator -->
                                        @if ($amount && $termMonths && $interestRate)
                                            <div class="mt-4 bg-gray-50 border border-gray-200 rounded-lg p-4">
                                                <h4 class="text-sm font-medium text-gray-900 mb-3">Payment Preview</h4>
                                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                                    <div class="bg-white p-3 rounded border">
                                                        <div class="text-xs text-gray-500">Monthly Payment</div>
                                                        <div class="text-lg font-bold text-blue-600">
                                                            GHS {{ number_format($monthlyPayment, 2) }}
                                                        </div>
                                                    </div>
                                                    <div class="bg-white p-3 rounded border">
                                                        <div class="text-xs text-gray-500">Total Interest</div>
                                                        <div class="text-lg font-bold text-purple-600">
                                                            GHS {{ number_format($totalInterest, 2) }}
                                                        </div>
                                                    </div>
                                                    <div class="bg-white p-3 rounded border">
                                                        <div class="text-xs text-gray-500">Total Amount</div>
                                                        <div class="text-lg font-bold text-green-600">
                                                            GHS {{ number_format($totalAmount, 2) }}
                                                        </div>
                                                    </div>
                                                    <div class="bg-white p-3 rounded border">
                                                        <div class="text-xs text-gray-500">Last Payment</div>
                                                        <div class="text-lg font-bold text-gray-600">
                                                            {{ now()->addMonths((int) ($termMonths ?? 0))->format('M Y') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <!-- Disbursement Method -->
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-3">
                                        Disbursement Method <span class="text-red-500">*</span>
                                    </label>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                        @foreach ([
        'bank_transfer' => ['icon' => 'university', 'label' => 'Bank Transfer', 'color' => 'blue'],
        'cash' => ['icon' => 'money-bill-wave', 'label' => 'Cash', 'color' => 'green'],
        'cheque' => ['icon' => 'file-invoice', 'label' => 'Cheque', 'color' => 'purple'],
        'mobile_money' => ['icon' => 'mobile-alt', 'label' => 'Mobile Money', 'color' => 'orange'],
    ] as $method => $data)
                                            <button type="button"
                                                wire:click="$set('disbursementMethod', '{{ $method }}')"
                                                class="p-4 border rounded-lg flex flex-col items-center justify-center transition-all duration-200
                {{ $disbursementMethod === $method ? 'border-' . $data['color'] . '-500 bg-' . $data['color'] . '-50 ring-2 ring-' . $data['color'] . '-200' : 'border-gray-300 hover:border-' . $data['color'] . '-300 hover:bg-' . $data['color'] . '-50' }}">
                                                <div
                                                    class="w-10 h-10 rounded-full flex items-center justify-center mb-2
                    {{ $disbursementMethod === $method ? 'bg-' . $data['color'] . '-100 text-' . $data['color'] . '-600' : 'bg-gray-100 text-gray-600' }}">
                                                    <i class="fas fa-{{ $data['icon'] }}"></i>
                                                </div>
                                                <span class="text-sm font-medium">{{ $data['label'] }}</span>
                                                @if ($disbursementMethod === $method)
                                                    <div class="mt-1 flex items-center text-{{ $data['color'] }}-600">
                                                        <i class="fas fa-check-circle mr-1 text-xs"></i>
                                                        <span class="text-xs">Selected</span>
                                                    </div>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                    @error('disbursementMethod')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Account Selection (only show if bank transfer selected AND customer has accounts) -->
                                @if ($disbursementMethod === 'bank_transfer' && $selectedCustomer && $hasAccounts)
                                    <div class="mt-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-3">
                                            Select Account for Disbursement <span class="text-red-500">*</span>
                                            <span class="text-xs text-gray-500 ml-2">Funds will be transferred to this
                                                account</span>
                                        </label>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            @foreach ($selectedCustomer['accounts'] as $account)
                                                <div class="relative">
                                                    <input type="radio" wire:model="account_id"
                                                        value="{{ $account['id'] }}"
                                                        id="account_{{ $account['id'] }}" class="sr-only">
                                                    <label for="account_{{ $account['id'] }}"
                                                        class="cursor-pointer block p-4 border rounded-lg transition-all duration-200
                        {{ $account_id == $account['id'] ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' : 'border-gray-300 hover:border-blue-300 hover:bg-blue-50' }}">
                                                        <div class="flex items-start justify-between">
                                                            <div class="flex-1">
                                                                <div class="flex items-center">
                                                                    <div class="flex-shrink-0">
                                                                        <div
                                                                            class="h-10 w-10 rounded-full flex items-center justify-center 
                                            {{ $account_id == $account['id'] ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600' }}">
                                                                            <i class="fas fa-wallet"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div class="ml-4">
                                                                        <div class="text-sm font-medium text-gray-900">
                                                                            {{ $account['account_number'] }}
                                                                        </div>
                                                                        <div class="text-xs text-gray-500">
                                                                            {{ $account['account_type'] }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="mt-3">
                                                                    <div class="text-xs text-gray-500">Current Balance
                                                                    </div>
                                                                    <div
                                                                        class="text-lg font-semibold {{ $account['current_balance'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                                        {{ number_format($account['current_balance'], 2) }}
                                                                        {{ $account['currency'] }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @if ($account_id == $account['id'])
                                                                <div class="ml-4 flex-shrink-0">
                                                                    <div
                                                                        class="h-6 w-6 rounded-full bg-blue-600 flex items-center justify-center">
                                                                        <i class="fas fa-check text-white text-xs"></i>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                        @error('account_id')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @elseif($disbursementMethod === 'bank_transfer' && $selectedCustomer && !$hasAccounts)
                                    <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-md p-4">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-exclamation-triangle text-yellow-500"></i>
                                            </div>
                                            <div class="ml-3">
                                                <h4 class="text-sm font-medium text-yellow-800">No Bank Account</h4>
                                                <div class="mt-2 text-sm text-yellow-700">
                                                    <p>This customer has no bank account. Please:</p>
                                                    <ul class="list-disc pl-5 mt-1 space-y-1">
                                                        <li>Select a different disbursement method (Cash, Cheque, or
                                                            Mobile Money)</li>
                                                        <li>Create a bank account for the customer first</li>
                                                        <li>Ask the customer to provide external bank account details
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="mt-3">
                                                    <a href="{{ route('accounts.create', ['customer_id' => $customerId]) }}"
                                                        target="_blank"
                                                        class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                        <i class="fas fa-plus-circle mr-1"></i>
                                                        Create New Account
                                                    </a>
                                                    <button type="button"
                                                        wire:click="$set('disbursementMethod', 'cash')"
                                                        class="ml-2 inline-flex items-center px-3 py-1 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                        <i class="fas fa-money-bill-wave mr-1"></i>
                                                        Switch to Cash
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Step 2: Collateral & Guarantors -->
                        @if ($step === 2)
                            <div class="space-y-6">
                                <!-- Collateral Information -->
                                <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                                    <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
                                        <i class="fas fa-landmark mr-2"></i>
                                        1. Collateral Information
                                    </h3>

                                    <div class="space-y-4">
                                        <!-- Collateral Value -->
                                        <div>
                                            <label for="collateralValue"
                                                class="block text-sm font-medium text-gray-700">
                                                Collateral Value (GHS)
                                            </label>
                                            <div class="mt-1 relative rounded-md shadow-sm">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 sm:text-sm">GHS</span>
                                                </div>
                                                <input type="number" wire:model="collateralValue"
                                                    id="collateralValue" step="100" min="0"
                                                    class="block w-full pl-12 pr-12 py-3 text-base border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                    placeholder="0.00">
                                            </div>
                                            @if ($collateralValue && $amount)
                                                <div
                                                    class="mt-2 text-sm {{ $collateralValue >= $amount ? 'text-green-600' : 'text-yellow-600' }}">
                                                    <i
                                                        class="fas fa-{{ $collateralValue >= $amount ? 'check-circle' : 'exclamation-triangle' }} mr-1"></i>
                                                    Collateral covers
                                                    {{ number_format(($collateralValue / $amount) * 100, 1) }}% of loan
                                                    amount
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Collateral Details -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Collateral Type
                                            </label>
                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                                @foreach ([
        'land' => ['icon' => 'mountain', 'label' => 'Land'],
        'building' => ['icon' => 'building', 'label' => 'Building'],
        'vehicle' => ['icon' => 'car', 'label' => 'Vehicle'],
        'equipment' => ['icon' => 'tools', 'label' => 'Equipment'],
        'savings' => ['icon' => 'piggy-bank', 'label' => 'Savings'],
        'investment' => ['icon' => 'chart-line', 'label' => 'Investment'],
        'guarantee' => ['icon' => 'handshake', 'label' => 'Guarantee'],
        'other' => ['icon' => 'boxes', 'label' => 'Other'],
    ] as $type => $data)
                                                    <button type="button"
                                                        wire:click="
                                                            @if (in_array($type, $collateralDetails)) $wire.set('collateralDetails', array_filter($collateralDetails, fn($item) => $item !== '{{ $type }}'))
                                                            @else
                                                                $wire.set('collateralDetails', [...$collateralDetails, '{{ $type }}']) @endif
                                                        "
                                                        class="p-3 border rounded-lg flex flex-col items-center justify-center transition-all duration-200
                                                        {{ in_array($type, $collateralDetails) ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' : 'border-gray-300 hover:border-blue-300 hover:bg-blue-50' }}">
                                                        <div
                                                            class="w-8 h-8 rounded-full flex items-center justify-center mb-1
                                                            {{ in_array($type, $collateralDetails) ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600' }}">
                                                            <i class="fas fa-{{ $data['icon'] }}"></i>
                                                        </div>
                                                        <span class="text-xs font-medium">{{ $data['label'] }}</span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>

                                        <!-- Collateral Description -->
                                        <div>
                                            <label for="collateralDescription"
                                                class="block text-sm font-medium text-gray-700">
                                                Collateral Description
                                            </label>
                                            <textarea wire:model="collateralDescription" id="collateralDescription" rows="2"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                placeholder="Describe the collateral (location, details, etc.)..."></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Guarantors -->
                                <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                                    <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
                                        <i class="fas fa-users mr-2"></i>
                                        2. Guarantors
                                    </h3>

                                    <!-- Add Guarantor Form -->
                                    <div class="mb-6 bg-white rounded-lg border border-gray-200 p-4">
                                        <h4 class="text-sm font-medium text-gray-900 mb-3">Add Guarantor</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                            <div>
                                                <input type="text" wire:model="newGuarantor.name"
                                                    class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                    placeholder="Full Name">
                                            </div>
                                            <div>
                                                <input type="text" wire:model="newGuarantor.relationship"
                                                    class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                    placeholder="Relationship">
                                            </div>
                                            <div class="flex space-x-2">
                                                <input type="text" wire:model="newGuarantor.phone"
                                                    class="flex-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                    placeholder="Phone Number">
                                                <button type="button" wire:click="addGuarantor"
                                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Guarantors List -->
                                    <div class="space-y-3">
                                        @if (!empty($guarantors))
                                            @foreach ($guarantors as $index => $guarantor)
                                                <div class="bg-white rounded-lg border border-gray-200 p-3">
                                                    <div class="flex items-center justify-between">
                                                        <div>
                                                            <div class="flex items-center">
                                                                <div
                                                                    class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                                                    <i class="fas fa-user text-blue-600"></i>
                                                                </div>
                                                                <div>
                                                                    <p class="text-sm font-medium text-gray-900">
                                                                        {{ $guarantor['name'] }}</p>
                                                                    <p class="text-xs text-gray-500">
                                                                        {{ $guarantor['relationship'] }} •
                                                                        {{ $guarantor['phone'] }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <button type="button"
                                                            wire:click="removeGuarantor({{ $index }})"
                                                            class="text-red-600 hover:text-red-800">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div
                                                class="text-center py-4 border-2 border-dashed border-gray-300 rounded-lg">
                                                <i class="fas fa-users text-gray-400 text-2xl mb-2"></i>
                                                <p class="text-sm text-gray-500">No guarantors added yet</p>
                                                <p class="text-xs text-gray-400 mt-1">Add at least one guarantor for
                                                    better approval chances</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Fees -->
                                <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                                    <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
                                        <i class="fas fa-file-invoice-dollar mr-2"></i>
                                        3. Fees & Charges
                                    </h3>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="processingFee"
                                                class="block text-sm font-medium text-gray-700">
                                                Processing Fee (GHS)
                                            </label>
                                            <div class="mt-1 relative rounded-md shadow-sm">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 sm:text-sm">GHS</span>
                                                </div>
                                                <input type="number" wire:model="processingFee" id="processingFee"
                                                    step="0.01" min="0"
                                                    class="block w-full pl-12 pr-12 py-3 text-base border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                    placeholder="0.00">
                                            </div>
                                        </div>

                                        <div>
                                            <label for="insuranceFee" class="block text-sm font-medium text-gray-700">
                                                Insurance Fee (GHS)
                                            </label>
                                            <div class="mt-1 relative rounded-md shadow-sm">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 sm:text-sm">GHS</span>
                                                </div>
                                                <input type="number" wire:model="insuranceFee" id="insuranceFee"
                                                    step="0.01" min="0"
                                                    class="block w-full pl-12 pr-12 py-3 text-base border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                    placeholder="0.00">
                                            </div>
                                        </div>
                                    </div>

                                    @if ($processingFee || $insuranceFee)
                                        <div class="mt-4 bg-white rounded-lg border border-gray-200 p-3">
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm font-medium text-gray-700">Total Fees:</span>
                                                <span class="text-lg font-bold text-blue-600">
                                                    GHS {{ number_format($processingFee + $insuranceFee, 2) }}
                                                </span>
                                            </div>
                                            <div class="mt-2 text-xs text-gray-500">
                                                These fees will be added to the total loan amount
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Step 3: Documentation & Review -->
                        @if ($step === 3)
                            <div class="space-y-6">
                                <!-- Required Documents -->
                                <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                                    <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
                                        <i class="fas fa-file-upload mr-2"></i>
                                        1. Required Documents
                                    </h3>

                                    <div class="space-y-4">
                                        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                                            <div class="flex">
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-exclamation-triangle text-yellow-500"></i>
                                                </div>
                                                <div class="ml-3">
                                                    <h4 class="text-sm font-medium text-yellow-800">Document Checklist
                                                    </h4>
                                                    <div class="mt-2 text-sm text-yellow-700">
                                                        <p>The following documents are required for loan processing:</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Document Upload -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            @foreach ([
        'application_form' => 'Application Form',
        'id_copy' => 'ID Copy',
        'proof_of_income' => 'Proof of Income',
        'bank_statements' => 'Bank Statements',
        'collateral_docs' => 'Collateral Documents',
        'guarantor_docs' => 'Guarantor Documents',
    ] as $doc => $label)
                                                <div class="bg-white border border-gray-300 rounded-lg p-4">
                                                    <div class="flex items-center justify-between mb-2">
                                                        <span
                                                            class="text-sm font-medium text-gray-900">{{ $label }}</span>
                                                        <span
                                                            class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-800">Required</span>
                                                    </div>
                                                    <div class="mt-2">
                                                        <input type="file"
                                                            wire:model="attachments.{{ $doc }}"
                                                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                                    </div>
                                                    @error('attachments.' . $doc)
                                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            @endforeach
                                        </div>

                                        <!-- Additional Documents -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Additional Documents (Optional)
                                            </label>
                                            <div class="flex items-center space-x-2">
                                                <input type="file" wire:model="additionalAttachments" multiple
                                                    class="flex-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dates & Timeline -->
                                <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                                    <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
                                        <i class="far fa-calendar-alt mr-2"></i>
                                        2. Timeline
                                    </h3>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Application Date -->
                                        <div>
                                            <label for="applicationDate"
                                                class="block text-sm font-medium text-gray-700">
                                                Application Date <span class="text-red-500">*</span>
                                            </label>
                                            <input type="date" wire:model="applicationDate" id="applicationDate"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            @error('applicationDate')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <!-- Proposed Start Date -->
                                        <div>
                                            <label for="startDate" class="block text-sm font-medium text-gray-700">
                                                Proposed Start Date <span class="text-red-500">*</span>
                                            </label>
                                            <input type="date" wire:model="startDate" id="startDate"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            @error('startDate')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                            @if ($startDate)
                                                <p class="mt-1 text-xs text-gray-500">
                                                    Loan will mature on
                                                    {{ \Carbon\Carbon::parse($startDate)->addMonths($termMonths)->format('F j, Y') }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Notes -->
                                <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                                    <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
                                        <i class="fas fa-sticky-note mr-2"></i>
                                        3. Additional Notes
                                    </h3>

                                    <div>
                                        <label for="additionalNotes" class="block text-sm font-medium text-gray-700">
                                            Notes for Committee Review
                                        </label>
                                        <textarea wire:model="additionalNotes" id="additionalNotes" rows="4"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            placeholder="Add any additional information for the committee review..."></textarea>
                                        <p class="mt-1 text-xs text-gray-500">
                                            These notes will be visible to the loan committee during review
                                        </p>
                                    </div>
                                </div>

                                <!-- Summary Preview -->
                                <div class="bg-green-50 border border-green-100 rounded-lg p-4">
                                    <h3 class="text-lg font-semibold text-green-900 mb-4 flex items-center">
                                        <i class="fas fa-chart-bar mr-2"></i>
                                        4. Application Summary
                                    </h3>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="bg-white p-4 rounded-lg border">
                                            <div class="text-xs text-gray-500 uppercase">Loan Amount</div>
                                            <div class="text-2xl font-bold text-blue-600">
                                                GHS {{ number_format($amount, 2) }}
                                            </div>
                                        </div>
                                        <div class="bg-white p-4 rounded-lg border">
                                            <div class="text-xs text-gray-500 uppercase">Monthly Payment</div>
                                            <div class="text-2xl font-bold text-green-600">
                                                GHS {{ number_format($monthlyPayment, 2) }}
                                            </div>
                                        </div>
                                        <div class="bg-white p-4 rounded-lg border">
                                            <div class="text-xs text-gray-500 uppercase">Total Payable</div>
                                            <div class="text-2xl font-bold text-purple-600">
                                                GHS {{ number_format($totalAmount, 2) }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 pt-4 border-t border-green-200">
                                        <div class="flex items-center text-green-700">
                                            <i class="fas fa-check-circle mr-2"></i>
                                            <span class="text-sm font-medium">Application is ready for review</span>
                                        </div>
                                        <p class="mt-1 text-sm text-green-600">
                                            All required information has been provided. Proceed to final review.
                                        </p>
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
                                <a href="{{ route('loans.index') }}"
                                    class="px-6 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                    <i class="fas fa-times mr-2"></i>
                                    Cancel Application
                                </a>
                            @endif

                            <button type="button" wire:click="nextStep" wire:loading.attr="disabled"
                                class="px-6 py-3 bg-blue-600 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition-all duration-200">
                                @if ($step < $totalSteps)
                                    Continue to Next Step
                                    <i class="fas fa-arrow-right ml-2"></i>
                                @else
                                    Review Application
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
                                    Processing Application
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Please wait while we process your loan application. This may take a few moments.
                                    </p>
                                    <div class="mt-4">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-blue-600 h-2.5 rounded-full animate-pulse"
                                                style="width: 80%"></div>
                                        </div>
                                    </div>
                                    <p class="mt-2 text-xs text-gray-500">
                                        Creating application, generating documents, and preparing for committee
                                        review...
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
            // Format amount inputs on blur
            const amountInputs = document.querySelectorAll('input[type="number"]');
            amountInputs.forEach(input => {
                input.addEventListener('blur', function(e) {
                    let value = parseFloat(e.target.value);
                    if (!isNaN(value) && value >= 0) {
                        e.target.value = value.toFixed(2);
                        // Trigger Livewire update
                        this.dispatchEvent(new Event('input', {
                            bubbles: true
                        }));
                    }
                });

                // Prevent invalid characters
                input.addEventListener('keypress', function(e) {
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
            });

            // Livewire event listeners
            Livewire.on('validation-failed', (message) => {
                // Show toast notification for validation errors
                showToast(message, 'error');
            });

            // Auto-scroll to top when step changes
            Livewire.on('step-changed', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });

            // Toast notification function
            function showToast(message, type = 'info') {
                const event = new CustomEvent('showToast', {
                    detail: {
                        message: message,
                        type: type
                    }
                });
                window.dispatchEvent(event);
            }
        });

        // Close search results when clicking outside
        document.addEventListener('click', function(event) {
            const customerSearch = document.getElementById('customerSearch');
            const searchResults = document.querySelector('.search-results-container');

            if (customerSearch && searchResults &&
                !customerSearch.contains(event.target) &&
                !searchResults.contains(event.target)) {
                Livewire.dispatch('close-search-results');
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Close search results when clicking outside
            document.addEventListener('click', function(event) {
                const searchContainer = document.getElementById('customerSearch');
                const searchResults = document.querySelector('.search-results-container');

                if (searchContainer && !searchContainer.contains(event.target)) {
                    // Check if click is outside search results dropdown
                    const dropdown = document.querySelector('[wire\\:key^="customer-"]');
                    if (dropdown && !dropdown.contains(event.target)) {
                        Livewire.dispatch('close-search-results');
                    }
                }
            });

            // Handle escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    Livewire.dispatch('clear-search');
                }
            });
        });
    </script>
@endpush
