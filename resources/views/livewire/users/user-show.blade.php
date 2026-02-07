<div class="max-w-4xl mx-auto py-8">
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 bg-gray-50 shadow-md border-b border-gray-100">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">User Details</h2>
                    <p class="text-sm text-gray-600 mt-1">View user information and activity</p>
                </div>
                <div class="flex space-x-2">
                    <!-- Safely generate the edit URL -->
                    @php
                        $editUrl = null;
                        if (isset($user) && $user->id) {
                            try {
                                $editUrl = route('users.edit', $user->id);
                            } catch (\Exception $e) {
                                \Log::error('Failed to generate edit URL', [
                                    'user_id' => $user->id,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }
                    @endphp
                    
                    @if($editUrl)
                        <a href="{{ $editUrl }}" 
                           class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <i class="fas fa-edit mr-2"></i> Edit
                        </a>
                    @else
                        <button disabled 
                           class="inline-flex items-center px-4 py-2 bg-gray-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest cursor-not-allowed">
                            <i class="fas fa-edit mr-2"></i> Edit
                        </button>
                    @endif
                    
                    <a href="{{ route('users.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- Check if user exists -->
        @if(!isset($user) || !$user->id)
            <div class="p-6 text-center">
                <div class="text-red-600">
                    <i class="fas fa-exclamation-triangle text-3xl mb-4"></i>
                    <p class="text-lg font-medium">User not found or invalid</p>
                    <p class="text-sm mt-2">Please check the user ID and try again.</p>
                </div>
            </div>
        @else
            <!-- User Profile -->
            <div class="p-6">
                <div class="flex flex-col md:flex-row gap-6">
                    <!-- Profile Image/Initials -->
                    <div class="flex-shrink-0">
                        <div class="h-32 w-32 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center text-white text-4xl font-bold">
                            {{ $user->initials }}
                        </div>
                    </div>

                    <!-- User Information -->
                    <div class="flex-1 space-y-6">
                        <!-- Basic Info -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Full Name</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $user->full_name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Email</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $user->email }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Phone</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $user->phone ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Employee ID</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $user->employee_id ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Employment Info -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Employment Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Role</p>
                                    <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        @php
                                            $roleEnum = \App\Enums\Role::tryFrom($user->role);
                                            $roleClass = 'bg-gray-100 text-gray-800';
                                            
                                            if ($roleEnum) {
                                                $roleClass = match($roleEnum) {
                                                    \App\Enums\Role::SUPER_ADMIN => 'bg-red-100 text-red-800',
                                                    \App\Enums\Role::ADMIN => 'bg-red-100 text-red-800',
                                                    \App\Enums\Role::MANAGER => 'bg-purple-100 text-purple-800',
                                                    \App\Enums\Role::TELLER => 'bg-blue-100 text-blue-800',
                                                    \App\Enums\Role::ACCOUNTANT => 'bg-green-100 text-green-800',
                                                    \App\Enums\Role::AUDITOR => 'bg-yellow-100 text-yellow-800',
                                                    \App\Enums\Role::CUSTOMER => 'bg-gray-100 text-gray-800',
                                                    \App\Enums\Role::SUPERVISOR => 'bg-gray-100 text-gray-800',
                                                };
                                            }
                                        @endphp
                                        {{ $roleClass }}">
                                        @if($roleEnum)
                                            {{ $roleEnum->label() }}
                                        @else
                                            {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                        @endif
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Branch</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $user->branch->name ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Department</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $user->department ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Position</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $user->position ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Status & Dates -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Account Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Status</p>
                                    <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $user->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Last Login</p>
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Account Created</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Last Updated</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $user->updated_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>