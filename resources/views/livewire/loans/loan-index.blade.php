<div>
    <div class="max-w-7xl mx-auto py-6 shadow-lg sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg">
            <!-- Header -->
            <div class="p-6 border-b shadow-md border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Loan Management</h2>
                        <p class="text-sm text-gray-600 mt-1">View and manage all loan applications</p>
                    </div>
                    <div class="flex space-x-3">
                        <!-- Filter Toggle Button -->
                        <button wire:click="toggleFilters"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-filter mr-2"></i>
                            Filters
                            @if ($hasActiveFilters)
                                <span
                                    class="ml-2 inline-flex items-center justify-center h-5 w-5 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold">
                                    {{ $activeFiltersCount }}
                                </span>
                            @endif
                        </button>
                        @if ($canCreate)
                            <a href="{{ route('loans.create') }}"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-plus-circle mr-2"></i>
                                New Loan Application
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5">
                    <!-- Total Loans -->
                    <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-12 w-12 rounded-lg bg-blue-100 flex items-center justify-center">
                                        <i class="fas fa-hand-holding-usd text-blue-600"></i>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Total Loans
                                        </dt>
                                        <dd class="text-2xl font-semibold text-gray-900">
                                            {{ number_format($stats['total']) }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending -->
                    <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-12 w-12 rounded-lg bg-yellow-100 flex items-center justify-center">
                                        <i class="fas fa-clock text-yellow-600"></i>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Pending
                                        </dt>
                                        <dd class="text-2xl font-semibold text-gray-900">
                                            {{ number_format($stats['pending']) }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3">
                            <div class="text-sm">
                                <button type="button" wire:click="$set('status', 'pending')"
                                    class="font-medium text-blue-700 hover:text-blue-900">
                                    View pending
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Approved -->
                    <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-12 w-12 rounded-lg bg-green-100 flex items-center justify-center">
                                        <i class="fas fa-check-circle text-green-600"></i>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Approved
                                        </dt>
                                        <dd class="text-2xl font-semibold text-gray-900">
                                            {{ number_format($stats['approved']) }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3">
                            <div class="text-sm">
                                <button type="button" wire:click="$set('status', 'approved')"
                                    class="font-medium text-blue-700 hover:text-blue-900">
                                    View approved
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Active -->
                    <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-12 w-12 rounded-lg bg-purple-100 flex items-center justify-center">
                                        <i class="fas fa-chart-line text-purple-600"></i>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Active
                                        </dt>
                                        <dd class="text-2xl font-semibold text-gray-900">
                                            {{ number_format($stats['active']) }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3">
                            <div class="text-sm">
                                <button type="button" wire:click="$set('status', 'active')"
                                    class="font-medium text-blue-700 hover:text-blue-900">
                                    View active
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Overdue -->
                    <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-12 w-12 rounded-lg bg-red-100 flex items-center justify-center">
                                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Overdue
                                        </dt>
                                        <dd class="text-2xl font-semibold text-gray-900">
                                            {{ number_format($stats['overdue']) }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3">
                            <div class="text-sm">
                                <button type="button" wire:click="$set('status', 'active')"
                                    class="font-medium text-blue-700 hover:text-blue-900">
                                    View overdue
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters Panel -->
                @if ($showFilters)
                    <div class="mt-6 transition-all duration-300 ease-in-out">
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Filter Loans</h3>
                                @if ($hasActiveFilters)
                                    <button wire:click="resetFilters"
                                        class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        Clear All Filters
                                    </button>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <!-- Search -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                    <div class="relative">
                                        <input type="text" wire:model.live.debounce.300ms="search"
                                            placeholder="Search by loan number, customer, purpose..."
                                            class="pl-10 pr-8 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-search text-gray-400"></i>
                                        </div>
                                        @if ($search)
                                            <button wire:click="$set('search', '')"
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <!-- Customer Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                                    <select wire:model.live="customer_id"
                                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">All Customers</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}">
                                                {{ $customer->customer_number }} - {{ $customer->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Loan Type Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Loan Type</label>
                                    <select wire:model.live="loan_type"
                                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">All Types</option>
                                        @foreach ($loanTypes as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Status Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <select wire:model.live="status"
                                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">All Statuses</option>
                                        @foreach ($statuses as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Date Range -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Application Date</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <input type="date" wire:model.live="start_date"
                                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <p class="text-xs text-gray-500 mt-1">From date</p>
                                        </div>
                                        <div>
                                            <input type="date" wire:model.live="end_date"
                                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <p class="text-xs text-gray-500 mt-1">To date</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Active Filters Badges -->
                            @if ($hasActiveFilters)
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @if ($search)
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Search: "{{ $search }}"
                                            <button wire:click="$set('search', '')"
                                                class="ml-1 text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </span>
                                    @endif
                                    @if ($customer_id)
                                        @php
                                            $customer = $customers->firstWhere('id', $customer_id);
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Customer: {{ $customer ? $customer->full_name : 'N/A' }}
                                            <button wire:click="$set('customer_id', '')"
                                                class="ml-1 text-green-600 hover:text-green-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </span>
                                    @endif
                                    @if ($loan_type)
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            Type: {{ $loanTypes[$loan_type] ?? $loan_type }}
                                            <button wire:click="$set('loan_type', '')"
                                                class="ml-1 text-purple-600 hover:text-purple-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </span>
                                    @endif
                                    @if ($status)
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            Status: {{ $statuses[$status] ?? $status }}
                                            <button wire:click="$set('status', '')"
                                                class="ml-1 text-orange-600 hover:text-orange-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </span>
                                    @endif
                                    @if ($start_date)
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            From: {{ $start_date }}
                                            <button wire:click="$set('start_date', '')"
                                                class="ml-1 text-indigo-600 hover:text-indigo-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </span>
                                    @endif
                                    @if ($end_date)
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            To: {{ $end_date }}
                                            <button wire:click="$set('end_date', '')"
                                                class="ml-1 text-indigo-600 hover:text-indigo-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Loans Table -->
            <div class="overflow-x-auto">
                <!-- Results Summary -->
                <div class="mt-4 px-6 flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        @if ($loans && $loans->total() > 0)
                            Showing {{ $loans->firstItem() }} to {{ $loans->lastItem() }} of
                            {{ $loans->total() }} loans
                            @if ($hasActiveFilters)
                                <span class="font-medium">(filtered)</span>
                            @endif
                        @endif
                    </div>

                    <!-- Items per page -->
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-600">Show:</span>
                        <select wire:model.live="perPage"
                            class="border border-gray-300 rounded-md shadow-sm py-1 px-2 text-sm w-20 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-sm text-gray-600">per page</span>
                    </div>
                </div>

                @if ($loans && $loans->count())
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Loan Details
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Customer
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Financials
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Dates
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($loans as $loan)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                @php
                                                    $icon = match ($loan->loan_type) {
                                                        'mortgage' => 'home',
                                                        'funeral' => 'cross',
                                                        'business' => 'briefcase',
                                                        'auto' => 'car',
                                                        'education' => 'graduation-cap',
                                                        'agriculture' => 'tractor',
                                                        'emergency' => 'ambulance',
                                                        default => 'hand-holding-usd',
                                                    };

                                                    $color = match ($loan->status) {
                                                        'approved' => 'text-green-600 bg-green-100',
                                                        'pending' => 'text-yellow-600 bg-yellow-100',
                                                        'active' => 'text-blue-600 bg-blue-100',
                                                        'rejected' => 'text-red-600 bg-red-100',
                                                        default => 'text-gray-600 bg-gray-100',
                                                    };
                                                @endphp
                                                <div
                                                    class="h-10 w-10 rounded-lg {{ $color }} flex items-center justify-center">
                                                    <i class="fas fa-{{ $icon }}"></i>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ ucfirst($loan->loan_type) }} Loan
                                                </div>
                                                <div class="text-sm text-gray-500 font-mono">
                                                    #{{ $loan->loan_number }}
                                                </div>
                                                <div class="text-xs text-gray-400 truncate max-w-xs">
                                                    {{ Str::limit($loan->purpose, 50) }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            @if ($loan->customer && $loan->customer->profile_photo_url)
                                                <img class="h-8 w-8 rounded-full"
                                                    src="{{ $loan->customer->profile_photo_url }}"
                                                    alt="{{ $loan->customer->full_name }}">
                                            @else
                                                <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                                    <span class="text-gray-500 text-xs font-medium">
                                                        {{ substr($loan->customer->first_name ?? 'C', 0, 1) }}
                                                    </span>
                                                </div>
                                            @endif
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $loan->customer->full_name ?? 'N/A' }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $loan->customer->customer_number ?? 'N/A' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ number_format($loan->amount, 2) }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $loan->interest_rate }}% for {{ $loan->term_months }} months
                                        </div>
                                        @if ($loan->status === 'active')
                                            <div class="text-xs mt-1">
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Paid:</span>
                                                    <span class="font-medium">{{ number_format($loan->amount_paid, 2) }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Balance:</span>
                                                    <span class="font-medium">{{ number_format($loan->remaining_balance, 2) }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ $loan->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $loan->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $loan->status === 'active' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $loan->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $loan->status === 'completed' ? 'bg-gray-100 text-gray-800' : '' }}">
                                            <i class="fas fa-circle mr-1 text-xs"></i>
                                            {{ ucfirst($loan->status) }}
                                        </span>
                                        @if ($loan->next_payment_date && $loan->status === 'active')
                                            <div class="mt-1 text-xs {{ $loan->is_overdue ? 'text-red-600' : 'text-gray-500' }}">
                                                Next: {{ $loan->next_payment_date->format('M d, Y') }}
                                                @if ($loan->is_overdue)
                                                    <br><span class="text-red-500 font-medium">{{ $loan->days_overdue }} days overdue</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            Applied: {{ $loan->application_date->format('M d, Y') }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            @if ($loan->approved_at)
                                                Approved: {{ $loan->approved_at->format('M d') }}
                                            @endif
                                            @if ($loan->disbursed_at)
                                                <br>Disbursed: {{ $loan->disbursed_at->format('M d') }}
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('loans.show', $loan->id) }}"
                                                class="text-blue-600 hover:text-blue-900 transition-colors duration-150 p-1 rounded hover:bg-blue-50"
                                                title="View Loan">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if (Gate::allows('approve loans') && $loan->status === 'pending')
                                                <a href="{{ route('loans.review', $loan->id) }}"
                                                    class="text-green-600 hover:text-green-900 transition-colors duration-150 p-1 rounded hover:bg-green-50"
                                                    title="Review Loan">
                                                    <i class="fas fa-check-circle"></i>
                                                </a>
                                            @endif
                                            @if (Gate::allows('disburse loans') && $loan->status === 'approved')
                                                <a href="{{ route('loans.disburse', $loan->id) }}"
                                                    class="text-purple-600 hover:text-purple-900 transition-colors duration-150 p-1 rounded hover:bg-purple-50"
                                                    title="Disburse Loan">
                                                    <i class="fas fa-money-bill-wave"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="text-center py-12">
                        <div class="text-gray-400 mb-4">
                            <i class="fas fa-hand-holding-usd text-4xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900">No loans found</h3>
                        <p class="text-gray-500 mt-1">
                            @if (!$loans)
                                Unable to load loans. Please check your permissions.
                            @else
                                @if ($hasActiveFilters)
                                    Try adjusting your search or filters
                                @else
                                    No loan applications recorded yet.
                                @endif
                            @endif
                        </p>
                        @if ($hasActiveFilters)
                            <button wire:click="resetFilters"
                                class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-times-circle mr-2"></i>
                                Clear All Filters
                            </button>
                        @endif
                        @if ($canCreate && !$hasActiveFilters)
                            <a href="{{ route('loans.create') }}"
                                class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-plus-circle mr-2"></i>
                                Create First Loan Application
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Pagination -->
            @if ($loans && $loans->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $loans->links() }}
                </div>
            @endif
        </div>
    </div>
</div>