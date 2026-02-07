<div>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <!-- Customer Avatar -->
                        <div class="mr-4">
                            @if ($customer->profile_photo_path)
                                <img class="h-16 w-16 rounded-full object-cover"
                                    src="{{ Storage::url($customer->profile_photo_path) }}"
                                    alt="{{ $customer->full_name }}">
                            @else
                                <div
                                    class="h-16 w-16 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center text-white text-xl font-bold">
                                    {{ $customer->initials }}
                                </div>
                            @endif
                        </div>

                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">{{ $customer->full_name }}</h2>
                            <div class="flex items-center space-x-4 mt-1">
                                <p class="text-sm text-gray-600 font-mono">
                                    <i class="fas fa-id-card mr-1"></i>
                                    {{ $customer->customer_number }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    <i class="fas fa-building mr-1"></i>
                                    {{ $customer->branch->name ?? 'No Branch' }}
                                </p>
                                <div class="flex space-x-2">
                                    <span
                                        class="px-2 py-1 text-xs font-medium rounded {{ $customer->status === 'active' ? 'bg-green-100 text-green-800' : ($customer->status === 'suspended' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ ucfirst($customer->status) }}
                                    </span>
                                    <span
                                        class="px-2 py-1 text-xs font-medium rounded {{ $customer->kyc_status === 'verified' ? 'bg-purple-100 text-purple-800' : ($customer->kyc_status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        KYC: {{ ucfirst($customer->kyc_status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex space-x-3">
                        @can('edit customers')
                            <a href="{{ route('customers.edit', $customer->id) }}"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-edit mr-2"></i>
                                Edit
                            </a>
                        @endcan

                        @can('create accounts')
                            <a href="{{ route('accounts.create', ['customer_id' => $customer->id]) }}"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <i class="fas fa-plus-circle mr-2"></i>
                                Add Account
                            </a>
                        @endcan

                        <a href="{{ route('customers.index') }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Column: Customer Info -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Customer Summary -->
                        <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">
                                    <i class="fas fa-user-circle mr-2 text-blue-500"></i>
                                    Customer Summary
                                </h3>
                            </div>
                            <div class="px-4 py-5 sm:p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 mb-3">Personal Information</h4>
                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-xs text-gray-500">Full Name</dt>
                                                <dd class="text-sm font-medium text-gray-900">
                                                    {{ $customer->full_name }}</dd>
                                            </div>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <dt class="text-xs text-gray-500">Gender</dt>
                                                    <dd class="text-sm font-medium text-gray-900">
                                                        {{ ucfirst($customer->gender) }}</dd>
                                                </div>
                                                <div>
                                                    <dt class="text-xs text-gray-500">Date of Birth</dt>
                                                    <dd class="text-sm font-medium text-gray-900">
                                                        {{ $customer->date_of_birth?->format('F d, Y') }}
                                                        @if ($customer->date_of_birth)
                                                            <span
                                                                class="text-gray-500">({{ $customer->date_of_birth->age }}
                                                                years)</span>
                                                        @endif
                                                    </dd>
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <dt class="text-xs text-gray-500">Marital Status</dt>
                                                    <dd class="text-sm font-medium text-gray-900">
                                                        {{ ucfirst($customer->marital_status) }}</dd>
                                                </div>
                                                <div>
                                                    <dt class="text-xs text-gray-500">Dependents</dt>
                                                    <dd class="text-sm font-medium text-gray-900">
                                                        {{ $customer->dependents ?? 0 }}</dd>
                                                </div>
                                            </div>
                                            <div>
                                                <dt class="text-xs text-gray-500">Nationality</dt>
                                                <dd class="text-sm font-medium text-gray-900">
                                                    {{ $customer->nationality }}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 mb-3">Contact Information</h4>
                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-xs text-gray-500">Email Address</dt>
                                                <dd class="text-sm font-medium text-gray-900">{{ $customer->email }}
                                                </dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs text-gray-500">Phone Number</dt>
                                                <dd class="text-sm font-medium text-gray-900">{{ $customer->phone }}
                                                </dd>
                                            </div>
                                            @if ($customer->phone_alt)
                                                <div>
                                                    <dt class="text-xs text-gray-500">Alternate Phone</dt>
                                                    <dd class="text-sm font-medium text-gray-900">
                                                        {{ $customer->phone_alt }}</dd>
                                                </div>
                                            @endif
                                            <div>
                                                <dt class="text-xs text-gray-500">Address</dt>
                                                <dd class="text-sm font-medium text-gray-900">
                                                    {{ $customer->address_line_1 }}<br>
                                                    @if ($customer->address_line_2)
                                                        {{ $customer->address_line_2 }}<br>
                                                    @endif
                                                    {{ $customer->city }}, {{ $customer->state }}<br>
                                                    {{ $customer->postal_code }}, {{ $customer->country }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Identification & KYC -->
                        <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">
                                    <i class="fas fa-shield-alt mr-2 text-purple-500"></i>
                                    Identification & KYC
                                </h3>
                            </div>
                            <div class="px-4 py-5 sm:p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-xs text-gray-500">ID Type</dt>
                                                <dd class="text-sm font-medium text-gray-900">
                                                    @switch($customer->id_type)
                                                        @case('ghana_card')
                                                            Ghana Card
                                                        @break

                                                        @case('passport')
                                                            Passport
                                                        @break

                                                        @case('drivers_license')
                                                            Driver's License
                                                        @break

                                                        @case('voters_id')
                                                            Voter's ID
                                                        @break

                                                        @default
                                                            {{ ucfirst($customer->id_type) }}
                                                    @endswitch
                                                </dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs text-gray-500">ID Number</dt>
                                                <dd class="text-sm font-medium text-gray-900">
                                                    {{ $customer->id_number }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs text-gray-500">Issuing Country</dt>
                                                <dd class="text-sm font-medium text-gray-900">
                                                    {{ $customer->id_issuing_country }}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <div>
                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-xs text-gray-500">ID Expiry Date</dt>
                                                <dd class="text-sm font-medium text-gray-900">
                                                    {{ $customer->id_expiry_date?->format('F d, Y') }}
                                                    @if ($customer->id_expiry_date)
                                                        @if ($customer->id_expiry_date->isPast())
                                                            <span class="text-red-500 ml-2">(Expired)</span>
                                                        @elseif($customer->id_expiry_date->diffInDays(now()) <= 30)
                                                            <span class="text-yellow-500 ml-2">(Expiring soon)</span>
                                                        @endif
                                                    @endif
                                                </dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs text-gray-500">Education Level</dt>
                                                <dd class="text-sm font-medium text-gray-900">
                                                    {{ $customer->education_level ?? 'Not specified' }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs text-gray-500">Customer Since</dt>
                                                <dd class="text-sm font-medium text-gray-900">
                                                    {{ $customer->registered_at?->format('F d, Y') }}
                                                    <span
                                                        class="text-gray-500">({{ $customer->registered_at?->diffForHumans() }})</span>
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>

                                <!-- ID Document Images -->
                                @if ($customer->id_front_image_path || $customer->id_back_image_path)
                                    <div class="mt-6 pt-6 border-t border-gray-200">
                                        <h4 class="text-sm font-medium text-gray-900 mb-4">ID Documents</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            @if ($customer->id_front_image_path)
                                                <div>
                                                    <p class="text-xs text-gray-500 mb-2">Front Side</p>
                                                    <a href="{{ Storage::url($customer->id_front_image_path) }}"
                                                        target="_blank" class="block">
                                                        <div
                                                            class="border border-gray-200 rounded-lg p-2 hover:bg-gray-50">
                                                            <div class="flex items-center">
                                                                <i class="fas fa-file-image text-gray-400 mr-2"></i>
                                                                <span class="text-sm text-gray-700">View Front ID</span>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>
                                            @endif

                                            @if ($customer->id_back_image_path)
                                                <div>
                                                    <p class="text-xs text-gray-500 mb-2">Back Side</p>
                                                    <a href="{{ Storage::url($customer->id_back_image_path) }}"
                                                        target="_blank" class="block">
                                                        <div
                                                            class="border border-gray-200 rounded-lg p-2 hover:bg-gray-50">
                                                            <div class="flex items-center">
                                                                <i class="fas fa-file-image text-gray-400 mr-2"></i>
                                                                <span class="text-sm text-gray-700">View Back ID</span>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @if ($customer->signature_image_path)
                                    <div class="mt-6 pt-6 border-t border-gray-200">
                                        <h4 class="text-sm font-medium text-gray-900 mb-4">Signature</h4>
                                        <a href="{{ Storage::url($customer->signature_image_path) }}" target="_blank"
                                            class="inline-block">
                                            <div class="border border-gray-200 rounded-lg p-2 hover:bg-gray-50">
                                                <div class="flex items-center">
                                                    <i class="fas fa-signature text-gray-400 mr-2"></i>
                                                    <span class="text-sm text-gray-700">View Signature</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Approval of KYC -->

                        @can('verify kyc')
                            <!-- KYC Verification Actions -->
                            <div class="bg-white border border-gray-200 rounded-lg shadow-sm mt-6">
                                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                                    <h3 class="text-lg font-medium text-gray-900">
                                        <i class="fas fa-user-check mr-2 text-green-500"></i>
                                        KYC Verification
                                    </h3>
                                </div>
                                <div class="px-4 py-5 sm:p-6">
                                    @if ($customer->kyc_status === 'pending')
                                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                                            <div class="flex">
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                                </div>
                                                <div class="ml-3">
                                                    <h3 class="text-sm font-medium text-yellow-800">
                                                        KYC Pending Verification
                                                    </h3>
                                                    <div class="mt-2 text-sm text-yellow-700">
                                                        <p>This customer's KYC documents require verification.</p>
                                                        <p class="mt-1">Please review the ID documents and either verify
                                                            or reject.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex space-x-3">
                                            <!-- Verify Button -->
                                            <button type="button" wire:click="verifyKyc"
                                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                <i class="fas fa-check-circle mr-2"></i>
                                                Verify KYC
                                            </button>

                                            <!-- Reject Button -->
                                            <button type="button" wire:click="openRejectModal"
                                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                <i class="fas fa-times-circle mr-2"></i>
                                                Reject KYC
                                            </button>
                                        </div>

                                        <!-- Rejection Modal -->
                                        @if ($showRejectModal)
                                            <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
                                                role="dialog" aria-modal="true">
                                                <div
                                                    class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                                    <!-- Background overlay -->
                                                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                                                        aria-hidden="true"></div>

                                                    <!-- Modal panel -->
                                                    <div
                                                        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                            <div class="sm:flex sm:items-start">
                                                                <div
                                                                    class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                                                    <i
                                                                        class="fas fa-exclamation-triangle text-red-600"></i>
                                                                </div>
                                                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                                    <h3 class="text-lg leading-6 font-medium text-gray-900"
                                                                        id="modal-title">
                                                                        Reject KYC Verification
                                                                    </h3>
                                                                    <div class="mt-2">
                                                                        <p class="text-sm text-gray-500">
                                                                            Please provide a reason for rejecting this
                                                                            customer's KYC verification.
                                                                        </p>
                                                                        <div class="mt-4">
                                                                            <label for="rejection-reason"
                                                                                class="sr-only">Rejection Reason</label>
                                                                            <textarea id="rejection-reason" wire:model="kycRejectionReason" rows="4"
                                                                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                                                                placeholder="Explain why the KYC verification is being rejected..."></textarea>
                                                                            @error('kycRejectionReason')
                                                                                <p class="mt-1 text-sm text-red-600">
                                                                                    {{ $message }}</p>
                                                                            @enderror
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div
                                                            class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                                            <button type="button" wire:click="rejectKyc"
                                                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                                                Confirm Rejection
                                                            </button>
                                                            <button type="button" wire:click="closeRejectModal"
                                                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                                Cancel
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @elseif($customer->kyc_status === 'verified')
                                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                                            <div class="flex">
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-check-circle text-green-400"></i>
                                                </div>
                                                <div class="ml-3">
                                                    <h3 class="text-sm font-medium text-green-800">
                                                        KYC Verified
                                                    </h3>
                                                    <div class="mt-2 text-sm text-green-700">
                                                        <p>This customer's KYC has been verified.</p>
                                                        @if ($customer->verified_at)
                                                            <p class="mt-1">
                                                                Verified on:
                                                                {{ $customer->verified_at->format('F d, Y \a\t h:i A') }}
                                                            </p>
                                                            @if ($customer->verified_by)
                                                                <p class="mt-1">
                                                                    By:
                                                                    {{ $customer->verifiedBy->full_name ?? 'User ID: ' . $customer->verified_by }}
                                                                </p>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="button" wire:click="markKycPending"
                                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                            <i class="fas fa-undo mr-2"></i>
                                            Mark as Pending
                                        </button>
                                    @elseif($customer->kyc_status === 'rejected')
                                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                                            <div class="flex">
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-times-circle text-red-400"></i>
                                                </div>
                                                <div class="ml-3">
                                                    <h3 class="text-sm font-medium text-red-800">
                                                        KYC Rejected
                                                    </h3>
                                                    <div class="mt-2 text-sm text-red-700">
                                                        <p>This customer's KYC has been rejected.</p>
                                                        @if ($customer->kyc_rejection_reason)
                                                            <p class="mt-1 font-medium">Reason:</p>
                                                            <p class="mt-1">{{ $customer->kyc_rejection_reason }}</p>
                                                        @endif
                                                        @if ($customer->kyc_rejected_at)
                                                            <p class="mt-1">
                                                                Rejected on:
                                                                {{ $customer->kyc_rejected_at->format('F d, Y \a\t h:i A') }}
                                                            </p>
                                                            @if ($customer->kyc_rejected_by)
                                                                <p class="mt-1">
                                                                    By:
                                                                    {{ $customer->rejectedBy->full_name ?? 'User ID: ' . $customer->kyc_rejected_by }}
                                                                </p>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex space-x-3">
                                            <button type="button" wire:click="verifyKyc"
                                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                <i class="fas fa-check-circle mr-2"></i>
                                                Verify KYC
                                            </button>

                                            <button type="button" wire:click="markKycPending"
                                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                                <i class="fas fa-undo mr-2"></i>
                                                Mark as Pending
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endcan

                        <!-- Also update the ID Documents section to be more prominent for verification: -->
                        @if ($customer->id_front_image_path || $customer->id_back_image_path)
                            <div class="bg-white border border-gray-200 rounded-lg shadow-sm mt-6">
                                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                                    <h3 class="text-lg font-medium text-gray-900">
                                        <i class="fas fa-id-card mr-2 text-blue-500"></i>
                                        ID Documents for Verification
                                    </h3>
                                </div>
                                <div class="px-4 py-5 sm:p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        @if ($customer->id_front_image_path)
                                            <div>
                                                <h4 class="text-sm font-medium text-gray-900 mb-3">Front Side</h4>
                                                <div class="border border-gray-200 rounded-lg overflow-hidden">
                                                    @if (str_ends_with($customer->id_front_image_path, '.pdf'))
                                                        <div class="p-4 bg-gray-50">
                                                            <i class="fas fa-file-pdf text-red-500 text-4xl"></i>
                                                            <p class="mt-2 text-sm text-gray-600">PDF Document</p>
                                                            <a href="{{ Storage::url($customer->id_front_image_path) }}"
                                                                target="_blank"
                                                                class="mt-3 inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                                                                <i class="fas fa-external-link-alt mr-1"></i>
                                                                Open PDF
                                                            </a>
                                                        </div>
                                                    @else
                                                        <a href="{{ Storage::url($customer->id_front_image_path) }}"
                                                            target="_blank">
                                                            <img src="{{ Storage::url($customer->id_front_image_path) }}"
                                                                alt="ID Front"
                                                                class="w-full h-64 object-contain bg-gray-50">
                                                        </a>
                                                    @endif
                                                </div>
                                                <div class="mt-2 flex justify-between items-center">
                                                    <span class="text-xs text-gray-500">Click image to view full
                                                        size</span>
                                                    <a href="{{ Storage::url($customer->id_front_image_path) }}"
                                                        download class="text-xs text-blue-600 hover:text-blue-800">
                                                        <i class="fas fa-download mr-1"></i>
                                                        Download
                                                    </a>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($customer->id_back_image_path)
                                            <div>
                                                <h4 class="text-sm font-medium text-gray-900 mb-3">Back Side</h4>
                                                <div class="border border-gray-200 rounded-lg overflow-hidden">
                                                    @if (str_ends_with($customer->id_back_image_path, '.pdf'))
                                                        <div class="p-4 bg-gray-50">
                                                            <i class="fas fa-file-pdf text-red-500 text-4xl"></i>
                                                            <p class="mt-2 text-sm text-gray-600">PDF Document</p>
                                                            <a href="{{ Storage::url($customer->id_back_image_path) }}"
                                                                target="_blank"
                                                                class="mt-3 inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                                                                <i class="fas fa-external-link-alt mr-1"></i>
                                                                Open PDF
                                                            </a>
                                                        </div>
                                                    @else
                                                        <a href="{{ Storage::url($customer->id_back_image_path) }}"
                                                            target="_blank">
                                                            <img src="{{ Storage::url($customer->id_back_image_path) }}"
                                                                alt="ID Back"
                                                                class="w-full h-64 object-contain bg-gray-50">
                                                        </a>
                                                    @endif
                                                </div>
                                                <div class="mt-2 flex justify-between items-center">
                                                    <span class="text-xs text-gray-500">Click image to view full
                                                        size</span>
                                                    <a href="{{ Storage::url($customer->id_back_image_path) }}"
                                                        download class="text-xs text-blue-600 hover:text-blue-800">
                                                        <i class="fas fa-download mr-1"></i>
                                                        Download
                                                    </a>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Document verification checklist -->
                                    @can('verify kyc')
                                        <div class="mt-6 pt-6 border-t border-gray-200">
                                            <h4 class="text-sm font-medium text-gray-900 mb-3">Verification Checklist</h4>
                                            <div class="space-y-2">
                                                <label class="flex items-center">
                                                    <input type="checkbox"
                                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    <span class="ml-2 text-sm text-gray-700">Document is clear and
                                                        readable</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="checkbox"
                                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    <span class="ml-2 text-sm text-gray-700">Photo matches customer
                                                        profile</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="checkbox"
                                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    <span class="ml-2 text-sm text-gray-700">ID number matches provided
                                                        information</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="checkbox"
                                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    <span class="ml-2 text-sm text-gray-700">Document is not expired</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="checkbox"
                                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    <span class="ml-2 text-sm text-gray-700">Security features are
                                                        visible</span>
                                                </label>
                                            </div>
                                        </div>
                                    @endcan
                                </div>
                            </div>
                        @endif

                        <!-- Employment & Financial Information -->
                        <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">
                                    <i class="fas fa-briefcase mr-2 text-green-500"></i>
                                    Employment & Financial Information
                                </h3>
                            </div>
                            <div class="px-4 py-5 sm:p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-xs text-gray-500">Occupation</dt>
                                                <dd class="text-sm font-medium text-gray-900">
                                                    {{ $customer->occupation }}</dd>
                                            </div>
                                            @if ($customer->employer_name)
                                                <div>
                                                    <dt class="text-xs text-gray-500">Employer</dt>
                                                    <dd class="text-sm font-medium text-gray-900">
                                                        {{ $customer->employer_name }}</dd>
                                                </div>
                                            @endif
                                            @if ($customer->employer_address)
                                                <div>
                                                    <dt class="text-xs text-gray-500">Employer Address</dt>
                                                    <dd class="text-sm font-medium text-gray-900">
                                                        {{ $customer->employer_address }}</dd>
                                                </div>
                                            @endif
                                        </dl>
                                    </div>

                                    <div>
                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-xs text-gray-500">Monthly Income</dt>
                                                <dd class="text-sm font-medium text-gray-900">
                                                    ₵{{ number_format($customer->monthly_income, 2) }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs text-gray-500">Source of Income</dt>
                                                <dd class="text-sm font-medium text-gray-900">
                                                    {{ $customer->source_of_income }}</dd>
                                            </div>
                                            @if ($customer->net_worth)
                                                <div>
                                                    <dt class="text-xs text-gray-500">Net Worth</dt>
                                                    <dd class="text-sm font-medium text-gray-900">
                                                        ₵{{ number_format($customer->net_worth, 2) }}</dd>
                                                </div>
                                            @endif
                                        </dl>
                                    </div>
                                </div>

                                <!-- Risk Assessment -->
                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <div class="flex justify-between items-center mb-4">
                                        <h4 class="text-sm font-medium text-gray-900">Risk Assessment</h4>
                                        <span
                                            class="px-3 py-1 text-xs font-medium rounded {{ $customer->risk_profile === 'low' ? 'bg-green-100 text-green-800' : ($customer->risk_profile === 'high' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                            {{ ucfirst($customer->risk_profile) }} Risk
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="bg-gray-50 p-3 rounded-lg">
                                            <p class="text-xs text-gray-500">Customer Type</p>
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ ucfirst($customer->customer_type) }}</p>
                                        </div>
                                        <div class="bg-gray-50 p-3 rounded-lg">
                                            <p class="text-xs text-gray-500">Customer Tier</p>
                                            <p class="text-sm font-medium text-gray-900">
                                                @switch($customer->customer_tier)
                                                    @case('basic')
                                                        <span class="text-gray-600">Basic</span>
                                                    @break

                                                    @case('premium')
                                                        <span class="text-blue-600">Premium</span>
                                                    @break

                                                    @case('platinum')
                                                        <span class="text-purple-600">Platinum</span>
                                                    @break
                                                @endswitch
                                            </p>
                                        </div>
                                        <div class="bg-gray-50 p-3 rounded-lg">
                                            <p class="text-xs text-gray-500">Last Updated</p>
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $customer->updated_at?->format('M d, Y') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Emergency Contacts -->
                        @if (!empty($customer->emergency_contacts))
                            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                                    <h3 class="text-lg font-medium text-gray-900">
                                        <i class="fas fa-first-aid mr-2 text-red-500"></i>
                                        Emergency Contacts
                                    </h3>
                                </div>
                                <div class="px-4 py-5 sm:p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        @foreach ($customer->emergency_contacts as $index => $contact)
                                            <div class="border border-gray-200 rounded-lg p-4">
                                                <h4 class="text-sm font-medium text-gray-900 mb-3">Contact
                                                    {{ $index + 1 }}</h4>
                                                <dl class="space-y-2">
                                                    <div>
                                                        <dt class="text-xs text-gray-500">Name</dt>
                                                        <dd class="text-sm font-medium text-gray-900">
                                                            {{ $contact['name'] ?? 'N/A' }}</dd>
                                                    </div>
                                                    <div>
                                                        <dt class="text-xs text-gray-500">Relationship</dt>
                                                        <dd class="text-sm font-medium text-gray-900">
                                                            {{ $contact['relationship'] ?? 'N/A' }}</dd>
                                                    </div>
                                                    <div>
                                                        <dt class="text-xs text-gray-500">Phone</dt>
                                                        <dd class="text-sm font-medium text-gray-900">
                                                            {{ $contact['phone'] ?? 'N/A' }}</dd>
                                                    </div>
                                                    @if (!empty($contact['email']))
                                                        <div>
                                                            <dt class="text-xs text-gray-500">Email</dt>
                                                            <dd class="text-sm font-medium text-gray-900">
                                                                {{ $contact['email'] }}</dd>
                                                        </div>
                                                    @endif
                                                </dl>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Notes -->
                        @if ($customer->notes)
                            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                                    <h3 class="text-lg font-medium text-gray-900">
                                        <i class="fas fa-sticky-note mr-2 text-yellow-500"></i>
                                        Internal Notes
                                    </h3>
                                </div>
                                <div class="px-4 py-5 sm:p-6">
                                    <div class="prose prose-sm max-w-none">
                                        <p class="text-gray-700 whitespace-pre-wrap">{{ $customer->notes }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Right Column: Accounts & Sidebar -->
                    <div class="space-y-6">
                        <!-- Accounts Summary -->
                        <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">
                                    <i class="fas fa-wallet mr-2 text-indigo-500"></i>
                                    Accounts Summary
                                </h3>
                            </div>
                            <div class="px-4 py-5 sm:p-6">
                                <div class="space-y-4">
                                    @if ($customer->accounts && $customer->accounts->count() > 0)
                                        <!-- Total Balance -->
                                        <div class="bg-blue-50 p-4 rounded-lg">
                                            <p class="text-xs text-blue-600">Total Balance</p>
                                            <p class="text-2xl font-bold text-blue-900">
                                                ₵{{ number_format($customer->accounts->sum('current_balance'), 2) }}
                                            </p>
                                            <p class="text-xs text-blue-600 mt-1">
                                                Across {{ $customer->accounts->count() }} account(s)
                                            </p>
                                        </div>

                                        <!-- Account List -->
                                        <div class="space-y-3">
                                            <h4 class="text-sm font-medium text-gray-900">Account List</h4>
                                            @foreach ($customer->accounts as $account)
                                                <a href="{{ route('accounts.show', $account->id) }}"
                                                    class="block border border-gray-200 rounded-lg p-3 hover:bg-gray-50 transition-colors duration-150">
                                                    <div class="flex justify-between items-start">
                                                        <div>
                                                            <p class="text-sm font-medium text-gray-900">
                                                                {{ $account->account_number }}</p>
                                                            <p class="text-xs text-gray-500">
                                                                {{ $account->accountType->name ?? 'N/A' }}</p>
                                                        </div>
                                                        <div class="text-right">
                                                            <p class="text-sm font-bold text-gray-900">
                                                                ₵{{ number_format($account->current_balance, 2) }}</p>
                                                            <span
                                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $account->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                                {{ ucfirst($account->status) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center py-6">
                                            <div class="mx-auto h-12 w-12 text-gray-400">
                                                <i class="fas fa-wallet text-3xl"></i>
                                            </div>
                                            <h3 class="mt-4 text-sm font-medium text-gray-900">No accounts</h3>
                                            <p class="mt-1 text-sm text-gray-500">
                                                This customer doesn't have any accounts yet.
                                            </p>
                                            @can('create accounts')
                                                <div class="mt-4">
                                                    <a href="{{ route('accounts.create', ['customer_id' => $customer->id]) }}"
                                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        <i class="fas fa-plus-circle mr-2"></i>
                                                        Create Account
                                                    </a>
                                                </div>
                                            @endcan
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Branch & Relationship Manager -->
                        <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">
                                    <i class="fas fa-university mr-2 text-blue-500"></i>
                                    Branch & Management
                                </h3>
                            </div>
                            <div class="px-4 py-5 sm:p-6">
                                <div class="space-y-4">
                                    <!-- Branch Information -->
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900 mb-2">Branch</h4>
                                        <div class="flex items-center space-x-3">
                                            <div class="flex-shrink-0">
                                                <div
                                                    class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <i class="fas fa-building text-blue-600"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $customer->branch->name ?? 'Not assigned' }}</p>
                                                <p class="text-xs text-gray-500">
                                                    {{ $customer->branch->address ?? '' }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Relationship Manager -->
                                    @if ($customer->relationshipManager)
                                        <div class="pt-4 border-t border-gray-200">
                                            <h4 class="text-sm font-medium text-gray-900 mb-2">Relationship Manager
                                            </h4>
                                            <div class="flex items-center space-x-3">
                                                <div class="flex-shrink-0">
                                                    @if ($customer->relationshipManager->profile_photo_path)
                                                        <img class="h-10 w-10 rounded-full object-cover"
                                                            src="{{ Storage::url($customer->relationshipManager->profile_photo_path) }}"
                                                            alt="{{ $customer->relationshipManager->full_name }}">
                                                    @else
                                                        <div
                                                            class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                                            <span
                                                                class="text-green-600 font-medium">{{ $customer->relationshipManager->initials }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">
                                                        {{ $customer->relationshipManager->full_name }}</p>
                                                    <p class="text-xs text-gray-500">
                                                        {{ $customer->relationshipManager->email }}</p>
                                                    <p class="text-xs text-gray-500">
                                                        {{ $customer->relationshipManager->phone }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">
                                    <i class="fas fa-bolt mr-2 text-yellow-500"></i>
                                    Quick Actions
                                </h3>
                            </div>
                            <div class="px-4 py-5 sm:p-6">
                                <div class="space-y-3">
                                    @can('edit customers')
                                        <a href="{{ route('customers.edit', $customer->id) }}"
                                            class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <i class="fas fa-edit mr-2"></i>
                                            Edit Customer
                                        </a>
                                    @endcan

                                    @can('create accounts')
                                        <a href="{{ route('accounts.create', ['customer_id' => $customer->id]) }}"
                                            class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            <i class="fas fa-plus-circle mr-2"></i>
                                            Create Account
                                        </a>
                                    @endcan

                                    @can('view transactions')
                                        <a href="#"
                                            class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <i class="fas fa-exchange-alt mr-2"></i>
                                            View Transactions
                                        </a>
                                    @endcan

                                    <button type="button" onclick="window.print()"
                                        class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <i class="fas fa-print mr-2"></i>
                                        Print Profile
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Statistics -->
                        <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">
                                    <i class="fas fa-chart-bar mr-2 text-purple-500"></i>
                                    Customer Statistics
                                </h3>
                            </div>
                            <div class="px-4 py-5 sm:p-6">
                                <div class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="bg-gray-50 p-3 rounded-lg">
                                            <p class="text-xs text-gray-500">Accounts</p>
                                            <p class="text-lg font-bold text-gray-900">
                                                {{ $customer->accounts->count() }}</p>
                                        </div>
                                        <div class="bg-gray-50 p-3 rounded-lg">
                                            <p class="text-xs text-gray-500">Age</p>
                                            <p class="text-lg font-bold text-gray-900">
                                                {{ $customer->date_of_birth?->age ?? 'N/A' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="pt-4 border-t border-gray-200">
                                        <p class="text-xs text-gray-500 mb-2">Customer Since</p>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $customer->registered_at?->format('F d, Y') }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ $customer->registered_at?->diffForHumans() }}
                                        </p>
                                    </div>

                                    @if ($customer->verified_at)
                                        <div class="pt-4 border-t border-gray-200">
                                            <p class="text-xs text-gray-500 mb-2">KYC Verified</p>
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $customer->verified_at->format('F d, Y') }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ $customer->verified_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex justify-between items-center text-sm text-gray-500">
                    <div>
                        <p>Customer ID: <span class="font-medium">{{ $customer->id }}</span></p>
                        <p>Created: <span
                                class="font-medium">{{ $customer->created_at->format('F d, Y \a\t h:i A') }}</span>
                        </p>
                    </div>
                    <div class="text-right">
                        <p>Last Updated: <span
                                class="font-medium">{{ $customer->updated_at->format('F d, Y \a\t h:i A') }}</span>
                        </p>
                        <p>Created By: <span
                                class="font-medium">{{ $customer->metadata['created_by'] ?? 'System' }}</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // Print functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Add print styles
            const style = document.createElement('style');
            style.innerHTML = `
            @media print {
                .no-print {
                    display: none !important;
                }
                body * {
                    visibility: hidden;
                }
                #customer-profile, #customer-profile * {
                    visibility: visible;
                }
                #customer-profile {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                }
            }
        `;
            document.head.appendChild(style);

            // Add ID to main container for print targeting
            document.querySelector('.bg-white.rounded-lg.shadow-lg').id = 'customer-profile';
        });

        // Copy customer number to clipboard
        function copyCustomerNumber() {
            const customerNumber = '{{ $customer->customer_number }}';
            navigator.clipboard.writeText(customerNumber).then(function() {
                // Show toast notification
                const toast = document.createElement('div');
                toast.className =
                    'fixed bottom-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded';
                toast.innerHTML = 'Customer number copied to clipboard!';
                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.remove();
                }, 3000);
            });
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + P to print
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                window.print();
            }

            // Ctrl/Cmd + E to edit
            if ((e.ctrlKey || e.metaKey) && e.key === 'e') {
                e.preventDefault();
                @can('edit customers')
                    window.location.href = '{{ route('customers.edit', $customer->id) }}';
                @endcan
            }

            // Ctrl/Cmd + C to copy customer number
            if ((e.ctrlKey || e.metaKey) && e.key === 'c') {
                e.preventDefault();
                copyCustomerNumber();
            }
        });

        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            const tooltips = document.querySelectorAll('[title]');
            tooltips.forEach(tooltip => {
                tooltip.addEventListener('mouseenter', function(e) {
                    // You can implement tooltip display logic here
                    // or use a tooltip library like Tippy.js
                });
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        /* Custom scrollbar */
        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Smooth transitions */
        .transition-all {
            transition-property: all;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 150ms;
        }

        /* Card hover effects */
        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        /* Status badge colors */
        .status-active {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-suspended {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .status-inactive {
            background-color: #f3f4f6;
            color: #374151;
        }

        /* Gradient backgrounds */
        .gradient-blue {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }

        .gradient-green {
            background: linear-gradient(135deg, #10b981 0%, #047857 100%);
        }

        .gradient-purple {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }
    </style>
@endpush
