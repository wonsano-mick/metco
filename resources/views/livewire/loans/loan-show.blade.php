<div>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <div class="flex items-center">
                            <a href="{{ route('loans.index') }}" 
                               class="text-blue-600 hover:text-blue-800 mr-3">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                            <h1 class="text-2xl font-bold text-gray-800">
                                Loan #{{ $loan->loan_number }}
                            </h1>
                            <span class="ml-3 px-3 py-1 text-xs font-medium rounded-full 
                                {{ $loan->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $loan->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $loan->status === 'active' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $loan->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $loan->status === 'disbursed' ? 'bg-purple-100 text-purple-800' : '' }}">
                                {{ strtoupper($loan->status) }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ ucfirst($loan->loan_type) }} Loan • 
                            Applied: {{ $loan->application_date->format('M d, Y') }} • 
                            Loan Officer: {{ $loan->loanOfficer->full_name ?? 'N/A' }}
                        </p>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex space-x-3">
                        @if($canApprove) 
                            <button wire:click="openApproveModal"
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <i class="fas fa-check-circle mr-2"></i>
                                Approve
                            </button>
                        @endif
                        @if($canReject)
                            <button wire:click="openRejectModal"
                                class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <i class="fas fa-times-circle mr-2"></i>
                                Reject
                            </button>
                        @endif
                        
                        @if($canDisburse)
                            <button wire:click="openDisburseModal"
                                class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                <i class="fas fa-money-bill-wave mr-2"></i>
                                Disburse
                            </button>
                        @endif
                        
                        <button onclick="window.print()"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-print mr-2"></i>
                            Print
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Loan Amount -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-lg bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-hand-holding-usd text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">Loan Amount</div>
                        <div class="text-2xl font-bold text-gray-900">
                            GHS {{ number_format($loan->amount, 2) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Amount Paid -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-lg bg-green-100 flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">Amount Paid</div>
                        <div class="text-2xl font-bold text-gray-900">
                            GHS {{ number_format($stats['total_paid'], 2) }}
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ number_format(($stats['total_paid'] / $loan->total_amount) * 100, 1) }}% of total
                        </div>
                    </div>
                </div>
            </div>

            <!-- Remaining Balance -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-lg bg-orange-100 flex items-center justify-center">
                            <i class="fas fa-balance-scale text-orange-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">Remaining Balance</div>
                        <div class="text-2xl font-bold text-gray-900">
                            GHS {{ number_format($stats['remaining_balance'], 2) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Next Payment -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-lg bg-purple-100 flex items-center justify-center">
                            <i class="far fa-calendar-alt text-purple-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">Next Payment</div>
                        <div class="text-lg font-bold text-gray-900">
                            @if($stats['next_payment'])
                                {{ $stats['next_payment']->format('M d, Y') }}
                            @else
                                N/A
                            @endif
                        </div>
                        @if($stats['days_overdue'] > 0)
                            <div class="text-sm text-red-600">
                                {{ $stats['days_overdue'] }} days overdue
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-lg shadow-lg">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <button wire:click="$set('activeTab', 'details')"
                        class="py-4 px-6 text-sm font-medium border-b-2 {{ $activeTab === 'details' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        <i class="fas fa-info-circle mr-2"></i>
                        Loan Details
                    </button>
                    <button wire:click="$set('activeTab', 'repayments')"
                        class="py-4 px-6 text-sm font-medium border-b-2 {{ $activeTab === 'repayments' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        <i class="fas fa-calendar-check mr-2"></i>
                        Repayment Schedule
                        <span class="ml-1 bg-gray-100 text-gray-800 text-xs font-medium px-2 py-0.5 rounded">
                            {{ $stats['installments_paid'] }}/{{ $stats['total_installments'] }}
                        </span>
                    </button>
                    <button wire:click="$set('activeTab', 'transactions')"
                        class="py-4 px-6 text-sm font-medium border-b-2 {{ $activeTab === 'transactions' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        <i class="fas fa-exchange-alt mr-2"></i>
                        Transactions
                    </button>
                    <button wire:click="$set('activeTab', 'documents')"
                        class="py-4 px-6 text-sm font-medium border-b-2 {{ $activeTab === 'documents' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        <i class="fas fa-file-alt mr-2"></i>
                        Documents
                    </button>
                    @if($loan->committeeReviews->isNotEmpty())
                        <button wire:click="$set('activeTab', 'committee')"
                            class="py-4 px-6 text-sm font-medium border-b-2 {{ $activeTab === 'committee' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            <i class="fas fa-users mr-2"></i>
                            Committee Reviews
                        </button>
                    @endif
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Details Tab -->
                @if($activeTab === 'details')
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Customer Information -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-user mr-2"></i>
                                Customer Information
                            </h3>
                            <div class="space-y-4">
                                <div class="flex items-center">
                                    @if($loan->customer->profile_photo_url)
                                        <img class="h-12 w-12 rounded-full" 
                                             src="{{ $loan->customer->profile_photo_url }}" 
                                             alt="{{ $loan->customer->full_name }}">
                                    @else
                                        <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                                            <span class="text-blue-600 font-medium text-lg">
                                                {{ substr($loan->customer->first_name, 0, 1) }}{{ substr($loan->customer->last_name, 0, 1) }}
                                            </span>
                                        </div>
                                    @endif
                                    <div class="ml-4">
                                        <div class="text-lg font-medium text-gray-900">
                                            {{ $loan->customer->full_name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            #{{ $loan->customer->customer_number }}
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <div class="text-sm text-gray-500">Phone</div>
                                        <div class="font-medium">{{ $loan->customer->phone }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-500">Email</div>
                                        <div class="font-medium">{{ $loan->customer->email }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-500">Monthly Income</div>
                                        <div class="font-medium">GHS {{ number_format($loan->customer->monthly_income, 2) }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-500">KYC Status</div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $loan->customer->kyc_status === 'verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ ucfirst($loan->customer->kyc_status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Loan Information -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-file-contract mr-2"></i>
                                Loan Information
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <div class="text-sm text-gray-500">Loan Type</div>
                                    <div class="font-medium capitalize">{{ $loan->loan_type }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Purpose</div>
                                    <div class="font-medium">{{ $loan->purpose }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Amount</div>
                                    <div class="font-medium">GHS {{ number_format($loan->amount, 2) }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Interest Rate</div>
                                    <div class="font-medium">{{ $loan->interest_rate }}% {{ ucfirst($loan->interest_type) }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Term</div>
                                    <div class="font-medium">{{ $loan->term_months }} months</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Repayment Frequency</div>
                                    <div class="font-medium capitalize">{{ $loan->repayment_frequency }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Monthly Payment</div>
                                    <div class="font-medium">GHS {{ number_format($loan->monthly_payment, 2) }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Total Payable</div>
                                    <div class="font-medium">GHS {{ number_format($loan->total_amount, 2) }}</div>
                                </div>
                                <div class="col-span-2">
                                    <div class="text-sm text-gray-500">Disbursement Method</div>
                                    <div class="font-medium capitalize">{{ str_replace('_', ' ', $loan->disbursement_method) }}</div>
                                </div>
                                @if($loan->account)
                                    <div class="col-span-2">
                                        <div class="text-sm text-gray-500">Linked Account</div>
                                        <div class="font-medium">{{ $loan->account->account_number }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Collateral Information -->
                        @if($loan->collateral_value || !empty($loan->collateral_details))
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <i class="fas fa-landmark mr-2"></i>
                                    Collateral Information
                                </h3>
                                <div class="space-y-4">
                                    @if($loan->collateral_value)
                                        <div>
                                            <div class="text-sm text-gray-500">Collateral Value</div>
                                            <div class="font-medium">GHS {{ number_format($loan->collateral_value, 2) }}</div>
                                        </div>
                                    @endif
                                    @if(!empty($loan->collateral_details))
                                        <div>
                                            <div class="text-sm text-gray-500">Collateral Type</div>
                                            <div class="font-medium">
                                                {{ implode(', ', array_map('ucfirst', $loan->collateral_details)) }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Guarantors -->
                        @if(!empty($loan->guarantors))
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <i class="fas fa-users mr-2"></i>
                                    Guarantors
                                </h3>
                                <div class="space-y-3">
                                    @foreach($loan->guarantors as $guarantor)
                                        <div class="border-b border-gray-200 pb-3 last:border-0 last:pb-0">
                                            <div class="font-medium">{{ $guarantor['name'] }}</div>
                                            <div class="text-sm text-gray-500">
                                                {{ $guarantor['relationship'] }} • {{ $guarantor['phone'] }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Timeline -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="far fa-clock mr-2"></i>
                                Timeline
                            </h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Application Date</span>
                                    <span class="font-medium">{{ $loan->application_date->format('M d, Y H:i') }}</span>
                                </div>
                                @if($loan->approved_at)
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">Approval Date</span>
                                        <span class="font-medium">{{ $loan->approved_at->format('M d, Y H:i') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">Approved By</span>
                                        <span class="font-medium">{{ $loan->approver->full_name ?? 'N/A' }}</span>
                                    </div>
                                @endif
                                @if($loan->disbursed_at)
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">Disbursement Date</span>
                                        <span class="font-medium">{{ $loan->disbursed_at->format('M d, Y H:i') }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Start Date</span>
                                    <span class="font-medium">{{ $loan->start_date->format('M d, Y') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">End Date</span>
                                    <span class="font-medium">{{ $loan->end_date->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Repayments Tab -->
                @if($activeTab === 'repayments')
                    <div class="space-y-6">
                        <!-- Repayment Summary -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <div class="text-sm text-blue-600">Total Installments</div>
                                    <div class="text-2xl font-bold text-blue-900">{{ $loan->term_months }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-green-600">Paid Installments</div>
                                    <div class="text-2xl font-bold text-green-900">
                                        {{ $loan->repayments()->where('status', 'paid')->count() }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-sm text-orange-600">Pending Installments</div>
                                    <div class="text-2xl font-bold text-orange-900">
                                        {{ $loan->repayments()->where('status', 'pending')->count() }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Repayments Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Installment
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Due Date
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Amount Due
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Paid Date
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($loan->repayments()->orderBy('installment_number')->get() as $repayment)
                                        <tr class="{{ $repayment->status === 'overdue' ? 'bg-red-50' : '' }}">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    #{{ $repayment->installment_number }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    {{ $repayment->due_date->format('M d, Y') }}
                                                </div>
                                                @if($repayment->is_overdue)
                                                    <div class="text-xs text-red-600">
                                                        {{ $repayment->days_overdue }} days overdue
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    GHS {{ number_format($repayment->total_due, 2) }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    Principal: {{ number_format($repayment->principal_amount, 2) }}
                                                    | Interest: {{ number_format($repayment->interest_amount, 2) }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs font-medium rounded-full 
                                                    {{ $repayment->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $repayment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                    {{ $repayment->status === 'overdue' ? 'bg-red-100 text-red-800' : '' }}">
                                                    {{ ucfirst($repayment->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($repayment->paid_date)
                                                    <div class="text-sm text-gray-900">
                                                        {{ $repayment->paid_date->format('M d, Y') }}
                                                    </div>
                                                @else
                                                    <span class="text-sm text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                @if($canProcessPayment && $repayment->status === 'pending')
                                                    <button wire:click="markAsPaid({{ $repayment->id }})"
                                                        class="text-green-600 hover:text-green-900 transition-colors duration-150 p-1 rounded hover:bg-green-50"
                                                        title="Mark as Paid">
                                                        <i class="fas fa-check-circle"></i>
                                                    </button>
                                                @endif
                                                @if($repayment->transaction_id)
                                                    <a href="{{ route('transactions.show', $repayment->transaction_id) }}"
                                                        class="text-blue-600 hover:text-blue-900 transition-colors duration-150 p-1 rounded hover:bg-blue-50 ml-2"
                                                        title="View Transaction">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Transactions Tab -->
                @if($activeTab === 'transactions')
                    <div class="space-y-6">
                        @if($loan->transactions->isNotEmpty())
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Transaction
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Amount
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Type
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Date
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($loan->transactions as $transaction)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        #{{ $transaction->transaction_reference }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $transaction->description }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        GHS {{ number_format($transaction->amount, 2) }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900 capitalize">
                                                        {{ str_replace('_', ' ', $transaction->type) }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                                                        {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                                        {{ $transaction->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                                        {{ ucfirst($transaction->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        {{ $transaction->initiated_at->format('M d, Y') }}
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        {{ $transaction->initiated_at->format('h:i A') }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('transactions.show', $transaction->id) }}"
                                                        class="text-blue-600 hover:text-blue-900 transition-colors duration-150 p-1 rounded hover:bg-blue-50"
                                                        title="View Transaction">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-12">
                                <div class="text-gray-400 mb-4">
                                    <i class="fas fa-exchange-alt text-4xl"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900">No transactions found</h3>
                                <p class="text-gray-500 mt-1">No transactions have been recorded for this loan yet.</p>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Committee Reviews Tab -->
                @if($activeTab === 'committee' && $loan->committeeReviews->isNotEmpty())
                    <div class="space-y-6">
                        @foreach($loan->committeeReviews as $review)
                            <div class="bg-gray-50 rounded-lg p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900">
                                            Review by {{ $review->reviewer->full_name }}
                                        </h4>
                                        <p class="text-sm text-gray-500">
                                            {{ $review->reviewed_at->format('M d, Y H:i') }}
                                        </p>
                                    </div>
                                    <span class="px-3 py-1 text-xs font-medium rounded-full 
                                        {{ $review->decision === 'approve' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $review->decision === 'reject' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $review->decision === 'refer' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                        {{ ucfirst($review->decision) }}
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div>
                                        <div class="text-sm text-gray-500">Score</div>
                                        <div class="text-lg font-bold text-gray-900">
                                            {{ $review->score }}/10
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-500">Risk Level</div>
                                        <div class="text-lg font-bold 
                                            {{ $review->risk_level === 'low' ? 'text-green-600' : '' }}
                                            {{ $review->risk_level === 'medium' ? 'text-yellow-600' : '' }}
                                            {{ $review->risk_level === 'high' ? 'text-orange-600' : '' }}
                                            {{ $review->risk_level === 'very_high' ? 'text-red-600' : '' }}">
                                            {{ ucfirst(str_replace('_', ' ', $review->risk_level)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-500">Recommendation</div>
                                        <div class="text-lg font-bold text-gray-900">
                                            {{ ucfirst($review->recommendation) }}
                                        </div>
                                    </div>
                                </div>

                                @if($review->comments)
                                    <div class="mb-4">
                                        <div class="text-sm text-gray-500 mb-1">Comments</div>
                                        <div class="text-gray-700 bg-white p-3 rounded border">
                                            {{ $review->comments }}
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($review->conditions))
                                    <div>
                                        <div class="text-sm text-gray-500 mb-1">Conditions</div>
                                        <div class="text-gray-700 bg-white p-3 rounded border">
                                            <ul class="list-disc pl-5 space-y-1">
                                                @foreach($review->conditions as $condition)
                                                    <li>{{ $condition }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    @if($showApproveModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-check-circle text-green-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Approve Loan Application
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Are you sure you want to approve loan #{{ $loan->loan_number }}?
                                    </p>
                                    <div class="mt-4 bg-gray-50 p-4 rounded-md">
                                        <h4 class="text-sm font-medium text-gray-700 mb-2">Loan Details:</h4>
                                        <div class="grid grid-cols-2 gap-2 text-sm">
                                            <div class="text-gray-600">Amount:</div>
                                            <div class="text-gray-900 font-medium">GHS {{ number_format($loan->amount, 2) }}</div>
                                            <div class="text-gray-600">Customer:</div>
                                            <div class="text-gray-900">{{ $loan->customer->full_name }}</div>
                                            <div class="text-gray-600">Term:</div>
                                            <div class="text-gray-900">{{ $loan->term_months }} months</div>
                                            <div class="text-gray-600">Interest Rate:</div>
                                            <div class="text-gray-900">{{ $loan->interest_rate }}%</div>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <label for="approvalNotes" class="block text-sm font-medium text-gray-700">
                                            Approval Notes (Optional)
                                        </label>
                                        <textarea wire:model="approvalNotes" id="approvalNotes" rows="3"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Add notes about this approval..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="approveLoan" wire:loading.attr="disabled"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                            <span wire:loading.remove wire:target="approveLoan">
                                Approve Loan
                            </span>
                            <span wire:loading wire:target="approveLoan">
                                <i class="fas fa-spinner fa-spin mr-2"></i> Processing...
                            </span>
                        </button>
                        <button type="button" wire:click="closeModal('showApproveModal')"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Reject Modal -->
    @if($showRejectModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-times-circle text-red-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Reject Loan Application
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Are you sure you want to reject loan #{{ $loan->loan_number }}?
                                    </p>
                                    <div class="mt-4">
                                        <label for="rejectionReason" class="block text-sm font-medium text-gray-700">
                                            Reason for Rejection <span class="text-red-500">*</span>
                                        </label>
                                        <textarea wire:model="rejectionReason" id="rejectionReason" rows="3"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Explain why this loan is being rejected..."
                                            required></textarea>
                                        @error('rejectionReason')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="rejectLoan" wire:loading.attr="disabled"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                            <span wire:loading.remove wire:target="rejectLoan">
                                Reject Loan
                            </span>
                            <span wire:loading wire:target="rejectLoan">
                                <i class="fas fa-spinner fa-spin mr-2"></i> Processing...
                            </span>
                        </button>
                        <button type="button" wire:click="closeModal('showRejectModal')"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Disburse Modal -->
    @if($showDisburseModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-purple-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-money-bill-wave text-purple-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Disburse Loan
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Disburse loan #{{ $loan->loan_number }} to customer.
                                    </p>
                                    <div class="mt-4 space-y-4">
                                        <!-- Disbursement Method -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Disbursement Method <span class="text-red-500">*</span>
                                            </label>
                                            <div class="grid grid-cols-2 gap-2">
                                                @foreach(['bank_transfer', 'cash', 'cheque', 'mobile_money'] as $method)
                                                    <button type="button" 
                                                        wire:click="$set('disbursementData.method', '{{ $method }}')"
                                                        class="p-2 border rounded text-center text-sm
                                                            {{ $disbursementData['method'] === $method ? 'border-purple-500 bg-purple-50' : 'border-gray-300 hover:bg-gray-50' }}">
                                                        {{ ucfirst(str_replace('_', ' ', $method)) }}
                                                    </button>
                                                @endforeach
                                            </div>
                                            @error('disbursementData.method')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <!-- Bank Transfer Details -->
                                        @if($disbursementData['method'] === 'bank_transfer' && $loan->account)
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">
                                                    Account for Transfer
                                                </label>
                                                <div class="mt-1 p-3 bg-gray-50 rounded border">
                                                    <div class="font-medium">{{ $loan->account->account_number }}</div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $loan->account->accountType->name ?? 'N/A' }} • 
                                                        Balance: GHS {{ number_format($loan->account->current_balance, 2) }}
                                                    </div>
                                                </div>
                                                <input type="hidden" wire:model="disbursementData.account_id" value="{{ $loan->account->id }}">
                                            </div>
                                        @endif

                                        <!-- Cheque Details -->
                                        @if($disbursementData['method'] === 'cheque')
                                            <div>
                                                <label for="cheque_number" class="block text-sm font-medium text-gray-700">
                                                    Cheque Number <span class="text-red-500">*</span>
                                                </label>
                                                <input type="text" wire:model="disbursementData.cheque_number" id="cheque_number"
                                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                                    placeholder="Enter cheque number">
                                                @error('disbursementData.cheque_number')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        @endif

                                        <!-- Mobile Money Details -->
                                        @if($disbursementData['method'] === 'mobile_money')
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label for="mobile_money_number" class="block text-sm font-medium text-gray-700">
                                                        Mobile Number <span class="text-red-500">*</span>
                                                    </label>
                                                    <input type="text" wire:model="disbursementData.mobile_money_number" id="mobile_money_number"
                                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                                        placeholder="Enter mobile number">
                                                    @error('disbursementData.mobile_money_number')
                                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                                <div>
                                                    <label for="mobile_money_provider" class="block text-sm font-medium text-gray-700">
                                                        Provider <span class="text-red-500">*</span>
                                                    </label>
                                                    <select wire:model="disbursementData.mobile_money_provider" id="mobile_money_provider"
                                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                                        <option value="">Select Provider</option>
                                                        <option value="mtn">MTN Mobile Money</option>
                                                        <option value="vodafone">Vodafone Cash</option>
                                                        <option value="airteltigo">AirtelTigo Money</option>
                                                    </select>
                                                    @error('disbursementData.mobile_money_provider')
                                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Notes -->
                                        <div>
                                            <label for="disbursement_notes" class="block text-sm font-medium text-gray-700">
                                                Notes (Optional)
                                            </label>
                                            <textarea wire:model="disbursementData.notes" id="disbursement_notes" rows="2"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                                placeholder="Add disbursement notes..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="disburseLoan" wire:loading.attr="disabled"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                            <span wire:loading.remove wire:target="disburseLoan">
                                Disburse Loan
                            </span>
                            <span wire:loading wire:target="disburseLoan">
                                <i class="fas fa-spinner fa-spin mr-2"></i> Processing...
                            </span>
                        </button>
                        <button type="button" wire:click="closeModal('showDisburseModal')"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Print functionality
    function printLoanDetails() {
        window.print();
    }
    
    // Tab switching with URL hash
    document.addEventListener('DOMContentLoaded', function() {
        const hash = window.location.hash.substring(1);
        if (hash && ['details', 'repayments', 'transactions', 'documents', 'committee'].includes(hash)) {
            Livewire.set('activeTab', hash);
        }
        
        // Update URL hash when tab changes
        Livewire.on('tab-changed', (tab) => {
            window.location.hash = tab;
        });
    });
</script>
@endpush