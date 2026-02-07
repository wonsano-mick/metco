<div>
    <div class="max-w-6xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-xl overflow-hidden">
            <!-- Header -->
            <div class="px-8 py-6 border-b border-gray-200 bg-gradient-to-r from-blue-600 to-blue-800 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold">Create New Account</h2>
                        <p class="text-blue-100 mt-1">Open a new bank account for individual or organizational customers
                        </p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('accounts.index') }}"
                            class="inline-flex items-center px-4 py-2 border border-blue-400 rounded-lg text-sm font-medium text-white bg-blue-700/30 hover:bg-blue-700/50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-arrow-left mr-2"></i> 
                            Back to Accounts
                        </a>
                    </div>
                </div>
            </div>

            <!-- Customer Type Selection -->
            <div class="px-8 py-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-blue-50">
                <div class="flex items-center space-x-4">
                    @foreach ([1 => 'Customer Type', 2 => 'Select Customer', 3 => 'Account Details', 4 => 'Review & Create'] as $step => $label)
                        <button type="button" wire:click="changeStep({{ $step }})"
                            class="flex items-center group" {{ $step > $currentStep ? 'disabled' : '' }}>
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-full 
                    {{ $step < $currentStep ? 'bg-green-500 text-white' : '' }}
                    {{ $step == $currentStep ? 'bg-blue-600 text-white ring-2 ring-blue-300' : '' }}
                    {{ $step > $currentStep ? 'bg-gray-200 text-gray-600' : '' }} 
                    font-bold transition-all duration-200 group-hover:scale-110">
                                @if ($step < $currentStep)
                                    <i class="fas fa-check text-xs"></i>
                                @else
                                    {{ $step }}
                                @endif
                            </span>
                            <span
                                class="ml-2 font-medium 
                    {{ $step <= $currentStep ? 'text-gray-900' : 'text-gray-600' }}
                    group-hover:text-blue-600">
                                {{ $label }}
                            </span>
                        </button>

                        @if ($step < 4)
                            <div
                                class="flex-1 h-px 
                    {{ $step < $currentStep ? 'bg-green-500' : '' }}
                    {{ $step == $currentStep - 1 ? 'bg-blue-300' : '' }}
                    {{ $step >= $currentStep ? 'bg-gray-300' : '' }} 
                    transition-all duration-300">
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Main Form -->
            <form wire:submit.prevent="save" class="p-8">
                @if ($customer_id && !$selectedCustomer)
    <!-- Loading State -->
    <div class="mb-6 p-6 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex items-center justify-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            <div class="ml-4">
                <h3 class="text-lg font-medium text-blue-900">Loading Customer Details</h3>
                <p class="text-blue-700">Please wait while we load the customer information...</p>
            </div>
        </div>
    </div>
