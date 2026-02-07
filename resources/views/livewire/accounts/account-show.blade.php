<div>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Account Details</h2>
                        <p class="text-sm text-gray-600 mt-1">Account Number: {{ $account->account_number }}</p>
                    </div>
                    <div class="flex space-x-3">
                        @can('update accounts')
                            <a href="{{ route('accounts.edit', $account->id) }}"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-edit mr-2"></i>
                                Edit Account
                            </a>
                        @endcan
                        <a href="{{ route('accounts.index') }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Accounts
                        </a>
                    </div>
                </div>
            </div>

            <!-- Account Details -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Account Information -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Account Information</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Account Number:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $account->account_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Account Type:</span>
                                <span
                                    class="text-sm font-medium text-gray-900">{{ $account->accountType->name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Currency:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $account->currency }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Status:</span>
                                <span class="text-sm font-medium text-gray-900 capitalize">{{ $account->status }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Opened Date:</span>
                                <span
                                    class="text-sm font-medium text-gray-900">{{ $account->opened_at?->format('Y-m-d') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Balance Information -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Balance Information</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Current Balance:</span>
                                <span
                                    class="text-sm font-medium text-gray-900">{{ number_format($account->current_balance, 2) }}
                                    {{ $account->currency }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Available Balance:</span>
                                <span
                                    class="text-sm font-medium text-gray-900">{{ number_format($account->available_balance, 2) }}
                                    {{ $account->currency }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Ledger Balance:</span>
                                <span
                                    class="text-sm font-medium text-gray-900">{{ number_format($account->ledger_balance, 2) }}
                                    {{ $account->currency }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Minimum Balance:</span>
                                <span
                                    class="text-sm font-medium text-gray-900">{{ number_format($account->minimum_balance, 2) }}
                                    {{ $account->currency }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Overdraft Limit:</span>
                                <span
                                    class="text-sm font-medium text-gray-900">{{ number_format($account->overdraft_limit, 2) }}
                                    {{ $account->currency }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="md:col-span-2 bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Information</h3>
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                @if ($account->customer->profile_photo_url)
                                    <img class="h-16 w-16 rounded-full object-cover"
                                        src="{{ $account->customer->profile_photo_url }}"
                                        alt="{{ $account->customer->full_name }}">
                                @else
                                    <div class="h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center">
                                        <span class="text-xl font-medium text-blue-600">
                                            {{ substr($account->customer->full_name, 0, 1) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900">{{ $account->customer->full_name }}</h4>
                                <p class="text-sm text-gray-500">{{ $account->customer->customer_number }}</p>
                                <div class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div>
                                        <p class="text-xs text-gray-500">Email</p>
                                        <p class="text-sm font-medium text-gray-900">{{ $account->customer->email }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Phone</p>
                                        <p class="text-sm font-medium text-gray-900">{{ $account->customer->phone }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">KYC Status</p>
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded text-xs font-medium 
                                            {{ $account->customer->kyc_status === 'verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ ucfirst($account->customer->kyc_status) }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Branch</p>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $account->customer->branch->name ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-8 flex justify-end space-x-4">
                    <a href="{{ route('accounts.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Back to Accounts
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
