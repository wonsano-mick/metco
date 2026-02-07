<div>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-xl overflow-hidden">
            <!-- Header -->
            <div class="px-8 py-6 border-b border-gray-200 bg-gradient-to-r from-blue-600 to-blue-800 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold">Create New Customer</h2>
                        <p class="text-blue-100 mt-1">Register a new individual or organizational customer in the banking
                            system</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('customers.index') }}"
                            class="inline-flex items-center px-4 py-2 border border-blue-400 rounded-lg text-sm font-medium text-white bg-blue-700/30 hover:bg-blue-700/50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Customers
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="px-8 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-blue-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-8">
                        @foreach ([['step' => 1, 'icon' => 'fa-user-tag', 'label' => 'Type', 'color' => 'blue'], ['step' => 2, 'icon' => 'fa-id-card', 'label' => 'Details', 'color' => 'purple'], ['step' => 3, 'icon' => 'fa-map-marker-alt', 'label' => 'Address', 'color' => 'green'], ['step' => 4, 'icon' => 'fa-university', 'label' => 'Bank Info', 'color' => 'indigo'], ['step' => 5, 'icon' => 'fa-file-contract', 'label' => 'KYC', 'color' => 'orange'], ['step' => 6, 'icon' => 'fa-check-circle', 'label' => 'Review', 'color' => 'red']] as $tab)
                            <button type="button" wire:click="goToStep({{ $tab['step'] }})"
                                class="flex items-center space-x-2 px-4 py-2 rounded-lg transition-all duration-200
                                           {{ $currentStep === $tab['step']
                                               ? 'bg-white shadow-md text-gray-900'
                                               : 'text-gray-600 hover:text-gray-900 hover:bg-white/50' }}">
                                <div
                                    class="flex items-center justify-center w-8 h-8 rounded-full 
                                            {{ $currentStep === $tab['step']
                                                ? 'bg-' . $tab['color'] . '-100 text-' . $tab['color'] . '-600'
                                                : 'bg-gray-200 text-gray-500' }}">
                                    <i class="fas {{ $tab['icon'] }} text-sm"></i>
                                </div>
                                <span class="font-medium text-sm">
                                    {{ $tab['step'] }}. {{ $tab['label'] }}
                                </span>
                                @if ($tab['step'] < $currentStep)
                                    <i class="fas fa-check text-green-500 ml-2"></i>
                                @endif
                            </button>
                        @endforeach
                    </div>

                    <!-- Progress Indicator -->
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-600">Step {{ $currentStep }} of 6</span>
                        <div class="w-32 h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-blue-500 to-green-500 transition-all duration-500"
                                style="width: {{ ($currentStep / 6) * 100 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Form -->
            <form wire:submit.prevent="save" class="p-8">
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

                <!-- Step 1: Customer Type Selection -->
                @if ($currentStep == 1)
                    <div class="mb-10">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">1. Select Customer Type</h3>
                                <p class="text-sm text-gray-600 mt-1">Choose whether you're registering an individual or
                                    an organization</p>
                            </div>
                            @if ($customer_type)
                                <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                                    <i class="fas fa-check mr-1"></i> Selected
                                </span>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Individual Customer Type -->
                            <button type="button" wire:click="selectCustomerType('individual')"
                                class="p-6 border rounded-xl text-left transition-all duration-200 hover:shadow-md
                                    {{ $customer_type === 'individual'
                                        ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200'
                                        : 'border-gray-300 hover:border-blue-300 hover:bg-blue-50' }}">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                                            <i class="fas fa-user text-blue-600 text-xl"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <h4 class="font-semibold text-gray-900 text-lg">Individual Customer</h4>
                                        <p class="text-gray-600 mt-2">For personal banking customers. Register
                                            individual persons with personal identification.</p>
                                        <div class="mt-4 space-y-2">
                                            <div class="flex items-center text-sm text-gray-500">
                                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                                Personal identification required
                                            </div>
                                            <div class="flex items-center text-sm text-gray-500">
                                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                                Date of birth and personal details
                                            </div>
                                            <div class="flex items-center text-sm text-gray-500">
                                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                                Employment and income information
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </button>

                            <!-- Organizational Customer Type -->
                            <button type="button" wire:click="selectCustomerType('organization')"
                                class="p-6 border rounded-xl text-left transition-all duration-200 hover:shadow-md
                                    {{ $customer_type === 'organization'
                                        ? 'border-purple-500 bg-purple-50 ring-2 ring-purple-200'
                                        : 'border-gray-300 hover:border-purple-300 hover:bg-purple-50' }}">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
                                            <i class="fas fa-building text-purple-600 text-xl"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <h4 class="font-semibold text-gray-900 text-lg">Organization Customer</h4>
                                        <p class="text-gray-600 mt-2">For businesses, companies, NGOs, and other
                                            organizations. Register with business registration documents.</p>
                                        <div class="mt-4 space-y-2">
                                            <div class="flex items-center text-sm text-gray-500">
                                                <i class="fas fa-check-circle text-purple-500 mr-2"></i>
                                                Business registration required
                                            </div>
                                            <div class="flex items-center text-sm text-gray-500">
                                                <i class="fas fa-check-circle text-purple-500 mr-2"></i>
                                                Organizational details and structure
                                            </div>
                                            <div class="flex items-center text-sm text-gray-500">
                                                <i class="fas fa-check-circle text-purple-500 mr-2"></i>
                                                Authorized signatories information
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </button>
                        </div>

                        @error('customer_type')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <!-- Next Button -->
                        @if ($customer_type)
                            <div class="mt-8 pt-6 border-t border-gray-200">
                                <div class="flex justify-end">
                                    <button type="button" wire:click="nextStep"
                                        class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg text-sm font-medium text-white {{ $customer_type === 'individual' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-purple-600 hover:bg-purple-700' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg">
                                        <i class="fas fa-arrow-right mr-2"></i>
                                        Continue to {{ $customer_type === 'individual' ? 'Personal' : 'Organization' }}
                                        Details
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Step 2: Personal/Organization Details -->
                @if ($currentStep == 2 && $customer_type)
                    <div class="mb-10">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    2. {{ $customer_type === 'individual' ? 'Personal' : 'Organization' }} Details
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    @if ($customer_type === 'individual')
                                        Enter the personal information for the individual customer
                                    @else
                                        Enter the organization details and registration information
                                    @endif
                                </p>
                            </div>
                            <button type="button" wire:click="previousStep"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Back
                            </button>
                        </div>

                        <!-- Individual Customer Form -->
                        @if ($customer_type === 'individual')
                            <div class="space-y-6">
                                <!-- Name Fields -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="first_name"
                                            class="block text-sm font-medium text-gray-700 mb-2 required">
                                            First Name
                                        </label>
                                        <input type="text" id="first_name" wire:model="first_name"
                                            class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Enter first name">
                                        @error('first_name')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="last_name"
                                            class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Last Name
                                        </label>
                                        <input type="text" id="last_name" wire:model="last_name"
                                            class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Enter last name">
                                        @error('last_name')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Contact Information -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <div>
                                        <label for="email"
                                            class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Email Address
                                        </label>
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-envelope text-gray-400"></i>
                                            </div>
                                            <input type="email" id="email" wire:model="email"
                                                class="block w-full pl-10 border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                placeholder="customer@example.com">
                                        </div>
                                        @error('email')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="phone"
                                            class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Phone Number
                                        </label>
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-phone text-gray-400"></i>
                                            </div>
                                            <input type="text" id="phone" wire:model="phone"
                                                class="block w-full pl-10 border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                placeholder="0243XXXXXX">
                                        </div>
                                        @error('phone')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="phone_alt" class="block text-sm font-medium text-gray-700 mb-1">
                                            Alternate Phone (Optional)
                                        </label>
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-phone-alt text-gray-400"></i>
                                            </div>
                                            <input type="text" id="phone" wire:model="phone_alt"
                                                class="block w-full pl-10 border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                placeholder="0243XXXXXX">
                                        </div>
                                        @error('phone_alt')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Personal Details -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label for="date_of_birth"
                                            class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Date of Birth
                                        </label>
                                        <input type="date" id="date_of_birth" wire:model.live="date_of_birth"
                                            max="{{ date('Y-m-d') }}"
                                            class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        @error('date_of_birth')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                        @if ($date_of_birth)
                                            <p class="mt-2 text-sm text-gray-500">
                                                Age: {{ $this->calculateAge() }} years
                                                @if ($this->calculateAge() < 18)
                                                    <span class="text-red-600 font-medium ml-2">(Must be 18+)</span>
                                                @endif
                                            </p>
                                        @endif
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Gender
                                        </label>
                                        <div class="grid grid-cols-2 gap-3">
                                            <label
                                                class="flex items-center space-x-2 p-3 border border-gray-300 rounded-lg hover:bg-blue-50 cursor-pointer transition-colors">
                                                <input type="radio" name="gender" value="male"
                                                    wire:model="gender" class="text-blue-600 focus:ring-blue-500">
                                                <span class="text-sm text-gray-700">Male</span>
                                            </label>
                                            <label
                                                class="flex items-center space-x-2 p-3 border border-gray-300 rounded-lg hover:bg-blue-50 cursor-pointer transition-colors">
                                                <input type="radio" name="gender" value="female"
                                                    wire:model="gender" class="text-blue-600 focus:ring-blue-500">
                                                <span class="text-sm text-gray-700">Female</span>
                                            </label>
                                        </div>
                                        @error('gender')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="nationality"
                                            class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Nationality
                                        </label>
                                        <input type="text" id="nationality" wire:model="nationality"
                                            class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="e.g., Ghanaian">
                                        @error('nationality')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Additional Personal Information -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label for="marital_status"
                                            class="block text-sm font-medium text-gray-700 mb-2">
                                            Marital Status
                                        </label>
                                        <select id="marital_status" wire:model="marital_status"
                                            class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="single">Single</option>
                                            <option value="married">Married</option>
                                            <option value="divorced">Divorced</option>
                                            <option value="widowed">Widowed</option>
                                        </select>
                                        @error('marital_status')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="dependents" class="block text-sm font-medium text-gray-700 mb-2">
                                            Number of Dependents
                                        </label>
                                        <input type="number" id="dependents" wire:model="dependents" min="0"
                                            class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="0">
                                        @error('dependents')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="education_level"
                                            class="block text-sm font-medium text-gray-700 mb-2">
                                            Education Level
                                        </label>
                                        <select id="education_level" wire:model="education_level"
                                            class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">---select education level---</option>
                                            <option value="tertiary">Tertiary</option>
                                            <option value="shs">SHS</option>
                                            <option value="jhs">JHS</option>
                                            <option value="none">None</option>
                                        </select>
                                        @error('education_level')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Organization Customer Form -->
                        @if ($customer_type === 'organization')
                            <div class="space-y-6">
                                <!-- Organization Name -->
                                <div>
                                    <label for="company_name"
                                        class="block text-sm font-medium text-gray-700 mb-2 required">
                                        Organization Name
                                    </label>
                                    <input type="text" id="company_name" wire:model="company_name"
                                        class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                        placeholder="Enter organization/company name">
                                    @error('company_name')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Contact Information -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="email"
                                            class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Business Email
                                        </label>
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-envelope text-gray-400"></i>
                                            </div>
                                            <input type="email" id="email" wire:model="email"
                                                class="block w-full pl-10 border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                                placeholder="business@company.com">
                                        </div>
                                        @error('email')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="phone"
                                            class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Business Phone
                                        </label>
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-phone text-gray-400"></i>
                                            </div>
                                            <input type="text" id="phone" wire:model="phone"
                                                class="block w-full pl-10 border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                                placeholder="0243XXXXXX">
                                        </div>
                                        @error('phone')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Organization Details -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label for="organization_type"
                                            class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Organization Type
                                        </label>
                                        <select id="organization_type" wire:model="organization_type"
                                            class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                            <option value="">Select Type</option>
                                            <option value="corporation">Corporation</option>
                                            <option value="llc">Limited Liability Company (LLC)</option>
                                            <option value="partnership">Partnership</option>
                                            <option value="sole_proprietorship">Sole Proprietorship</option>
                                            <option value="ngo">Non-Governmental Organization (NGO)</option>
                                            <option value="government">Government Entity</option>
                                            <option value="other">Other</option>
                                        </select>
                                        @error('organization_type')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="registration_number"
                                            class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Registration Number
                                        </label>
                                        <input type="text" id="registration_number"
                                            wire:model="registration_number"
                                            class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                            placeholder="e.g., CG123456789">
                                        @error('registration_number')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="tax_identification_number"
                                            class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Tax Identification Number
                                        </label>
                                        <input type="text" id="tax_identification_number"
                                            wire:model="tax_identification_number"
                                            class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                            placeholder="e.g., TIN123456789">
                                        @error('tax_identification_number')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Industry and Business Nature -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="industry"
                                            class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Industry
                                        </label>
                                        <select id="industry" wire:model="industry"
                                            class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                            <option value="">Select Industry</option>
                                            <option value="agriculture">Agriculture</option>
                                            <option value="manufacturing">Manufacturing</option>
                                            <option value="construction">Construction</option>
                                            <option value="retail">Retail & Wholesale</option>
                                            <option value="technology">Technology</option>
                                            <option value="finance">Finance & Insurance</option>
                                            <option value="healthcare">Healthcare</option>
                                            <option value="education">Education</option>
                                            <option value="real_estate">Real Estate</option>
                                            <option value="transportation">Transportation</option>
                                            <option value="hospitality">Hospitality & Tourism</option>
                                            <option value="other">Other</option>
                                        </select>
                                        @error('industry')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="business_nature"
                                            class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Nature of Business
                                        </label>
                                        <textarea id="business_nature" wire:model="business_nature" rows="3"
                                            class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                            placeholder="Describe the nature of your business activities"></textarea>
                                        @error('business_nature')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Contact Person -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label for="contact_person"
                                            class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Contact Person Name
                                        </label>
                                        <input type="text" id="contact_person" wire:model="contact_person"
                                            class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                            placeholder="Full name of contact person">
                                        @error('contact_person')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="contact_person_position"
                                            class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Position/Title
                                        </label>
                                        <input type="text" id="contact_person_position"
                                            wire:model="contact_person_position"
                                            class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                            placeholder="e.g., Managing Director, CEO">
                                        @error('contact_person_position')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="contact_person_phone"
                                            class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Contact Person Phone
                                        </label>
                                        <input type="text" id="contact_person_phone"
                                            wire:model="contact_person_phone"
                                            class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                            placeholder="0243XXXXXX">
                                        @error('contact_person_phone')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Navigation Buttons -->
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <div class="flex justify-between">
                                <button type="button" wire:click="previousStep"
                                    class="inline-flex items-center px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="fas fa-arrow-left mr-2"></i>
                                    Back
                                </button>
                                <button type="button" wire:click="nextStep"
                                    class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg text-sm font-medium text-white {{ $customer_type === 'individual' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-purple-600 hover:bg-purple-700' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Continue to Address Details
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Step 3: Address Details -->
                @if ($currentStep == 3 && $customer_type)
                    <div class="mb-10">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">3. Address Details</h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    Enter the address information
                                </p>
                            </div>
                            <button type="button" wire:click="previousStep"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Back
                            </button>
                        </div>

                        <div class="space-y-6">
                            <!-- Address Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="address_line_1"
                                        class="block text-sm font-medium text-gray-700 mb-2 required">
                                        @if ($customer_type === 'individual')
                                            Digital Address
                                        @else
                                            Business Address Line 1
                                        @endif
                                    </label>
                                    <input type="text" id="address_line_1" wire:model="address_line_1"
                                        class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 {{ $customer_type === 'individual' ? 'focus:ring-blue-500 focus:border-blue-500' : 'focus:ring-purple-500 focus:border-purple-500' }}"
                                        placeholder="{{ $customer_type === 'individual' ? 'BU-XXXX-XXXX' : 'Street address, P.O. Box' }}">
                                    @error('address_line_1')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="address_line_2" class="block text-sm font-medium text-gray-700 mb-2">
                                        @if ($customer_type === 'individual')
                                            Address Line 2 (Optional)
                                        @else
                                            Business Address Line 2
                                        @endif
                                    </label>
                                    <input type="text" id="address_line_2" wire:model="address_line_2"
                                        class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 {{ $customer_type === 'individual' ? 'focus:ring-blue-500 focus:border-blue-500' : 'focus:ring-purple-500 focus:border-purple-500' }}"
                                        placeholder="{{ $customer_type === 'individual' ? 'Apartment, suite, unit' : 'Suite, unit, building, floor' }}">
                                    @error('address_line_2')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Location Details -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="city"
                                        class="block text-sm font-medium text-gray-700 mb-2 required">
                                        City
                                    </label>
                                    <input type="text" id="city" wire:model="city"
                                        class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 {{ $customer_type === 'individual' ? 'focus:ring-blue-500 focus:border-blue-500' : 'focus:ring-purple-500 focus:border-purple-500' }}"
                                        placeholder="City">
                                    @error('city')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="state"
                                        class="block text-sm font-medium text-gray-700 mb-2 required">
                                        Region
                                    </label>
                                    <input type="text" id="state" wire:model="state"
                                        class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 {{ $customer_type === 'individual' ? 'focus:ring-blue-500 focus:border-blue-500' : 'focus:ring-purple-500 focus:border-purple-500' }}"
                                        placeholder="State or Region">
                                    @error('state')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="country"
                                        class="block text-sm font-medium text-gray-700 mb-2 required">
                                        Country
                                    </label>
                                    <select id="country" wire:model="country"
                                        class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 {{ $customer_type === 'individual' ? 'focus:ring-blue-500 focus:border-blue-500' : 'focus:ring-purple-500 focus:border-purple-500' }}">
                                        <option value="">Select Country</option>
                                        @foreach ($countries as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @error('country')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            @if ($customer_type === 'organization')
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="website" class="block text-sm font-medium text-gray-700 mb-2">
                                            Website (Optional)
                                        </label>
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-globe text-gray-400"></i>
                                            </div>
                                            <input type="url" id="website" wire:model="website"
                                                class="block w-full pl-10 border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                                placeholder="https://example.com">
                                        </div>
                                        @error('website')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <div class="flex justify-between">
                                <button type="button" wire:click="previousStep"
                                    class="inline-flex items-center px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="fas fa-arrow-left mr-2"></i>
                                    Back
                                </button>
                                <button type="button" wire:click="nextStep"
                                    class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg text-sm font-medium text-white {{ $customer_type === 'individual' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-purple-600 hover:bg-purple-700' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Continue to Bank Information
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Step 4: Bank Information -->
                @if ($currentStep == 4 && $customer_type)
                    <div class="mb-10">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">4. Bank Information</h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    Configure bank-specific settings and relationships
                                </p>
                            </div>
                            <button type="button" wire:click="previousStep"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Back
                            </button>
                        </div>

                        <div class="space-y-6">
                            <div
                                class="bg-gradient-to-r from-indigo-50 to-blue-50 border border-indigo-200 rounded-xl p-6">
                                <div class="flex items-center mb-4">
                                    <div
                                        class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center mr-3">
                                        <i class="fas fa-university text-indigo-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">Bank Settings</h3>
                                        <span
                                            class="px-3 py-1 bg-indigo-100 text-indigo-800 text-sm font-medium rounded-full">
                                            System Configuration
                                        </span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    @if (auth()->user()->can('view all branches'))
                                        <div>
                                            <label for="branch_id"
                                                class="block text-sm font-medium text-gray-700 mb-2 required">
                                                Branch
                                            </label>
                                            <select id="branch_id" wire:model="branch_id"
                                                class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                                                <option value="">Select Branch</option>
                                                @foreach ($branches as $branch)
                                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('branch_id')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    @endif

                                    <div>
                                        <label for="relationship_manager_id"
                                            class="block text-sm font-medium text-gray-700 mb-2">
                                            Relationship Manager (Optional)
                                        </label>
                                        <select id="relationship_manager_id" wire:model="relationship_manager_id"
                                            class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                                            <option value="">Select Relationship Manager</option>
                                            @foreach ($relationshipManagers as $manager)
                                                <option value="{{ $manager->id }}">{{ $manager->full_name }}
                                                    ({{ $manager->email }})</option>
                                            @endforeach
                                        </select>
                                        @error('relationship_manager_id')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="status"
                                            class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Status
                                        </label>
                                        <select id="status" wire:model="status"
                                            class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                            <option value="suspended">Suspended</option>
                                        </select>
                                        @error('status')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="customer_tier"
                                            class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Customer Tier
                                        </label>
                                        <select id="customer_tier" wire:model="customer_tier"
                                            class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                                            <option value="basic">Basic</option>
                                            <option value="premium">Premium</option>
                                            <option value="platinum">Platinum</option>
                                        </select>
                                        @error('customer_tier')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="risk_profile"
                                            class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Risk Profile
                                        </label>
                                        <select id="risk_profile" wire:model="risk_profile"
                                            class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                                            <option value="low">Low</option>
                                            <option value="medium">Medium</option>
                                            <option value="high">High</option>
                                        </select>
                                        @error('risk_profile')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                        Internal Notes (Optional)
                                    </label>
                                    <textarea id="notes" wire:model="notes" rows="3"
                                        class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                                        placeholder="Any internal notes or comments about this customer..."></textarea>
                                    @error('notes')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <div class="flex justify-between">
                                <button type="button" wire:click="previousStep"
                                    class="inline-flex items-center px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="fas fa-arrow-left mr-2"></i>
                                    Back
                                </button>
                                <button type="button" wire:click="nextStep"
                                    class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg text-sm font-medium text-white {{ $customer_type === 'individual' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-purple-600 hover:bg-purple-700' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Continue to KYC Details
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Step 5: KYC Details -->
                @if ($currentStep == 5 && $customer_type)
                    <div class="mb-10">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">5. KYC & Identification</h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    Complete Know Your Customer (KYC) requirements and upload documents
                                </p>
                            </div>
                            <button type="button" wire:click="previousStep"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Back
                            </button>
                        </div>

                        <div class="space-y-8">
                            <!-- KYC Section -->
                            <div
                                class="bg-gradient-to-r from-orange-50 to-amber-50 border border-orange-200 rounded-xl p-6">
                                <div class="flex items-center mb-4">
                                    <div
                                        class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center mr-3">
                                        <i class="fas fa-id-card text-orange-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">Identification Details</h3>
                                        <span
                                            class="px-3 py-1 bg-orange-100 text-orange-800 text-sm font-medium rounded-full">
                                            Required for KYC Compliance
                                        </span>
                                    </div>
                                </div>

                                @if ($customer_type === 'individual')
                                    <!-- Individual KYC Validation -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                        <div>
                                            <label for="id_type"
                                                class="block text-sm font-medium text-gray-700 mb-2 required">
                                                ID Type
                                            </label>
                                            <select id="id_type" wire:model="id_type"
                                                class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                                <option value="">Select ID Type</option>
                                                @foreach ($idTypes as $key => $value)
                                                    <option value="{{ $key }}">{{ $value }}</option>
                                                @endforeach
                                            </select>
                                            @error('id_type')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="id_number"
                                                class="block text-sm font-medium text-gray-700 mb-2 required">
                                                ID Number
                                            </label>
                                            <input type="text" id="id_number" wire:model="id_number"
                                                class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                                placeholder="Enter ID number">
                                            @error('id_number')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="id_expiry_date"
                                                class="block text-sm font-medium text-gray-700 mb-2 required">
                                                ID Expiry Date
                                            </label>
                                            <input type="date" id="id_expiry_date" wire:model="id_expiry_date"
                                                class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                            @error('id_expiry_date')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="id_issuing_country"
                                                class="block text-sm font-medium text-gray-700 mb-2 required">
                                                ID Issuing Country
                                            </label>
                                            <input type="text" id="id_issuing_country"
                                                wire:model="id_issuing_country"
                                                class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                                placeholder="Country that issued the ID">
                                            @error('id_issuing_country')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                @endif

                                <!-- Document Uploads -->
                                <div class="mt-8">
                                    <h4 class="text-lg font-medium text-gray-900 mb-4">Document Upload</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        @if ($customer_type === 'individual')
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                                    ID Front Image
                                                </label>
                                                <div
                                                    class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-orange-400 transition-colors">
                                                    <div class="space-y-1 text-center">
                                                        <svg class="mx-auto h-12 w-12 text-gray-400"
                                                            stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                            <path
                                                                d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                                                stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round" />
                                                        </svg>
                                                        <div class="flex text-sm text-gray-600 justify-center">
                                                            <label
                                                                class="relative cursor-pointer bg-white rounded-md font-medium text-orange-600 hover:text-orange-500">
                                                                <span>Upload ID Front</span>
                                                                <input type="file" wire:model="id_front_image"
                                                                    class="sr-only" accept="image/*,.pdf">
                                                            </label>
                                                        </div>
                                                        <p class="text-xs text-gray-500">
                                                            PNG, JPG, PDF up to 5MB
                                                        </p>
                                                    </div>
                                                </div>
                                                @error('id_front_image')
                                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                                @if ($id_front_image)
                                                    <div class="mt-4">
                                                        <p class="text-sm text-green-600 mb-2">
                                                            <i class="fas fa-check-circle mr-1"></i>
                                                            ID front image selected
                                                        </p>
                                                        <div
                                                            class="relative w-48 h-32 bg-white rounded-lg overflow-hidden border-2 border-orange-200 p-2">
                                                            <img src="{{ $id_front_image->temporaryUrl() }}"
                                                                alt="ID front preview"
                                                                class="w-full h-full object-contain">
                                                            <div class="absolute inset-0 bg-orange-500 bg-opacity-5">
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    ID Back Image (Optional)
                                                </label>
                                                <div
                                                    class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-orange-400 transition-colors">
                                                    <div class="space-y-1 text-center">
                                                        <svg class="mx-auto h-12 w-12 text-gray-400"
                                                            stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                            <path
                                                                d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                                                stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round" />
                                                        </svg>
                                                        <div class="flex text-sm text-gray-600 justify-center">
                                                            <label
                                                                class="relative cursor-pointer bg-white rounded-md font-medium text-orange-600 hover:text-orange-500">
                                                                <span>Upload ID Back</span>
                                                                <input type="file" wire:model="id_back_image"
                                                                    class="sr-only" accept="image/*,.pdf">
                                                            </label>
                                                        </div>
                                                        <p class="text-xs text-gray-500">
                                                            PNG, JPG, PDF up to 5MB
                                                        </p>
                                                    </div>
                                                </div>
                                                @if ($id_back_image)
                                                    <div class="mt-4">
                                                        <p class="text-sm text-green-600 mb-2">
                                                            <i class="fas fa-check-circle mr-1"></i>
                                                            ID back image selected
                                                        </p>
                                                        <div
                                                            class="relative w-48 h-32 bg-white rounded-lg overflow-hidden border-2 border-orange-200 p-2">
                                                            <img src="{{ $id_back_image->temporaryUrl() }}"
                                                                alt="ID back preview"
                                                                class="w-full h-full object-contain">
                                                            <div class="absolute inset-0 bg-orange-500 bg-opacity-5">
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <!-- Organization Documents -->
                                            <div class="col-span-2">
                                                <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                                    Business Registration Document
                                                </label>
                                                <div
                                                    class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-orange-400 transition-colors">
                                                    <div class="space-y-1 text-center">
                                                        <svg class="mx-auto h-12 w-12 text-gray-400"
                                                            stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                            <path
                                                                d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                                                stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round" />
                                                        </svg>
                                                        <div class="flex text-sm text-gray-600 justify-center">
                                                            <label
                                                                class="relative cursor-pointer bg-white rounded-md font-medium text-orange-600 hover:text-orange-500">
                                                                <span>Upload Registration Document</span>
                                                                <input type="file" wire:model="id_front_image"
                                                                    class="sr-only" accept="image/*,.pdf,.doc,.docx">
                                                            </label>
                                                        </div>
                                                        <p class="text-xs text-gray-500">
                                                            PDF, DOC, JPG up to 5MB
                                                        </p>
                                                    </div>
                                                </div>
                                                @error('id_front_image')
                                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Profile Photo and Signature -->
                                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Profile Photo (Optional)
                                            </label>
                                            <div
                                                class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-orange-400 transition-colors">
                                                <div class="space-y-1 text-center">
                                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor"
                                                        fill="none" viewBox="0 0 48 48">
                                                        <path
                                                            d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                                            stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                    </svg>
                                                    <div class="flex text-sm text-gray-600 justify-center">
                                                        <label
                                                            class="relative cursor-pointer bg-white rounded-md font-medium text-orange-600 hover:text-orange-500">
                                                            <span>Upload Profile Photo</span>
                                                            <input type="file" wire:model="profile_photo"
                                                                class="sr-only" accept="image/*">
                                                        </label>
                                                    </div>
                                                    <p class="text-xs text-gray-500">
                                                        PNG, JPG up to 2MB
                                                    </p>
                                                </div>
                                            </div>
                                            @if ($profile_photo)
                                                <div class="mt-4">
                                                    <p class="text-sm text-green-600 mb-2">
                                                        <i class="fas fa-check-circle mr-1"></i>
                                                        Profile photo selected
                                                    </p>
                                                    <div
                                                        class="relative w-32 h-32 rounded-full overflow-hidden border-2 border-orange-200">
                                                        <img src="{{ $profile_photo->temporaryUrl() }}"
                                                            alt="Profile preview" class="w-full h-full object-cover">
                                                        <div class="absolute inset-0 bg-orange-500 bg-opacity-10">
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Signature Image (Optional)
                                            </label>
                                            <div
                                                class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-orange-400 transition-colors">
                                                <div class="space-y-1 text-center">
                                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor"
                                                        fill="none" viewBox="0 0 48 48">
                                                        <path
                                                            d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                                            stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                    </svg>
                                                    <div class="flex text-sm text-gray-600 justify-center">
                                                        <label
                                                            class="relative cursor-pointer bg-white rounded-md font-medium text-orange-600 hover:text-orange-500">
                                                            <span>Upload Signature</span>
                                                            <input type="file" wire:model="signature_image"
                                                                class="sr-only" accept="image/*">
                                                        </label>
                                                    </div>
                                                    <p class="text-xs text-gray-500">
                                                        PNG, JPG up to 2MB
                                                    </p>
                                                </div>
                                            </div>
                                            @if ($signature_image)
                                                <div class="mt-4">
                                                    <p class="text-sm text-green-600 mb-2">
                                                        <i class="fas fa-check-circle mr-1"></i>
                                                        Signature image selected
                                                    </p>
                                                    <div
                                                        class="relative w-48 h-24 bg-white rounded-lg overflow-hidden border-2 border-orange-200 p-2">
                                                        <img src="{{ $signature_image->temporaryUrl() }}"
                                                            alt="Signature preview"
                                                            class="w-full h-full object-contain">
                                                        <div class="absolute inset-0 bg-orange-500 bg-opacity-5"></div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Emergency Contacts (Individuals only) -->
                            @if ($customer_type === 'individual')
                                <div
                                    class="bg-gradient-to-r from-red-50 to-pink-50 border border-red-200 rounded-xl p-6">
                                    <div class="flex items-center justify-between mb-6">
                                        <div class="flex items-center">
                                            <div
                                                class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center mr-3">
                                                <i class="fas fa-first-aid text-red-600"></i>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-medium text-gray-900">Emergency Contacts</h3>
                                                <span
                                                    class="px-3 py-1 bg-red-100 text-red-800 text-sm font-medium rounded-full">
                                                    Recommended
                                                </span>
                                            </div>
                                        </div>
                                        <button type="button" wire:click="addEmergencyContact"
                                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                            <i class="fas fa-plus mr-2"></i>
                                            Add Contact
                                        </button>
                                    </div>

                                    @foreach ($emergency_contacts as $index => $contact)
                                        <div class="mb-4 p-4 border border-gray-200 rounded-lg bg-white">
                                            <div class="flex justify-between items-center mb-3">
                                                <h4 class="text-sm font-medium text-gray-900">Emergency Contact
                                                    {{ $index + 1 }}</h4>
                                                @if ($index > 0)
                                                    <button type="button"
                                                        wire:click="removeEmergencyContact({{ $index }})"
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
                                                    <input type="text"
                                                        wire:model="emergency_contacts.{{ $index }}.name"
                                                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                        placeholder="Full name">
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                                        Relationship
                                                    </label>
                                                    <input type="text"
                                                        wire:model="emergency_contacts.{{ $index }}.relationship"
                                                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                        placeholder="e.g., Spouse, Parent">
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                                        Phone Number
                                                    </label>
                                                    <input type="tel"
                                                        wire:model="emergency_contacts.{{ $index }}.phone"
                                                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                        placeholder="Phone number">
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                                        Email (Optional)
                                                    </label>
                                                    <input type="email"
                                                        wire:model="emergency_contacts.{{ $index }}.email"
                                                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                        placeholder="Email address">
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Next of Kin (Individuals only) -->
                            @if ($customer_type === 'individual')
                                <div
                                    class="bg-gradient-to-r from-teal-50 to-cyan-50 border border-teal-200 rounded-xl p-6">
                                    <div class="flex items-center justify-between mb-6">
                                        <div class="flex items-center">
                                            <div
                                                class="w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center mr-3">
                                                <i class="fas fa-users text-teal-600"></i>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-medium text-gray-900">Next of Kin Details</h3>
                                                <span
                                                    class="px-3 py-1 bg-teal-100 text-teal-800 text-sm font-medium rounded-full">
                                                    For Inheritance Purposes
                                                </span>
                                            </div>
                                        </div>
                                        <button type="button" wire:click="addNextOfKin"
                                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                            <i class="fas fa-plus mr-2"></i>
                                            Add Next of Kin
                                        </button>
                                    </div>

                                    @foreach ($next_of_kin as $index => $nextOfKin)
                                        <div class="mb-4 p-4 border border-gray-200 rounded-lg bg-white">
                                            <div class="flex justify-between items-center mb-3">
                                                <h4 class="text-sm font-medium text-gray-900">Next of Kin
                                                    {{ $index + 1 }}</h4>
                                                @if ($index > 0)
                                                    <button type="button"
                                                        wire:click="removeNextOfKin({{ $index }})"
                                                        class="text-red-600 hover:text-red-800">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                                        Full Name
                                                    </label>
                                                    <input type="text"
                                                        wire:model="next_of_kin.{{ $index }}.name"
                                                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                        placeholder="Full name">
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                                        Relationship
                                                    </label>
                                                    <input type="text"
                                                        wire:model="next_of_kin.{{ $index }}.relationship"
                                                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                        placeholder="e.g., Spouse, Child">
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                                        Phone Number
                                                    </label>
                                                    <input type="tel"
                                                        wire:model="next_of_kin.{{ $index }}.phone"
                                                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                        placeholder="Phone number">
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                                        Percentage
                                                    </label>
                                                    <div class="relative">
                                                        <input type="number"
                                                            wire:model="next_of_kin.{{ $index }}.percentage"
                                                            class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 pr-8 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                            placeholder="0" min="0" max="100"
                                                            step="1">
                                                        <div
                                                            class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                            <span class="text-gray-500">%</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Financial Information -->
                            <div
                                class="bg-gradient-to-r from-amber-50 to-yellow-50 border border-amber-200 rounded-xl p-6">
                                <div class="flex items-center mb-4">
                                    <div
                                        class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center mr-3">
                                        <i class="fas fa-chart-line text-amber-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">
                                            @if ($customer_type === 'individual')
                                                Employment & Income Details
                                            @else
                                                Financial Information
                                            @endif
                                        </h3>
                                        <span
                                            class="px-3 py-1 bg-amber-100 text-amber-800 text-sm font-medium rounded-full">
                                            Required for Risk Assessment
                                        </span>
                                    </div>
                                </div>

                                @if ($customer_type === 'individual')
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                        <div>
                                            <label for="occupation"
                                                class="block text-sm font-medium text-gray-700 mb-2 required">
                                                Occupation
                                            </label>
                                            <input type="text" id="occupation" wire:model="occupation"
                                                class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                                placeholder="e.g., Software Engineer">
                                            @error('occupation')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="monthly_income"
                                                class="block text-sm font-medium text-gray-700 mb-2 required">
                                                Monthly Income (₵)
                                            </label>
                                            <div class="relative rounded-lg shadow-sm">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500">₵</span>
                                                </div>
                                                <input type="number" id="monthly_income" wire:model="monthly_income"
                                                    step="0.01" min="0"
                                                    class="block w-full pl-10 border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                                    placeholder="0.00">
                                            </div>
                                            @error('monthly_income')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="source_of_income"
                                                class="block text-sm font-medium text-gray-700 mb-2 required">
                                                Source of Income
                                            </label>
                                            <input type="text" id="source_of_income" wire:model="source_of_income"
                                                class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                                placeholder="e.g., Employment, Business">
                                            @error('source_of_income')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="employer_name"
                                                class="block text-sm font-medium text-gray-700 mb-2">
                                                Employer Name (Optional)
                                            </label>
                                            <input type="text" id="employer_name" wire:model="employer_name"
                                                class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                                placeholder="Company name">
                                            @error('employer_name')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="net_worth"
                                                class="block text-sm font-medium text-gray-700 mb-2">
                                                Net Worth (₵) - Optional
                                            </label>
                                            <div class="relative rounded-lg shadow-sm">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500">₵</span>
                                                </div>
                                                <input type="number" id="net_worth" wire:model="net_worth"
                                                    step="0.01" min="0"
                                                    class="block w-full pl-10 border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                                    placeholder="0.00">
                                            </div>
                                            @error('net_worth')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                @else
                                    <!-- Organization Financial Information -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="annual_revenue"
                                                class="block text-sm font-medium text-gray-700 mb-2 required">
                                                Annual Revenue (₵)
                                            </label>
                                            <div class="relative rounded-lg shadow-sm">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500">₵</span>
                                                </div>
                                                <input type="number" id="annual_revenue" wire:model="annual_revenue"
                                                    step="0.01" min="0"
                                                    class="block w-full pl-10 border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                                    placeholder="0.00">
                                            </div>
                                            @error('annual_revenue')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="number_of_employees"
                                                class="block text-sm font-medium text-gray-700 mb-2 required">
                                                Number of Employees
                                            </label>
                                            <input type="number" id="number_of_employees"
                                                wire:model="number_of_employees" min="1"
                                                class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                                placeholder="e.g., 50">
                                            @error('number_of_employees')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="mt-6">
                                        <label for="employer_address"
                                            class="block text-sm font-medium text-gray-700 mb-2">
                                            Business Description
                                        </label>
                                        <textarea id="employer_address" wire:model="employer_address" rows="3"
                                            class="block w-full border border-gray-300 rounded-lg shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                            placeholder="Describe the business operations and activities"></textarea>
                                        @error('employer_address')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endif
                            </div>

                            <!-- Authorized Signatories (Organizations only) -->
                            @if ($customer_type === 'organization')
                                <div
                                    class="bg-gradient-to-r from-violet-50 to-purple-50 border border-violet-200 rounded-xl p-6">
                                    <div class="flex items-center justify-between mb-6">
                                        <div class="flex items-center">
                                            <div
                                                class="w-10 h-10 rounded-full bg-violet-100 flex items-center justify-center mr-3">
                                                <i class="fas fa-signature text-violet-600"></i>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-medium text-gray-900">Authorized Signatories
                                                </h3>
                                                <span
                                                    class="px-3 py-1 bg-violet-100 text-violet-800 text-sm font-medium rounded-full">
                                                    Required for Organizational Accounts
                                                </span>
                                            </div>
                                        </div>
                                        <button type="button" wire:click="addSignatory"
                                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                            <i class="fas fa-plus mr-2"></i>
                                            Add Signatory
                                        </button>
                                    </div>

                                    @foreach ($authorized_signatories as $index => $signatory)
                                        <div class="mb-4 p-4 border border-gray-200 rounded-lg bg-white">
                                            <div class="flex justify-between items-center mb-3">
                                                <h4 class="text-sm font-medium text-gray-900">Authorized Signatory
                                                    {{ $index + 1 }}</h4>
                                                @if ($index > 0)
                                                    <button type="button"
                                                        wire:click="removeSignatory({{ $index }})"
                                                        class="text-red-600 hover:text-red-800">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                                        Full Name
                                                    </label>
                                                    <input type="text"
                                                        wire:model="authorized_signatories.{{ $index }}.name"
                                                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-violet-500 focus:border-violet-500 sm:text-sm"
                                                        placeholder="Full name">
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                                        Position
                                                    </label>
                                                    <input type="text"
                                                        wire:model="authorized_signatories.{{ $index }}.position"
                                                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-violet-500 focus:border-violet-500 sm:text-sm"
                                                        placeholder="e.g., Director, CEO">
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                                        Phone Number
                                                    </label>
                                                    <input type="tel"
                                                        wire:model="authorized_signatories.{{ $index }}.phone"
                                                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-violet-500 focus:border-violet-500 sm:text-sm"
                                                        placeholder="Phone number">
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                                        Email
                                                    </label>
                                                    <input type="email"
                                                        wire:model="authorized_signatories.{{ $index }}.email"
                                                        class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-violet-500 focus:border-violet-500 sm:text-sm"
                                                        placeholder="Email address">
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <div class="flex justify-between">
                                <button type="button" wire:click="previousStep"
                                    class="inline-flex items-center px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="fas fa-arrow-left mr-2"></i>
                                    Back
                                </button>
                                <button type="button" wire:click="nextStep"
                                    class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg text-sm font-medium text-white {{ $customer_type === 'individual' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-purple-600 hover:bg-purple-700' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Continue to Review
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Step 6: Review & Submit -->
                @if ($currentStep == 6 && $customer_type)
                    <div class="mb-10">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">6. Review & Create Customer</h3>
                                <p class="text-sm text-gray-600 mt-1">Review all information before creating the
                                    customer account</p>
                            </div>
                            <button type="button" wire:click="previousStep"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Back
                            </button>
                        </div>

                        <div class="space-y-8">
                            <!-- Customer Preview -->
                            <div
                                class="bg-gradient-to-r from-gray-50 to-blue-50 border border-gray-200 rounded-xl p-8">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="text-xl font-bold text-gray-900">
                                        <i class="fas fa-user-check mr-2 text-blue-600"></i>
                                        Customer Preview
                                    </h3>
                                    <span class="px-4 py-2 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                                        Ready for Creation
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                    <!-- Customer Summary -->
                                    <div class="space-y-6">
                                        <div class="flex items-start space-x-4">
                                            <div class="flex-shrink-0">
                                                @if ($customer_type === 'individual')
                                                    <div
                                                        class="h-24 w-24 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center text-white text-3xl font-bold shadow-lg">
                                                        {{ strtoupper(substr($first_name, 0, 1)) }}{{ strtoupper(substr($last_name, 0, 1)) }}
                                                    </div>
                                                @else
                                                    <div
                                                        class="h-24 w-24 rounded-full bg-gradient-to-r from-purple-500 to-purple-600 flex items-center justify-center text-white text-3xl font-bold shadow-lg">
                                                        <i class="fas fa-building"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="text-2xl font-bold text-gray-900">
                                                    @if ($customer_type === 'individual')
                                                        {{ $first_name }} {{ $last_name }}
                                                    @else
                                                        {{ $company_name }}
                                                    @endif
                                                </h4>
                                                <p class="text-sm text-gray-500 mt-1">
                                                    {{ $customer_type === 'individual' ? 'Individual Customer' : 'Organization' }}
                                                </p>
                                                <div class="flex flex-wrap gap-2 mt-3">
                                                    <span
                                                        class="px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                                        {{ ucfirst($customer_type) }}
                                                    </span>
                                                    <span
                                                        class="px-3 py-1 text-xs font-medium rounded-full {{ $status === 'active' ? 'bg-green-100 text-green-800' : ($status === 'suspended' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                        {{ ucfirst($status) }}
                                                    </span>
                                                    <span
                                                        class="px-3 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-800">
                                                        {{ ucfirst($customer_tier) }} Tier
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Contact Information -->
                                        <div class="bg-white rounded-lg p-4 border border-gray-200">
                                            <h5 class="text-sm font-semibold text-gray-900 mb-3">
                                                <i class="fas fa-address-card mr-2 text-gray-400"></i>
                                                Contact Information
                                            </h5>
                                            <div class="space-y-2">
                                                <div class="flex items-center">
                                                    <i class="fas fa-envelope text-gray-400 w-5 mr-3"></i>
                                                    <span class="text-sm text-gray-900">{{ $email }}</span>
                                                </div>
                                                <div class="flex items-center">
                                                    <i class="fas fa-phone text-gray-400 w-5 mr-3"></i>
                                                    <span class="text-sm text-gray-900">{{ $phone }}</span>
                                                </div>
                                                @if ($phone_alt)
                                                    <div class="flex items-center">
                                                        <i class="fas fa-phone-alt text-gray-400 w-5 mr-3"></i>
                                                        <span
                                                            class="text-sm text-gray-900">{{ $phone_alt }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Address Information -->
                                        <div class="bg-white rounded-lg p-4 border border-gray-200">
                                            <h5 class="text-sm font-semibold text-gray-900 mb-3">
                                                <i class="fas fa-map-marker-alt mr-2 text-gray-400"></i>
                                                Address
                                            </h5>
                                            <div class="space-y-1">
                                                <p class="text-sm text-gray-900">{{ $address_line_1 }}</p>
                                                @if ($address_line_2)
                                                    <p class="text-sm text-gray-900">{{ $address_line_2 }}</p>
                                                @endif
                                                <p class="text-sm text-gray-900">{{ $city }},
                                                    {{ $state }}</p>
                                                <p class="text-sm text-gray-900">{{ $country }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Additional Details -->
                                    <div class="space-y-6">
                                        @if ($customer_type === 'individual')
                                            <!-- Individual Details -->
                                            <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                <h5 class="text-sm font-semibold text-gray-900 mb-3">
                                                    <i class="fas fa-user-circle mr-2 text-gray-400"></i>
                                                    Personal Details
                                                </h5>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <p class="text-xs text-gray-500">Date of Birth</p>
                                                        <p class="text-sm text-gray-900">
                                                            {{ \Carbon\Carbon::parse($date_of_birth)->format('M d, Y') }}
                                                        </p>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs text-gray-500">Age</p>
                                                        <p class="text-sm text-gray-900">{{ $this->calculateAge() }}
                                                            years</p>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs text-gray-500">Gender</p>
                                                        <p class="text-sm text-gray-900">{{ ucfirst($gender) }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs text-gray-500">Nationality</p>
                                                        <p class="text-sm text-gray-900">{{ $nationality }}</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Employment Details -->
                                            <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                <h5 class="text-sm font-semibold text-gray-900 mb-3">
                                                    <i class="fas fa-briefcase mr-2 text-gray-400"></i>
                                                    Employment Details
                                                </h5>
                                                <div class="space-y-2">
                                                    <div>
                                                        <p class="text-xs text-gray-500">Occupation</p>
                                                        <p class="text-sm text-gray-900">{{ $occupation }}</p>
                                                    </div>
                                                    <div class="grid grid-cols-2 gap-4">
                                                        <div>
                                                            <p class="text-xs text-gray-500">Monthly Income</p>
                                                            <p class="text-sm text-gray-900">
                                                                ₵{{ number_format($monthly_income, 2) }}</p>
                                                        </div>
                                                        <div>
                                                            <p class="text-xs text-gray-500">Source of Income</p>
                                                            <p class="text-sm text-gray-900">{{ $source_of_income }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <!-- Organization Details -->
                                            <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                <h5 class="text-sm font-semibold text-gray-900 mb-3">
                                                    <i class="fas fa-building mr-2 text-gray-400"></i>
                                                    Organization Details
                                                </h5>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <p class="text-xs text-gray-500">Type</p>
                                                        <p class="text-sm text-gray-900">
                                                            {{ ucfirst(str_replace('_', ' ', $organization_type)) }}
                                                        </p>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs text-gray-500">Industry</p>
                                                        <p class="text-sm text-gray-900">
                                                            {{ ucfirst(str_replace('_', ' ', $industry)) }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs text-gray-500">Registration #</p>
                                                        <p class="text-sm text-gray-900">{{ $registration_number }}
                                                        </p>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs text-gray-500">TIN</p>
                                                        <p class="text-sm text-gray-900">
                                                            {{ $tax_identification_number }}</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Contact Person -->
                                            <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                <h5 class="text-sm font-semibold text-gray-900 mb-3">
                                                    <i class="fas fa-user-tie mr-2 text-gray-400"></i>
                                                    Contact Person
                                                </h5>
                                                <div class="space-y-2">
                                                    <p class="text-sm text-gray-900">{{ $contact_person }}</p>
                                                    <p class="text-sm text-gray-600">{{ $contact_person_position }}
                                                    </p>
                                                    <p class="text-sm text-gray-600">{{ $contact_person_phone }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Bank Information -->
                                        <div class="bg-white rounded-lg p-4 border border-gray-200">
                                            <h5 class="text-sm font-semibold text-gray-900 mb-3">
                                                <i class="fas fa-university mr-2 text-gray-400"></i>
                                                Bank Information
                                            </h5>
                                            <div class="space-y-2">
                                                @if ($branch_id && isset($branches))
                                                    <div class="flex items-center">
                                                        <i class="fas fa-code-branch text-gray-400 mr-2 text-sm"></i>
                                                        <span class="text-sm text-gray-900">
                                                            {{ $branches->firstWhere('id', $branch_id)?->name ?? 'Not assigned' }}
                                                        </span>
                                                    </div>
                                                @endif
                                                @if ($relationship_manager_id)
                                                    <div class="flex items-center">
                                                        <i class="fas fa-user-tie text-gray-400 mr-2 text-sm"></i>
                                                        <span class="text-sm text-gray-900">
                                                            {{ $relationshipManagers->firstWhere('id', $relationship_manager_id)?->full_name ?? 'Not assigned' }}
                                                        </span>
                                                    </div>
                                                @endif
                                                <div class="flex items-center">
                                                    <i class="fas fa-shield-alt text-gray-400 mr-2 text-sm"></i>
                                                    <span class="text-sm text-gray-900">
                                                        Risk Profile: {{ ucfirst($risk_profile) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Document Summary -->
                                <div class="mt-8 pt-6 border-t border-gray-200">
                                    <h5 class="text-sm font-semibold text-gray-900 mb-3">
                                        <i class="fas fa-file-alt mr-2 text-gray-400"></i>
                                        Document Summary
                                    </h5>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                            <i
                                                class="fas {{ $id_front_image ? 'fa-check-circle text-green-500' : 'fa-times-circle text-red-400' }} mr-3"></i>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">
                                                    @if ($customer_type === 'individual')
                                                        ID Front
                                                    @else
                                                        Registration Doc
                                                    @endif
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    {{ $id_front_image ? 'Uploaded' : 'Required' }}
                                                </p>
                                            </div>
                                        </div>
                                        @if ($customer_type === 'individual')
                                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                                <i
                                                    class="fas {{ $id_back_image ? 'fa-check-circle text-green-500' : 'fa-file text-gray-400' }} mr-3"></i>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">ID Back</p>
                                                    <p class="text-xs text-gray-500">
                                                        {{ $id_back_image ? 'Uploaded' : 'Optional' }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                            <i
                                                class="fas {{ $profile_photo ? 'fa-check-circle text-green-500' : 'fa-file text-gray-400' }} mr-3"></i>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Profile Photo</p>
                                                <p class="text-xs text-gray-500">
                                                    {{ $profile_photo ? 'Uploaded' : 'Optional' }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                            <i
                                                class="fas {{ $signature_image ? 'fa-check-circle text-green-500' : 'fa-file text-gray-400' }} mr-3"></i>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Signature</p>
                                                <p class="text-xs text-gray-500">
                                                    {{ $signature_image ? 'Uploaded' : 'Optional' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Terms & Conditions -->
                            <div class="bg-gradient-to-r from-red-50 to-pink-50 border border-red-200 rounded-xl p-6">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-file-contract text-red-500 text-xl mt-1"></i>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <h4 class="text-sm font-semibold text-gray-900">Terms & Conditions</h4>
                                        <div class="mt-4 space-y-3">
                                            <div class="flex items-start">
                                                <input type="checkbox" id="terms_accepted"
                                                    wire:model="terms_accepted"
                                                    class="h-4 w-4 text-red-600 border-gray-300 rounded focus:ring-red-500 mt-1">
                                                <label for="terms_accepted"
                                                    class="ml-2 block text-sm text-gray-900">
                                                    I confirm that all information provided is accurate and complete.
                                                    @if ($customer_type === 'individual')
                                                        The customer has been properly identified and all personal
                                                        details are correct.
                                                    @else
                                                        The organization has provided valid registration documents and
                                                        all business details are accurate.
                                                    @endif
                                                </label>
                                            </div>
                                            @error('terms_accepted')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror

                                            @if ($customer_type === 'organization')
                                                <div class="flex items-start">
                                                    <input type="checkbox" id="signatories_verified"
                                                        wire:model="signatories_verified"
                                                        class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500 mt-1">
                                                    <label for="signatories_verified"
                                                        class="ml-2 block text-sm text-gray-900">
                                                        I verify that all authorized signatories have been properly
                                                        identified and documented.
                                                    </label>
                                                </div>
                                                @error('signatories_verified')
                                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Final Action Buttons -->
                        <div class="mt-10 pt-8 border-t border-gray-200">
                            <div class="flex justify-between">
                                <button type="button" wire:click="previousStep"
                                    class="inline-flex items-center px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="fas fa-arrow-left mr-2"></i>
                                    Back
                                </button>
                                <div class="flex space-x-4">
                                    <a href="{{ route('customers.index') }}"
                                        class="inline-flex items-center px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Cancel
                                    </a>
                                    <button type="submit"
                                        class="inline-flex items-center px-8 py-3 border border-transparent rounded-lg text-sm font-medium text-white {{ $customer_type === 'individual' ? 'bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800' : 'bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg hover:shadow-xl transition-all duration-200">
                                        <i class="fas fa-user-plus mr-2"></i>
                                        Create {{ $customer_type === 'individual' ? 'Individual' : 'Organizational' }}
                                        Customer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-format phone numbers
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

            // Apply phone formatting
            const phoneInputs = document.querySelectorAll(
                'input[type="tel"], input[placeholder*="Phone"], input[id*="phone"]');
            phoneInputs.forEach(formatPhoneNumber);

            // Set minimum date for ID expiry to today
            const idExpiryInput = document.getElementById('id_expiry_date');
            if (idExpiryInput) {
                const today = new Date().toISOString().split('T')[0];
                idExpiryInput.min = today;
            }

            // Livewire event listeners
            document.addEventListener('livewire:initialized', () => {
                // Handle customer creation success
                Livewire.on('customer-created', (data) => {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                });

                // Handle validation errors
                Livewire.on('validation-error', () => {
                    // Scroll to first error
                    setTimeout(() => {
                        const firstError = document.querySelector('.text-red-600');
                        if (firstError) {
                            firstError.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                        }
                    }, 100);
                });
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        .required::after {
            content: " *";
            color: #ef4444;
        }

        /* Smooth transitions */
        .transition-all {
            transition-property: all;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 200ms;
        }

        /* File upload styling */
        .border-dashed:hover {
            border-color: #3b82f6;
            background-color: #f0f9ff;
        }

        /* Checkbox styling */
        input[type="checkbox"]:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Gradient backgrounds */
        .bg-gradient-to-r {
            background-size: 200% 200%;
        }

        /* Animation for progress bar */
        @keyframes progress {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }
    </style>
@endpush
