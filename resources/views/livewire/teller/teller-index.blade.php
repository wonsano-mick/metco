<div>
    <div class="max-w-7xl mx-auto py-6 shadow-lg sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg">
            <!-- Header -->
            <div class="p-6 border-b shadow-md border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">
                            <i class="fas fa-users mr-2 text-purple-600"></i>
                            Teller Management
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">Manage tellers and their cash accounts</p>
                    </div>
                    <div class="flex space-x-3">
                        <!-- Filter Toggle Button -->
                        <button wire:click="toggleFilters"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                            <i class="fas fa-filter mr-2"></i>
                            Filters
                            @if ($hasActiveFilters)
                                <span 
                                    class="ml-2 inline-flex items-center justify-center h-5 w-5 rounded-full bg-purple-100 text-purple-800 text-xs font-semibold">
                                    {{ $activeFiltersCount }}
                                </span>
                            @endif
                        </button>
                    </div>
                </div>

                <!-- Filters Panel -->
                @if ($showFilters)
                    <div class="mt-6 transition-all duration-300 ease-in-out">
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Filter Tellers</h3>
                                @if ($hasActiveFilters)
                                    <button wire:click="resetFilters"
                                        class="text-sm text-purple-600 hover:text-purple-800 font-medium">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        Clear All Filters
                                    </button>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Search -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                    <div class="relative">
                                        <input type="text" wire:model.live.debounce.300ms="search"
                                            placeholder="Search by name, email, username..."
                                            class="pl-10 pr-8 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-search text-gray-400"></i>
                                        </div>
                                        @if ($search)
                                            <button wire:click="clearSearch"
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <!-- Branch Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                                    <div class="relative">
                                        <select wire:model.live="branchId"
                                            class="pr-8 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                                            <option value="">All Branches</option>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Status Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <div class="relative">
                                        <select wire:model.live="status"
                                            class="pr-8 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                                            <option value="">All Status</option>
                                            <option value="active">Active</option>
                                            <option value="suspended">Suspended</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Active Filters Badges -->
                            @if ($hasActiveFilters)
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @if ($search)
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            Search: "{{ $search }}"
                                            <button wire:click="clearSearch"
                                                class="ml-1 text-purple-600 hover:text-purple-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </span>
                                    @endif
                                    @if ($branchId)
                                        @php
                                            $selectedBranch = $branches->firstWhere('id', $branchId);
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Branch: {{ $selectedBranch->name ?? 'N/A' }}
                                            <button wire:click="resetFilters"
                                                class="ml-1 text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </span>
                                    @endif
                                    @if ($status)
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Status: {{ ucfirst($status) }}
                                            <button wire:click="resetFilters"
                                                class="ml-1 text-green-600 hover:text-green-800">
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

            <!-- Tellers Table -->
            <div class="overflow-x-auto">
                <!-- Results Summary -->
                <div class="px-6 py-4 flex items-center justify-between border-b border-gray-200">
                    <div class="text-sm text-gray-600">
                        @if ($tellers && $tellers->total() > 0)
                            Showing {{ $tellers->firstItem() }} to {{ $tellers->lastItem() }} of {{ $tellers->total() }}
                            tellers
                            @if ($hasActiveFilters)
                                <span class="font-medium">(filtered)</span>
                            @endif
                        @endif
                    </div>

                    <!-- Items per page -->
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-600">Show:</span>
                        <select wire:model.live="perPage"
                            class="border border-gray-300 rounded-md shadow-sm py-1 px-2 text-sm w-20 focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                        </select>
                        <span class="text-sm text-gray-600">per page</span>
                    </div>
                </div>

                @if ($tellers && $tellers->count())
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Teller
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Branch
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Cash Balance
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Last Login
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($tellers as $teller)
                                @php
                                    $cashBalance = $this->getTellerCashBalance($teller->id);
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div
                                                    class="h-10 w-10 rounded-full bg-gradient-to-r from-purple-500 to-purple-600 flex items-center justify-center text-white font-bold">
                                                    {{ $teller->initials }}
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $teller->full_name }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $teller->email }}
                                                </div>
                                                @if ($teller->username)
                                                    <div class="text-xs text-gray-400">
                                                        @: {{ $teller->username }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $teller->branch->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @switch($teller->status)
                                                @case('active') bg-green-100 text-green-800 @break
                                                @case('suspended') bg-red-100 text-red-800 @break
                                                @case('inactive') bg-gray-100 text-gray-800 @break
                                                @default bg-gray-100 text-gray-800
                                            @endswitch">
                                            {{ ucfirst($teller->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span class="text-lg font-bold 
                                            @if($cashBalance > 1000) text-green-600 
                                            @elseif($cashBalance > 0) text-blue-600 
                                            @else text-gray-400 @endif">
                                            GHS {{ number_format($cashBalance, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if ($teller->last_login_at)
                                            {{ $teller->last_login_at->diffForHumans() }}
                                        @else
                                            Never
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center justify-center space-x-3">
                                            <button wire:click="openTopUpModal({{ $teller->id }})"
                                                class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 rounded-md hover:bg-green-200 transition-colors duration-200"
                                                title="Top Up Teller Cash">
                                                <i class="fas fa-plus-circle mr-1"></i>
                                                <span class="text-xs">Top Up</span>
                                            </button>
                                            
                                            <button wire:click="openWithdrawModal({{ $teller->id }})"
                                                class="inline-flex items-center px-3 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition-colors duration-200"
                                                title="Withdraw Teller Cash">
                                                <i class="fas fa-minus-circle mr-1"></i>
                                                <span class="text-xs">Withdraw</span>
                                            </button>
                                            
                                            <a href="{{ route('users.show', $teller->id) }}"
                                                class="text-blue-600 hover:text-blue-900 p-1"
                                                title="View Teller Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="text-center py-12">
                        <div class="text-gray-400 mb-4">
                            <i class="fas fa-users text-4xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900">No tellers found</h3>
                        <p class="text-gray-500 mt-1">
                            @if ($hasActiveFilters)
                                Try adjusting your search or filters
                            @else
                                No tellers in the system yet.
                            @endif
                        </p>
                        @if ($hasActiveFilters)
                            <button wire:click="resetFilters"
                                class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-purple-700 bg-purple-100 hover:bg-purple-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                <i class="fas fa-times-circle mr-2"></i>
                                Clear All Filters
                            </button>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Pagination -->
            @if ($tellers && $tellers->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $tellers->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Cash Transaction Modal (Top Up / Withdraw) -->
    @if ($showCashModal && $selectedTeller)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full 
                                {{ $transactionType === 'topup' ? 'bg-green-100' : 'bg-red-100' }} sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas {{ $transactionType === 'topup' ? 'fa-plus-circle text-green-600' : 'fa-minus-circle text-red-600' }} text-xl"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    {{ $transactionType === 'topup' ? 'Top Up Teller Cash' : 'Withdraw Teller Cash' }}
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        {{ $transactionType === 'topup' ? 'Add cash to' : 'Withdraw cash from' }} 
                                        <strong>{{ $selectedTeller->full_name }}'s</strong> cash drawer.
                                    </p>
                                </div>

                                <form wire:submit.prevent="processCashTransaction" class="mt-4">
                                    <div class="space-y-4">
                                        <!-- Current Balance Display -->
                                        <div class="bg-gray-50 p-3 rounded-lg">
                                            <p class="text-sm text-gray-600">Current Balance:</p>
                                            <p class="text-xl font-bold text-gray-900">
                                                GHS {{ number_format($this->getTellerCashBalance($selectedTeller->id), 2) }}
                                            </p>
                                        </div>

                                        <!-- Amount Input -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Amount (GHS) <span class="text-red-500">*</span>
                                            </label>
                                            <input type="number" 
                                                wire:model="amount" 
                                                step="0.01" 
                                                min="1"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm"
                                                placeholder="Enter amount">
                                            @error('amount') 
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <!-- Reference Input -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Reference <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" 
                                                wire:model="reference" 
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm"
                                                placeholder="{{ $transactionType === 'topup' ? 'e.g., Cash from vault' : 'e.g., Cash to vault' }}">
                                            @error('reference') 
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="mt-6 flex justify-end space-x-3">
                                        <button type="button" 
                                            wire:click="closeModal"
                                            class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:text-sm">
                                            Cancel
                                        </button>
                                        <button type="submit"
                                            class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 
                                                {{ $transactionType === 'topup' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700' }} 
                                                text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 
                                                {{ $transactionType === 'topup' ? 'focus:ring-green-500' : 'focus:ring-red-500' }} sm:text-sm">
                                            {{ $transactionType === 'topup' ? 'Top Up' : 'Withdraw' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>