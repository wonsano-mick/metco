<div>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">
                            <i class="fas fa-chart-pie mr-2 text-blue-600"></i>
                            Reports & Analytics
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">
                            Generate and view comprehensive reports for your banking operations
                        </p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <!-- Date Range Selector -->
                        <div class="relative">
                            <select 
                                wire:model.live="dateRange" 
                                class="rounded-md w-full pl-10 pr-10 py-3 text-base border-gray-300 rounded-l-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            >
                                <option value="today">Today</option>
                                <option value="yesterday">Yesterday</option>
                                <option value="this_week">This Week</option>
                                <option value="last_week">Last Week</option>
                                <option value="this_month">This Month</option>
                                <option value="last_month">Last Month</option>
                                <option value="this_quarter">This Quarter</option>
                                <option value="this_year">This Year</option>
                                <option value="last_year">Last Year</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>
                        
                        <!-- Export Button -->
                        <button 
                            wire:click="exportReport"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            <i class="fas fa-download mr-2"></i>
                            Export
                        </button>
                    </div>
                </div>
                
                <!-- Custom Date Range -->
                @if($dateRange === 'custom')
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input 
                                type="date" 
                                wire:model.live="startDate"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <input 
                                type="date" 
                                wire:model.live="endDate"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Stats Cards -->
        @if($activeReport === 'dashboard')
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                            <i class="fas fa-university text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Accounts</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ number_format($totalAccounts) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                            <i class="fas fa-users text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Customers</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ number_format($totalCustomers) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                            <i class="fas fa-exchange-alt text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Transactions</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ number_format($totalTransactions) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                            <i class="fas fa-money-bill-wave text-yellow-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Volume</p>
                            <p class="text-2xl font-semibold text-gray-900">GHS {{ number_format($totalVolume, 2) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-100 rounded-lg p-3">
                            <i class="fas fa-user-tie text-red-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Active Users</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ number_format($activeUsers) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Sidebar - Report Types -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-lg">
                    <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                        <h3 class="font-semibold text-gray-700">
                            <i class="fas fa-file-alt mr-2 text-blue-600"></i>
                            Report Types
                        </h3>
                    </div>
                    <div class="p-2">
                        <nav class="space-y-1">
                            @foreach($reportTypes as $key => $label)
                                <button 
                                    wire:click="$set('activeReport', '{{ $key }}')"
                                    class="w-full text-left px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 ease-in-out
                                        {{ $activeReport === $key 
                                            ? 'bg-blue-50 text-blue-700 border-l-4 border-blue-500' 
                                            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
                                >
                                    @switch($key)
                                        @case('dashboard')
                                            <i class="fas fa-tachometer-alt w-5 h-5 mr-3 inline-block text-gray-400"></i>
                                            @break
                                        @case('account_statement')
                                            <i class="fas fa-file-invoice w-5 h-5 mr-3 inline-block text-gray-400"></i>
                                            @break
                                        @case('transaction_report')
                                            <i class="fas fa-list w-5 h-5 mr-3 inline-block text-gray-400"></i>
                                            @break
                                        @case('customer_report')
                                            <i class="fas fa-users w-5 h-5 mr-3 inline-block text-gray-400"></i>
                                            @break
                                        @case('branch_report')
                                            <i class="fas fa-building w-5 h-5 mr-3 inline-block text-gray-400"></i>
                                            @break
                                        @case('daily_summary')
                                            <i class="fas fa-calendar-day w-5 h-5 mr-3 inline-block text-gray-400"></i>
                                            @break
                                        @case('monthly_summary')
                                            <i class="fas fa-calendar-alt w-5 h-5 mr-3 inline-block text-gray-400"></i>
                                            @break
                                        @case('audit_trail')
                                            <i class="fas fa-history w-5 h-5 mr-3 inline-block text-gray-400"></i>
                                            @break
                                        @case('revenue_report')
                                            <i class="fas fa-chart-line w-5 h-5 mr-3 inline-block text-gray-400"></i>
                                            @break
                                        @case('account_analysis')
                                            <i class="fas fa-chart-bar w-5 h-5 mr-3 inline-block text-gray-400"></i>
                                            @break
                                        @default
                                            <i class="fas fa-file w-5 h-5 mr-3 inline-block text-gray-400"></i>
                                    @endswitch
                                    {{ $label }}
                                </button>
                            @endforeach
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="lg:col-span-3">
                @switch($activeReport)
                    @case('dashboard')
                        <!-- Dashboard Overview -->
                        <div class="space-y-6">
                            <!-- Stats Cards -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-white rounded-lg shadow-lg p-4">
                                    <h4 class="text-sm font-medium text-gray-500 mb-2">Period Summary</h4>
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600">Total Transactions</span>
                                            <span class="font-semibold">{{ number_format($dashboardStats['total_transactions'] ?? 0) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600">Total Volume</span>
                                            <span class="font-semibold text-green-600">GHS {{ number_format($dashboardStats['total_volume'] ?? 0, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600">Average Transaction</span>
                                            <span class="font-semibold">GHS {{ number_format($dashboardStats['avg_transaction'] ?? 0, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600">Total Fees</span>
                                            <span class="font-semibold">GHS {{ number_format($dashboardStats['total_fees'] ?? 0, 2) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white rounded-lg shadow-lg p-4">
                                    <h4 class="text-sm font-medium text-gray-500 mb-2">Transaction Types</h4>
                                    <div class="space-y-2">
                                        @foreach(($dashboardStats['by_type'] ?? []) as $type => $stats)
                                            <div class="flex justify-between items-center text-sm">
                                                <span class="text-gray-600 capitalize">{{ str_replace('_', ' ', $type) }}</span>
                                                <div>
                                                    <span class="font-medium">{{ number_format($stats['count']) }}</span>
                                                    <span class="text-gray-400 ml-2">GHS {{ number_format($stats['total'], 2) }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Chart -->
                            @if(!empty($chartData['labels']))
                                <div class="bg-white rounded-lg shadow-lg p-4">
                                    <h4 class="text-sm font-medium text-gray-500 mb-4">Transaction Volume Trend</h4>
                                    <div class="h-64">
                                        <canvas id="transactionChart" 
                                                wire:ignore
                                                x-data="{
                                                    chart: null,
                                                    init() {
                                                        const ctx = document.getElementById('transactionChart').getContext('2d');
                                                        this.chart = new Chart(ctx, {
                                                            type: 'line',
                                                            data: {
                                                                labels: {{ json_encode($chartData['labels']) }},
                                                                datasets: [
                                                                    {
                                                                        label: 'Credits (In)',
                                                                        data: {{ json_encode($chartData['credits']) }},
                                                                        borderColor: '#10b981',
                                                                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                                                        tension: 0.4
                                                                    },
                                                                    {
                                                                        label: 'Debits (Out)',
                                                                        data: {{ json_encode($chartData['debits']) }},
                                                                        borderColor: '#ef4444',
                                                                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                                                        tension: 0.4
                                                                    }
                                                                ]
                                                            },
                                                            options: {
                                                                responsive: true,
                                                                maintainAspectRatio: false,
                                                                plugins: {
                                                                    legend: {
                                                                        position: 'bottom'
                                                                    }
                                                                },
                                                                scales: {
                                                                    y: {
                                                                        beginAtZero: true,
                                                                        ticks: {
                                                                            callback: function(value) {
                                                                                return 'GHS ' + value.toLocaleString();
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        });
                                                    }
                                                }"></canvas>
                                    </div>
                                </div>
                            @endif

                            <!-- Recent Transactions -->
                            <div class="bg-white rounded-lg shadow-lg">
                                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                                    <h4 class="font-semibold text-gray-700">Recent Transactions</h4>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Date</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Reference</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Customer</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Type</th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Amount</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @forelse($recentTransactions as $transaction)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-2 text-sm text-gray-500">
                                                        {{ $transaction->initiated_at->format('d M H:i') }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm font-mono text-gray-600">
                                                        {{ $transaction->transaction_reference }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm">
                                                        @if($transaction->sourceAccount)
                                                            {{ $transaction->sourceAccount->customer->full_name ?? 'N/A' }}
                                                        @elseif($transaction->destinationAccount)
                                                            {{ $transaction->destinationAccount->customer->full_name ?? 'N/A' }}
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-2 text-sm">
                                                        <span class="px-2 py-1 text-xs font-medium rounded-full
                                                            @if(in_array($transaction->type, ['deposit', 'cash_deposit'])) bg-green-100 text-green-800
                                                            @elseif(in_array($transaction->type, ['withdrawal', 'cash_withdrawal'])) bg-red-100 text-red-800
                                                            @elseif($transaction->type == 'transfer') bg-blue-100 text-blue-800
                                                            @else bg-gray-100 text-gray-800 @endif">
                                                            {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-right font-medium">
                                                        GHS {{ number_format($transaction->amount, 2) }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm">
                                                        <span class="px-2 py-1 text-xs font-medium rounded-full
                                                            @if($transaction->status === 'completed') bg-green-100 text-green-800
                                                            @elseif($transaction->status === 'pending') bg-yellow-100 text-yellow-800
                                                            @elseif($transaction->status === 'failed') bg-red-100 text-red-800
                                                            @else bg-gray-100 text-gray-800 @endif">
                                                            {{ ucfirst($transaction->status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                                        No recent transactions
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @break

                    @case('account_statement')
                        <!-- Account Statement Generator -->
                        <div class="bg-white rounded-lg shadow-lg">
                            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                                <h3 class="font-semibold text-gray-700">
                                    <i class="fas fa-file-invoice mr-2 text-blue-600"></i>
                                    Generate Account Statement
                                </h3>
                            </div>
                            
                            <div class="p-6">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Search Account
                                    </label>
                                    <div class="relative">
                                        <input 
                                            type="text" 
                                            wire:model.live.debounce.300ms="searchAccount"
                                            placeholder="Enter account number or customer name..."
                                            class="w-full pl-10 pr-10 py-3 text-base border-gray-300 rounded-l-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        
                                        @if($selectedAccount)
                                            <button 
                                                wire:click="clearSelectedAccount"
                                                class="absolute right-2 top-2 text-gray-400 hover:text-gray-600"
                                            >
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                    
                                    <!-- Search Results -->
                                    @if(!empty($accounts) && !$selectedAccount)
                                        <div class="mt-1 absolute z-50 bg-white shadow-lg max-h-96 overflow-y-auto rounded-lg border border-gray-300 search-results-container">
                                            @foreach($accounts as $account)
                                                <button wire:click="selectAccount({{ $account->id }})"
                                                    class="w-full text-left px-4 py-3 focus:outline-none focus:bg-blue-50 hover:bg-blue-50">
                                                    <div class="flex justify-between items-center">
                                                        <div class="flex-shrink-0">
                                                                        <div
                                                                            class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                                            <i class="fas fa-wallet text-blue-600"></i>
                                                                        </div>
                                                                    </div>
                                                        <div>
                                                            <span class="font-medium ml-4">{{ $account->account_number }}</span>
                                                            <span class="text-sm text-gray-500 ml-2">{{ ucwords($account->customer->full_name) }}</span>
                                                        </div>
                                                        <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded-full ml-4">
                                                            {{ $account->currency }} {{ number_format($account->current_balance, 2) }}
                                                        </span>
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 ml-4">
                                                            <i class="fas fa-check-circle mr-1"></i> 
                                                            Select 
                                                        </span>
                                                    </div>
                                                    <div class="text-xs text-gray-400 mt-1">
                                                        {{ $account->accountType->name ?? 'N/A' }} • Opened: {{ $account->opened_at->format('d M Y') }}
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Selected Account Info -->
                                @if($selectedAccount)
                                    <div class="bg-blue-50 rounded-lg p-4 mb-4">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <h4 class="font-medium text-blue-900">Selected Account</h4>
                                                <p class="text-sm text-blue-700 mt-1">
                                                    <span class="font-medium">{{ $selectedAccount->account_number }}</span> - 
                                                    {{ ucwords($selectedAccount->customer->full_name) }}
                                                </p>
                                                <div class="grid grid-cols-2 gap-4 mt-3">
                                                    <div>
                                                        <p class="text-xs text-blue-600">Account Type</p>
                                                        <p class="text-sm font-medium">{{ $selectedAccount->accountType->name ?? 'N/A' }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs text-blue-600">Current Balance</p>
                                                        <p class="text-sm font-medium">{{ $selectedAccount->currency }} {{ number_format($selectedAccount->current_balance, 2) }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs text-blue-600">Opened Date</p>
                                                        <p class="text-sm">{{ $selectedAccount->opened_at->format('d M Y') }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs text-blue-600">Status</p>
                                                        <p class="text-sm capitalize">{{ $selectedAccount->status }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex justify-end">
                                        <button 
                                            wire:click="generateStatement"
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                        >
                                            <i class="fas fa-file-pdf mr-2"></i>
                                            Generate Statement
                                        </button>
                                    </div>
                                @else
                                    <div class="text-center py-8 text-gray-500">
                                        <i class="fas fa-search text-4xl mb-3 text-gray-300"></i>
                                        <p>Search for an account to generate a statement</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @break

                    @case('transaction_report')
                        <!-- Transaction Report -->
                        <div class="bg-white rounded-lg shadow-lg">
                            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                                <h3 class="font-semibold text-gray-700">
                                    <i class="fas fa-list mr-2 text-blue-600"></i>
                                    Transaction Report
                                </h3>
                            </div>
                            
                            <div class="p-4">
                                <!-- Filters -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Type</label>
                                        <select wire:model.live="selectedTransactionType" class="w-full pl-10 pr-10 py-3 text-base border-gray-300 rounded-l-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="">All Types</option>
                                            @foreach($transactionTypes as $type)
                                                <option value="{{ $type }}">{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                        <select wire:model.live="selectedStatus" class="w-full pl-10 pr-10 py-3 text-base border-gray-300 rounded-l-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="">All Status</option>
                                            <option value="completed">Completed</option>
                                            <option value="pending">Pending</option>
                                            <option value="failed">Failed</option>
                                            <option value="reversed">Reversed</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Transactions Table -->
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Date</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Reference</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Description</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Type</th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Amount</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @forelse($this->transaction_report as $transaction)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-2 text-sm text-gray-500">
                                                        {{ $transaction->initiated_at->format('d M Y H:i') }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm font-mono text-gray-600">
                                                        {{ $transaction->transaction_reference }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm">
                                                        {{ $transaction->description }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm">
                                                        <span class="px-2 py-1 text-xs font-medium rounded-full
                                                            @if(in_array($transaction->type, ['deposit', 'cash_deposit'])) bg-green-100 text-green-800
                                                            @elseif(in_array($transaction->type, ['withdrawal', 'cash_withdrawal'])) bg-red-100 text-red-800
                                                            @elseif($transaction->type == 'transfer') bg-blue-100 text-blue-800
                                                            @else bg-gray-100 text-gray-800 @endif">
                                                            {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-right font-medium">
                                                        GHS {{ number_format($transaction->amount, 2) }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm">
                                                        <span class="px-2 py-1 text-xs font-medium rounded-full
                                                            @if($transaction->status === 'completed') bg-green-100 text-green-800
                                                            @elseif($transaction->status === 'pending') bg-yellow-100 text-yellow-800
                                                            @elseif($transaction->status === 'failed') bg-red-100 text-red-800
                                                            @else bg-gray-100 text-gray-800 @endif">
                                                            {{ ucfirst($transaction->status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                                        No transactions found
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Pagination -->
                                @if($this->transaction_report->hasPages())
                                    <div class="mt-4">
                                        {{ $this->transaction_report->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        @break

                    @case('customer_report')
                        <!-- Customer Report -->
                        <div class="bg-white rounded-lg shadow-lg">
                            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                                <h3 class="font-semibold text-gray-700">
                                    <i class="fas fa-users mr-2 text-blue-600"></i>
                                    Customer Report
                                </h3>
                            </div>
                            
                            <div class="p-4">
                                <!-- Filters -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                                        <select wire:model.live="selectedBranch" class="w-full pl-10 pr-10 py-3 text-base border-gray-300 rounded-l-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="">All Branches</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Customers Table -->
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Customer</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Customer #</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Branch</th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Accounts</th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Total Balance</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">KYC Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @forelse($this->customer_report as $customer)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-2">
                                                        <div class="flex items-center">
                                                            <div class="flex-shrink-0 h-8 w-8 bg-gray-200 rounded-full overflow-hidden">
                                                                @if($customer->profile_photo_path)
                                                                    <img src="{{ $customer->profile_photo_url }}" alt="" class="h-8 w-8 object-cover">
                                                                @else
                                                                    <div class="h-8 w-8 bg-blue-100 flex items-center justify-center">
                                                                        <span class="text-blue-600 font-medium text-sm">
                                                                            {{ substr($customer->first_name, 0, 1) }}{{ substr($customer->last_name, 0, 1) }}
                                                                        </span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="ml-3">
                                                                <p class="text-sm font-medium text-gray-900">{{ $customer->full_name }}</p>
                                                                <p class="text-xs text-gray-500">{{ $customer->email }}</p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-gray-500">
                                                        {{ $customer->customer_number }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-gray-500">
                                                        {{ $customer->branch->name ?? 'N/A' }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-right">
                                                        {{ $customer->accounts_count }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-right font-medium">
                                                        GHS {{ number_format($customer->accounts_sum_current_balance ?? 0, 2) }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm">
                                                        <span class="px-2 py-1 text-xs font-medium rounded-full
                                                            @if($customer->kyc_status === 'verified') bg-green-100 text-green-800
                                                            @elseif($customer->kyc_status === 'pending') bg-yellow-100 text-yellow-800
                                                            @elseif($customer->kyc_status === 'rejected') bg-red-100 text-red-800
                                                            @else bg-gray-100 text-gray-800 @endif">
                                                            {{ ucfirst($customer->kyc_status ?? 'pending') }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                                        No customers found
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Pagination -->
                                @if($this->customer_report->hasPages())
                                    <div class="mt-4">
                                        {{ $this->customer_report->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        @break

                    @default
                        <!-- Coming Soon -->
                        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                            <i class="fas fa-tools text-5xl text-gray-300 mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Report Coming Soon</h3>
                            <p class="text-gray-500">
                                The {{ $reportTypes[$activeReport] }} feature is currently under development.
                            </p>
                        </div>
                @endswitch
            </div>
        </div>

        <!-- Loading Indicator -->
        <div wire:loading class="fixed bottom-4 right-4 bg-blue-600 text-white px-4 py-2 rounded-lg shadow-lg z-50">
            <i class="fas fa-spinner fa-spin mr-2"></i>
            Loading...
        </div>
    </div>

    <!-- Chart.js Script -->
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('notify', (data) => {
                // You can integrate with your notification system here
                alert(data.message);
            });
        });
    </script>
    @endpush
</div>