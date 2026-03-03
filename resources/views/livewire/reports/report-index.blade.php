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
                            <select wire:model.live="dateRange"
                                class="rounded-md w-full pl-10 pr-10 py-3 text-base border-gray-300 rounded-l-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
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
                        <button wire:click="exportReport"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <i class="fas fa-download mr-2"></i>
                            Export
                        </button>
                    </div>
                </div>

                <!-- Custom Date Range -->
                @if ($dateRange === 'custom')
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input type="date" wire:model.live="startDate"
                                class="w-full pl-10 pr-10 py-3 text-base border-gray-300 rounded-l-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <input type="date" wire:model.live="endDate"
                                class="w-full pl-10 pr-10 py-3 text-base border-gray-300 rounded-l-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Stats Cards -->
        @if ($activeReport === 'dashboard')
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
                            @foreach ($reportTypes as $key => $label)
                                <button wire:click="$set('activeReport', '{{ $key }}')"
                                    class="w-full text-left px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150 ease-in-out
                                        {{ $activeReport === $key
                                            ? 'bg-blue-50 text-blue-700 border-l-4 border-blue-500'
                                            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
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
                                            <span
                                                class="font-semibold">{{ number_format($dashboardStats['total_transactions'] ?? 0) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600">Total Volume</span>
                                            <span class="font-semibold text-green-600">GHS
                                                {{ number_format($dashboardStats['total_volume'] ?? 0, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600">Average Transaction</span>
                                            <span class="font-semibold">GHS
                                                {{ number_format($dashboardStats['avg_transaction'] ?? 0, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600">Total Fees</span>
                                            <span class="font-semibold">GHS
                                                {{ number_format($dashboardStats['total_fees'] ?? 0, 2) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white rounded-lg shadow-lg p-4">
                                    <h4 class="text-sm font-medium text-gray-500 mb-2">Transaction Types</h4>
                                    <div class="space-y-2">
                                        @foreach ($dashboardStats['by_type'] ?? [] as $type => $stats)
                                            <div class="flex justify-between items-center text-sm">
                                                <span
                                                    class="text-gray-600 capitalize">{{ str_replace('_', ' ', $type) }}</span>
                                                <div>
                                                    <span class="font-medium">{{ number_format($stats['count']) }}</span>
                                                    <span class="text-gray-400 ml-2">GHS
                                                        {{ number_format($stats['total'], 2) }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Chart -->
                            @if (!empty($chartData['labels']))
                                <div class="bg-white rounded-lg shadow-lg p-4">
                                    <h4 class="text-sm font-medium text-gray-500 mb-4">Transaction Volume Trend</h4>
                                    <div class="h-64">
                                        <canvas id="transactionChart" wire:ignore x-data="{
                                            chart: null,
                                            init() {
                                                const ctx = document.getElementById('transactionChart').getContext('2d');
                                                this.chart = new Chart(ctx, {
                                                    type: 'line',
                                                    data: {
                                                        labels: {{ json_encode($chartData['labels']) }},
                                                        datasets: [{
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
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Reference
                                                </th>
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
                                                        @if ($transaction->sourceAccount)
                                                            {{ ucwords($transaction->sourceAccount->customer->full_name ?? 'N/A') }}
                                                        @elseif($transaction->destinationAccount)
                                                            {{ ucwords($transaction->destinationAccount->customer->full_name ?? 'N/A') }}
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-2 text-sm">
                                                        <span
                                                            class="px-2 py-1 text-xs font-medium rounded-full
                                                            @if (in_array($transaction->type, ['deposit', 'cash_deposit'])) bg-green-100 text-green-800
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
                                                        <span
                                                            class="px-2 py-1 text-xs font-medium rounded-full
                                                            @if ($transaction->status === 'completed') bg-green-100 text-green-800
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
                                        <input type="text" wire:model.live.debounce.300ms="searchAccount"
                                            placeholder="Enter account number or customer name..."
                                            class="w-full pl-10 pr-10 py-3 text-base border-gray-300 rounded-l-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" autofocus>

                                        @if ($selectedAccount)
                                            <button wire:click="clearSelectedAccount"
                                                class="absolute right-2 top-2 text-gray-400 hover:text-gray-600">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>

                                    <!-- Search Results -->
                                    @if (!empty($accounts) && !$selectedAccount)
                                        <div
                                            class="mt-1 absolute z-50 w-100 bg-white shadow-lg max-h-96 overflow-y-auto rounded-lg border border-gray-300 search-results-container">
                                            <ul class="divide-y divide-gray-200">
                                                @foreach ($accounts as $account)
                                                    <li> 
                                                        <button wire:click="selectAccount({{ $account->id }})"
                                                            class="text-left px-4 py-3 focus:outline-none focus:bg-blue-50 hover:bg-blue-50">
                                                            <div class="flex items-center">
                                                                <div class="flex-shrink-0">
                                                                    <div
                                                                        class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                                        <i class="fas fa-wallet text-blue-600"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="ml-4 flex-1">
                                                                    <div class="flex justify-between items-start">
                                                                        <div>
                                                                            <div class="text-sm font-medium text-gray-900">
                                                                                {{ $account->account_number }}
                                                                            </div>
                                                                            <div class="text-sm text-gray-600">
                                                                                {{ ucwords($account->customer->full_name) ?? 'N/A' }}
                                                                            </div>
                                                                            <div class="text-xs text-gray-500 mt-1">
                                                                                Type:
                                                                                {{ $account->accountType->name ?? 'N/A' }}
                                                                                |
                                                                                Balance:
                                                                                {{ number_format($account->current_balance, 2) }}
                                                                                {{ $account->currency }}
                                                                            </div>
                                                                            <div class="text-xs text-gray-400 mt-1">
                                                                                Opened:
                                                                                {{ $account->opened_at->format('d M Y') }}
                                                                            </div>
                                                                        </div>
                                                                        <span
                                                                            class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                                                                            <i class="fas fa-check-circle mr-1"></i>
                                                                            Select
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                    </li>
                                                    </button>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>

                                <!-- Selected Account Info -->
                                @if ($selectedAccount)
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
                                                        <p class="text-sm font-medium">
                                                            {{ $selectedAccount->accountType->name ?? 'N/A' }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs text-blue-600">Current Balance</p>
                                                        <p class="text-sm font-medium">{{ $selectedAccount->currency }}
                                                            {{ number_format($selectedAccount->current_balance, 2) }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs text-blue-600">Opened Date</p>
                                                        <p class="text-sm">{{ $selectedAccount->opened_at->format('d M Y') }}
                                                        </p>
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
                                        <button wire:click="generateStatement"
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
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
                                        <select wire:model.live="selectedTransactionType"
                                            class="w-full pl-10 pr-10 py-3 text-base border-gray-300 rounded-l-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="">All Types</option>
                                            @foreach ($transactionTypes as $type)
                                                <option value="{{ $type }}">
                                                    {{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                        <select wire:model.live="selectedStatus"
                                            class="w-full pl-10 pr-10 py-3 text-base border-gray-300 rounded-l-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
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
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Reference
                                                </th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Description
                                                </th>
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
                                                        <span
                                                            class="px-2 py-1 text-xs font-medium rounded-full
                                                            @if (in_array($transaction->type, ['deposit', 'cash_deposit'])) bg-green-100 text-green-800
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
                                                        <span
                                                            class="px-2 py-1 text-xs font-medium rounded-full
                                                            @if ($transaction->status === 'completed') bg-green-100 text-green-800
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
                                @if ($this->transaction_report->hasPages())
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
                                        <select wire:model.live="selectedBranch"
                                            class="w-full pl-10 pr-10 py-3 text-base border-gray-300 rounded-l-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="">All Branches</option>
                                            @foreach ($branches as $branch)
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
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Customer #
                                                </th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Branch</th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Accounts
                                                </th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Total
                                                    Balance</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">KYC Status
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @forelse($this->customer_report as $customer)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-2">
                                                        <div class="flex items-center">
                                                            <div
                                                                class="flex-shrink-0 h-8 w-8 bg-gray-200 rounded-full overflow-hidden">
                                                                @if ($customer->profile_photo_path)
                                                                    <img src="{{ $customer->profile_photo_url }}"
                                                                        alt="" class="h-8 w-8 object-cover">
                                                                @else
                                                                    <div
                                                                        class="h-8 w-8 bg-blue-100 flex items-center justify-center">
                                                                        <span class="text-blue-600 font-medium text-sm">
                                                                            {{ substr($customer->first_name, 0, 1) }}{{ substr($customer->last_name, 0, 1) }}
                                                                        </span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="ml-3">
                                                                <p class="text-sm font-medium text-gray-900">
                                                                    {{ $customer->full_name }}</p>
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
                                                        GHS
                                                        {{ number_format($customer->accounts_sum_current_balance ?? 0, 2) }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm">
                                                        <span
                                                            class="px-2 py-1 text-xs font-medium rounded-full
                                                            @if ($customer->kyc_status === 'verified') bg-green-100 text-green-800
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
                                @if ($this->customer_report->hasPages())
                                    <div class="mt-4">
                                        {{ $this->customer_report->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @break

                    @case('daily_summary')
                        <!-- Daily Summary Report -->
                        <div class="bg-white rounded-lg shadow-lg">
                            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                                <h3 class="font-semibold text-gray-700">
                                    <i class="fas fa-calendar-day mr-2 text-blue-600"></i>
                                    Daily Summary Report
                                </h3>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ Carbon\Carbon::parse($startDate)->format('M d, Y') }} -
                                    {{ Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                                </p>
                            </div>

                            <div class="p-4">
                                <!-- Summary Cards -->
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                                    @php
                                        $dailyData = $this->daily_summary;
                                        $totalTransactions = $dailyData->sum('transaction_count');
                                        $totalVolume = $dailyData->sum('total_volume');
                                        $totalFees = $dailyData->sum('total_fees');
                                        $avgDailyVolume =
                                            $dailyData->count() > 0 ? $totalVolume / $dailyData->count() : 0;
                                    @endphp

                                    <div class="bg-blue-50 rounded-lg p-3">
                                        <p class="text-xs text-blue-600 font-medium">Total Days</p>
                                        <p class="text-xl font-bold text-blue-800">{{ $dailyData->count() }}</p>
                                    </div>

                                    <div class="bg-green-50 rounded-lg p-3">
                                        <p class="text-xs text-green-600 font-medium">Total Transactions</p>
                                        <p class="text-xl font-bold text-green-800">{{ number_format($totalTransactions) }}
                                        </p>
                                    </div>

                                    <div class="bg-purple-50 rounded-lg p-3">
                                        <p class="text-xs text-purple-600 font-medium">Total Volume</p>
                                        <p class="text-xl font-bold text-purple-800">GHS {{ number_format($totalVolume, 2) }}
                                        </p>
                                    </div>

                                    <div class="bg-yellow-50 rounded-lg p-3">
                                        <p class="text-xs text-yellow-600 font-medium">Avg Daily Volume</p>
                                        <p class="text-xl font-bold text-yellow-800">GHS
                                            {{ number_format($avgDailyVolume, 2) }}</p>
                                    </div>
                                </div>

                                <!-- Daily Breakdown Table -->
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Date</th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Transactions
                                                </th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Deposits
                                                </th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Withdrawals
                                                </th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Transfers
                                                </th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Total Volume
                                                </th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Fees</th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Unique
                                                    Customers</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @forelse($dailyData as $day)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-2 text-sm font-medium text-gray-900">
                                                        {{ $day['date'] }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-right">
                                                        {{ number_format($day['transaction_count']) }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-right text-green-600">
                                                        GHS {{ number_format($day['deposits'], 2) }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-right text-red-600">
                                                        GHS {{ number_format($day['withdrawals'], 2) }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-right text-blue-600">
                                                        GHS {{ number_format($day['transfers'], 2) }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-right font-medium">
                                                        GHS {{ number_format($day['total_volume'], 2) }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-right">
                                                        GHS {{ number_format($day['total_fees'], 2) }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-right">
                                                        {{ $day['unique_customers'] }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="px-4 py-6 text-center text-gray-500">
                                                        No data available for the selected period
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        <tfoot class="bg-gray-50 font-medium">
                                            <tr>
                                                <td class="px-4 py-2 text-sm text-gray-900">Totals</td>
                                                <td class="px-4 py-2 text-sm text-right">
                                                    {{ number_format($totalTransactions) }}</td>
                                                <td class="px-4 py-2 text-sm text-right text-green-600">GHS
                                                    {{ number_format($dailyData->sum('deposits'), 2) }}</td>
                                                <td class="px-4 py-2 text-sm text-right text-red-600">GHS
                                                    {{ number_format($dailyData->sum('withdrawals'), 2) }}</td>
                                                <td class="px-4 py-2 text-sm text-right text-blue-600">GHS
                                                    {{ number_format($dailyData->sum('transfers'), 2) }}</td>
                                                <td class="px-4 py-2 text-sm text-right">GHS
                                                    {{ number_format($totalVolume, 2) }}</td>
                                                <td class="px-4 py-2 text-sm text-right">GHS
                                                    {{ number_format($totalFees, 2) }}</td>
                                                <td class="px-4 py-2 text-sm text-right">
                                                    {{ $dailyData->sum('unique_customers') }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @break

                    @case('monthly_summary')
                        <!-- Monthly Summary Report -->
                        <div class="bg-white rounded-lg shadow-lg">
                            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                                <h3 class="font-semibold text-gray-700">
                                    <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>
                                    Monthly Summary Report
                                </h3>
                            </div>

                            <div class="p-4">
                                @php
                                    $monthlyData = $this->monthly_summary;
                                    $totalRevenue = $monthlyData->sum('total_volume');
                                    $totalFees = $monthlyData->sum('fees_collected');
                                @endphp

                                <!-- Monthly Trend Chart -->
                                <div class="mb-6 h-64">
                                    <canvas id="monthlyChart" wire:ignore x-data="{
                                        chart: null,
                                        init() {
                                            const ctx = document.getElementById('monthlyChart').getContext('2d');
                                            this.chart = new Chart(ctx, {
                                                type: 'bar',
                                                data: {
                                                    labels: {{ json_encode($monthlyData->pluck('month')) }},
                                                    datasets: [{
                                                            label: 'Transaction Volume',
                                                            data: {{ json_encode($monthlyData->pluck('total_volume')) }},
                                                            backgroundColor: 'rgba(59, 130, 246, 0.5)',
                                                            borderColor: '#3b82f6',
                                                            borderWidth: 1
                                                        },
                                                        {
                                                            label: 'Fees Collected',
                                                            data: {{ json_encode($monthlyData->pluck('fees_collected')) }},
                                                            backgroundColor: 'rgba(245, 158, 11, 0.5)',
                                                            borderColor: '#f59e0b',
                                                            borderWidth: 1
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

                                <!-- Monthly Breakdown Table -->
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Month</th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Transactions
                                                </th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Deposits
                                                </th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Withdrawals
                                                </th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Transfers
                                                </th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Total Volume
                                                </th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Fees</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Peak Day</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @forelse($monthlyData as $month)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-2 text-sm font-medium text-gray-900">
                                                        {{ $month['month'] }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-right">
                                                        {{ number_format($month['transaction_count']) }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-right text-green-600">
                                                        GHS {{ number_format($month['deposits'], 2) }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-right text-red-600">
                                                        GHS {{ number_format($month['withdrawals'], 2) }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-right text-blue-600">
                                                        GHS {{ number_format($month['transfers'], 2) }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-right font-medium">
                                                        GHS {{ number_format($month['total_volume'], 2) }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-right">
                                                        GHS {{ number_format($month['fees_collected'], 2) }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm">
                                                        {{ $month['peak_day'] }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="px-4 py-6 text-center text-gray-500">
                                                        No data available for the selected period
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @break

                    @case('audit_trail')
                        <!-- Audit Trail Report -->
                        <div class="bg-white rounded-lg shadow-lg">
                            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                                <h3 class="font-semibold text-gray-700">
                                    <i class="fas fa-history mr-2 text-blue-600"></i>
                                    Audit Trail
                                </h3>
                            </div>

                            <div class="p-4">
                                <!-- Filters -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">User</label>
                                        <select wire:model.live="selectedUser"
                                            class="w-full pl-10 pr-10 py-3 text-base border-gray-300 rounded-l-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="">All Users</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                                        <select wire:model.live="selectedTransactionType"
                                            class="w-full pl-10 pr-10 py-3 text-base border-gray-300 rounded-l-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="">All Actions</option>
                                            <option value="created">Created</option>
                                            <option value="updated">Updated</option>
                                            <option value="deleted">Deleted</option>
                                            <option value="login">Login</option>
                                            <option value="logout">Logout</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Audit Log Table -->
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Timestamp
                                                </th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">User</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Action</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Entity Type
                                                </th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Entity ID
                                                </th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">IP Address
                                                </th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Description
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @forelse($this->audit_trail as $log)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-2 text-sm text-gray-500">
                                                        {{ $log->created_at->format('d M Y H:i:s') }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm">
                                                        {{ $log->user->full_name ?? 'System' }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm">
                                                        <span
                                                            class="px-2 py-1 text-xs font-medium rounded-full
                                        @if ($log->action === 'created') bg-green-100 text-green-800
                                        @elseif($log->action === 'updated') bg-blue-100 text-blue-800
                                        @elseif($log->action === 'deleted') bg-red-100 text-red-800
                                        @elseif($log->action === 'login') bg-purple-100 text-purple-800
                                        @elseif($log->action === 'logout') bg-gray-100 text-gray-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                                            {{ ucfirst($log->action) }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-gray-500">
                                                        {{ class_basename($log->entity_type) }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-right text-gray-500">
                                                        {{ $log->entity_id }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-gray-500">
                                                        {{ $log->ip_address ?? 'N/A' }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-gray-600">
                                                        {{ $log->description }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                                        No audit logs found
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                @if ($this->audit_trail->hasPages())
                                    <div class="mt-4">
                                        {{ $this->audit_trail->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @break

                    @case('account_analysis')
                        <!-- Account Analysis Report -->
                        <div class="bg-white rounded-lg shadow-lg">
                            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                                <h3 class="font-semibold text-gray-700">
                                    <i class="fas fa-chart-bar mr-2 text-blue-600"></i>
                                    Account Analysis
                                </h3>
                            </div>

                            <div class="p-4">
                                @php
                                    $analysis = $this->account_analysis;
                                    $summary = $analysis['summary'];
                                @endphp

                                <!-- Summary Cards -->
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                                    <div class="bg-blue-50 rounded-lg p-3">
                                        <p class="text-xs text-blue-600 font-medium">Total Accounts</p>
                                        <p class="text-xl font-bold text-blue-800">
                                            {{ number_format($summary['total_accounts']) }}</p>
                                    </div>

                                    <div class="bg-green-50 rounded-lg p-3">
                                        <p class="text-xs text-green-600 font-medium">Active Accounts</p>
                                        <p class="text-xl font-bold text-green-800">
                                            {{ number_format($summary['active_accounts']) }}</p>
                                    </div>

                                    <div class="bg-yellow-50 rounded-lg p-3">
                                        <p class="text-xs text-yellow-600 font-medium">Dormant Accounts</p>
                                        <p class="text-xl font-bold text-yellow-800">
                                            {{ number_format($summary['dormant_accounts']) }}</p>
                                    </div>

                                    <div class="bg-purple-50 rounded-lg p-3">
                                        <p class="text-xs text-purple-600 font-medium">Total Balance</p>
                                        <p class="text-xl font-bold text-purple-800">GHS
                                            {{ number_format($summary['total_balance'], 2) }}</p>
                                    </div>
                                </div>

                                <!-- Balance Distribution -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-700 mb-3">Balance Distribution</h4>
                                        <div class="space-y-2">
                                            @foreach ($summary['balance_ranges'] as $range => $count)
                                                @php
                                                    $percentage =
                                                        $summary['total_accounts'] > 0
                                                            ? round(($count / $summary['total_accounts']) * 100, 1)
                                                            : 0;
                                                @endphp
                                                <div>
                                                    <div class="flex justify-between text-sm mb-1">
                                                        <span class="text-gray-600">GHS {{ $range }}</span>
                                                        <span class="font-medium">{{ $count }} accounts
                                                            ({{ $percentage }}%)
                                                        </span>
                                                    </div>
                                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                                        <div class="bg-blue-600 h-2 rounded-full"
                                                            style="width: {{ $percentage }}%"></div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div>
                                        <h4 class="text-sm font-medium text-gray-700 mb-3">Key Metrics</h4>
                                        <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Average Balance</span>
                                                <span class="font-medium">GHS
                                                    {{ number_format($summary['avg_balance'], 2) }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Inactivity Rate</span>
                                                <span class="font-medium">{{ $summary['inactive_rate'] }}%</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Active Ratio</span>
                                                <span
                                                    class="font-medium">{{ $summary['total_accounts'] > 0 ? round(($summary['active_accounts'] / $summary['total_accounts']) * 100, 1) : 0 }}%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Accounts Table -->
                                <h4 class="text-sm font-medium text-gray-700 mb-3">Account Details</h4>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Account #
                                                </th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Customer</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Type</th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Balance</th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Transactions
                                                </th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Total Volume
                                                </th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Status</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Last Activity
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @forelse($analysis['accounts'] as $account)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-2 text-sm font-mono text-gray-600">
                                                        {{ $account->account_number }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm">
                                                        {{ ucwords($account->customer->full_name) ?? 'N/A' }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm">
                                                        {{ $account->accountType->name ?? 'N/A' }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-right font-medium">
                                                        GHS {{ number_format($account->current_balance, 2) }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-right">
                                                        {{ number_format($account->transactions_count ?? 0) }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-right">
                                                        GHS {{ number_format($account->transactions_total ?? 0, 2) }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm">
                                                        <span
                                                            class="px-2 py-1 text-xs font-medium rounded-full
                            @if ($account->status === 'active') bg-green-100 text-green-800
                            @elseif($account->status === 'frozen') bg-yellow-100 text-yellow-800
                            @elseif($account->status === 'closed') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                                                            {{ ucfirst($account->status) }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-gray-500">
                                                        @if ($account->last_activity)
                                                            {{ $account->last_activity->format('d M Y') }}
                                                        @else
                                                            Never
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="px-4 py-6 text-center text-gray-500">
                                                        No accounts found
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                @if ($analysis['accounts']->hasPages())
                                    <div class="mt-4">
                                        {{ $analysis['accounts']->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @break

                    @case('revenue_report')
                        <!-- Revenue Report -->
                        <div class="bg-white rounded-lg shadow-lg">
                            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                                <h3 class="font-semibold text-gray-700">
                                    <i class="fas fa-chart-line mr-2 text-blue-600"></i>
                                    Revenue Report
                                </h3>
                            </div>

                            <div class="p-4">
                                @php
                                    $revenue = $this->revenue_report;
                                    $summary = $revenue['summary'];
                                @endphp

                                <!-- Summary Cards -->
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                                    <div class="bg-green-50 rounded-lg p-3">
                                        <p class="text-xs text-green-600 font-medium">Total Revenue</p>
                                        <p class="text-xl font-bold text-green-800">GHS
                                            {{ number_format($summary['total_revenue'], 2) }}</p>
                                    </div>

                                    <div class="bg-blue-50 rounded-lg p-3">
                                        <p class="text-xs text-blue-600 font-medium">Revenue Transactions</p>
                                        <p class="text-xl font-bold text-blue-800">
                                            {{ number_format($summary['total_transactions']) }}</p>
                                    </div>

                                    <div class="bg-purple-50 rounded-lg p-3">
                                        <p class="text-xs text-purple-600 font-medium">Average per Transaction</p>
                                        <p class="text-xl font-bold text-purple-800">GHS
                                            {{ number_format($summary['avg_revenue_per_transaction'], 2) }}</p>
                                    </div>

                                    <div class="bg-yellow-50 rounded-lg p-3">
                                        <p class="text-xs text-yellow-600 font-medium">Projected Monthly</p>
                                        <p class="text-xl font-bold text-yellow-800">GHS
                                            {{ number_format($summary['projected_monthly'], 2) }}</p>
                                    </div>
                                </div>

                                <!-- Revenue Chart -->
                                @if ($revenue['by_day']->isNotEmpty())
                                    <div class="mb-6 h-64">
                                        <canvas id="revenueChart" wire:ignore x-data="{
                                            chart: null,
                                            init() {
                                                const ctx = document.getElementById('revenueChart').getContext('2d');
                                                this.chart = new Chart(ctx, {
                                                    type: 'line',
                                                    data: {
                                                        labels: {{ json_encode(
                                                            $revenue['by_day']->pluck('date')->map(function ($date) {
                                                                return Carbon\Carbon::parse($date)->format('d M');
                                                            }),
                                                        ) }},
                                                        datasets: [{
                                                            label: 'Daily Revenue',
                                                            data: {{ json_encode($revenue['by_day']->pluck('total')) }},
                                                            borderColor: '#10b981',
                                                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                                            tension: 0.4,
                                                            fill: true
                                                        }]
                                                    },
                                                    options: {
                                                        responsive: true,
                                                        maintainAspectRatio: false,
                                                        plugins: {
                                                            legend: {
                                                                display: false
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
                                @endif

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Revenue by Category -->
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-700 mb-3">Revenue by Category</h4>
                                        <div class="bg-gray-50 rounded-lg overflow-hidden">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-100">
                                                    <tr>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-600">
                                                            Category</th>
                                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-600">
                                                            Count</th>
                                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-600">
                                                            Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200">
                                                    @foreach ($revenue['by_category'] as $category)
                                                        <tr>
                                                            <td class="px-4 py-2 text-sm capitalize">
                                                                {{ str_replace('_', ' ', $category->type) }}
                                                            </td>
                                                            <td class="px-4 py-2 text-sm text-right">
                                                                {{ number_format($category->count) }}
                                                            </td>
                                                            <td
                                                                class="px-4 py-2 text-sm text-right font-medium text-green-600">
                                                                GHS {{ number_format($category->total, 2) }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot class="bg-gray-100 font-medium">
                                                    <tr>
                                                        <td class="px-4 py-2 text-sm">Total</td>
                                                        <td class="px-4 py-2 text-sm text-right">
                                                            {{ number_format($revenue['by_category']->sum('count')) }}</td>
                                                        <td class="px-4 py-2 text-sm text-right text-green-600">GHS
                                                            {{ number_format($summary['total_revenue'], 2) }}</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Revenue by Branch (Admin only) -->
                                    @if ($isAdmin && isset($revenue['by_branch']) && $revenue['by_branch']->isNotEmpty())
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-700 mb-3">Revenue by Branch</h4>
                                            <div class="bg-gray-50 rounded-lg overflow-hidden">
                                                <table class="min-w-full divide-y divide-gray-200">
                                                    <thead class="bg-gray-100">
                                                        <tr>
                                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-600">
                                                                Branch</th>
                                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-600">
                                                                Revenue</th>
                                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-600">
                                                                % of Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-gray-200">
                                                        @foreach ($revenue['by_branch'] as $branch)
                                                            @php
                                                                $percentage =
                                                                    $summary['total_revenue'] > 0
                                                                        ? round(
                                                                            ($branch->revenue /
                                                                                $summary['total_revenue']) *
                                                                                100,
                                                                            1,
                                                                        )
                                                                        : 0;
                                                            @endphp
                                                            <tr>
                                                                <td class="px-4 py-2 text-sm">
                                                                    {{ $branch->name }}
                                                                </td>
                                                                <td
                                                                    class="px-4 py-2 text-sm text-right font-medium text-green-600">
                                                                    GHS {{ number_format($branch->revenue, 2) }}
                                                                </td>
                                                                <td class="px-4 py-2 text-sm text-right">
                                                                    {{ $percentage }}%
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Daily Revenue Table -->
                                <div class="mt-6">
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">Daily Breakdown</h4>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Date</th>
                                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Revenue
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @forelse($revenue['by_day'] as $day)
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-4 py-2 text-sm">
                                                            {{ Carbon\Carbon::parse($day->date)->format('D, M d, Y') }}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-right font-medium text-green-600">
                                                            GHS {{ number_format($day->total, 2) }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="2" class="px-4 py-4 text-center text-gray-500">
                                                            No revenue data for the selected period
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
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
