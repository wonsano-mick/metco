<div>
    <!-- Debug/Status Bar -->
    <div class="p-3 mb-4 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-blue-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <p class="text-sm font-medium text-blue-800">METCU Banking Dashboard</p>
                    <p class="text-xs text-blue-600">User: {{ $userEmail ?? 'Loading...' }} | Status: {{ $loading ? 'Loading' : 'Ready' }}</p>
                </div>
            </div>
            <button wire:click="refreshData" 
                    wire:loading.attr="disabled"
                    class="text-xs px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 disabled:opacity-50">
                <span wire:loading.remove>🔄 Refresh</span>
                <span wire:loading>🔄 Refreshing...</span>
            </button>
        </div>
    </div>

    <!-- Error State -->
    @if($error)
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Error Loading Dashboard</h3>
                <div class="mt-1 text-sm text-red-700">
                    <p>{{ $error }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Loading State -->
    @if($loading)
    <div class="min-h-[400px] flex items-center justify-center">
        <div class="text-center">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-gray-300 border-t-blue-500"></div>
            <p class="mt-4 text-gray-600">{{ $message }}</p>
        </div>
    </div>
    @else
    <!-- Main Dashboard Content -->
    <div class="space-y-6">
        <!-- Welcome Header -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2">Welcome back, {{ $userName }}!</h1>
                    <p class="text-blue-100 opacity-90">Here's your financial overview</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <span class="inline-flex items-center px-4 py-2 bg-white bg-opacity-20 rounded-full">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {{ now()->format('F j, Y') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Total Balance -->
            <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-lg bg-blue-50">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-green-600">+2.5%</span>
                </div>
                <p class="text-sm font-medium text-gray-500">Total Balance</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">${{ number_format($totalBalance, 2) }}</p>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-500">Across {{ count($accounts) }} accounts</p>
                </div>
            </div>

            <!-- Monthly Income -->
            <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-lg bg-green-50">
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <span class="text-xs text-gray-500">This Month</span>
                </div>
                <p class="text-sm font-medium text-gray-500">Monthly Income</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">${{ number_format($monthlyIncome, 2) }}</p>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-500">{{ now()->format('F Y') }}</p>
                </div>
            </div>

            <!-- Monthly Expenses -->
            <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-lg bg-red-50">
                        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                        </svg>
                    </div>
                    <span class="text-xs text-gray-500">This Month</span>
                </div>
                <p class="text-sm font-medium text-gray-500">Monthly Expenses</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">${{ number_format($monthlyExpenses, 2) }}</p>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-500">{{ now()->format('F Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Accounts Section -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Your Accounts</h2>
                    <p class="text-gray-500 text-sm">Manage your bank accounts</p>
                </div>
                <span class="text-sm text-gray-500">{{ count($accounts) }} accounts</span>
            </div>
            
            <div class="p-6">
                @if(count($accounts) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($accounts as $account)
                    <div class="border border-gray-200 rounded-lg p-5 hover:border-blue-300 transition-colors">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center mr-3">
                                    @if($account['account_type']['code'] === 'SAVINGS')
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    @else
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                    @endif
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900">{{ $account['account_type']['name'] }}</h4>
                                    <p class="text-sm text-gray-500">{{ $account['account_number'] }}</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ ucfirst($account['status']) }}
                            </span>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">Current Balance</span>
                                <span class="text-xl font-bold text-gray-900">${{ number_format($account['current_balance'], 2) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-500">Available</span>
                                <span class="text-green-600 font-medium">${{ number_format($account['available_balance'], 2) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-500">Currency</span>
                                <span class="font-medium">{{ $account['currency'] }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-12">
                    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">No Accounts Found</h4>
                    <p class="text-gray-600">You don't have any bank accounts yet.</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Recent Transactions</h2>
                    <p class="text-gray-500 text-sm">Latest account activity</p>
                </div>
                <span class="text-sm text-gray-500">{{ count($recentTransactions) }} transactions</span>
            </div>
            
            <div class="p-6">
                @if(count($recentTransactions) > 0)
                <div class="space-y-4">
                    @foreach($recentTransactions as $transaction)
                    <div class="flex items-center justify-between p-4 border border-gray-100 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center mr-4
                                {{ $transaction['type'] === 'credit' ? 'bg-green-100' : 'bg-red-100' }}">
                                @if($transaction['type'] === 'credit')
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                                @else
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                                </svg>
                                @endif
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $transaction['description'] }}</p>
                                <div class="flex items-center text-sm text-gray-500 mt-1">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>{{ \Carbon\Carbon::parse($transaction['created_at'])->format('M d, Y · h:i A') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold {{ $transaction['type'] === 'credit' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $transaction['type'] === 'credit' ? '+' : '-' }}${{ number_format($transaction['amount'], 2) }}
                            </p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-1
                                {{ $transaction['status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                                   ($transaction['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ ucfirst($transaction['status']) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-12">
                    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </div>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">No Recent Transactions</h4>
                    <p class="text-gray-600">Your transaction history will appear here.</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Footer Note -->
        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-blue-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <p class="text-sm text-blue-800">
                        Dashboard last updated: {{ now()->format('F j, Y \\a\\t g:i A') }}
                    </p>
                    <p class="text-xs text-blue-600 mt-1">
                        Data is securely loaded from your banking accounts.
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>