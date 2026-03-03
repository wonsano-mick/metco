<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-semibold text-gray-900">Create Fee Configuration</h1>
                <a href="{{ route('fee.configurations') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Back to List
                </a>
            </div>

            <div class="mt-6 bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
                <form wire:submit="save">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"> 
                        <!-- Account Type Selection -->
                        <div>
                            <label for="account_type_id" class="block text-sm font-medium text-gray-700">Account Type *</label>
                            <select wire:model.live="account_type_id" id="account_type_id" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Account Type</option>
                                @foreach($accountTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('account_type_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Account Selection (if specific accounts needed) -->
                        @if($account_type_id)
                        <div>
                            <label for="apply_to_all_accounts" class="block text-sm font-medium text-gray-700">Apply To</label>
                            <div class="mt-2 space-y-2">
                                <div class="flex items-center">
                                    <input type="radio" wire:model.live="apply_to_all_accounts" id="apply_all" value="1" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                    <label for="apply_all" class="ml-3 block text-sm font-medium text-gray-700">
                                        All {{ $this->selectedAccountTypeName }} Accounts
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" wire:model.live="apply_to_all_accounts" id="apply_selected" value="0" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                    <label for="apply_selected" class="ml-3 block text-sm font-medium text-gray-700">
                                        Selected Accounts Only
                                    </label>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Account Search and Selection -->
                        @if($account_type_id && !$apply_to_all_accounts)
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Accounts</label>
                            
                            <!-- Search Box -->
                            <div class="mb-4">
                                <input type="text" wire:model.live="accountSearch" placeholder="Search by account number or customer name..." 
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Account Selection Table -->
                            <div class="border rounded-lg overflow-hidden">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <input type="checkbox" wire:model.live="selectAll" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                            </th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Number</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($this->availableAccounts as $account)
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap">
                                                <input type="checkbox" wire:model="selectedAccounts" value="{{ $account->id }}" 
                                                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $account->account_number }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                                {{ $account->customer->full_name ?? 'N/A' }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                                {{ number_format($account->current_balance, 2) }} {{ $account->currency }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $account->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ ucfirst($account->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-2 text-center text-sm text-gray-500">
                                                No accounts found for this account type
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Selected Count -->
                            <div class="mt-2 text-sm text-gray-600">
                                {{ count($selectedAccounts) }} account(s) selected
                            </div>
                        </div>
                        @endif

                        <!-- Rest of the form fields (same as before) -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Configuration Name *</label>
                            <input type="text" wire:model="name" id="name" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" placeholder="e.g., Monthly Maintenance Fee">
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-700">Code *</label>
                            <input type="text" wire:model="code" id="code" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" placeholder="e.g., MAINT-FEE-001">
                            @error('code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="frequency" class="block text-sm font-medium text-gray-700">Frequency *</label>
                            <select wire:model="frequency" id="frequency" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                            @error('frequency') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="calculation_method" class="block text-sm font-medium text-gray-700">Calculation Method *</label>
                            <select wire:model.live="calculation_method" id="calculation_method" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="fixed">Fixed Amount</option>
                                <option value="percentage">Percentage of Balance</option>
                                <option value="tiered">Tiered</option>
                            </select>
                            @error('calculation_method') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        @if($calculation_method === 'fixed' || $calculation_method === 'tiered')
                            <div>
                                <label for="fee_amount" class="block text-sm font-medium text-gray-700">Fee Amount *</label>
                                <input type="number" step="0.01" wire:model="fee_amount" id="fee_amount" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                @error('fee_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        @if($calculation_method === 'percentage')
                            <div>
                                <label for="percentage_rate" class="block text-sm font-medium text-gray-700">Percentage Rate (%) *</label>
                                <input type="number" step="0.01" wire:model="percentage_rate" id="percentage_rate" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                @error('percentage_rate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        @if($calculation_method === 'tiered')
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tiered Fee Structure</label>
                                <div class="space-y-2">
                                    @foreach($tiers as $index => $tier)
                                    <div class="flex items-center space-x-2">
                                        <input type="number" step="0.01" wire:model="tiers.{{ $index }}.min" placeholder="Min Balance" class="block w-1/4 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <span>-</span>
                                        <input type="number" step="0.01" wire:model="tiers.{{ $index }}.max" placeholder="Max Balance" class="block w-1/4 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <input type="number" step="0.01" wire:model="tiers.{{ $index }}.fee" placeholder="Fee Amount" class="block w-1/4 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <button type="button" wire:click="removeTier({{ $index }})" class="text-red-600 hover:text-red-900">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                    @endforeach
                                    <button type="button" wire:click="addTier" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Add Tier
                                    </button>
                                </div>
                            </div>
                        @endif

                        <div>
                            <label for="currency" class="block text-sm font-medium text-gray-700">Currency *</label>
                            <input type="text" wire:model="currency" id="currency" maxlength="3" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @error('currency') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="charge_day" class="block text-sm font-medium text-gray-700">Charge Day *</label>
                            <select wire:model.live="charge_day" id="charge_day" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="day_of_month">Day of Month</option>
                                <option value="day_of_week">Day of Week</option>
                                <option value="last_day">Last Day of Period</option>
                            </select>
                            @error('charge_day') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        @if($charge_day === 'day_of_month')
                        <div>
                            <label for="charge_day_value" class="block text-sm font-medium text-gray-700">Day of Month (1-31) *</label>
                            <input type="number" wire:model="charge_day_value" id="charge_day_value" min="1" max="31" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @error('charge_day_value') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        @elseif($charge_day === 'day_of_week')
                        <div>
                            <label for="charge_day_value" class="block text-sm font-medium text-gray-700">Day of Week (1=Monday, 7=Sunday) *</label>
                            <input type="number" wire:model="charge_day_value" id="charge_day_value" min="1" max="7" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @error('charge_day_value') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        @endif

                        <div class="sm:col-span-2">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" wire:model.live="has_minimum_balance_waiver" id="has_minimum_balance_waiver" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="has_minimum_balance_waiver" class="font-medium text-gray-700">Enable Minimum Balance Waiver</label>
                                    <p class="text-gray-500">Waive fee if account balance meets threshold</p>
                                </div>
                            </div>
                        </div>

                        @if($has_minimum_balance_waiver)
                            <div>
                                <label for="minimum_balance_threshold" class="block text-sm font-medium text-gray-700">Minimum Balance Threshold *</label>
                                <input type="number" step="0.01" wire:model="minimum_balance_threshold" id="minimum_balance_threshold" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                @error('minimum_balance_threshold') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div class="sm:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea wire:model="description" id="description" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                            @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" wire:model="is_active" id="is_active" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_active" class="font-medium text-gray-700">Active</label>
                                    <p class="text-gray-500">Enable this fee configuration</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Create Configuration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>