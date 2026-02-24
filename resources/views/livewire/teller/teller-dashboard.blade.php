<div>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">
                            <i class="fas fa-cash-register mr-2 text-blue-600"></i>
                            Teller Dashboard
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">
                            Manage your cash drawer and view today's transactions
                        </p>
                    </div>
                    <div class="bg-white border border-gray-300 rounded-lg px-4 py-2">
                        <div class="text-sm font-medium text-gray-700">
                            <i class="far fa-clock mr-1"></i>
                            {{ now()->format('l, F j, Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cash Balance Card with Action Buttons -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg mb-6">
            <div class="px-6 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Current Cash Balance</p>
                        <p class="text-white text-4xl font-bold mt-2">
                            GHS {{ number_format($cashBalance, 2) }}
                        </p>
                        <p class="text-blue-100 text-xs mt-1">
                            Account: {{ $tellerAccount->code ?? 'Not assigned' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Transactions -->
        <div class="bg-white rounded-lg shadow-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-history mr-2 text-blue-600"></i>
                    Today's Transactions
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Time
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Description
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Debit (In)
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Credit (Out)
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Balance
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            $runningBalance = $openingBalance;
                        @endphp
                        
                        <!-- Opening Balance Row -->
                        <tr class="bg-gray-50">
                            <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900">Opening</td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">-</td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">Opening Balance</td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 text-right">-</td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 text-right">-</td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                GHS {{ number_format($openingBalance, 2) }}
                            </td>
                        </tr>

                        @forelse($todayTransactions as $entry)
                            @php
                                if ($entry['entry_type'] === 'debit') {
                                    $runningBalance += $entry['amount'];
                                } else {
                                    $runningBalance -= $entry['amount'];
                                }
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($entry['created_at'])->format('h:i A') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        @if($entry['entry_type'] === 'debit') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($entry['entry_type']) }}
                                        @if(isset($entry['type']))
                                            <span class="ml-1 text-xs opacity-75">
                                                ({{ ucfirst(str_replace('_', ' ', $entry['type'])) }})
                                            </span>
                                        @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $entry['description'] }}
                                    @if(isset($entry['transaction_reference']))
                                        <br>
                                        <span class="text-xs text-gray-500">
                                            Ref: {{ $entry['transaction_reference'] }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-green-600">
                                    @if($entry['entry_type'] === 'debit')
                                        GHS {{ number_format($entry['amount'], 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-red-600">
                                    @if($entry['entry_type'] === 'credit')
                                        GHS {{ number_format($entry['amount'], 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                                    GHS {{ number_format($runningBalance, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-receipt text-4xl mb-3 text-gray-300"></i>
                                    <p class="text-lg font-medium text-gray-600">No transactions today</p>
                                    <p class="text-sm text-gray-400 mt-1">Your transactions will appear here</p>
                                </td>
                            </tr>
                        @endforelse

                        <!-- Current Balance Row -->
                        <tr class="bg-blue-50 font-semibold">
                            <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900">Current</td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">-</td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">Current Balance</td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 text-right">-</td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 text-right">-</td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm font-bold text-gray-900 text-right">
                                GHS {{ number_format($cashBalance, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>