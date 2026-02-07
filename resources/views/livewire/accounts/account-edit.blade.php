<div>
    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Edit Account</h2>
                        <p class="text-sm text-gray-600 mt-1">Update account details for {{ $account->account_number }}</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('accounts.show', $account->id) }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-eye mr-2"></i>
                            View Account
                        </a>
                        <a href="{{ route('accounts.index') }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Accounts
                        </a>
                    </div>
                </div>
            </div>

            <!-- Account Summary -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Account Summary</h3>
                        <div class="mt-2 flex flex-wrap gap-4">
                            <div>
                                <span class="text-sm text-gray-600">Account Number:</span>
                                <span class="ml-2 text-sm font-medium text-gray-900 font-mono">{{ $account->account_number }}</span>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Opened:</span>
                                <span class="ml-2 text-sm font-medium text-gray-900">{{ $account->opened_at?->format('F d, Y') }}</span>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Last Activity:</span>
                                <span class="ml-2 text-sm font-medium text-gray-900">{{ $account->last_activity_at?->format('F d, Y') ?? 'Never' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <span class="px-3 py-1 rounded-full text-sm font-medium
                            {{ $account->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $account->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $account->status === 'dormant' ? 'bg-gray-100 text-gray-800' : '' }}
                            {{ $account->status === 'frozen' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $account->status === 'closed' ? 'bg-gray-100 text-gray-800' : '' }}
                            {{ $account->status === 'suspended' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ ucfirst($account->status) }}
                        </span>
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

                <!-- Customer Information (Readonly) -->
                <div class="mb-8">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">1. Customer Information</h3>
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                            <i class="fas fa-user mr-1"></i> Fixed
                        </span>
                    </div>

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
                                        <a href="{{-- route('customers.show', $selectedCustomer['id'] ?? '#') --}}"
                                            class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
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
                                                class="inline-flex items-center px-2 py-1 rounded text-xs font-medium 
                                    {{ ($selectedCustomer['kyc_status'] ?? '') === 'verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ ucfirst($selectedCustomer['kyc_status'] ?? 'pending') }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Existing Accounts -->
                                    @if (($selectedCustomer['existing_accounts'] ?? 0) > 0)
                                        <div class="mt-4">
                                            <p class="text-sm text-gray-600 mb-2">
                                                <i class="fas fa-wallet mr-1"></i>
                                                Customer's Accounts ({{ $selectedCustomer['existing_accounts'] ?? 0 }})
                                            </p>
                                            <div class="space-y-2">
                                                @foreach ($selectedCustomer['accounts'] ?? [] as $custAccount)
                                                    <div class="flex justify-between items-center text-sm">
                                                        <span class="text-gray-700">{{ $custAccount['account_number'] ?? 'N/A' }}</span>
                                                        <span class="text-gray-500">{{ $custAccount['type'] ?? 'N/A' }}</span>
                                                        <span class="font-medium">{{ number_format($custAccount['balance'] ?? 0, 2) }}</span>
                                                        <span
                                                            class="px-2 py-1 text-xs rounded 
                                                {{ ($custAccount['status'] ?? '') === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                            {{ ucfirst($custAccount['status'] ?? 'unknown') }}
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

                <!-- Account Details -->
                <div class="mb-8">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">2. Account Details</h3>
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

                        <!-- Current Balance -->
                        <div>
                            <label for="current_balance" class="block text-sm font-medium text-gray-700 mb-1">
                                Current Balance *
                            </label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    {{-- <span class="text-gray-500 sm:text-sm">$</span> --}}
                                </div>
                                <input type="number" id="current_balance" wire:model.lazy="current_balance"
                                    step="0.01"
                                    class="block w-full pl-7 pr-12 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="0.00">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">{{ $currency }}</span>
                                </div>
                            </div>
                            @error('current_balance')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Available Balance -->
                        <div>
                            <label for="available_balance" class="block text-sm font-medium text-gray-700 mb-1">
                                Available Balance *
                            </label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    {{-- <span class="text-gray-500 sm:text-sm">$</span> --}}
                                </div>
                                <input type="number" id="available_balance" wire:model.lazy="available_balance"
                                    step="0.01"
                                    class="block w-full pl-7 pr-12 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="0.00">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">{{ $currency }}</span>
                                </div>
                            </div>
                            @error('available_balance')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Ledger Balance -->
                        <div>
                            <label for="ledger_balance" class="block text-sm font-medium text-gray-700 mb-1">
                                Ledger Balance *
                            </label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    {{-- <span class="text-gray-500 sm:text-sm">$</span> --}}
                                </div>
                                <input type="number" id="ledger_balance" wire:model.lazy="ledger_balance"
                                    step="0.01"
                                    class="block w-full pl-7 pr-12 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="0.00">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">{{ $currency }}</span>
                                </div>
                            </div>
                            @error('ledger_balance')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Minimum Balance -->
                        <div>
                            <label for="minimum_balance" class="block text-sm font-medium text-gray-700 mb-1">
                                Minimum Balance *
                            </label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    {{-- <span class="text-gray-500 sm:text-sm">$</span> --}}
                                </div>
                                <input type="number" id="minimum_balance" wire:model.lazy="minimum_balance"
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
                        </div>

                        <!-- Overdraft Limit -->
                        <div>
                            <label for="overdraft_limit" class="block text-sm font-medium text-gray-700 mb-1">
                                Overdraft Limit *
                            </label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    {{-- <span class="text-gray-500 sm:text-sm">$</span> --}}
                                </div>
                                <input type="number" id="overdraft_limit" wire:model.lazy="overdraft_limit"
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
                        @if (auth()->user()->can('view all branches'))
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
                        @endif

                        <!-- Notes -->
                        <div class="md:col-span-2">
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

                <!-- Current State Summary -->
                <div class="mb-8 bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Current Account State</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-white p-3 rounded border">
                            <p class="text-xs text-gray-500">Current Balance</p>
                            <p class="text-lg font-bold text-gray-900">
                                {{ number_format($account->current_balance, 2) }} {{ $account->currency }}
                            </p>
                        </div>
                        <div class="bg-white p-3 rounded border">
                            <p class="text-xs text-gray-500">Available Balance</p>
                            <p class="text-lg font-bold text-gray-900">
                                {{ number_format($account->available_balance, 2) }} {{ $account->currency }}
                            </p>
                        </div>
                        <div class="bg-white p-3 rounded border">
                            <p class="text-xs text-gray-500">Ledger Balance</p>
                            <p class="text-lg font-bold text-gray-900">
                                {{ number_format($account->ledger_balance, 2) }} {{ $account->currency }}
                            </p>
                        </div>
                        <div class="bg-white p-3 rounded border">
                            <p class="text-xs text-gray-500">Overdraft Limit</p>
                            <p class="text-lg font-bold text-gray-900">
                                {{ number_format($account->overdraft_limit, 2) }} {{ $account->currency }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Terms & Conditions -->
                <div class="mt-6 pt-6 border-t border-blue-200">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-500 mt-1"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Warning:</strong> Changing account balances and status should be done with caution.
                                Ensure all changes are properly documented and authorized.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-8 flex justify-end space-x-4">
                    <a href="{{ route('accounts.show', $account->id) }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <button type="submit"
                        class="inline-flex items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-save mr-2"></i>
                        Update Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // Auto-format currency inputs
        document.addEventListener('DOMContentLoaded', function() {
            const currencyInputs = document.querySelectorAll(
                'input[type="number"][id*="balance"], input[type="number"][id*="limit"]'
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
        });
    </script>
@endpush