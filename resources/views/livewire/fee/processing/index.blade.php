<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-semibold text-gray-900">Fee Processing</h1>
            </div>

            <!-- Stats -->
            <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-4">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">Pending Fees</dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['pending'] }}</dd>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">Processed Fees</dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['processed'] }}</dd>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">Failed Fees</dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['failed'] }}</dd>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">Pending Amount</dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($stats['total_pending_amount'], 2) }}</dd>
                    </div>
                </div>
            </div>

            <!-- Processing Actions -->
            <div class="mt-6 bg-white shadow px-4 py-5 sm:rounded-lg">
                <div class="flex items-end space-x-4">
                    <div class="flex-1 max-w-xs">
                        <label for="processingDate" class="block text-sm font-medium text-gray-700">Processing Date</label>
                        <input type="date" wire:model="processingDate" id="processingDate" class="mblock w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <button wire:click="generatePending" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Generate Pending Fees
                    </button>
                    <button wire:click="processPending" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Process Pending Fees
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="mt-6 bg-white shadow px-4 py-5 sm:rounded-lg">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                        <input type="text" wire:model.live="search" id="search" class="mblock w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" placeholder="Account Number...">
                    </div>
                    <div>
                        <label for="statusFilter" class="block text-sm font-medium text-gray-700">Status</label>
                        <select wire:model.live="statusFilter" id="statusFilter" class="mblock w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="processed">Processed</option>
                            <option value="failed">Failed</option>
                            <option value="waived">Waived</option>
                        </select>
                    </div>
                    <div>
                        <label for="dateFrom" class="block text-sm font-medium text-gray-700">From Date</label>
                        <input type="date" wire:model.live="dateFrom" id="dateFrom" class="mblock w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="dateTo" class="block text-sm font-medium text-gray-700">To Date</label>
                        <input type="date" wire:model.live="dateTo" id="dateTo" class="mblock w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            <!-- Fees Table -->
            <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fee Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Charge Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($fees as $fee)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $fee->fee_reference }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $fee->account->account_number ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $fee->feeConfiguration->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ number_format($fee->amount, 2) }} {{ $fee->currency }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $fee->period_start->format('d/m/Y') }} - {{ $fee->period_end->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $fee->charge_date->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($fee->status === 'processed') bg-green-100 text-green-800
                                        @elseif($fee->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($fee->status === 'failed') bg-red-100 text-red-800
                                        @elseif($fee->status === 'waived') bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($fee->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    @if($fee->status === 'failed')
                                        <button wire:click="retryFee({{ $fee->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Retry</button>
                                    @endif
                                    @if($fee->status === 'pending')
                                        <button wire:click="waiveFee({{ $fee->id }})" class="text-yellow-600 hover:text-yellow-900 mr-3">Waive</button>
                                    @endif
                                    <a href="#" class="text-blue-600 hover:text-blue-900">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No fees found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $fees->links() }}
            </div>

            <!-- Processing Results Modal -->
            @if($showProcessingModal && $processingResults)
                <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                            Processing Results
                                        </h3>
                                        <div class="mt-4">
                                            <dl class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500">Total Processed</dt>
                                                    <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $processingResults['total_processed'] ?? 0 }}</dd>
                                                </div>
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500">Total Amount</dt>
                                                    <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($processingResults['total_amount'] ?? 0, 2) }}</dd>
                                                </div>
                                                @if(isset($processingResults['successful']))
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500">Successful</dt>
                                                    <dd class="mt-1 text-2xl font-semibold text-green-600">{{ $processingResults['successful'] }}</dd>
                                                </div>
                                                @endif
                                                @if(isset($processingResults['failed']))
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500">Failed</dt>
                                                    <dd class="mt-1 text-2xl font-semibold text-red-600">{{ $processingResults['failed'] }}</dd>
                                                </div>
                                                @endif
                                                @if(isset($processingResults['fees_generated']))
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500">Fees Generated</dt>
                                                    <dd class="mt-1 text-2xl font-semibold text-indigo-600">{{ $processingResults['fees_generated'] }}</dd>
                                                </div>
                                                @endif
                                                @if(isset($processingResults['waived']))
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500">Waived</dt>
                                                    <dd class="mt-1 text-2xl font-semibold text-gray-600">{{ $processingResults['waived'] }}</dd>
                                                </div>
                                                @endif
                                            </dl>

                                            @if(!empty($processingResults['errors']))
                                            <div class="mt-4">
                                                <h4 class="text-sm font-medium text-gray-900">Errors:</h4>
                                                <ul class="mt-2 text-sm text-red-600 list-disc list-inside">
                                                    @foreach($processingResults['errors'] as $error)
                                                        <li>{{ $error['error'] ?? $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="button" wire:click="$set('showProcessingModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>