<div class="min-h-screen bg-gray-50">
    <!-- Header Section - Matching Reports Style -->
    <div class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Left: Title and Role Badge -->
                <div class="flex items-center space-x-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                            <i class="fas fa-tachometer-alt mr-3 text-blue-600"></i>
                            Dashboard
                        </h1>
                        <p class="text-sm text-gray-500 mt-0.5">
                            Welcome back, {{ auth()->user()->first_name }}! Here's your banking overview
                        </p>
                    </div>

                    <!-- Role Badge - Enhanced -->
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                        {{ auth()->user()->role === 'super-admin'
                            ? 'bg-purple-100 text-purple-800 border border-purple-200'
                            : (auth()->user()->role === 'manager'
                                ? 'bg-blue-100 text-blue-800 border border-blue-200'
                                : 'bg-green-100 text-green-800 border border-green-200') }}">
                        <i
                            class="fas fa-circle text-[8px] mr-1.5 
                            {{ auth()->user()->role === 'super-admin'
                                ? 'text-purple-500'
                                : (auth()->user()->role === 'manager'
                                    ? 'text-blue-500'
                                    : 'text-green-500') }}"></i>
                        {{ ucfirst(str_replace('-', ' ', auth()->user()->role)) }}
                    </span>
                </div>

                <!-- Right: Controls -->
                <div class="flex items-center space-x-3">
                    <!-- Period Selector - Enhanced -->
                    <div class="relative">
                        <select wire:model.live="selectedPeriod"
                            class="appearance-none pl-10 pr-8 py-2.5 text-sm border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white shadow-sm">
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                            <option value="quarter">This Quarter</option>
                            <option value="year">This Year</option>
                        </select>
                        <i class="fas fa-calendar-alt absolute left-3 top-3 text-gray-400 text-sm"></i>
                    </div>

                    <!-- Quick Actions Dropdown - Enhanced -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-150 shadow-sm">
                            <i class="fas fa-plus mr-2"></i>
                            Quick Actions
                            <i class="fas fa-chevron-down ml-2 text-xs" :class="{ 'rotate-180': open }"></i>
                        </button>

                        <div x-show="open" @click.away="open = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                            <a href="{{ route('transactions.create') }}"
                                class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-exchange-alt w-5 text-gray-400 mr-3"></i>
                                New Transaction
                            </a>
                            <a href="{{ route('customers.create') }}"
                                class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-user-plus w-5 text-gray-400 mr-3"></i>
                                New Customer
                            </a>
                            <a href="{{ route('accounts.create') }}"
                                class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-wallet w-5 text-gray-400 mr-3"></i>
                                New Account
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Pending Actions Alert - Enhanced Cards -->
        @if (count($pendingActions) > 0)
            <div class="mb-8">
                <div class="flex items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Pending Actions</h2>
                    <span class="ml-2 px-2.5 py-0.5 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">
                        {{ count($pendingActions) }} pending
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($pendingActions as $action)
                        <a href="{{ route($action['route']) }}"
                            class="group flex items-center p-4 {{ $action['bgColor'] }} border {{ $action['borderColor'] }} rounded-xl hover:shadow-md transition-all duration-200">
                            <div class="flex-shrink-0">
                                <div
                                    class="w-12 h-12 {{ $action['iconBg'] }} rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                                    <svg class="w-6 h-6 {{ $action['iconColor'] }}" fill="currentColor"
                                        viewBox="0 0 24 24">
                                        <path d="{{ $action['icon'] }}" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium {{ $action['textColor'] }}">{{ $action['message'] }}</p>
                                <p class="text-xs {{ $action['textColor'] }} opacity-75 mt-0.5">Click to review</p>
                            </div>
                            <i
                                class="fas fa-chevron-right {{ $action['textColor'] }} opacity-50 group-hover:opacity-100 transition-opacity"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Quick Stats Row - New Addition -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Today's Transactions</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">
                            {{ number_format($quickStats['today_transactions'] ?? 0) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exchange-alt text-blue-600"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">
                    Volume: GH₵ {{ number_format($quickStats['today_volume'] ?? 0, 2) }}
                </p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Active Accounts</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">
                            {{ number_format($quickStats['active_accounts'] ?? 0) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center">
                        <i class="fas fa-wallet text-green-600"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">
                    <span class="text-green-600">●</span> Currently active
                </p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Pending KYC</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">
                            {{ number_format($quickStats['pending_kyc'] ?? 0) }}</p>
                    </div>
                    <div class="w-10 h-10 bg-yellow-50 rounded-lg flex items-center justify-center">
                        <i class="fas fa-id-card text-yellow-600"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">
                    <span class="text-yellow-600">●</span> Awaiting verification
                </p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Date Range</p>
                        <p class="text-sm font-semibold text-gray-900 mt-1">
                            {{ \Carbon\Carbon::parse($dateRange['start'])->format('M d') }} -
                            {{ \Carbon\Carbon::parse($dateRange['end'])->format('M d, Y') }}
                        </p>
                    </div>
                    <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-check text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Stats Grid - Enhanced Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            @foreach ($stats as $key => $stat)
                <div
                    class="bg-white rounded-xl border border-gray-200 p-6 hover:shadow-lg transition-all duration-200 group">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-500">{{ $stat['label'] }}</p>
                            <p
                                class="text-2xl font-bold text-gray-900 mt-2 group-hover:text-{{ $stat['color'] }}-600 transition-colors">
                                {{ $stat['value'] }}
                            </p>

                            <!-- Change Indicator -->
                            @if (isset($stat['change']) && $stat['change'] != 0)
                                <div class="flex items-center mt-2">
                                    @if ($stat['trend'] === 'up')
                                        <span
                                            class="flex items-center text-xs font-medium text-green-600 bg-green-50 px-2 py-0.5 rounded-full">
                                            <i class="fas fa-arrow-up mr-1 text-[10px]"></i>
                                            {{ number_format(abs($stat['change']), 1) }}%
                                        </span>
                                    @elseif($stat['trend'] === 'down')
                                        <span
                                            class="flex items-center text-xs font-medium text-red-600 bg-red-50 px-2 py-0.5 rounded-full">
                                            <i class="fas fa-arrow-down mr-1 text-[10px]"></i>
                                            {{ number_format(abs($stat['change']), 1) }}%
                                        </span>
                                    @else
                                        <span
                                            class="flex items-center text-xs font-medium text-gray-600 bg-gray-50 px-2 py-0.5 rounded-full">
                                            <i class="fas fa-minus mr-1 text-[10px]"></i>
                                            {{ number_format(abs($stat['change']), 1) }}%
                                        </span>
                                    @endif
                                    <span class="text-xs text-gray-400 ml-2">vs last period</span>
                                </div>
                            @endif
                        </div>

                        <!-- Icon with enhanced styling -->
                        <div
                            class="w-12 h-12 {{ $stat['bgColor'] }} rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-6 h-6 {{ $stat['textColor'] }}" fill="currentColor" viewBox="0 0 24 24">
                                <path d="{{ $stat['icon'] }}" />
                            </svg>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Daily Trend Cards -->
        @if (count($dailyTrend) > 0)
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Daily Trend</h2>
                    <span class="text-xs text-gray-500">Last 7 days</span>
                </div>

                <div class="grid grid-cols-7 gap-3">
                    @foreach ($dailyTrend as $day)
                        <div
                            class="bg-white rounded-xl border border-gray-200 p-3 text-center hover:shadow-md transition-shadow">
                            <p class="text-xs font-medium text-gray-500">{{ $day['day'] }}</p>
                            <p class="text-[10px] text-gray-400 mb-2">{{ $day['date'] }}</p>

                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-[10px]">
                                    <span class="text-green-600">In</span>
                                    <span class="font-medium text-gray-700">GH₵
                                        {{ number_format($day['credits'] / 1000, 1) }}k</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5">
                                    <div class="bg-green-500 h-1.5 rounded-full"
                                        style="width: {{ $day['credits'] > 0 ? min(100, ($day['credits'] / max($day['credits'], $day['debits'])) * 100) : 0 }}%">
                                    </div>
                                </div>

                                <div class="flex items-center justify-between text-[10px] mt-2">
                                    <span class="text-red-600">Out</span>
                                    <span class="font-medium text-gray-700">GH₵
                                        {{ number_format($day['debits'] / 1000, 1) }}k</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5">
                                    <div class="bg-red-500 h-1.5 rounded-full"
                                        style="width: {{ $day['debits'] > 0 ? min(100, ($day['debits'] / max($day['credits'], $day['debits'])) * 100) : 0 }}%">
                                    </div>
                                </div>

                                <div class="mt-2 pt-2 border-t border-gray-100">
                                    <p
                                        class="text-xs font-bold {{ $day['net'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $day['net'] >= 0 ? '+' : '' }}{{ number_format($day['net'] / 1000, 1) }}k
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Charts and Activity Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Chart - Enhanced -->
<div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">Performance Overview</h3>
            <p class="text-sm text-gray-500 mt-0.5">Transaction volume and balance trend</p>
        </div>
        <div class="flex items-center space-x-2 bg-gray-50 p-1 rounded-lg">
            <button wire:click="$set('selectedPeriod', 'week')" 
                    class="px-3 py-1.5 text-xs font-medium rounded-md transition-all
                        {{ $selectedPeriod === 'week' ? 'bg-white text-gray-700 shadow-sm' : 'text-gray-600 hover:bg-white hover:shadow-sm' }}">
                7D
            </button>
            <button wire:click="$set('selectedPeriod', 'month')" 
                    class="px-3 py-1.5 text-xs font-medium rounded-md transition-all
                        {{ $selectedPeriod === 'month' ? 'bg-white text-gray-700 shadow-sm' : 'text-gray-600 hover:bg-white hover:shadow-sm' }}">
                30D
            </button>
            <button wire:click="$set('selectedPeriod', 'quarter')" 
                    class="px-3 py-1.5 text-xs font-medium rounded-md transition-all
                        {{ $selectedPeriod === 'quarter' ? 'bg-white text-gray-700 shadow-sm' : 'text-gray-600 hover:bg-white hover:shadow-sm' }}">
                90D
            </button>
            <button wire:click="$set('selectedPeriod', 'year')" 
                    class="px-3 py-1.5 text-xs font-medium rounded-md transition-all
                        {{ $selectedPeriod === 'year' ? 'bg-white text-gray-700 shadow-sm' : 'text-gray-600 hover:bg-white hover:shadow-sm' }}">
                1Y
            </button>
        </div>
    </div>
    
    <div class="h-96 w-full relative"> <!-- Increased height from h-80 to h-96 -->
        <div wire:ignore class="absolute inset-0">
            <canvas id="dashboardChart" class="w-full h-full"></canvas>
        </div>
    </div>
</div>

            <!-- Recent Transactions - Enhanced with proper type display -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Recent Transactions</h3>
                        <p class="text-sm text-gray-500 mt-0.5">Latest 10 activities</p>
                    </div>
                    <a href="{{ route('transactions.index') }}"
                        class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center">
                        View All
                        <i class="fas fa-arrow-right ml-1 text-xs"></i>
                    </a>
                </div>

                <div class="space-y-3 max-h-[400px] overflow-y-auto pr-2">
                    @forelse($recentTransactions as $transaction)
                        @php
                            // Determine transaction category for styling
                            $isCredit = in_array($transaction->type, [
                                'deposit',
                                'cash_deposit',
                                'initial_deposit',
                                'transfer_in',
                                'interest',
                                'refund',
                            ]);
                            $isDebit = in_array($transaction->type, [
                                'withdrawal',
                                'cash_withdrawal',
                                'transfer_out',
                                'fee',
                                'charge',
                                'payment',
                            ]);

                            // Get customer name for display
                            $customerName = null;
                            if ($transaction->sourceAccount && $transaction->sourceAccount->customer) {
                                $customerName = $transaction->sourceAccount->customer->full_name;
                            } elseif ($transaction->destinationAccount && $transaction->destinationAccount->customer) {
                                $customerName = $transaction->destinationAccount->customer->full_name;
                            }

                            // Format transaction type for display
                            $displayType = ucfirst(str_replace('_', ' ', $transaction->type));

                            // Get icon based on transaction type
                            $icon = 'fa-exchange-alt';
                            if (
                                in_array($transaction->type, [
                                    'deposit',
                                    'cash_deposit',
                                    'initial_deposit',
                                    'transfer_in',
                                ])
                            ) {
                                $icon = 'fa-arrow-up';
                            } elseif (in_array($transaction->type, ['withdrawal', 'cash_withdrawal', 'transfer_out'])) {
                                $icon = 'fa-arrow-down';
                            } elseif ($transaction->type == 'transfer') {
                                $icon = 'fa-right-left';
                            } elseif ($transaction->type == 'fee' || $transaction->type == 'charge') {
                                $icon = 'fa-percent';
                            }
                        @endphp
                        <div
                            class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors group">
                            <div class="flex items-center min-w-0 flex-1">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-10 h-10 rounded-xl 
                        {{ $isCredit ? 'bg-green-50' : ($isDebit ? 'bg-red-50' : 'bg-blue-50') }} 
                        flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <i
                                            class="fas {{ $icon }} 
                            {{ $isCredit ? 'text-green-600' : ($isDebit ? 'text-red-600' : 'text-blue-600') }}"></i>
                                    </div>
                                </div>
                                <div class="ml-3 min-w-0 flex-1">
                                    <div class="flex items-center">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ $displayType }}
                                        </p>
                                        {{-- @if ($transaction->transaction_reference)
                        <span class="ml-2 text-xs font-mono text-gray-400">
                            #{{ substr($transaction->transaction_reference, -8) }}
                        </span>
                        @endif --}}
                                    </div>
                                    @if ($transaction->transaction_reference)
                                        <p class="text-xs font-mono text-gray-400">
                                            Ref:{{ substr($transaction->transaction_reference, -8) }}
                                        </p>
                                    @endif
                                    {{-- @if ($customerName)
                    <p class="text-xs text-gray-600 truncate">
                        {{ ucwords($customerName) }}
                    </p>
                    @endif --}}
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ $transaction->created_at->format('d M, h:i A') }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right ml-2 flex-shrink-0">
                                <p
                                    class="text-sm font-bold 
                    {{ $isCredit ? 'text-green-600' : ($isDebit ? 'text-red-600' : 'text-blue-600') }}">
                                    {{ $isCredit ? '+' : ($isDebit ? '-' : '') }}GH₵
                                    {{ number_format($transaction->amount, 2) }}
                                </p>
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium
                    @if ($transaction->status === 'completed') bg-green-100 text-green-800
                    @elseif($transaction->status === 'pending') bg-yellow-100 text-yellow-800
                    @elseif($transaction->status === 'failed') bg-red-100 text-red-800
                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <i class="fas fa-exchange-alt text-4xl text-gray-300 mb-3"></i>
                            <p class="text-sm text-gray-500">No recent transactions</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Role-Specific Sections - Enhanced -->
        @if (auth()->user()->role === 'manager' && isset($performanceMetrics['top_tellers']))
            <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Teller Performance -->
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Teller Performance</h3>
                            <p class="text-sm text-gray-500 mt-0.5">Transaction volume by teller</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded-full">
                                {{ $performanceMetrics['teller_count'] ?? 0 }} Tellers
                            </span>
                            <span class="text-xs bg-green-50 text-green-700 px-2 py-1 rounded-full">
                                Avg: {{ $performanceMetrics['avg_per_teller'] ?? 0 }}/day
                            </span>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @foreach ($performanceMetrics['top_tellers'] as $teller)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-user-tie text-blue-600 text-sm"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $teller->full_name }}</p>
                                        <p class="text-xs text-gray-500">{{ $teller->transaction_count ?? 0 }}
                                            transactions</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="w-32 bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full"
                                            style="width: {{ (($teller->transaction_count ?? 0) / max(1, $performanceMetrics['total_teller_transactions'])) * 100 }}%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Total Transactions</span>
                            <span
                                class="font-semibold">{{ number_format($performanceMetrics['total_teller_transactions'] ?? 0) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Reports -->
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Quick Reports</h3>
                            <p class="text-sm text-gray-500 mt-0.5">Generate reports instantly</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <a href="{{ route('reports.index', ['activeReport' => 'daily_summary']) }}"
                            class="group p-4 border border-gray-200 rounded-xl hover:border-blue-300 hover:bg-blue-50 transition-all text-center">
                            <div
                                class="w-12 h-12 bg-blue-100 rounded-xl mx-auto mb-3 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fas fa-calendar-day text-blue-600 text-xl"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-900 group-hover:text-blue-700">Daily
                                Summary</span>
                            <p class="text-xs text-gray-500 mt-1">Today's activity</p>
                        </a>

                        <a href="{{ route('reports.index', ['activeReport' => 'transaction_report']) }}"
                            class="group p-4 border border-gray-200 rounded-xl hover:border-green-300 hover:bg-green-50 transition-all text-center">
                            <div
                                class="w-12 h-12 bg-green-100 rounded-xl mx-auto mb-3 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fas fa-list text-green-600 text-xl"></i>
                            </div>
                            <span
                                class="text-sm font-medium text-gray-900 group-hover:text-green-700">Transactions</span>
                            <p class="text-xs text-gray-500 mt-1">All transactions</p>
                        </a>

                        <a href="{{ route('reports.index', ['activeReport' => 'customer_report']) }}"
                            class="group p-4 border border-gray-200 rounded-xl hover:border-purple-300 hover:bg-purple-50 transition-all text-center">
                            <div
                                class="w-12 h-12 bg-purple-100 rounded-xl mx-auto mb-3 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fas fa-users text-purple-600 text-xl"></i>
                            </div>
                            <span
                                class="text-sm font-medium text-gray-900 group-hover:text-purple-700">Customers</span>
                            <p class="text-xs text-gray-500 mt-1">Customer analytics</p>
                        </a>

                        <a href="{{ route('reports.index', ['activeReport' => 'account_analysis']) }}"
                            class="group p-4 border border-gray-200 rounded-xl hover:border-amber-300 hover:bg-amber-50 transition-all text-center">
                            <div
                                class="w-12 h-12 bg-amber-100 rounded-xl mx-auto mb-3 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fas fa-chart-bar text-amber-600 text-xl"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-900 group-hover:text-amber-700">Accounts</span>
                            <p class="text-xs text-gray-500 mt-1">Account analysis</p>
                        </a>
                    </div>
                </div>
            </div>
        @endif

        @if (auth()->user()->role === 'super-admin' && isset($performanceMetrics['top_branches']))
            <div class="mt-6">
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">System Overview</h3>
                            <p class="text-sm text-gray-500 mt-0.5">Multi-branch performance metrics</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="px-3 py-1 bg-blue-50 text-blue-700 text-xs rounded-full">
                                {{ $performanceMetrics['total_branches'] ?? 0 }} Branches
                            </span>
                            <span class="px-3 py-1 bg-green-50 text-green-700 text-xs rounded-full">
                                {{ number_format($performanceMetrics['total_customers'] ?? 0) }} Customers
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-blue-700">Total Accounts</span>
                                <i class="fas fa-wallet text-blue-600"></i>
                            </div>
                            <p class="text-2xl font-bold text-blue-900">
                                {{ number_format($performanceMetrics['total_accounts'] ?? 0) }}</p>
                        </div>

                        <div class="p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-xl">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-green-700">Total Volume</span>
                                <i class="fas fa-chart-line text-green-600"></i>
                            </div>
                            <p class="text-2xl font-bold text-green-900">GH₵
                                {{ number_format($performanceMetrics['total_volume'] ?? 0, 2) }}</p>
                        </div>

                        <div class="p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-purple-700">Avg per Branch</span>
                                <i class="fas fa-building text-purple-600"></i>
                            </div>
                            <p class="text-2xl font-bold text-purple-900">
                                GH₵
                                {{ number_format(($performanceMetrics['total_volume'] ?? 0) / max(1, $performanceMetrics['total_branches'] ?? 1), 2) }}
                            </p>
                        </div>
                    </div>

                    <h4 class="text-sm font-medium text-gray-700 mb-3">Top Performing Branches</h4>
                    <div class="space-y-3">
                        @foreach ($performanceMetrics['top_branches'] as $branch)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-building text-indigo-600 text-sm"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $branch->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $branch->customers_count ?? 0 }} customers
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-green-600">GH₵
                                        {{ number_format($branch->transaction_volume ?? 0, 2) }}</p>
                                    <div class="w-24 bg-gray-200 rounded-full h-1.5 mt-1">
                                        <div class="bg-green-500 h-1.5 rounded-full"
                                            style="width: {{ (($branch->transaction_volume ?? 0) / max(1, $performanceMetrics['total_volume'])) * 100 }}%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            document.addEventListener('livewire:initialized', function () {
        let chart = null;
        
        function initChart() {
            const canvas = document.getElementById('dashboardChart');
            if (!canvas) {
                console.error('Canvas element not found');
                return;
            }
            
            const ctx = canvas.getContext('2d');
            
            // Get chart data from Livewire
            const chartData = @json($chartData);
            
            console.log('Raw chart data from PHP:', chartData);
            
            if (!chartData || !chartData.labels || chartData.labels.length === 0) {
                console.log('No chart data available');
                // Create empty canvas with message
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.font = '14px Arial';
                ctx.fillStyle = '#999';
                ctx.textAlign = 'center';
                ctx.fillText('No data available for selected period', canvas.width/2, canvas.height/2);
                return;
            }
            
            // Destroy existing chart if it exists
            if (chart) {
                chart.destroy();
            }
            
            try {
                chart = new Chart(ctx, {
                    type: 'line',
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: {
                            padding: {
                                top: 10,
                                bottom: 10,
                                left: 10,
                                right: 10
                            }
                        },
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                align: 'center',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 8,
                                    padding: 15,
                                    font: {
                                        size: 11
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: '#1F2937',
                                titleColor: '#F9FAFB',
                                bodyColor: '#D1D5DB',
                                borderColor: '#374151',
                                borderWidth: 1,
                                padding: 10,
                                cornerRadius: 6,
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            if (context.dataset.label === 'Total Balance (GH₵)') {
                                                label += Number(context.parsed.y).toLocaleString(undefined, {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2
                                                });
                                            } else {
                                                label += context.parsed.y + ' transactions';
                                            }
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            'y-transactions': {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                title: {
                                    display: true,
                                    text: 'Number of Transactions',
                                    color: '#6B7280',
                                    font: {
                                        size: 11,
                                        weight: 'normal'
                                    }
                                },
                                grid: {
                                    drawBorder: false,
                                    color: 'rgba(0, 0, 0, 0.05)',
                                    drawTicks: false
                                },
                                ticks: {
                                    padding: 8,
                                    color: '#6B7280',
                                    font: {
                                        size: 10
                                    },
                                    callback: function(value) {
                                        return value;
                                    }
                                },
                                min: 0
                            },
                            'y-balance': {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'Balance (GH₵)',
                                    color: '#6B7280',
                                    font: {
                                        size: 11,
                                        weight: 'normal'
                                    }
                                },
                                grid: {
                                    drawOnChartArea: false,
                                },
                                ticks: {
                                    padding: 8,
                                    color: '#6B7280',
                                    font: {
                                        size: 10
                                    },
                                    callback: function(value) {
                                        return 'GH₵ ' + (value / 1000).toFixed(0) + 'k';
                                    }
                                },
                                min: 0
                            },
                            x: {
                                grid: {
                                    display: false,
                                    drawTicks: true
                                },
                                ticks: {
                                    maxRotation: 0,
                                    minRotation: 0,
                                    autoSkip: true,
                                    maxTicksLimit: 8,
                                    padding: 8,
                                    color: '#6B7280',
                                    font: {
                                        size: 10
                                    }
                                },
                                title: {
                                    display: false
                                }
                            }
                        },
                        elements: {
                            line: {
                                borderWidth: 2,
                                fill: true,
                                tension: 0.3
                            },
                            point: {
                                radius: 3,
                                hoverRadius: 5,
                                hitRadius: 5,
                                borderWidth: 1
                            }
                        }
                    }
                });
                
                console.log('Chart initialized successfully');
                
                // Force resize to ensure it fills the container
                setTimeout(() => {
                    if (chart) {
                        chart.resize();
                    }
                }, 100);
                
            } catch (error) {
                console.error('Error initializing chart:', error);
            }
        }
        
        // Initial chart load with a slight delay to ensure DOM is ready
        setTimeout(() => {
            initChart();
        }, 200);
        
        // Listen for chart updates
        Livewire.on('chartUpdated', () => {
            console.log('Chart update event received');
            setTimeout(() => {
                initChart();
            }, 300);
        });
        
        // Handle window resize
        window.addEventListener('resize', () => {
            if (chart) {
                chart.resize();
            }
        });
    });
        </script>
    @endpush
</div>
