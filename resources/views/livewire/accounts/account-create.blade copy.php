<div>
    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Create New Account</h2>
                        <p class="text-sm text-gray-600 mt-1">Open a new bank account for a customer</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('accounts.index') }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Accounts
                        </a>
                    </div>
                </div>
            </div>

            <!-- Progress Steps -->
            <div class="mb-8 mt-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-600 text-white">
                            <span class="text-sm font-bold">1</span>
                        </div>
                        <span class="text-sm font-medium text-gray-900">Personal Information</span>
                    </div>
                    <div class="h-1 flex-1 bg-blue-200 mx-4"></div>
                    <div class="flex items-center space-x-2">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-600 text-white">
                            <span class="text-sm font-bold">2</span>
                        </div>
                        <span class="text-sm font-medium text-gray-900">Contact & Address</span>
                    </div>
                    <div class="h-1 flex-1 bg-blue-200 mx-4"></div>
                    <div class="flex items-center space-x-2">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-600 text-white">
                            <span class="text-sm font-bold">3</span>
                        </div>
                        <span class="text-sm font-medium text-gray-900">Employment & KYC</span>
                    </div>
                </div>
            </div>

            <!-- Main Form -->
            <form wire:submit.prevent="save" class="p-6">
                @if ($errors->has('general'))
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">
                                    Error
                                </h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <p>{{ $errors->first('general') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Step 1: Customer Selection -->
                <div class="mb-8">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">1. Select Customer</h3>
                        @if ($selectedCustomer)
                            <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                                <i class="fas fa-check mr-1"></i> Selected
                            </span>
                        @endif
                    </div>

                    @if (!$customer_id)
                        <!-- Customer Search and List -->
                        <div class="mb-6">
                            <div class="mb-4">
                                <div class="relative">
                                    <input type="text" wire:model.live.debounce.300ms="customerSearch"
                                        placeholder="Search customers by name, customer number, email, or phone..."
                                        class="pl-10 pr-8 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        autofocus>
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                    @if ($customerSearch)
                                        <button type="button" wire:click="$set('customerSearch', '')"
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                </div>
                                @if (empty($customerSearch))
                                    <p class="mt-2 text-sm text-gray-500">
                                        Showing all eligible customers. Start typing to search by name, number, email,
                                        or phone.
                                    </p>
                                @endif
                            </div>

                            <!-- Customer List -->
                            <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-lg">
                                @php
                                    $customerList = $this->customers; // Access the computed property
                                @endphp
                                @if (is_array($customerList) && count($customerList) > 0)
                                    <div class="divide-y divide-gray-200">
                                        @foreach ($customerList as $customer)
                                            <button type="button" wire:click="selectCustomer('{{ $customer['id'] }}')"
                                                class="w-full text-left p-4 hover:bg-blue-50 transition-colors {{ $customer['is_selectable'] ? 'hover:bg-blue-50' : 'opacity-50 cursor-not-allowed' }}"
                                                @if (!$customer['is_selectable']) disabled @endif>
                                                <div class="flex items-center">
                                                    <!-- Customer Photo -->
                                                    <div class="flex-shrink-0">
                                                        <img class="h-12 w-12 rounded-full object-cover"
                                                            src="{{ $customer['profile_photo_url'] }}"
                                                            alt="{{ $customer['full_name'] }}">
                                                    </div>

                                                 <!-- Customer Info -->
                                                    <div class="ml-4 flex-1">
                                                        <div class="flex justify-between items-start">
                                                            <div>
                                                                <p class="text-sm font-medium text-gray-900">
                                                                    {{ $customer['full_name'] }}
                                                                    @if (!$customer['is_eligible'])
                                                                        <span
                                                                            class="ml-2 px-2 py-1 text-xs bg-red-100 text-red-800 rounded">
                                                                            Not Eligible
                                                                        </span>
                                                                    @endif
                                                                </p>
                                                                <p class="text-sm text-gray-500">
                                                                    {{ $customer['email'] }} •
                                                                    {{ $customer['phone'] }}
                                                                </p>
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
                                                        <div class="mt-2 flex items-center text-sm text-gray-500">
                                                            <i class="fas fa-building mr-1"></i>
                                                            {{ $customer['branch_name'] }}
                                                            <span class="mx-2">•</span>
                                                            <i class="fas fa-wallet mr-1"></i>
                                                            Total: {{ number_format($customer['total_balance'], 2) }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="p-8 text-center">
                                        @if ($customerSearch)
                                            <i class="fas fa-search text-4xl text-gray-300 mb-4"></i>
                                            <p class="text-gray-500">No customers found for "{{ $customerSearch }}"</p>
                                            <p class="text-sm text-gray-400 mt-1">Try a different search term</p>
                                        @else
                                            <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                                            <p class="text-gray-500">No eligible customers found</p>
                                            <p class="text-sm text-gray-400 mt-1">All eligible customers in your branch
                                                are listed here</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Selected Customer Display -->
                    @if (!empty($selectedCustomer))
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <!-- Customer Photo -->
                                <div class="flex-shrink-0">
                                    <img class="h-16 w-16 rounded-full object-cover"
                                        src="{{ $selectedCustomer['profile_photo_url'] ?? $this->getDefaultProfilePhoto($selectedCustomer['full_name'] ?? 'Customer') }}"
                                        alt="{{ $selectedCustomer['full_name'] ?? 'Customer' }}">
                                </div>

                                <!-- Customer Details -->
                                <div class="ml-4 flex-1">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="text-lg font-medium text-gray-900">
                                                {{ $selectedCustomer['full_name'] ?? 'Unknown Customer' }}
                                            </h4>
                                            <p class="text-sm text-gray-500">
                                                {{ $selectedCustomer['customer_number'] ?? 'N/A' }}
                                            </p>
                                        </div>
                                        <button type="button" wire:click="clearCustomerSelection"
                                            class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>

                                    <div class="mt-3 grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-gray-600">Email</p>
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $selectedCustomer['email'] ?? 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Phone</p>
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $selectedCustomer['phone'] ?? 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Age</p>
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $selectedCustomer['age'] ?? 'N/A' }} years</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">KYC Status</p>
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ ($selectedCustomer['kyc_status'] ?? '') === 'verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ ucfirst($selectedCustomer['kyc_status'] ?? 'pending') }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Existing Accounts -->
                                    @if (($selectedCustomer['existing_accounts'] ?? 0) > 0)
                                        <div class="mt-4">
                                            <p class="text-sm text-gray-600 mb-2">
                                                <i class="fas fa-wallet mr-1"></i>
                                                Existing Accounts ({{ $selectedCustomer['existing_accounts'] ?? 0 }})
                                            </p>
                                            <div class="space-y-2">
                                                @foreach ($selectedCustomer['accounts'] ?? [] as $account)
                                                    <div class="flex justify-between items-center text-sm">
                                                        <span
                                                            class="text-gray-700">{{ $account['account_number'] ?? 'N/A' }}</span>
                                                        <span
                                                            class="text-gray-500">{{ $account['type'] ?? 'N/A' }}</span>
                                                        <span
                                                            class="font-medium">{{ number_format($account['balance'] ?? 0, 2) }}</span>
                                                        <span
                                                            class="px-2 py-1 text-xs rounded 
                                {{ ($account['status'] ?? '') === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                            {{ ucfirst($account['status'] ?? 'unknown') }}
                                                        </span>
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

                <!-- Step 2: Account Details (shown only when customer is selected) -->
                @if ($customer_id)
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">2. Account Details</h3>
                            @if ($account_type_id)
                                <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                                    <i class="fas fa-check mr-1"></i> Selected
                                </span>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Account Type -->
                            <div class="md:col-span-2">
                                <label for="account_type_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Account Type *
                                </label>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach ($accountTypes as $type)
                                        <button type="button"
                                            wire:click="$set('account_type_id', '{{ $type['id'] }}')"
                                            class="p-4 border rounded-lg text-left transition-all duration-200
                                {{ $account_type_id == $type['id']
                                    ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200'
                                    : 'border-gray-300 hover:border-blue-300 hover:bg-blue-50' }}">
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0">
                                                    <div
                                                        class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                                        <i class="fas fa-wallet text-blue-600"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-3 flex-1">
                                                    <h4 class="font-medium text-gray-900">{{ $type['name'] }}</h4>
                                                    <p class="text-sm text-gray-500 mt-1">{{ $type['description'] }}
                                                    </p>
                                                    <div class="mt-2 flex items-center text-xs text-gray-500">
                                                        <span class="mr-3">
                                                            <i class="fas fa-percentage mr-1"></i>
                                                            {{ number_format($type['interest_rate'], 2) }}%
                                                        </span>
                                                        <span>
                                                            <i class="fas fa-balance-scale mr-1"></i>
                                                            Min: {{ number_format($type['min_balance'], 2) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                                @error('account_type_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Selected Account Type Details -->
                            @if ($selectedAccountType)
                                <div class="md:col-span-2">
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                        <div class="flex justify-between items-center mb-3">
                                            <h4 class="font-medium text-gray-900">{{ $selectedAccountType['name'] }}
                                                Details</h4>
                                            <span
                                                class="text-sm text-gray-500">{{ $selectedAccountType['code'] }}</span>
                                        </div>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                            <div>
                                                <p class="text-xs text-gray-500">Interest Rate</p>
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ number_format($selectedAccountType['interest_rate'], 2) }}%
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500">Minimum Balance</p>
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ number_format($selectedAccountType['min_balance'], 2) }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500">Maximum Balance</p>
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $selectedAccountType['max_balance'] ? number_format($selectedAccountType['max_balance'], 2) : 'No Limit' }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500">Status</p>
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                                                    Active
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Currency -->
                            <div>
                                <label for="currency" class="block text-sm font-medium text-gray-700 mb-1">
                                    Currency *
                                </label>
                                <select id="currency" wire:model="currency"
                                    class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    @foreach ($currencies as $curr)
                                        <option value="{{ $curr }}">{{ $curr }}</option>
                                    @endforeach
                                </select>
                                @error('currency')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                    Account Status *
                                </label>
                                <select id="status" wire:model="status"
                                    class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    @foreach ($statusOptions as $option)
                                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Initial Deposit -->
                            <div>
                                <label for="initial_deposit" class="block text-sm font-medium text-gray-700 mb-1">
                                    Initial Deposit *
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        {{-- <span class="text-gray-500 sm:text-sm">$</span> --}}
                                    </div>
                                    <input type="number" id="initial_deposit" wire:model.lazy="initial_deposit"
                                        step="0.01"
                                        class="block w-full pl-7 pr-12 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="0.00">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">{{ $currency }}</span>
                                    </div>
                                </div>
                                @error('initial_deposit')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                @if ($selectedAccountType && $selectedAccountType['min_balance'] > 0)
                                    <p class="mt-1 text-xs text-gray-500">
                                        Minimum deposit: {{ number_format($selectedAccountType['min_balance'], 2) }}
                                    </p>
                                @endif
                            </div>

                            <!-- Minimum Balance -->
                            {{-- <div>
                                <label for="minimum_balance" class="block text-sm font-medium text-gray-700 mb-1">
                                    Minimum Balance *
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    </div>
                                    <input type="number" id="minimum_balance" wire:model="minimum_balance"
                                        step="0.01"
                                        class="block w-full pl-7 pr-12 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="0.00">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">{{ $currency }}</span>
                                    </div>
                                </div>
                                @error('minimum_balance')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div> --}}

                            <!-- Overdraft Limit -->
                            <div>
                                <label for="overdraft_limit" class="block text-sm font-medium text-gray-700 mb-1">
                                    Overdraft Limit *
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        {{-- <span class="text-gray-500 sm:text-sm">$</span> --}}
                                    </div>
                                    <input type="number" id="overdraft_limit" wire:model.live="overdraft_limit"
                                        step="0.01"
                                        class="block w-full pl-7 pr-12 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="0.00">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">{{ $currency }}</span>
                                    </div>
                                </div>
                                @error('overdraft_limit')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">
                                    Maximum amount customer can overdraw
                                </p>
                            </div>

                            <!-- Branch (if admin) -->
                            {{-- @if (auth()->user()->can('view all branches'))
                                <div>
                                    <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">
                                        Branch
                                    </label>
                                    <select id="branch_id" wire:model="branch_id"
                                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Branch</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif --}}

                            <!-- Notes -->
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                    Notes (Optional)
                                </label>
                                <textarea id="notes" wire:model="notes" rows="3"
                                    class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Any special instructions or notes about this account..."></textarea>
                                @error('notes')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Review & Generate Account Number -->
                    @if ($account_type_id)
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">3. Review & Create</h3>

                            <!-- Generated Account Number -->
                            <div class="mb-6 bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-gray-600">Generated Account Number</p>
                                        <p class="text-2xl font-bold text-gray-900 font-mono">
                                            {{ $generatedAccountNumber }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            This account number will be assigned to the new account
                                        </p>
                                    </div>
                                    <button type="button" wire:click="generateAccountNumber"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <i class="fas fa-redo mr-2"></i>
                                        Regenerate
                                    </button>
                                </div>
                            </div>

                            <!-- Customer Summary -->
                            @if ($selectedCustomer)
                                <div class="mb-6">
                                    <h5 class="text-sm font-medium text-gray-700 mb-3">Customer Information</h5>
                                    <div class="space-y-3">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <img class="h-10 w-10 rounded-full"
                                                    src="{{ $selectedCustomer['profile_photo_url'] ?? $this->getDefaultProfilePhoto($selectedCustomer['full_name'] ?? 'Customer') }}"
                                                    alt="{{ $selectedCustomer['full_name'] ?? 'Customer' }}">
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $selectedCustomer['full_name'] ?? 'Unknown Customer' }}
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    {{ $selectedCustomer['customer_number'] ?? 'N/A' }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-sm">
                                            <p class="text-gray-600">{{ $selectedCustomer['email'] ?? 'N/A' }}</p>
                                            <p class="text-gray-600">{{ $selectedCustomer['phone'] ?? 'N/A' }}</p>
                                            <p class="text-gray-600">{{ $selectedCustomer['address'] ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Account Summary -->
                            @if ($selectedAccountType)
                                <div class="mb-6">
                                    <h5 class="text-sm font-medium text-gray-700 mb-3">Account Details</h5>
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Account Type:</span>
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ $selectedAccountType['name'] ?? 'Not Selected' }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Account Type Code:</span>
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ $selectedAccountType['code'] ?? 'N/A' }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Interest Rate:</span>
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ number_format($selectedAccountType['interest_rate'] ?? 0, 2) }}%
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Currency:</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $currency }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Initial Deposit:</span>
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ number_format($initial_deposit, 2) }} {{ $currency }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Minimum Balance:</span>
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ number_format($minimum_balance, 2) }} {{ $currency }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Overdraft Limit:</span>
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ number_format($overdraft_limit, 2) }} {{ $currency }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Status:</span>
                                            <span
                                                class="text-sm font-medium text-gray-900 capitalize">{{ $status }}</span>
                                        </div>
                                        @if ($notes)
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-600">Notes:</span>
                                                <span
                                                    class="text-sm font-medium text-gray-900">{{ $notes }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Account Type Requirements -->
                                <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <h5 class="text-sm font-medium text-blue-800 mb-2">Account Type Requirements</h5>
                                    <div class="space-y-1 text-sm text-blue-700">
                                        <div class="flex justify-between">
                                            <span>Minimum Opening Balance:</span>
                                            <span
                                                class="font-medium">{{ number_format($selectedAccountType['min_balance'] ?? 0, 2) }}
                                                {{ $currency }}</span>
                                        </div>
                                        @if ($selectedAccountType['max_balance'] ?? null)
                                            <div class="flex justify-between">
                                                <span>Maximum Balance:</span>
                                                <span
                                                    class="font-medium">{{ number_format($selectedAccountType['max_balance'], 2) }}
                                                    {{ $currency }}</span>
                                            </div>
                                        @endif
                                        <div class="flex justify-between">
                                            <span>Annual Interest Rate:</span>
                                            <span
                                                class="font-medium">{{ number_format($selectedAccountType['interest_rate'] ?? 0, 2) }}%</span>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                        </div>
                                        <div class="ml-3">
                                            <h5 class="text-sm font-medium text-yellow-800">Account Type Required</h5>
                                            <div class="mt-2 text-sm text-yellow-700">
                                                <p>Please select an account type to see the complete account details and
                                                    proceed with account creation.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Terms & Conditions -->
                        <div class="mt-6 pt-6 border-t border-blue-200">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-blue-500 mt-1"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">
                                        By creating this account, you confirm that all information is accurate and
                                        the customer has provided valid identification and KYC documents.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-8 flex justify-end space-x-4">
                            <a href="{{ route('accounts.index') }}"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Cancel
                            </a>
                            <button type="submit"
                                class="inline-flex items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <i class="fas fa-check mr-2"></i>
                                Create Account
                            </button>
                        </div>
                    @else
                        <!-- Prompt to select account type -->
                        <div class="text-center py-12">
                            <div class="text-gray-400 mb-4">
                                <i class="fas fa-wallet text-6xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Select an Account Type</h3>
                            <p class="text-gray-500 mt-1">
                                Please select an account type to proceed with account creation
                            </p>
                        </div>
                    @endif
                @else
                    <!-- Prompt to select customer first -->
                    <div class="text-center py-12">
                        <div class="text-gray-400 mb-4">
                            <i class="fas fa-user-circle text-6xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900">Select a Customer First</h3>
                        <p class="text-gray-500 mt-1">
                            Please select a customer to proceed with account creation
                        </p>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // Auto-format currency inputs
        document.addEventListener('DOMContentLoaded', function() {
            const currencyInputs = document.querySelectorAll(
                'input[type="number"][id*="deposit"], input[type="number"][id*="balance"], input[type="number"][id*="limit"]'
            );

            currencyInputs.forEach(input => {
                input.addEventListener('blur', function(e) {
                    if (this.value) {
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

            // Listen for Livewire event to update URL
            Livewire.on('update-url', (data) => {
                const url = new URL(window.location);

                if (data.customer_id) {
                    url.searchParams.set('customer_id', data.customer_id);
                } else {
                    url.searchParams.delete('customer_id');
                }

                // Update URL without reloading the page
                window.history.pushState({}, '', url.toString());
            });
        });
    </script>
@endpush
