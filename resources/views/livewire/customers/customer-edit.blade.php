<div>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Edit Customer</h2>
                        <p class="text-sm text-gray-600 mt-1">
                            Update customer information for 
                            <span class="font-semibold">{{ $customer->customer_number }}</span>
                        </p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('customers.show', $customer->id) }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-eye mr-2"></i>
                            View Details
                        </a>
                        <a href="{{ route('customers.index') }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Customers
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Form -->
            <form wire:submit.prevent="update" class="p-6">
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">
                                    Please fix the following errors
                                </h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="list-disc list-inside space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Progress Steps -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <div class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-600 text-white">
                                <span class="text-sm font-bold">1</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900">Personal Information</span>
                        </div>
                        <div class="h-1 flex-1 bg-blue-200 mx-4"></div>
                        <div class="flex items-center space-x-2">
                            <div class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-600 text-white">
                                <span class="text-sm font-bold">2</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900">Contact & Address</span>
                        </div>
                        <div class="h-1 flex-1 bg-blue-200 mx-4"></div>
                        <div class="flex items-center space-x-2">
                            <div class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-600 text-white">
                                <span class="text-sm font-bold">3</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900">Employment & KYC</span>
                        </div>
                    </div>
                </div>

                <!-- Customer Info Badge -->
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center text-white text-lg font-bold">
                                {{ strtoupper(substr($customer->first_name, 0, 1)) }}{{ strtoupper(substr($customer->last_name, 0, 1)) }}
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900">{{ $customer->full_name }}</h4>
                                <p class="text-sm text-gray-600">Customer #: {{ $customer->customer_number }}</p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <span class="px-3 py-1 text-xs font-medium rounded {{ $customer->status === 'active' ? 'bg-green-100 text-green-800' : ($customer->status === 'suspended' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ ucfirst($customer->status) }}
                            </span>
                            <span class="px-3 py-1 text-xs font-medium rounded {{ $customer->kyc_status === 'verified' ? 'bg-purple-100 text-purple-800' : ($customer->kyc_status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ ucfirst($customer->kyc_status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Section 1: Personal Information -->
                <div class="mb-8">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-user-circle mr-2 text-blue-500"></i>
                            Personal Information
                        </h3>
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                            Required
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- First Name -->
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1 required">
                                First Name
                            </label>
                            <input type="text" id="first_name" wire:model="first_name"
                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Enter first name">
                            @error('first_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Last Name -->
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1 required">
                                Last Name
                            </label>
                            <input type="text" id="last_name" wire:model="last_name"
                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Enter last name">
                            @error('last_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 gap-6 mt-4">
                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1 required">
                                Email Address
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input type="email" id="email" wire:model="email"
                                    class="block w-full pl-10 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    placeholder="customer@example.com">
                            </div>
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Phone Number -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1 required">
                                Phone Number
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-phone text-gray-400"></i>
                                </div>
                                <input type="text" id="phone" wire:model="phone"
                                    class="block w-full pl-10 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    placeholder="0243XXXXXX">
                            </div>
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Alternate Phone -->
                        <div>
                            <label for="phone_alt" class="block text-sm font-medium text-gray-700 mb-1">
                                Alternate Phone
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-phone-alt text-gray-400"></i>
                                </div>
                                <input type="tel" id="phone_alt" wire:model="phone_alt"
                                    class="block w-full pl-10 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    placeholder="Optional">
                            </div>
                            @error('phone_alt')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Date of Birth -->
                        <div>
                            <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-1 required">
                                Date of Birth
                            </label>
                            <input type="date" id="date_of_birth" wire:model="date_of_birth"
                                max="{{ date('Y-m-d') }}"
                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            @error('date_of_birth')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @if ($date_of_birth)
                                <p class="mt-1 text-xs text-gray-500">
                                    Age: {{ $this->calculateAge() }} years
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-6 mt-4">
                        <!-- Gender -->
                        <div>
                            <label for="gender" class="block text-sm font-medium text-gray-700 mb-1 required">
                                Gender
                            </label>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="flex items-center space-x-2 p-2 border border-gray-300 rounded-md hover:bg-blue-50 cursor-pointer">
                                    <input type="radio" name="gender" value="male" wire:model="gender"
                                        class="text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Male</span>
                                </label>
                                <label class="flex items-center space-x-2 p-2 border border-gray-300 rounded-md hover:bg-blue-50 cursor-pointer">
                                    <input type="radio" name="gender" value="female" wire:model="gender"
                                        class="text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Female</span>
                                </label>
                            </div>
                            @error('gender')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Marital Status -->
                        <div>
                            <label for="marital_status" class="block text-sm font-medium text-gray-700 mb-1 required">
                                Marital Status
                            </label>
                            <select id="marital_status" wire:model="marital_status"
                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="single">Single</option>
                                <option value="married">Married</option>
                                <option value="divorced">Divorced</option>
                                <option value="widowed">Widowed</option>
                            </select>
                            @error('marital_status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Dependents -->
                        <div>
                            <label for="dependents" class="block text-sm font-medium text-gray-700 mb-1">
                                Number of Dependents
                            </label>
                            <input type="number" id="dependents" wire:model="dependents" min="0"
                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="0">
                            @error('dependents')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-6 mt-4">
                        <!-- Education Level -->
                        <div>
                            <label for="education_level" class="block text-sm font-medium text-gray-700 mb-1">
                                Education Level
                            </label>
                            <input type="text" id="education_level" wire:model="education_level"
                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="e.g., Bachelor's Degree">
                            @error('education_level')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Nationality -->
                        <div>
                            <label for="nationality" class="block text-sm font-medium text-gray-700 mb-1 required">
                                Nationality
                            </label>
                            <input type="text" id="nationality" wire:model="nationality"
                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="e.g., Ghanaian">
                            @error('nationality')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 2: Contact & Address -->
                <div class="mb-8 pt-6 border-t border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-address-book mr-2 text-green-500"></i>
                            Contact & Address Details
                        </h3>
                        <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                            Required
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                        <!-- Address Line 1 -->
                        <div>
                            <label for="address_line_1" class="block text-sm font-medium text-gray-700 mb-1 required">
                                Address Line 1
                            </label>
                            <input type="text" id="address_line_1" wire:model="address_line_1"
                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Street address, P.O. Box, Company name">
                            @error('address_line_1')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Address Line 2 -->
                        <div>
                            <label for="address_line_2" class="block text-sm font-medium text-gray-700 mb-1">
                                Address Line 2 (Optional)
                            </label>
                            <input type="text" id="address_line_2" wire:model="address_line_2"
                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Apartment, suite, unit, building, floor">
                            @error('address_line_2')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-6 mt-4">
                        <!-- City -->
                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700 mb-1 required">
                                City
                            </label>
                            <input type="text" id="city" wire:model="city"
                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="City">
                            @error('city')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- State -->
                        <div>
                            <label for="state" class="block text-sm font-medium text-gray-700 mb-1 required">
                                Region
                            </label>
                            <input type="text" id="state" wire:model="state"
                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="State or Region">
                            @error('state')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Country -->
                        <div>
                            <label for="country" class="block text-sm font-medium text-gray-700 mb-1 required">
                                Country
                            </label>
                            <select id="country" wire:model="country"
                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Select Country</option>
                                @foreach ($countries as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                            @error('country')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 3: Identification -->
                <div class="mb-8 pt-6 border-t border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-id-card mr-2 text-purple-500"></i>
                            Identification Details
                        </h3>
                        <span class="px-3 py-1 bg-purple-100 text-purple-800 text-sm font-medium rounded-full">
                            Required for KYC
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- ID Type -->
                        <div>
                            <label for="id_type" class="block text-sm font-medium text-gray-700 mb-1 required">
                                ID Type
                            </label>
                            <select id="id_type" wire:model="id_type"
                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Select ID Type</option>
                                @foreach ($idTypes as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                            @error('id_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- ID Number -->
                        <div>
                            <label for="id_number" class="block text-sm font-medium text-gray-700 mb-1 required">
                                ID Number
                            </label>
                            <input type="text" id="id_number" wire:model="id_number"
                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Enter ID number">
                            @error('id_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- ID Expiry Date -->
                        <div>
                            <label for="id_expiry_date" class="block text-sm font-medium text-gray-700 mb-1 required">
                                ID Expiry Date
                            </label>
                            <input type="date" id="id_expiry_date" wire:model="id_expiry_date"
                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            @error('id_expiry_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- ID Issuing Country -->
                        <div>
                            <label for="id_issuing_country" class="block text-sm font-medium text-gray-700 mb-1 required">
                                ID Issuing Country
                            </label>
                            <input type="text" id="id_issuing_country" wire:model="id_issuing_country"
                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Country that issued the ID">
                            @error('id_issuing_country')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- File Uploads Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <!-- ID Document Upload -->
                        <div class="md:col-span-2">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- ID Front Image -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        ID Front Image
                                    </label>
                                    @if ($existing_id_front_image)
                                        <div class="mb-3 p-3 border border-gray-200 rounded-lg bg-gray-50">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-3">
                                                    <div class="h-12 w-12 bg-gray-100 rounded-md flex items-center justify-center">
                                                        <i class="fas fa-file-image text-gray-400"></i>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900">Front ID Image</p>
                                                        <p class="text-xs text-gray-500">Uploaded</p>
                                                    </div>
                                                </div>
                                                <div class="flex space-x-2">
                                                    <a href="{{ $existing_id_front_image }}" target="_blank" 
                                                       class="px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100">
                                                        View
                                                    </a>
                                                    <button type="button" wire:click="removeExistingFile('id_front')"
                                                            class="px-3 py-1 text-xs font-medium text-red-600 bg-red-50 rounded-md hover:bg-red-100">
                                                        Remove
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                        <div class="space-y-1 text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <div class="flex text-sm text-gray-600 justify-center">
                                                <label class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                                    <span>Upload Front</span>
                                                    <input type="file" wire:model="id_front_image" class="sr-only" accept="image/*,.pdf">
                                                </label>
                                            </div>
                                            <p class="text-xs text-gray-500">
                                                PNG, JPG, PDF up to 5MB
                                            </p>
                                        </div>
                                    </div>
                                    @error('id_front_image')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    @if ($id_front_image)
                                        <p class="mt-2 text-sm text-green-600">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            New front image selected
                                        </p>
                                    @endif
                                </div>

                                <!-- ID Back Image -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        ID Back Image (Optional)
                                    </label>
                                    @if ($existing_id_back_image)
                                        <div class="mb-3 p-3 border border-gray-200 rounded-lg bg-gray-50">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-3">
                                                    <div class="h-12 w-12 bg-gray-100 rounded-md flex items-center justify-center">
                                                        <i class="fas fa-file-image text-gray-400"></i>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900">Back ID Image</p>
                                                        <p class="text-xs text-gray-500">Uploaded</p>
                                                    </div>
                                                </div>
                                                <div class="flex space-x-2">
                                                    <a href="{{ $existing_id_back_image }}" target="_blank" 
                                                       class="px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100">
                                                        View
                                                    </a>
                                                    <button type="button" wire:click="removeExistingFile('id_back')"
                                                            class="px-3 py-1 text-xs font-medium text-red-600 bg-red-50 rounded-md hover:bg-red-100">
                                                        Remove
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                        <div class="space-y-1 text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <div class="flex text-sm text-gray-600 justify-center">
                                                <label class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                                    <span>Upload Back</span>
                                                    <input type="file" wire:model="id_back_image" class="sr-only" accept="image/*,.pdf">
                                                </label>
                                            </div>
                                            <p class="text-xs text-gray-500">
                                                PNG, JPG, PDF up to 5MB
                                            </p>
                                        </div>
                                    </div>
                                    @if ($id_back_image)
                                        <p class="mt-2 text-sm text-green-600">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            New back image selected
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Profile Photo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Profile Photo (Optional)
                            </label>
                            @if ($existing_profile_photo)
                                <div class="mb-3">
                                    <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg bg-gray-50">
                                        <div class="flex items-center space-x-3">
                                            <div class="h-12 w-12 rounded-full overflow-hidden">
                                                <img src="{{ $existing_profile_photo }}" alt="Profile" class="h-full w-full object-cover">
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Profile Photo</p>
                                                <p class="text-xs text-gray-500">Uploaded</p>
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            <button type="button" wire:click="removeExistingFile('profile_photo')"
                                                    class="px-3 py-1 text-xs font-medium text-red-600 bg-red-50 rounded-md hover:bg-red-100">
                                                Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600 justify-center">
                                        <label class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                            <span>Upload Photo</span>
                                            <input type="file" wire:model="profile_photo" class="sr-only" accept="image/*">
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        PNG, JPG up to 2MB
                                    </p>
                                </div>
                            </div>
                            @if ($profile_photo)
                                <p class="mt-2 text-sm text-green-600">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    New profile photo selected
                                </p>
                            @endif
                        </div>

                        <!-- Signature Image -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Signature Image (Optional)
                            </label>
                            @if ($existing_signature_image)
                                <div class="mb-3 p-3 border border-gray-200 rounded-lg bg-gray-50">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="h-12 w-12 bg-gray-100 rounded-md flex items-center justify-center">
                                                <i class="fas fa-signature text-gray-400"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Signature</p>
                                                <p class="text-xs text-gray-500">Uploaded</p>
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            <a href="{{ $existing_signature_image }}" target="_blank" 
                                               class="px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100">
                                                View
                                            </a>
                                            <button type="button" wire:click="removeExistingFile('signature')"
                                                    class="px-3 py-1 text-xs font-medium text-red-600 bg-red-50 rounded-md hover:bg-red-100">
                                                Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600 justify-center">
                                        <label class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                            <span>Upload Signature</span>
                                            <input type="file" wire:model="signature_image" class="sr-only" accept="image/*">
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        PNG, JPG up to 2MB
                                    </p>
                                </div>
                            </div>
                            @if ($signature_image)
                                <p class="mt-2 text-sm text-green-600">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    New signature image selected
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Section 4: Employment & Income -->
                <div class="mb-8 pt-6 border-t border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-briefcase mr-2 text-orange-500"></i>
                            Employment & Income Details
                        </h3>
                        <span class="px-3 py-1 bg-orange-100 text-orange-800 text-sm font-medium rounded-full">
                            Required for Risk Assessment
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Occupation -->
                        <div>
                            <label for="occupation" class="block text-sm font-medium text-gray-700 mb-1 required">
                                Occupation
                            </label>
                            <input type="text" id="occupation" wire:model="occupation"
                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="e.g., Software Engineer">
                            @error('occupation')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Employer Name -->
                        <div>
                            <label for="employer_name" class="block text-sm font-medium text-gray-700 mb-1">
                                Employer Name (Optional)
                            </label>
                            <input type="text" id="employer_name" wire:model="employer_name"
                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Company name">
                            @error('employer_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Employer Address -->
                        <div>
                            <label for="employer_address" class="block text-sm font-medium text-gray-700 mb-1">
                                Employer Address (Optional)
                            </label>
                            <textarea id="employer_address" wire:model="employer_address" rows="2"
                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Company address"></textarea>
                            @error('employer_address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4">
                        <!-- Monthly Income -->
                        <div>
                            <label for="monthly_income" class="block text-sm font-medium text-gray-700 mb-1 required">
                                Monthly Income (₵)
                            </label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">₵</span>
                                </div>
                                <input type="number" id="monthly_income" wire:model="monthly_income" step="0.01" min="0"
                                    class="block w-full pl-7 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    placeholder="0.00">
                            </div>
                            @error('monthly_income')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Source of Income -->
                        <div>
                            <label for="source_of_income" class="block text-sm font-medium text-gray-700 mb-1 required">
                                Source of Income
                            </label>
                            <input type="text" id="source_of_income" wire:model="source_of_income"
                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="e.g., Employment, Business, Investments">
                            @error('source_of_income')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Net Worth -->
                        <div>
                            <label for="net_worth" class="block text-sm font-medium text-gray-700 mb-1">
                                Net Worth (₵) - Optional
                            </label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">₵</span>
                                </div>
                                <input type="number" id="net_worth" wire:model="net_worth" step="0.01" min="0"
                                    class="block w-full pl-7 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    placeholder="0.00">
                            </div>
                            @error('net_worth')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 5: Emergency Contacts -->
                <div class="mb-8 pt-6 border-t border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-first-aid mr-2 text-red-500"></i>
                            Emergency Contacts
                        </h3>
                        <span class="px-3 py-1 bg-red-100 text-red-800 text-sm font-medium rounded-full">
                            Recommended
                        </span>
                    </div>
                    @foreach ($emergency_contacts as $index => $contact)
                        <div class="mb-4 p-4 border border-gray-200 rounded-lg bg-gray-50">
                            <div class="flex justify-between items-center mb-3">
                                <h4 class="text-sm font-medium text-gray-900">Emergency Contact {{ $index + 1 }}</h4>
                                @if ($index > 0)
                                    <button type="button" wire:click="removeEmergencyContact({{ $index }})"
                                        class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Contact Name
                                    </label>
                                    <input type="text" wire:model="emergency_contacts.{{ $index }}.name"
                                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        placeholder="Full name">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Relationship
                                    </label>
                                    <input type="text" wire:model="emergency_contacts.{{ $index }}.relationship"
                                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        placeholder="e.g., Spouse, Parent, Sibling">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Phone Number
                                    </label>
                                    <input type="tel" wire:model="emergency_contacts.{{ $index }}.phone"
                                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        placeholder="Phone number">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Email (Optional)
                                    </label>
                                    <input type="email" wire:model="emergency_contacts.{{ $index }}.email"
                                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        placeholder="Email address">
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <button type="button" wire:click="addEmergencyContact"
                        class="mt-2 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-plus mr-2"></i>
                        Add Another Emergency Contact
                    </button>
                </div>

                <!-- Section 6: Bank Information -->
                <div class="mb-8 pt-6 border-t border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-university mr-2 text-indigo-500"></i>
                            Bank Information
                        </h3>
                        <span class="px-3 py-1 bg-indigo-100 text-indigo-800 text-sm font-medium rounded-full">
                            System Settings
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Branch -->
                        @if (auth()->user()->can('view all branches'))
                            <div>
                                <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1 required">
                                    Branch
                                </label>
                                <select id="branch_id" wire:model="branch_id"
                                    class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="">Select Branch</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                @error('branch_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <!-- Relationship Manager -->
                        <div>
                            <label for="relationship_manager_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Relationship Manager (Optional)
                            </label>
                            <select id="relationship_manager_id" wire:model="relationship_manager_id"
                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Select Relationship Manager</option>
                                @foreach ($relationshipManagers as $manager)
                                    <option value="{{ $manager->id }}">{{ $manager->full_name }} ({{ $manager->email }})</option>
                                @endforeach
                            </select>
                            @error('relationship_manager_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Customer Type -->
                        <div>
                            <label for="customer_type" class="block text-sm font-medium text-gray-700 mb-1 required">
                                Customer Type
                            </label>
                            <select id="customer_type" wire:model="customer_type"
                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="individual">Individual</option>
                                <option value="corporate">Corporate</option>
                            </select>
                            @error('customer_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Customer Tier -->
                        <div>
                            <label for="customer_tier" class="block text-sm font-medium text-gray-700 mb-1 required">
                                Customer Tier
                            </label>
                            <select id="customer_tier" wire:model="customer_tier"
                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="basic">Basic</option>
                                <option value="premium">Premium</option>
                                <option value="platinum">Platinum</option>
                            </select>
                            @error('customer_tier')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4">
                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1 required">
                                Status
                            </label>
                            <select id="status" wire:model="status"
                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- KYC Status -->
                        <div>
                            <label for="kyc_status" class="block text-sm font-medium text-gray-700 mb-1 required">
                                KYC Status
                            </label>
                            <select id="kyc_status" wire:model="kyc_status"
                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="pending">Pending</option>
                                <option value="verified">Verified</option>
                                <option value="rejected">Rejected</option>
                            </select>
                            @error('kyc_status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Risk Profile -->
                        <div>
                            <label for="risk_profile" class="block text-sm font-medium text-gray-700 mb-1 required">
                                Risk Profile
                            </label>
                            <select id="risk_profile" wire:model="risk_profile"
                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                            @error('risk_profile')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="md:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                Internal Notes (Optional)
                            </label>
                            <textarea id="notes" wire:model="notes" rows="3"
                                class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Any internal notes or comments about this customer..."></textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Terms & Conditions -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-500 mt-1"></i>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-gray-900">Important Information</h4>
                            <ul class="mt-2 text-sm text-gray-600 list-disc list-inside space-y-1">
                                <li>Sections marked required must be completed before submission</li>
                                <li>Customer updates will be effective immediately upon submission</li>
                                <li>KYC verification may be required after updating identification details</li>
                                <li>Ensure all information is accurate before submitting</li>
                                <li>Customer number cannot be changed: {{ $customer->customer_number }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-8 flex justify-end space-x-4">
                    <a href="{{ route('customers.show', $customer->id) }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <button type="submit"
                        class="inline-flex items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-save mr-2"></i>
                        Update Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // Same scripts as create page for consistency
        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.getElementById('phone');
            const altPhoneInput = document.getElementById('phone_alt');

            function formatPhoneNumber(input) {
                if (!input) return;

                input.addEventListener('input', function(e) {
                    let value = this.value.replace(/\D/g, '');

                    // Format for Ghanaian phone numbers
                    if (value.startsWith('0')) {
                        value = '+233' + value.substring(1);
                    } else if (value.startsWith('233')) {
                        value = '+' + value;
                    } else if (value.length > 0 && !value.startsWith('+')) {
                        value = '+233' + value;
                    }

                    // Format with spaces for readability
                    if (value.startsWith('+233') && value.length > 4) {
                        const prefix = value.substring(0, 4); // +233
                        const rest = value.substring(4);

                        if (rest.length <= 3) {
                            value = prefix + ' ' + rest;
                        } else if (rest.length <= 6) {
                            value = prefix + ' ' + rest.substring(0, 3) + ' ' + rest.substring(3);
                        } else {
                            value = prefix + ' ' + rest.substring(0, 3) + ' ' + rest.substring(3, 6) + ' ' +
                                rest.substring(6, 9);
                        }
                    }

                    this.value = value;
                });
            }

            formatPhoneNumber(phoneInput);
            formatPhoneNumber(altPhoneInput);

            // Auto-calculate age when date of birth changes
            const dobInput = document.getElementById('date_of_birth');
            if (dobInput) {
                dobInput.addEventListener('change', function(e) {
                    if (this.value) {
                        @this.calculateAge();
                    }
                });
            }

            // Format currency inputs
            const currencyInputs = document.querySelectorAll(
                'input[type="number"][id*="income"], input[type="number"][id*="worth"]');

            currencyInputs.forEach(input => {
                input.addEventListener('blur', function(e) {
                    if (this.value) {
                        this.value = parseFloat(this.value).toFixed(2);
                    }
                });

                input.addEventListener('input', function(e) {
                    // Remove any non-numeric characters except decimal point
                    this.value = this.value.replace(/[^\d.]/g, '');

                    // Ensure only one decimal point
                    const decimalCount = (this.value.match(/\./g) || []).length;
                    if (decimalCount > 1) {
                        this.value = this.value.slice(0, -1);
                    }

                    // Limit to 2 decimal places
                    if (this.value.includes('.')) {
                        const parts = this.value.split('.');
                        if (parts[1].length > 2) {
                            this.value = parts[0] + '.' + parts[1].substring(0, 2);
                        }
                    }
                });
            });

            // Set minimum date for ID expiry to today
            const idExpiryInput = document.getElementById('id_expiry_date');
            if (idExpiryInput) {
                const today = new Date().toISOString().split('T')[0];
                idExpiryInput.min = today;
            }
        });

        // Livewire event listeners
        document.addEventListener('livewire:init', () => {
            Livewire.on('customer-updated', (data) => {
                // Show success toast or redirect
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            });

            Livewire.on('validation-error', (errors) => {
                // Scroll to first error
                const firstError = document.querySelector('.text-red-600');
                if (firstError) {
                    firstError.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            });
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + S to save
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                document.querySelector('button[type="submit"]').click();
            }

            // Ctrl/Cmd + Enter to save
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                document.querySelector('button[type="submit"]').click();
            }
        });
    </script>
@endpush

@push('styles')
    <style>
        /* Required field indicator */
        .required::after {
            content: " *";
            color: #ef4444;
        }

        /* Smooth transitions */
        .transition-all {
            transition-property: all;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 150ms;
        }

        /* File upload styling */
        .border-dashed:hover {
            border-color: #3b82f6;
            background-color: #f0f9ff;
        }

        /* Radio button styling */
        input[type="radio"]:checked+span {
            font-weight: 600;
            color: #1e40af;
        }

        /* Focus styles */
        .focus\:ring-blue-500:focus {
            --tw-ring-color: rgba(59, 130, 246, 0.5);
        }
    </style>
@endpush