@endif


                <!-- Step 1: Customer Type Selection -->
                <div class="mb-10">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">1. Select Customer Type</h3>
                            <p class="text-sm text-gray-600 mt-1">Choose whether you're creating an account for an
                                individual or an organization</p>
                        </div>
                        @if ($customer_type)
                            <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                                <i class="fas fa-check mr-1"></i> Selected
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Individual Customer Type -->
                        <button type="button" wire:click="$set('customer_type', 'individual')"
                            class="p-6 border rounded-xl text-left transition-all duration-200
                    {{ $customer_type === 'individual'
                        ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200'
                        : 'border-gray-300 hover:border-blue-300 hover:bg-blue-50' }}">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                                        <i class="fas fa-user text-blue-600 text-xl"></i>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h4 class="font-semibold text-gray-900 text-lg">Individual Account</h4>
                                    <p class="text-gray-600 mt-2">For personal banking customers. Create accounts for
                                        individual persons with personal identification.</p>
                                    <div class="mt-4 space-y-2">
                                        <div class="flex items-center text-sm text-gray-500">
                                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                            Personal identification required
                                        </div>
                                        <div class="flex items-center text-sm text-gray-500">
                                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                            Simplified KYC process
                                        </div>
                                        <div class="flex items-center text-sm text-gray-500">
                                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                            Personal credit limits apply
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </button>

                        <!-- Organizational Customer Type -->
                        <button type="button" wire:click="$set('customer_type', 'organization')"
                            class="p-6 border rounded-xl text-left transition-all duration-200
                    {{ $customer_type === 'organization'
                        ? 'border-purple-500 bg-purple-50 ring-2 ring-purple-200'
                        : 'border-gray-300 hover:border-purple-300 hover:bg-purple-50' }}">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
                                        <i class="fas fa-building text-purple-600 text-xl"></i>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h4 class="font-semibold text-gray-900 text-lg">Organizational Account</h4>
                                    <p class="text-gray-600 mt-2">For businesses, companies, NGOs, and other
                                        organizations. Requires business registration documents.</p>
                                    <div class="mt-4 space-y-2">
                                        <div class="flex items-center text-sm text-gray-500">
                                            <i class="fas fa-check-circle text-purple-500 mr-2"></i>
                                            Business registration required
                                        </div>
                                        <div class="flex items-center text-sm text-gray-500">
                                            <i class="fas fa-check-circle text-purple-500 mr-2"></i>
                                            Enhanced KYC for organizations
                                        </div>
                                        <div class="flex items-center text-sm text-gray-500">
                                            <i class="fas fa-check-circle text-purple-500 mr-2"></i>
                                            Higher transaction limits
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </button>
                    </div>

                    @error('customer_type')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Step 2: Customer Selection (shown only when customer type is selected) -->
                @if ($currentStep >= 2 && $customer_type && (!$customer_id || $selectedCustomer))
                    <div class="mb-10">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">2. Select {{ ucfirst($customer_type) }}
                                    Customer</h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    @if ($customer_type === 'individual')
                                        Search and select an individual customer from your branch
                                    @else
                                        Search and select an organization/company customer
                                    @endif
                                </p>
                            </div>
                            @if ($customer_id)
                                <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                                    <i class="fas fa-check mr-1"></i> Selected
                                </span>
                            @endif
                        </div>

                        @if (!$customer_id)
                            <!-- Customer Search -->
                            <div class="mb-6">
                                <div class="mb-4">
                                    <div class="relative">
                                        <input type="text" wire:model.live.debounce.300ms="customerSearch"
                                            placeholder="{{ $customer_type === 'individual' ? 'Search individual customers by name, customer number, or ID...' : 'Search organizations by name, registration number, or contact...' }}"
                                            class="pl-12 pr-8 block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base"
                                            autofocus>
                                        <div
                                            class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <i class="fas fa-search text-gray-400"></i>
                                        </div>
                                        @if ($customerSearch)
                                            <button type="button" wire:click="$set('customerSearch', '')"
                                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                    @if (empty($customerSearch))
                                        <p class="mt-2 text-sm text-gray-500">
                                            @if ($customer_type === 'individual')
                                                Showing all eligible individual customers. Start typing to search.
                                            @else
                                                Showing all registered organizations. Start typing to search.
                                            @endif
                                        </p>
                                    @endif
                                </div>

                                <!-- Customer List -->
                                <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-lg shadow-inner">
                                    @php
                                        $customerList = $this->customers;
                                    @endphp
                                    @if (is_array($customerList) && count($customerList) > 0)
                                        <div class="divide-y divide-gray-200">
                                            @foreach ($customerList as $customer)
                                                <button type="button"
                                                    wire:click="selectCustomer('{{ $customer['id'] }}')"
                                                    class="w-full text-left p-4 hover:bg-blue-50 transition-colors duration-150 {{ $customer['is_selectable'] ? 'hover:bg-blue-50' : 'opacity-50 cursor-not-allowed' }}"
                                                    @if (!$customer['is_selectable']) disabled @endif>
                                                    <div class="flex items-center">
                                                        <!-- Customer/Oganization Avatar -->
                                                        <div class="flex-shrink-0">
                                                            @if ($customer_type === 'individual')
                                                                <img class="h-14 w-14 rounded-full object-cover border-2 border-gray-200"
                                                                    src="{{ $customer['profile_photo_url'] }}"
                                                                    alt="{{ $customer['full_name'] }}">
                                                            @else
                                                                <div
                                                                    class="h-14 w-14 rounded-full bg-purple-100 flex items-center justify-center border-2 border-purple-200">
                                                                    <i
                                                                        class="fas fa-building text-purple-600 text-xl"></i>
                                                                </div>
                                                            @endif
                                                        </div>

                                                        <!-- Customer/Oganization Info -->
                                                        <div class="ml-4 flex-1">
                                                            <div class="flex justify-between items-start">
                                                                <div>
                                                                    <p class="text-sm font-semibold text-gray-900">
                                                                        {{ $customer['full_name'] ?? $customer['name'] }}
                                                                        @if (!$customer['is_eligible'])
                                                                            <span
                                                                                class="ml-2 px-2 py-1 text-xs bg-red-100 text-red-800 rounded">
                                                                                Not Eligible
                                                                            </span>
                                                                        @endif
                                                                        @if ($customer_type === 'organization')
                                                                            <span
                                                                                class="ml-2 px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded">
                                                                                Organization
                                                                            </span>
                                                                        @endif
                                                                    </p>
                                                                    <p class="text-sm text-gray-500 mt-1">
                                                                        {{ $customer['email'] }} •
                                                                        {{ $customer['phone'] }}
                                                                    </p>
                                                                    @if ($customer_type === 'organization')
                                                                        <p class="text-xs text-gray-500 mt-1">
                                                                            <i class="fas fa-id-card mr-1"></i>
                                                                            {{ $customer['registration_number'] ?? 'Not specified' }}
                                                                        </p>
                                                                    @endif
                                                                </div>
                                                                <div class="text-right">
                                                                    <p class="text-sm font-medium text-gray-900">
                                                                        {{ $customer['customer_number'] }}
                                                                    </p>
                                                                    <p class="text-xs text-gray-500">
                                                                        {{ $customer['existing_accounts'] }} accounts
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div class="mt-3 flex items-center text-sm text-gray-500">
                                                                <i class="fas fa-building mr-1"></i>
                                                                {{ $customer['branch_name'] }}
                                                                <span class="mx-2">•</span>
                                                                <i class="fas fa-wallet mr-1"></i>
                                                                Total:
                                                                {{ number_format($customer['total_balance'], 2) }}
                                                                @if ($customer['kyc_status'] ?? false)
                                                                    <span class="mx-2">•</span>
                                                                    <span
                                                                        class="px-2 py-1 rounded text-xs font-medium {{ $customer['kyc_status'] === 'verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                                        {{ ucfirst($customer['kyc_status']) }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="p-10 text-center">
                                            @if ($customerSearch)
                                                <i class="fas fa-search text-5xl text-gray-300 mb-4"></i>
                                                <p class="text-gray-500 text-lg">No {{ $customer_type }} customers
                                                    found for "{{ $customerSearch }}"</p>
                                                <p class="text-sm text-gray-400 mt-1">Try a different search term or
                                                    check your filters</p>
                                            @else
                                                @if ($customer_type === 'individual')
                                                    <i class="fas fa-users text-5xl text-gray-300 mb-4"></i>
                                                    <p class="text-gray-500 text-lg">No eligible individual customers
                                                        found</p>
                                                    <p class="text-sm text-gray-400 mt-1">All eligible customers in
                                                        your branch are listed here</p>
                                                @else
                                                    <i class="fas fa-building text-5xl text-gray-300 mb-4"></i>
                                                    <p class="text-gray-500 text-lg">No registered organizations found
                                                    </p>
                                                    <p class="text-sm text-gray-400 mt-1">Register organizations before
                                                        creating accounts for them</p>
                                                @endif
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Selected Customer Display -->
                        @if (!empty($selectedCustomer))
                            <div
                                class="{{ $customer_type === 'individual' ? 'bg-blue-50 border border-blue-200' : 'bg-purple-50 border border-purple-200' }} rounded-xl p-6">
                                <div class="flex items-start">
                                    <!-- Customer/Oganization Avatar -->
                                    <div class="flex-shrink-0">
                                        @if ($customer_type === 'individual')
                                            <img class="h-20 w-20 rounded-full object-cover border-4 border-white shadow"
                                                src="{{ $selectedCustomer['profile_photo_url'] ?? $this->getDefaultProfilePhoto($selectedCustomer['full_name'] ?? 'Customer') }}"
                                                alt="{{ $selectedCustomer['full_name'] ?? 'Customer' }}">
                                        @else
                                            <div
                                                class="h-20 w-20 rounded-full bg-purple-100 flex items-center justify-center border-4 border-white shadow">
                                                <i class="fas fa-building text-purple-600 text-3xl"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Customer Details -->
                                    <div class="ml-6 flex-1">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h4 class="text-xl font-bold text-gray-900">
                                                    {{ $selectedCustomer['full_name'] ?? ($selectedCustomer['name'] ?? 'Unknown Customer') }}
                                                    @if ($customer_type === 'organization')
                                                        <span class="ml-2 text-sm font-normal text-gray-600">
                                                            ({{ $selectedCustomer['organization_type'] ?? 'Organization' }})
                                                        </span>
                                                    @endif
                                                </h4>
                                                <p class="text-sm text-gray-500 mt-1">
                                                    {{ $selectedCustomer['customer_number'] ?? 'N/A' }}
                                                </p>
                                            </div>
                                            <button type="button" wire:click="clearCustomerSelection"
                                                class="text-red-600 hover:text-red-800 p-2 rounded-full hover:bg-red-50">
                                                <i class="fas fa-times text-lg"></i>
                                            </button>
                                        </div>

                                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                            <div>
                                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Email</p>
                                                <p class="text-sm font-medium text-gray-900 mt-1">
                                                    {{ $selectedCustomer['email'] ?? 'N/A' }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Phone</p>
                                                <p class="text-sm font-medium text-gray-900 mt-1">
                                                    {{ $selectedCustomer['phone'] ?? 'N/A' }}
                                                </p>
                                            </div>
                                            @if ($customer_type === 'individual')
                                                <div>
                                                    <p
                                                        class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Age</p>
                                                    <p class="text-sm font-medium text-gray-900 mt-1">
                                                        {{ $selectedCustomer['age'] ?? 'N/A' }} years
                                                    </p>
                                                </div>
                                            @else
                                                <div>
                                                    <p
                                                        class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Registration</p>
                                                    <p class="text-sm font-medium text-gray-900 mt-1">
                                                        {{ $selectedCustomer['registration_number'] ?? 'N/A' }}
                                                    </p>
                                                </div>
                                            @endif
                                            <div>
                                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    KYC Status</p>
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded text-xs font-medium mt-1 {{ ($selectedCustomer['kyc_status'] ?? '') === 'verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                    {{ ucfirst($selectedCustomer['kyc_status'] ?? 'pending') }}
                                                </span>
                                            </div>
                                        </div>

                                        @if ($customer_type === 'organization')
                                            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div>
                                                    <p
                                                        class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Organization Type</p>
                                                    <p class="text-sm font-medium text-gray-900 mt-1">
                                                        {{ $selectedCustomer['organization_type'] ?? 'N/A' }}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p
                                                        class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Industry</p>
                                                    <p class="text-sm font-medium text-gray-900 mt-1">
                                                        {{ $selectedCustomer['industry'] ?? 'N/A' }}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p
                                                        class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Contact Person</p>
                                                    <p class="text-sm font-medium text-gray-900 mt-1">
                                                        {{ $selectedCustomer['contact_person'] ?? 'N/A' }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Existing Accounts -->
                                        @if (($selectedCustomer['existing_accounts'] ?? 0) > 0)
                                            <div class="mt-6 pt-6 border-t border-gray-200">
                                                <div class="flex items-center mb-3">
                                                    <i class="fas fa-wallet text-gray-400 mr-2"></i>
                                                    <p class="text-sm font-medium text-gray-700">
                                                        Existing Accounts
                                                        ({{ $selectedCustomer['existing_accounts'] ?? 0 }})
                                                    </p>
                                                </div>
                                                <div class="space-y-3">
                                                    @foreach ($selectedCustomer['accounts'] ?? [] as $account)
                                                        <div
                                                            class="flex justify-between items-center p-3 bg-white rounded-lg border border-gray-200">
                                                            <div class="flex items-center">
                                                                <div
                                                                    class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                                                    <i class="fas fa-wallet text-blue-600 text-sm"></i>
                                                                </div>
                                                                <div>
                                                                    <p class="text-sm font-medium text-gray-900">
                                                                        {{ $account['account_number'] ?? 'N/A' }}</p>
                                                                    <p class="text-xs text-gray-500">
                                                                        {{ $account['type'] ?? 'N/A' }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="flex items-center space-x-4">
                                                                <span class="text-sm font-bold text-gray-900">
                                                                    {{ number_format($account['balance'] ?? 0, 2) }}
                                                                    {{ $account['currency'] ?? $currency }}
                                                                </span>
                                                                <span
                                                                    class="px-2 py-1 text-xs rounded {{ ($account['status'] ?? '') === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                                    {{ ucfirst($account['status'] ?? 'unknown') }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Step 3: Account Details (shown only when customer is selected) -->
                @if ($customer_id)
                    <div class="mb-10">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">3. Account Details</h3>
                                <p class="text-sm text-gray-600 mt-1">Configure the account settings and parameters</p>
                            </div>
                            @if ($account_type_id)
                                <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                                    <i class="fas fa-check mr-1"></i> Selected
                                </span>
                            @endif
                        </div>

                        <div class="space-y-8">
                            <!-- Account Type Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    Account Type *
                                </label>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach ($accountTypes as $type)
                                        @if ($customer_type === 'individual' && !($type['is_for_organizations'] ?? false))
                                            <button type="button"
                                                wire:click="$set('account_type_id', '{{ $type['id'] }}')"
                                                class="p-5 border rounded-xl text-left transition-all duration-200 hover:shadow-md
                                                    {{ $account_type_id == $type['id']
                                                        ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200'
                                                        : 'border-gray-300 hover:border-blue-300 hover:bg-blue-50' }}">
                                                <div class="flex items-start">
                                                    <div class="flex-shrink-0">
                                                        <div
                                                            class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                                            <i
                                                                class="fas {{ $type['icon'] ?? 'fa-wallet' }} text-blue-600"></i>
                                                        </div>
                                                    </div>
                                                    <div class="ml-3 flex-1">
                                                        <h4 class="font-semibold text-gray-900">{{ $type['name'] }}
                                                        </h4>
                                                        <p class="text-sm text-gray-500 mt-2">
                                                            {{ $type['description'] }}</p>
                                                        <div class="mt-4 flex items-center justify-between text-sm">
                                                            <span class="text-blue-600 font-medium">
                                                                <i class="fas fa-percentage mr-1"></i>
                                                                {{ number_format($type['interest_rate'], 2) }}%
                                                            </span>
                                                            <span class="text-gray-600">
                                                                <i class="fas fa-balance-scale mr-1"></i>
                                                                Min: {{ number_format($type['min_balance'], 2) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </button>
                                        @elseif ($customer_type === 'organization' && ($type['is_for_organizations'] ?? false))
                                            <button type="button"
                                                wire:click="$set('account_type_id', '{{ $type['id'] }}')"
                                                class="p-5 border rounded-xl text-left transition-all duration-200 hover:shadow-md
                                                    {{ $account_type_id == $type['id']
                                                        ? 'border-purple-500 bg-purple-50 ring-2 ring-purple-200'
                                                        : 'border-gray-300 hover:border-purple-300 hover:bg-purple-50' }}">
                                                <div class="flex items-start">
                                                    <div class="flex-shrink-0">
                                                        <div
                                                            class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                                                            <i
                                                                class="fas {{ $type['icon'] ?? 'fa-building' }} text-purple-600"></i>
                                                        </div>
                                                    </div>
                                                    <div class="ml-3 flex-1">
                                                        <h4 class="font-semibold text-gray-900">{{ $type['name'] }}
                                                        </h4>
                                                        <p class="text-sm text-gray-500 mt-2">
                                                            {{ $type['description'] }}</p>
                                                        <div class="mt-4 flex items-center justify-between text-sm">
                                                            <span class="text-purple-600 font-medium">
                                                                <i class="fas fa-percentage mr-1"></i>
                                                                {{ number_format($type['interest_rate'], 2) }}%
                                                            </span>
                                                            <span class="text-gray-600">
                                                                <i class="fas fa-balance-scale mr-1"></i>
                                                                Min: {{ number_format($type['min_balance'], 2) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </button>
                                        @endif
                                    @endforeach
                                </div>
                                @error('account_type_id')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Selected Account Type Details -->
                            @if ($selectedAccountType)
                                <div
                                    class="bg-gradient-to-r {{ $customer_type === 'individual' ? 'from-blue-50 to-blue-100' : 'from-purple-50 to-purple-100' }} border {{ $customer_type === 'individual' ? 'border-blue-200' : 'border-purple-200' }} rounded-xl p-6">
                                    <div class="flex justify-between items-center mb-4">
                                        <div>
                                            <h4 class="font-bold text-gray-900 text-lg">
                                                {{ $selectedAccountType['name'] }} Details</h4>
                                            <p class="text-sm text-gray-600 mt-1">{{ $selectedAccountType['code'] }}
                                            </p>
                                        </div>
                                        <span
                                            class="px-3 py-1 rounded-full text-sm font-medium bg-white {{ $customer_type === 'individual' ? 'text-blue-700 border border-blue-200' : 'text-purple-700 border border-purple-200' }}">
                                            {{ $customer_type === 'individual' ? 'Individual Account' : 'Organizational Account' }}
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Interest Rate</p>
                                            <p class="text-lg font-bold text-gray-900 mt-1">
                                                {{ number_format($selectedAccountType['interest_rate'], 2) }}%
                                            </p>
                                        </div>
                                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Minimum Balance</p>
                                            <p class="text-lg font-bold text-gray-900 mt-1">
                                                {{ number_format($selectedAccountType['min_balance'], 2) }}
                                            </p>
                                        </div>
                                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Maximum Balance</p>
                                            <p class="text-lg font-bold text-gray-900 mt-1">
                                                {{ $selectedAccountType['max_balance'] ? number_format($selectedAccountType['max_balance'], 2) : 'No Limit' }}
                                            </p>
                                        </div>
                                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status</p>
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium mt-1 bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Active
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Account Configuration Form -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Currency -->
                                <div>
                                    <label for="currency" class="block text-sm font-medium text-gray-700 mb-2">
                                        Currency *
                                    </label>
                                    <div class="relative">
                                        <select id="currency" wire:model="currency"
                                            class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none bg-white">
                                            @foreach ($currencies as $curr)
                                                <option value="{{ $curr }}">{{ $curr }} -
                                                    {{ $this->getCurrencyName($curr) }}</option>
                                            @endforeach
                                        </select>
                                        <div
                                            class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <i class="fas fa-chevron-down text-gray-400"></i>
                                        </div>
                                    </div>
                                    @error('currency')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Status -->
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                        Account Status *
                                    </label>
                                    <div class="relative">
                                        <select id="status" wire:model="status"
                                            class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none bg-white">
                                            @foreach ($statusOptions as $option)
                                                <option value="{{ $option['value'] }}">{{ $option['label'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div
                                            class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <i class="fas fa-chevron-down text-gray-400"></i>
                                        </div>
                                    </div>
                                    @error('status')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Initial Deposit -->
                                <div>
                                    <label for="initial_deposit" class="block text-sm font-medium text-gray-700 mb-2">
                                        Initial Deposit *
                                    </label>
                                    <div class="relative rounded-lg shadow-sm">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500">{{ $currencySymbol }}</span>
                                        </div>
                                        <input type="number" id="initial_deposit" wire:model.lazy="initial_deposit"
                                            step="0.01" min="0"
                                            class="block w-full pl-10 pr-12 border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="0.00">
                                        <div
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500">{{ $currency }}</span>
                                        </div>
                                    </div>
                                    @error('initial_deposit')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    @if ($selectedAccountType && $selectedAccountType['min_balance'] > 0)
                                        <p class="mt-2 text-xs text-gray-500">
                                            Minimum deposit:
                                            {{ number_format($selectedAccountType['min_balance'], 2) }}
                                            {{ $currency }}
                                        </p>
                                    @endif
                                </div>

                                <!-- Overdraft Limit -->
                                <div>
                                    <label for="overdraft_limit" class="block text-sm font-medium text-gray-700 mb-2">
                                        Overdraft Limit *
                                        <span class="text-xs text-gray-500 font-normal">(Maximum negative
                                            balance)</span>
                                    </label>
                                    <div class="relative rounded-lg shadow-sm">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500">{{ $currencySymbol }}</span>
                                        </div>
                                        <input type="number" id="overdraft_limit" wire:model.lazy="overdraft_limit"
                                            step="0.01" min="0"
                                            class="block w-full pl-10 pr-12 border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="0.00">
                                        <div
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500">{{ $currency }}</span>
                                        </div>
                                    </div>
                                    @error('overdraft_limit')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    @if ($customer_type === 'organization')
                                        <p class="mt-2 text-xs text-purple-600">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Organizational accounts may have higher overdraft limits
                                        </p>
                                    @endif
                                </div>

                                <!-- Additional Fields for Organizations -->
                                @if ($customer_type === 'organization')
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Account Signatories
                                            <span class="text-xs text-gray-500 font-normal">(Authorized persons for
                                                transactions)</span>
                                        </label>
                                        <div class="space-y-3">
                                            @foreach ($signatories as $index => $signatory)
                                                <div
                                                    class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                                    <input type="text"
                                                        wire:model="signatories.{{ $index }}.name"
                                                        placeholder="Full name"
                                                        class="flex-1 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                    <input type="email"
                                                        wire:model="signatories.{{ $index }}.email"
                                                        placeholder="Email"
                                                        class="flex-1 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                    <input type="text"
                                                        wire:model="signatories.{{ $index }}.phone"
                                                        placeholder="Phone"
                                                        class="flex-1 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                    <button type="button"
                                                        wire:click="removeSignatory({{ $index }})"
                                                        class="text-red-600 hover:text-red-800">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                            <button type="button" wire:click="addSignatory"
                                                class="inline-flex items-center px-4 py-2 border border-dashed border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                                <i class="fas fa-plus mr-2"></i>
                                                Add Signatory
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                <!-- Notes -->
                                <div class="md:col-span-2">
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                        Notes (Optional)
                                        <span class="text-xs text-gray-500 font-normal">(Internal notes about this
                                            account)</span>
                                    </label>
                                    <textarea id="notes" wire:model="notes" rows="4"
                                        class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Any special instructions, account purpose, or additional information..."></textarea>
                                    @error('notes')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Step 4: Review & Generate Account Number (shown only when account type is selected) -->
                @if ($account_type_id)
                    <div class="mb-10">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">4. Review & Create Account</h3>

                        <!-- Generated Account Number -->
                        <div
                            class="mb-8 bg-gradient-to-r from-gray-50 to-gray-100 border border-gray-300 rounded-xl p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600 uppercase tracking-wider">Generated
                                        Account Number</p>
                                    <p class="text-3xl font-bold text-gray-900 font-mono mt-2 tracking-wider">
                                        {{ $generatedAccountNumber }}
                                    </p>
                                    <p class="text-sm text-gray-500 mt-2">
                                        This unique account number will be assigned to the new {{ $customer_type }}
                                        account
                                    </p>
                                </div>
                                <div class="flex flex-col items-end space-y-3">
                                    <button type="button" wire:click="generateAccountNumber"
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <i class="fas fa-redo mr-2"></i>
                                        Regenerate
                                    </button>
                                    @if ($customer_type === 'organization')
                                        <span class="text-xs text-gray-500">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Organizational account numbers start with 'ORG'
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Summary Cards -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                            <!-- Customer Summary -->
                            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                                <div class="flex items-center mb-4">
                                    <div
                                        class="w-10 h-10 rounded-full {{ $customer_type === 'individual' ? 'bg-blue-100' : 'bg-purple-100' }} flex items-center justify-center mr-3">
                                        <i
                                            class="fas {{ $customer_type === 'individual' ? 'fa-user text-blue-600' : 'fa-building text-purple-600' }}"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900">{{ ucfirst($customer_type) }}
                                            Information</h4>
                                        <p class="text-sm text-gray-500">Customer details</p>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div class="flex items-center">
                                        @if ($customer_type === 'individual')
                                            <img class="h-12 w-12 rounded-full"
                                                src="{{ $selectedCustomer['profile_photo_url'] ?? $this->getDefaultProfilePhoto($selectedCustomer['full_name'] ?? 'Customer') }}"
                                                alt="{{ $selectedCustomer['full_name'] ?? 'Customer' }}">
                                        @else
                                            <div
                                                class="h-12 w-12 rounded-full bg-purple-100 flex items-center justify-center">
                                                <i class="fas fa-building text-purple-600"></i>
                                            </div>
                                        @endif
                                        <div class="ml-3">
                                            <p class="font-medium text-gray-900">
                                                {{ $selectedCustomer['full_name'] ?? ($selectedCustomer['name'] ?? 'Unknown Customer') }}
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                {{ $selectedCustomer['customer_number'] ?? 'N/A' }}</p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-xs text-gray-500">Email</p>
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $selectedCustomer['email'] ?? 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Phone</p>
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $selectedCustomer['phone'] ?? 'N/A' }}</p>
                                        </div>
                                    </div>

                                    @if ($customer_type === 'organization')
                                        <div class="pt-4 border-t border-gray-200">
                                            <p class="text-xs text-gray-500 mb-2">Organization Details</p>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <p class="text-xs text-gray-500">Type</p>
                                                    <p class="text-sm font-medium text-gray-900">
                                                        {{ $selectedCustomer['organization_type'] ?? 'N/A' }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-xs text-gray-500">Industry</p>
                                                    <p class="text-sm font-medium text-gray-900">
                                                        {{ $selectedCustomer['industry'] ?? 'N/A' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Account Summary -->
                            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                                <div class="flex items-center mb-4">
                                    <div
                                        class="w-10 h-10 rounded-full {{ $customer_type === 'individual' ? 'bg-blue-100' : 'bg-purple-100' }} flex items-center justify-center mr-3">
                                        <i
                                            class="fas fa-wallet {{ $customer_type === 'individual' ? 'text-blue-600' : 'text-purple-600' }}"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900">Account Details</h4>
                                        <p class="text-sm text-gray-500">Configuration summary</p>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <span class="text-sm text-gray-600">Account Type:</span>
                                        <span
                                            class="text-sm font-medium text-gray-900">{{ $selectedAccountType['name'] ?? 'Not Selected' }}</span>
                                    </div>
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <span class="text-sm text-gray-600">Currency:</span>
                                        <span class="text-sm font-medium text-gray-900">{{ $currency }}</span>
                                    </div>
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <span class="text-sm text-gray-600">Initial Deposit:</span>
                                        <span class="text-sm font-bold text-gray-900">
                                            {{ number_format($initial_deposit, 2) }} {{ $currency }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <span class="text-sm text-gray-600">Minimum Balance:</span>
                                        <span class="text-sm font-medium text-gray-900">
                                            {{ number_format($minimum_balance, 2) }} {{ $currency }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <span class="text-sm text-gray-600">Overdraft Limit:</span>
                                        <span class="text-sm font-medium text-gray-900">
                                            {{ number_format($overdraft_limit, 2) }} {{ $currency }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <span class="text-sm text-gray-600">Status:</span>
                                        <span
                                            class="text-sm font-medium text-gray-900 capitalize">{{ $status }}</span>
                                    </div>
                                    @if ($notes)
                                        <div class="pt-3">
                                            <p class="text-xs text-gray-500 mb-1">Notes:</p>
                                            <p class="text-sm text-gray-700 bg-gray-50 p-3 rounded-lg">
                                                {{ $notes }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Terms & Conditions -->
                        <div class="mt-8 pt-8 border-t border-gray-200">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-file-contract text-blue-500 text-xl mt-1"></i>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-sm font-semibold text-gray-900">Terms & Conditions</h4>
                                    <div class="mt-3 bg-gray-50 p-4 rounded-lg border border-gray-200">
                                        <div class="flex items-start mb-3">
                                            <input type="checkbox" id="terms" wire:model="termsAccepted"
                                                class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-1">
                                            <label for="terms" class="ml-2 block text-sm text-gray-900">
                                                I confirm that all information provided is accurate and complete.
                                                @if ($customer_type === 'individual')
                                                    The customer has provided valid identification and KYC documents.
                                                @else
                                                    The organization has provided valid registration documents and
                                                    authorized signatories.
                                                @endif
                                            </label>
                                        </div>
                                        @error('termsAccepted')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror

                                        @if ($customer_type === 'organization')
                                            <div class="flex items-start mt-3">
                                                <input type="checkbox" id="signatories_verified"
                                                    wire:model="signatoriesVerified"
                                                    class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500 mt-1">
                                                <label for="signatories_verified"
                                                    class="ml-2 block text-sm text-gray-900">
                                                    I verify that all authorized signatories have been properly
                                                    identified and documented.
                                                </label>
                                            </div>
                                            @error('signatoriesVerified')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-10 flex justify-between items-center pt-8 border-t border-gray-200">
                            <div>
                                <button type="button" wire:click="previousStep"
                                    class="inline-flex items-center px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="fas fa-arrow-left mr-2"></i>
                                    Previous Step
                                </button>
                            </div>
                            <div class="flex space-x-4">
                                <a href="{{ route('accounts.index') }}"
                                    class="inline-flex items-center px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Cancel
                                </a>
                                <button type="submit"
                                    class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg text-sm font-medium text-white {{ $customer_type === 'individual' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-purple-600 hover:bg-purple-700' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    Create {{ $customer_type === 'individual' ? 'Personal' : 'Organizational' }}
                                    Account
                                </button>
                            </div>
                        </div>
                    </div>
                @else
                    @if ($customer_id)
                        <!-- Prompt to select account type -->
                        <div class="text-center py-12">
                            <div
                                class="{{ $customer_type === 'individual' ? 'text-blue-400' : 'text-purple-400' }} mb-4">
                                <i class="fas fa-wallet text-6xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Select an Account Type</h3>
                            <p class="text-gray-500 mt-1">
                                Please select an account type suitable for
                                {{ $customer_type === 'individual' ? 'individual' : 'organizational' }} customers
                            </p>
                            <button type="button" wire:click="previousStep"
                                class="mt-6 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Back to Customer Selection
                            </button>
                        </div>
                    @endif
                @endif
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // Auto-format currency inputs
        document.addEventListener('DOMContentLoaded', function() {
            // Format currency inputs
            const currencyInputs = document.querySelectorAll('input[type="number"]');

            currencyInputs.forEach(input => {
                input.addEventListener('blur', function(e) {
                    if (this.value && !isNaN(this.value)) {
                        this.value = parseFloat(this.value).toFixed(2);
                    }
                });

                input.addEventListener('input', function(e) {
                    // Remove any non-numeric characters except decimal point
                    this.value = this.value.replace(/[^\d.]/g, '');

                    // Ensure only one decimal point
                    const decimalCount = (this.value.match(/\./g) || []).length;
                    if (decimalCount > 1) {
                        this.value = this.value.slice(0, -1);
                    }

                    // Limit to 2 decimal places
                    if (this.value.includes('.')) {
                        const parts = this.value.split('.');
                        if (parts[1].length > 2) {
                            this.value = parts[0] + '.' + parts[1].substring(0, 2);
                        }
                    }
                });
            });

            // Listen for Livewire events
            Livewire.on('update-url', (data) => {
                const url = new URL(window.location);

                if (data.customer_type) {
                    url.searchParams.set('customer_type', data.customer_type);
                } else {
                    url.searchParams.delete('customer_type');
                }

                if (data.customer_id) {
                    url.searchParams.set('customer_id', data.customer_id);
                } else {
                    url.searchParams.delete('customer_id');
                }

                // Update URL without reloading the page
                window.history.pushState({}, '', url.toString());
            });

            // Smooth scrolling for form sections
            Livewire.on('scroll-to-top', () => {
                // Get the form container
                const formContainer = document.querySelector('.max-w-6xl');
                if (formContainer) {
                    formContainer.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                } else {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                }
            });

            // Handle customer type selection
            document.addEventListener('livewire:initialized', () => {
                // Add click handlers for customer type buttons
                const individualBtn = document.querySelector('button[wire\\:click*="individual"]');
                const organizationBtn = document.querySelector('button[wire\\:click*="organization"]');

                if (individualBtn) {
                    individualBtn.addEventListener('click', function() {
                        // Force Livewire to process the click
                        setTimeout(() => {
                            Livewire.dispatch('refresh');
                        }, 50);
                    });
                }

                if (organizationBtn) {
                    organizationBtn.addEventListener('click', function() {
                        // Force Livewire to process the click
                        setTimeout(() => {
                            Livewire.dispatch('refresh');
                        }, 50);
                    });
                }
            });

            // Handle dynamic step progression
            Livewire.on('step-changed', (data) => {
                // Update progress indicators
                updateProgressIndicators(data.step);

                // Scroll to top
                setTimeout(() => {
                    Livewire.dispatch('scroll-to-top');
                }, 100);
            });

            // Update progress indicators
            function updateProgressIndicators(currentStep) {
                const steps = document.querySelectorAll('.flex.items-center.space-x-4 > div.flex.items-center');

                steps.forEach((step, index) => {
                    const stepNumber = index + 1;
                    const circle = step.querySelector('span:first-child');
                    const label = step.querySelector('span:last-child');
                    const connector = step.nextElementSibling?.classList?.contains('h-px') ?
                        step.nextElementSibling :
                        null;

                    if (stepNumber < currentStep) {
                        // Completed step
                        circle.classList.remove('bg-gray-200', 'text-gray-600');
                        circle.classList.add('bg-green-500', 'text-white');
                        circle.innerHTML = '<i class="fas fa-check text-xs"></i>';

                        if (label) {
                            label.classList.remove('text-gray-600');
                            label.classList.add('text-green-700', 'font-semibold');
                        }

                        if (connector) {
                            connector.classList.remove('bg-gray-300');
                            connector.classList.add('bg-green-500');
                        }
                    } else if (stepNumber === currentStep) {
                        // Current step
                        circle.classList.remove('bg-gray-200', 'text-gray-600');
                        circle.classList.add('bg-blue-600', 'text-white');
                        circle.innerHTML = stepNumber;

                        if (label) {
                            label.classList.remove('text-gray-600');
                            label.classList.add('text-blue-700', 'font-semibold');
                        }

                        if (connector) {
                            connector.classList.remove('bg-gray-300');
                            connector.classList.add('bg-blue-300');
                        }
                    } else if (stepNumber > currentStep) {
                        // Future step
                        circle.classList.remove('bg-blue-600', 'text-white', 'bg-green-500');
                        circle.classList.add('bg-gray-200', 'text-gray-600');
                        circle.innerHTML = stepNumber;

                        if (label) {
                            label.classList.remove('text-blue-700', 'text-green-700', 'font-semibold');
                            label.classList.add('text-gray-600');
                        }

                        if (connector) {
                            connector.classList.remove('bg-blue-300', 'bg-green-500');
                            connector.classList.add('bg-gray-300');
                        }
                    }
                });
            }

            // Account creation success
            Livewire.on('account-created', (data) => {
                // Show success notification if needed
                if (typeof toastr !== 'undefined') {
                    const message = data.customer_type === 'individual' ?
                        'Individual account created successfully!' :
                        'Organizational account created successfully!';
                    toastr.success(message);
                }
            });

            // Initialize progress indicators on page load
            document.addEventListener('livewire:navigated', () => {
                // Wait a bit for Livewire to settle
                setTimeout(() => {
                    // Get current step from URL or default to 1
                    const url = new URL(window.location);
                    const customerType = url.searchParams.get('customer_type');
                    const customerId = url.searchParams.get('customer_id');

                    let currentStep = 1;
                    if (customerId) {
                        currentStep = 3;
                    } else if (customerType) {
                        currentStep = 2;
                    }

                    updateProgressIndicators(currentStep);
                }, 200);
            });

            // Handle browser back/forward buttons
            window.addEventListener('popstate', function(event) {
                // Force Livewire to update based on URL
                setTimeout(() => {
                    Livewire.dispatch('refresh');
                }, 100);
            });
        });

        // Add a global refresh handler
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('refresh', () => {
                // This will trigger a Livewire re-render
            });
        });
    </script>
@endpush
