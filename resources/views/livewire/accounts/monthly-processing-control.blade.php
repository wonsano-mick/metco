<div class="bg-white shadow-lg sm:rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-base font-semibold leading-6 text-gray-900">Monthly Processing Control</h3>
        <div class="mt-2 max-w-xl text-sm text-gray-500">
            <p>Manually trigger monthly fee and interest processing for accounts. This will process all active accounts that haven't been processed for the selected month.</p>
        </div>
        
        <form wire:submit="triggerProcessing" class="mt-5">
            <div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-6">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium leading-6 text-gray-900">Processing Month</label>
                    <input type="month" wire:model="processingMonth" 
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        max="{{ now()->format('Y-m') }}" 
                        @if($isProcessing) disabled @endif>
                    @error('processingMonth') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium leading-6 text-gray-900">Account Type (Optional)</label>
                    <select wire:model="accountTypeId"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        @if($isProcessing) disabled @endif>
                        <option value="">All Account Types</option>
                        @foreach($accountTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }} ({{ $type->interest_rate }}% / Fee: {{ number_format($type->monthly_fee, 2) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2 flex items-end">
                    <button type="submit" 
                        wire:loading.attr="disabled" 
                        wire:target="triggerProcessing"
                        class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50">
                        <span wire:loading.remove wire:target="triggerProcessing">Trigger Processing</span>
                        <span wire:loading wire:target="triggerProcessing">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                    </button>
                </div>
            </div>
        </form>

        @if($isProcessing)
            <div class="mt-4">
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="animate-spin h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                Processing accounts... Please wait. This may take a few moments.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(!empty($processingLog))
            <div class="mt-6">
                <h4 class="text-sm font-medium text-gray-900">Processing Log</h4>
                <div class="mt-2 bg-gray-50 rounded-md p-4 max-h-96 overflow-y-auto">
                    <div class="space-y-2">
                        @foreach($processingLog as $log)
                            <div class="text-sm {{ $log['type'] === 'error' ? 'text-red-600' : ($log['type'] === 'warning' ? 'text-yellow-600' : 'text-gray-600') }}">
                                <span class="text-gray-400">{{ $log['time'] }}</span> - {{ $log['message'] }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <div class="mt-6 border-t border-gray-200 pt-4">
            <h4 class="text-sm font-medium text-gray-900">Processing Summary</h4>
            <dl class="mt-2 grid grid-cols-1 gap-5 sm:grid-cols-4">
                <div class="overflow-hidden rounded-lg bg-gray-50 px-4 py-5 sm:p-6">
                    <dt class="truncate text-sm font-medium text-gray-500">Total Account Types</dt>
                    <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $accountTypes->count() }}</dd>
                </div>
                <div class="overflow-hidden rounded-lg bg-gray-50 px-4 py-5 sm:p-6">
                    <dt class="truncate text-sm font-medium text-gray-500">Avg Interest Rate</dt>
                    <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ number_format($accountTypes->avg('interest_rate'), 2) }}%</dd>
                </div>
                <div class="overflow-hidden rounded-lg bg-gray-50 px-4 py-5 sm:p-6">
                    <dt class="truncate text-sm font-medium text-gray-500">Avg Monthly Fee</dt>
                    <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ number_format($accountTypes->avg('monthly_fee'), 2) }}</dd>
                </div>
                <div class="overflow-hidden rounded-lg bg-gray-50 px-4 py-5 sm:p-6">
                    <dt class="truncate text-sm font-medium text-gray-500">Last Processed</dt>
                    <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">
                        @php
                            $lastProcessing = \App\Models\Eloquent\MonthlyAccountProcessing::latest('processed_at')->first();
                        @endphp
                        {{ $lastProcessing ? $lastProcessing->processed_at->format('M Y') : 'Never' }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</div>