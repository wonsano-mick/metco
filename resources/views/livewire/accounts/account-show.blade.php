<div>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100">
                <div class="flex justify-between items-center">
                    <div>
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center mr-3">
                                <i class="fas {{ $this->getAccountTypeIcon() }} text-white"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">Account Details</h2>
                                <p class="text-sm text-gray-600 mt-1">Account Number: 
                                    <span class="font-mono font-medium">{{ $account->account_number }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        @can('update accounts')
                            <a href="{{ route('accounts.edit', $account->id) }}"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-edit mr-2"></i>
                                Edit Account
                            </a>
                        @endcan
                        <a href="{{ route('accounts.index') }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Accounts
                        </a>
                    </div>
                </div>
            </div>

            <!-- Status Banner -->
            @if($account->status !== 'active')
                <div class="px-6 py-3 bg-{{ $this->getStatusColor() }}-50 border-b border-{{ $this->getStatusColor() }}-200">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-{{ $this->getStatusColor() }}-500 mr-2"></i>
                        <span class="text-sm font-medium text-{{ $this->getStatusColor() }}-800">
                            This account is {{ ucfirst($account->status) }}. 
                            @if($account->status === 'dormant')
                                No activity for over 6 months.
                            @elseif($account->status === 'frozen')
                                Transactions are temporarily suspended.
                            @endif
                        </span>
                    </div>
                </div>
            @endif

            <!-- Account Details -->
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Column: Account & Customer Info -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Account Information Card -->
                        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                Account Information
                            </h3>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wider">Account Type</p>
                                    <p class="text-sm font-medium text-gray-900 mt-1">{{ $account->accountType->name ?? 'N/A' }}</p>
                                    @if($account->accountType)
                                        <p class="text-xs text-gray-500 mt-1">{{ $account->accountType->code }}</p>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wider">Currency</p>
                                    <p class="text-sm font-medium text-gray-900 mt-1">{{ $account->currency }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wider">Status</p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-1 
                                        bg-{{ $this->getStatusColor() }}-100 text-{{ $this->getStatusColor() }}-800">
                                        {{ ucfirst($account->status) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wider">Opened Date</p>
                                    <p class="text-sm font-medium text-gray-900 mt-1">{{ $account->opened_at?->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wider">Last Activity</p>
                                    <p class="text-sm font-medium text-gray-900 mt-1">
                                        {{ $account->last_activity_at?->diffForHumans() ?? 'Never' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wider">Branch</p>
                                    <p class="text-sm font-medium text-gray-900 mt-1">{{ $account->customer->branch->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Balance Information Card -->
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-6 border border-blue-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-chart-line text-blue-500 mr-2"></i>
                                Balance Information
                            </h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div class="bg-white p-4 rounded-lg shadow-sm">
                                    <p class="text-xs text-gray-500 uppercase tracking-wider">Current Balance</p>
                                    <p class="text-2xl font-bold text-gray-900 mt-1">
                                        {{ number_format($account->current_balance, 2) }}
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $account->currency }}</p>
                                </div>
                                <div class="bg-white p-4 rounded-lg shadow-sm">
                                    <p class="text-xs text-gray-500 uppercase tracking-wider">Available Balance</p>
                                    <p class="text-2xl font-bold text-green-600 mt-1">
                                        {{ number_format($account->available_balance, 2) }}
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $account->currency }}</p>
                                </div>
                                <div class="bg-white p-4 rounded-lg shadow-sm">
                                    <p class="text-xs text-gray-500 uppercase tracking-wider">Ledger Balance</p>
                                    <p class="text-xl font-semibold text-gray-700 mt-1">
                                        {{ number_format($account->ledger_balance, 2) }}
                                    </p>
                                </div>
                                <div class="bg-white p-4 rounded-lg shadow-sm">
                                    <p class="text-xs text-gray-500 uppercase tracking-wider">Overdraft Limit</p>
                                    <p class="text-xl font-semibold text-{{ $account->overdraft_limit > 0 ? 'orange' : 'gray' }}-600 mt-1">
                                        {{ number_format($account->overdraft_limit, 2) }}
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4 grid grid-cols-2 md:grid-cols-3 gap-4">
                                <div>
                                    <p class="text-xs text-gray-600">Minimum Balance Required</p>
                                    <p class="text-sm font-medium text-gray-900">{{ number_format($account->minimum_balance, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-600">Available for Withdrawal</p>
                                    <p class="text-sm font-medium text-green-600">{{ number_format($account->available_balance, 2) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Account Type Details Card (NEW) -->
                        @if($account->accountType)
                            <div class="bg-purple-50 rounded-lg p-6 border border-purple-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                    <i class="fas fa-percentage text-purple-500 mr-2"></i>
                                    Account Type Benefits & Charges
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="bg-white p-4 rounded-lg">
                                        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                            <i class="fas fa-coins text-yellow-500 mr-2"></i>
                                            Interest Rate
                                        </h4>
                                        <div class="space-y-2">
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm text-gray-600">Annual Rate:</span>
                                                <span class="text-2xl font-bold text-purple-600">
                                                    {{ number_format($account->accountType->interest_rate, 2) }}%
                                                </span>
                                            </div>
                                            <div class="flex justify-between items-center text-sm">
                                                <span class="text-gray-600">Monthly Rate:</span>
                                                <span class="font-medium text-gray-800">
                                                    {{ number_format($account->accountType->interest_rate / 12, 4) }}%
                                                </span>
                                            </div>
                                            <div class="mt-3 pt-3 border-t border-gray-200">
                                                <p class="text-xs text-gray-500">
                                                    <i class="fas fa-info-circle mr-1 text-blue-500"></i>
                                                    Interest is calculated monthly on the account balance
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-white p-4 rounded-lg">
                                        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                            <i class="fas fa-file-invoice text-red-500 mr-2"></i>
                                            Monthly Fee
                                        </h4>
                                        <div class="space-y-2">
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm text-gray-600">Fee Amount:</span>
                                                <span class="text-2xl font-bold text-red-600">
                                                    {{ number_format($account->accountType->monthly_fee, 2) }}
                                                </span>
                                            </div>
                                            <div class="flex justify-between items-center text-sm">
                                                <span class="text-gray-600">Due Date:</span>
                                                <span class="font-medium text-gray-800">1st of each month</span>
                                            </div>
                                            <div class="mt-3 pt-3 border-t border-gray-200">
                                                <p class="text-xs text-gray-500">
                                                    <i class="fas fa-info-circle mr-1 text-blue-500"></i>
                                                    Monthly fee is automatically deducted from your account
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if($account->accountType->description)
                                    <div class="mt-4 pt-4 border-t border-purple-200">
                                        <p class="text-sm text-gray-600">{{ $account->accountType->description }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Customer Information Card -->
                        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-user text-blue-500 mr-2"></i>
                                Customer Information
                            </h3>
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    @if ($account->customer->profile_photo_url)
                                        <img class="h-20 w-20 rounded-full object-cover border-4 border-white shadow"
                                            src="{{ $account->customer->profile_photo_url }}"
                                            alt="{{ $account->customer->full_name }}">
                                    @else
                                        <div class="h-20 w-20 rounded-full bg-blue-100 flex items-center justify-center border-4 border-white shadow">
                                            <span class="text-2xl font-medium text-blue-600">
                                                {{ substr($account->customer->full_name, 0, 1) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-6 flex-1">
                                    <h4 class="text-xl font-bold text-gray-900">{{ $account->customer->full_name }}</h4>
                                    <p class="text-sm text-gray-500">{{ $account->customer->customer_number }}</p>
                                    
                                    <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase">Email</p>
                                            <p class="text-sm font-medium text-gray-900">{{ $account->customer->email }}</p>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-xs text-gray-500 uppercase">Phone</p>
                                            <p class="text-sm font-medium text-gray-900">{{ $account->customer->phone }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase">KYC Status</p>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $account->customer->kyc_status === 'verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ ucfirst($account->customer->kyc_status) }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase">Customer Since</p>
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $account->customer->created_at?->format('M Y') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Statistics & Monthly Processing -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Quick Statistics Card -->
                        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-lg p-6 border border-indigo-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-chart-pie text-indigo-500 mr-2"></i>
                                Account Statistics
                            </h3>
                            <div class="space-y-4">
                                <div class="bg-white p-4 rounded-lg">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Total Fees Paid</span>
                                        <span class="text-lg font-bold text-red-600">
                                            {{ number_format($statistics['total_fees_paid'], 2) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Over {{ $statistics['months_processed'] }} months</p>
                                </div>
                                <div class="bg-white p-4 rounded-lg">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Total Interest Earned</span>
                                        <span class="text-lg font-bold text-green-600">
                                            {{ number_format($statistics['total_interest_earned'], 2) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">At {{ number_format($account->accountType?->interest_rate ?? 0, 2) }}% p.a.</p>
                                </div>
                                <div class="bg-white p-4 rounded-lg">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Avg Monthly Balance</span>
                                        <span class="text-lg font-bold text-blue-600">
                                            {{ number_format($statistics['avg_monthly_balance'], 2) }}
                                        </span>
                                    </div>
                                </div>
                                @if($statistics['last_processing'])
                                    <div class="bg-white p-4 rounded-lg">
                                        <p class="text-sm text-gray-600">Last Processed</p>
                                        <p class="text-base font-medium text-gray-900">
                                            {{ $statistics['last_processing']->processing_month->format('F Y') }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $statistics['last_processing']->processed_at->format('M d, Y H:i') }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Next Processing Card -->
                        <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-lg p-6 border border-blue-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>
                                Next Processing
                            </h3>
                            @php
                                $nextProcessing = now()->startOfMonth();
                                if(now()->day > 1) {
                                    $nextProcessing = now()->addMonth()->startOfMonth();
                                }
                                $daysUntilProcessing = now()->diffInDays($nextProcessing);
                            @endphp
                            <div class="text-center">
                                <div class="text-3xl font-bold text-blue-600 mb-2">
                                    {{ $nextProcessing->format('M d, Y') }}
                                </div>
                                <p class="text-sm text-gray-600 mb-4">
                                    {{ $daysUntilProcessing }} {{ Str::plural('day', $daysUntilProcessing) }} remaining
                                </p>
                                <div class="grid grid-cols-2 gap-3 mt-4">
                                    <div class="bg-white p-3 rounded-lg">
                                        <p class="text-xs text-gray-500">Est. Monthly Fee</p>
                                        <p class="text-lg font-semibold text-red-600">
                                            {{ number_format($account->accountType?->monthly_fee ?? 0, 2) }}
                                        </p>
                                    </div>
                                    <div class="bg-white p-3 rounded-lg">
                                        <p class="text-xs text-gray-500">Est. Monthly Interest</p>
                                        <p class="text-lg font-semibold text-green-600">
                                            @php
                                                $monthlyRate = ($account->accountType?->interest_rate ?? 0) / 12 / 100;
                                                $estInterest = $account->current_balance * $monthlyRate;
                                            @endphp
                                            {{ number_format($estInterest, 2) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Processing History Table -->
                <div class="mt-8 bg-white border border-gray-200 rounded-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            <i class="fas fa-history text-blue-500 mr-2"></i>
                            Monthly Processing History
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Monthly fee deductions and interest credits</p>
                    </div>
                    
                    @if($monthlyProcessings->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Month
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Balance Before
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Monthly Fee
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Interest Earned
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Balance After
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Net Change
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Processed At
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($monthlyProcessings as $processing)
                                        @php
                                            $netChange = $processing->interest_earned - $processing->monthly_fee_applied;
                                            $changeClass = $netChange > 0 ? 'text-green-600' : ($netChange < 0 ? 'text-red-600' : 'text-gray-500');
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $processing->processing_month->format('F Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ number_format($processing->balance_before, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if($processing->monthly_fee_applied > 0)
                                                    <span class="text-red-600">-{{ number_format($processing->monthly_fee_applied, 2) }}</span>
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if($processing->interest_earned > 0)
                                                    <span class="text-green-600">+{{ number_format($processing->interest_earned, 2) }}</span>
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ number_format($processing->balance_after, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $changeClass }}">
                                                {{ $netChange > 0 ? '+' : '' }}{{ number_format($netChange, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $processing->processed_at->format('M d, Y H:i') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-sm text-gray-500">
                                            <div class="flex items-center justify-between">
                                                <span>
                                                    <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                                                    Showing last {{ $monthlyProcessings->count() }} months
                                                </span>
                                                <span class="text-xs text-gray-400">
                                                    * Monthly fees are deducted on the 1st of each month
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="px-6 py-12 text-center">
                            <i class="fas fa-calendar-alt text-5xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500 text-lg">No monthly processing records yet</p>
                            <p class="text-sm text-gray-400 mt-2">
                                The first processing will occur on 
                                <span class="font-medium">{{ now()->startOfMonth()->format('F j, Y') }}</span>
                                @if(now()->day > 1)
                                    <br>(next month)
                                @endif
                            </p>
                            @if($account->accountType?->monthly_fee > 0 || $account->accountType?->interest_rate > 0)
                                <div class="mt-6 inline-flex items-center px-4 py-2 bg-blue-50 rounded-lg">
                                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                    <span class="text-sm text-blue-700">
                                        @if($account->accountType->monthly_fee > 0)
                                            Monthly fee: {{ number_format($account->accountType->monthly_fee, 2) }} |
                                        @endif
                                        @if($account->accountType->interest_rate > 0)
                                            Interest rate: {{ number_format($account->accountType->interest_rate, 2) }}% p.a.
                                        @endif
                                    </span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Quick Actions -->
                <div class="mt-6 flex flex-wrap gap-3 justify-end">
                    @can('create transactions')
                        <a href="{{ route('transactions.create', ['account_id' => $account->id]) }}" 
                           class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <i class="fas fa-exchange-alt mr-2"></i>
                            New Transaction
                        </a>
                    @endcan
                    
                    @can('view transactions')
                        <a href="{{ route('accounts.transactions', $account->id) }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-list mr-2"></i>
                            View Transactions
                        </a>
                    @endcan
                    
                    @can('create accounts')
                        <a href="{{ route('accounts.create', ['customer_id' => $account->customer_id]) }}" 
                           class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                            <i class="fas fa-plus-circle mr-2"></i>
                            New Account for this Customer
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>