<div>
    <div class="max-w-6xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <div class="flex items-center">
                            <a href="{{ route('loans.show', $loan->id) }}" 
                               class="text-blue-600 hover:text-blue-800 mr-3">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                            <h1 class="text-2xl font-bold text-gray-800">
                                Committee Review: Loan #{{ $loan->loan_number }}
                            </h1>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ ucfirst($loan->loan_type) }} Loan • 
                            Customer: {{ $loan->customer->full_name }} • 
                            Amount: GHS {{ number_format($loan->amount, 2) }}
                        </p>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <!-- Risk Score Badge -->
                        <div class="bg-{{ $riskScore >= 7 ? 'green' : ($riskScore >= 5 ? 'yellow' : 'red') }}-100 text-{{ $riskScore >= 7 ? 'green' : ($riskScore >= 5 ? 'yellow' : 'red') }}-800 px-3 py-1 rounded-full text-sm font-medium">
                            Risk Score: {{ $riskScore }}/10
                        </div>
                    </div>
                </div>
            </div>

            <!-- Review Form -->
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Column: Loan Summary -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Loan Summary Card -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Loan Summary</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Customer</span>
                                    <span class="font-medium">{{ $loan->customer->full_name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Monthly Income</span>
                                    <span class="font-medium">GHS {{ number_format($loan->customer->monthly_income, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Loan Amount</span>
                                    <span class="font-medium">GHS {{ number_format($loan->amount, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Monthly Payment</span>
                                    <span class="font-medium">GHS {{ number_format($loan->monthly_payment, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Debt-to-Income Ratio</span>
                                    <span class="font-medium {{ ($loan->monthly_payment / $loan->customer->monthly_income) * 100 <= 30 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format(($loan->monthly_payment / $loan->customer->monthly_income) * 100, 1) }}%
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Collateral Summary -->
                        @if($loan->collateral_value || !empty($loan->collateral_details))
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Collateral</h3>
                                <div class="space-y-3">
                                    @if($loan->collateral_value)
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-500">Value</span>
                                            <span class="font-medium">GHS {{ number_format($loan->collateral_value, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-500">Coverage Ratio</span>
                                            <span class="font-medium {{ ($loan->collateral_value / $loan->amount) >= 1.5 ? 'text-green-600' : 'text-yellow-600' }}">
                                                {{ number_format(($loan->collateral_value / $loan->amount), 1) }}x
                                            </span>
                                        </div>
                                    @endif
                                    @if(!empty($loan->collateral_details))
                                        <div>
                                            <div class="text-sm text-gray-500 mb-1">Types</div>
                                            <div class="font-medium">
                                                {{ implode(', ', array_map('ucfirst', $loan->collateral_details)) }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Previous Reviews -->
                        @if($loan->committeeReviews->isNotEmpty())
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Previous Reviews</h3>
                                <div class="space-y-3">
                                    @foreach($loan->committeeReviews as $review)
                                        <div class="border-l-4 {{ $review->decision === 'approve' ? 'border-green-500' : 'border-red-500' }} pl-3">
                                            <div class="text-sm font-medium">{{ $review->reviewer->full_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $review->reviewed_at->format('M d, Y') }}</div>
                                            <div class="text-sm mt-1">{{ $review->recommendation }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Right Column: Review Form -->
                    <div class="lg:col-span-2">
                        <form wire:submit.prevent="submitReview">
                            <!-- Decision Selection -->
                            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Review Decision</h3>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    @foreach([
                                        'approve' => ['color' => 'green', 'icon' => 'check-circle', 'label' => 'Approve'],
                                        'reject' => ['color' => 'red', 'icon' => 'times-circle', 'label' => 'Reject'],
                                        'refer' => ['color' => 'yellow', 'icon' => 'share', 'label' => 'Refer'],
                                        'hold' => ['color' => 'gray', 'icon' => 'pause-circle', 'label' => 'Hold'],
                                    ] as $decision => $data)
                                        <button type="button" 
                                            wire:click="$set('review.decision', '{{ $decision }}')"
                                            class="p-4 border rounded-lg flex flex-col items-center justify-center transition-all duration-200
                                            {{ $review['decision'] === $decision ? 'border-' . $data['color'] . '-500 bg-' . $data['color'] . '-50 ring-2 ring-' . $data['color'] . '-200' : 'border-gray-300 hover:border-' . $data['color'] . '-300 hover:bg-' . $data['color'] . '-50' }}">
                                            <div class="w-10 h-10 rounded-full flex items-center justify-center mb-2
                                                {{ $review['decision'] === $decision ? 'bg-' . $data['color'] . '-100 text-' . $data['color'] . '-600' : 'bg-gray-100 text-gray-600' }}">
                                                <i class="fas fa-{{ $data['icon'] }}"></i>
                                            </div>
                                            <span class="text-sm font-medium">{{ $data['label'] }}</span>
                                        </button>
                                    @endforeach
                                </div>
                                @error('review.decision')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Risk Assessment -->
                            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Risk Assessment</h3>
                                
                                <!-- Score -->
                                <div class="mb-4">
                                    <label for="score" class="block text-sm font-medium text-gray-700 mb-2">
                                        Risk Score (1-10)
                                    </label>
                                    <div class="flex items-center space-x-4">
                                        <input type="range" wire:model="review.score" id="score"
                                            min="1" max="10" step="1"
                                            class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                                        <span class="text-lg font-bold 
                                            {{ $review['score'] >= 7 ? 'text-green-600' : ($review['score'] >= 5 ? 'text-yellow-600' : 'text-red-600') }}">
                                            {{ $review['score'] }}/10
                                        </span>
                                    </div>
                                    <div class="mt-2 flex justify-between text-xs text-gray-500">
                                        <span>Low Risk</span>
                                        <span>Medium Risk</span>
                                        <span>High Risk</span>
                                    </div>
                                    @error('review.score')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Risk Level -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Risk Level
                                    </label>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                        @foreach(['low', 'medium', 'high', 'very_high'] as $level)
                                            <button type="button"
                                                wire:click="$set('review.risk_level', '{{ $level }}')"
                                                class="p-3 border rounded text-center text-sm
                                                {{ $review['risk_level'] === $level ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:bg-gray-50' }}">
                                                {{ ucfirst(str_replace('_', ' ', $level)) }}
                                            </button>
                                        @endforeach
                                    </div>
                                    @error('review.risk_level')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Recommendation -->
                                <div>
                                    <label for="recommendation" class="block text-sm font-medium text-gray-700 mb-2">
                                        Recommendation <span class="text-red-500">*</span>
                                    </label>
                                    <textarea wire:model="review.recommendation" id="recommendation" rows="3"
                                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Provide your recommendation..."></textarea>
                                    @error('review.recommendation')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Conditions -->
                            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Conditions for Approval</h3>
                                <div class="mb-4">
                                    <div class="flex space-x-2">
                                        <input type="text" wire:model="newCondition"
                                            class="flex-1 block border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Add a condition...">
                                        <button type="button" wire:click="addCondition"
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            Add
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Conditions List -->
                                @if(!empty($review['conditions']))
                                    <div class="space-y-2">
                                        @foreach($review['conditions'] as $index => $condition)
                                            <div class="flex items-center justify-between bg-gray-50 p-3 rounded">
                                                <span>{{ $condition }}</span>
                                                <button type="button" wire:click="removeCondition({{ $index }})"
                                                    class="text-red-600 hover:text-red-800">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500 italic">No conditions added</p>
                                @endif
                            </div>

                            <!-- Comments -->
                            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Comments</h3>
                                <div>
                                    <label for="comments" class="block text-sm font-medium text-gray-700 mb-2">
                                        Comments (Optional)
                                    </label>
                                    <textarea wire:model="review.comments" id="comments" rows="4"
                                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Add any additional comments..."></textarea>
                                    @error('review.comments')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex justify-between pt-6 border-t border-gray-200">
                                <a href="{{ route('loans.show', $loan->id) }}"
                                    class="px-6 py-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="fas fa-times mr-2"></i>
                                    Cancel Review
                                </a>
                                
                                <button type="submit" wire:loading.attr="disabled" wire:target="submitReview"
                                    class="px-6 py-3 bg-blue-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                                    <span wire:loading.remove wire:target="submitReview">
                                        <i class="fas fa-paper-plane mr-2"></i>
                                        Submit Review
                                    </span>
                                    <span wire:loading wire:target="submitReview">
                                        <i class="fas fa-spinner fa-spin mr-2"></i>
                                        Processing...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Update risk score display based on slider
    document.getElementById('score').addEventListener('input', function(e) {
        const value = e.target.value;
        const display = document.querySelector('.score-display');
        if (display) {
            display.textContent = value + '/10';
            
            // Update color
            if (value >= 7) {
                display.className = 'text-lg font-bold text-green-600';
            } else if (value >= 5) {
                display.className = 'text-lg font-bold text-yellow-600';
            } else {
                display.className = 'text-lg font-bold text-red-600';
            }
        }
    });
</script>
@endpush