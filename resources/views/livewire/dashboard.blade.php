<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-gray-900">Bank Dashboard</h1>
                    <span class="ml-3 px-3 py-1 text-xs font-semibold rounded-full 
                        {{ auth()->user()->role === 'super-admin' ? 'bg-purple-100 text-purple-800' : 
                           (auth()->user()->role === 'manager' ? 'bg-blue-100 text-blue-800' : 
                           'bg-green-100 text-green-800') }}">
                        {{ ucfirst(str_replace('-', ' ', auth()->user()->role)) }}
                    </span>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Period Selector -->
                    <select wire:model="selectedPeriod" 
                            class="border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="year">This Year</option>
                    </select>
                    
                    <!-- Date Range Picker -->
                    <div class="flex items-center space-x-2">
                        <input type="date" wire:model="dateRange.start" 
                               class="border-gray-300 rounded-lg text-sm">
                        <span class="text-gray-500">to</span>
                        <input type="date" wire:model="dateRange.end" 
                               class="border-gray-300 rounded-lg text-sm">
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                class="p-2 text-gray-600 hover:text-gray-900">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" 
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg z-10">
                            <a href="{{ route('transactions.create') }}" 
                               class="block px-4 py-2 text-sm hover:bg-gray-100">New Transaction</a>
                            <a href="{{ route('customers.create') }}" 
                               class="block px-4 py-2 text-sm hover:bg-gray-100">New Customer</a>
                            <a href="{{ route('accounts.create') }}" 
                               class="block px-4 py-2 text-sm hover:bg-gray-100">New Account</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <!-- Pending Actions Alert -->
        @if(count($pendingActions) > 0)
        <div class="mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Pending Actions</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($pendingActions as $action)
                    <a href="{{ route($action['route']) }}" 
                       class="flex items-center p-3 border rounded-lg hover:shadow-md transition-shadow">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-{{ $action['color'] }}-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-{{ $action['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.346 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">{{ $action['message'] }}</p>
                            <p class="text-xs text-gray-500">Click to review</p>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            @foreach($stats as $stat)
            <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ $stat['label'] }}</p>
                        <p class="text-2xl font-semibold text-gray-900 mt-2">{{ $stat['value'] }}</p>
                        <p class="text-sm {{ str_contains($stat['change'], '+') ? 'text-green-600' : 'text-red-600' }} mt-1">
                            {{ $stat['change'] }} from last period
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-{{ $stat['color'] }}-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-{{ $stat['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Charts and Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Chart -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Performance Overview</h3>
                    <div class="flex space-x-2">
                        <button class="px-3 py-1 text-sm rounded-lg bg-indigo-100 text-indigo-700">30 Days</button>
                        <button class="px-3 py-1 text-sm rounded-lg text-gray-600 hover:bg-gray-100">90 Days</button>
                        <button class="px-3 py-1 text-sm rounded-lg text-gray-600 hover:bg-gray-100">1 Year</button>
                    </div>
                </div>
                <div class="h-80">
                    <canvas id="dashboardChart" 
                            x-data="{
                                init() {
                                    const ctx = this.$el.getContext('2d');
                                    new Chart(ctx, {
                                        type: 'line',
                                        data: @json($chartData),
                                        options: {
                                            responsive: true,
                                            maintainAspectRatio: false,
                                            plugins: {
                                                legend: {
                                                    position: 'top',
                                                }
                                            },
                                            scales: {
                                                y: {
                                                    beginAtZero: true,
                                                    grid: {
                                                        drawBorder: false
                                                    }
                                                },
                                                x: {
                                                    grid: {
                                                        display: false
                                                    }
                                                }
                                            }
                                        }
                                    });
                                }
                            }"></canvas>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Recent Transactions</h3>
                    <a href="{{ route('transactions.index') }}" 
                       class="text-sm text-indigo-600 hover:text-indigo-900">View All</a>
                </div>
                <div class="space-y-4">
                    @foreach($recentTransactions as $transaction)
                    <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full 
                                {{ $transaction->type === 'credit' ? 'bg-green-100' : 'bg-red-100' }} 
                                flex items-center justify-center">
                                <svg class="w-5 h-5 {{ $transaction->type === 'credit' ? 'text-green-600' : 'text-red-600' }}" 
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $transaction->type === 'credit' ? 'Deposit' : 'Withdrawal' }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ optional($transaction->sourceAccount)->account_number ?? 'External' }} 
                                    → 
                                    {{ optional($transaction->destinationAccount)->account_number ?? 'External' }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium 
                                {{ $transaction->type === 'credit' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $transaction->type === 'credit' ? '+' : '-' }}{{ number_format($transaction->amount, 2) }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $transaction->created_at->format('h:i A') }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Role-Specific Sections -->
        @if(auth()->user()->role === 'manager')
        <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Branch Performance -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Branch Performance</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Teller Performance</span>
                        <div class="w-48">
                            <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-green-500 rounded-full" style="width: 85%"></div>
                            </div>
                        </div>
                        <span class="text-sm font-medium">85%</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Customer Satisfaction</span>
                        <div class="w-48">
                            <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-500 rounded-full" style="width: 92%"></div>
                            </div>
                        </div>
                        <span class="text-sm font-medium">92%</span>
                    </div>
                </div>
            </div>

            <!-- Quick Reports -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Reports</h3>
                <div class="grid grid-cols-2 gap-4">
                    <a href="#" class="p-4 border rounded-lg hover:bg-gray-50 text-center">
                        <svg class="w-8 h-8 text-indigo-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span class="text-sm font-medium">Daily Report</span>
                    </a>
                    <a href="#" class="p-4 border rounded-lg hover:bg-gray-50 text-center">
                        <svg class="w-8 h-8 text-green-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm font-medium">Revenue</span>
                    </a>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->role === 'super-admin')
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">System Overview</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-4 border rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-600">Active Users</span>
                        <span class="text-sm font-medium text-green-600">+12%</span>
                    </div>
                    <p class="text-2xl font-semibold">142</p>
                </div>
                <div class="p-4 border rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-600">System Uptime</span>
                        <span class="text-sm font-medium text-green-600">99.9%</span>
                    </div>
                    <p class="text-2xl font-semibold">30 Days</p>
                </div>
                <div class="p-4 border rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-600">Security Score</span>
                        <span class="text-sm font-medium text-yellow-600">94/100</span>
                    </div>
                    <p class="text-2xl font-semibold">Excellent</p>
                </div>
            </div>
        </div>
        @endif
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:load', function () {
            // Initialize any additional JavaScript here
        });
    </script>
    @endpush
</div>