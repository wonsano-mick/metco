<div class="max-w-4xl mx-auto py-8">
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100">
            <h2 class="text-xl font-semibold text-gray-900">
                {{ $mode === 'create' ? 'Create New User' : 'Edit User' }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">
                {{ $mode === 'create' ? 'Add a new user to the system' : 'Update user information' }}
            </p>
        </div>

        <!-- Form -->
        <form wire:submit.prevent="save" class="space-y-6 p-6">
            <!-- Session Success Message (fallback) -->
            @if (session()->has('success'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4" x-data="{ show: true }" x-show="show"
                    x-init="setTimeout(() => show = false, 5000)">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Personal Information -->
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900">Personal Information</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- First Name -->
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700">
                            First Name *
                        </label>
                        <input type="text" id="first_name" wire:model="first_name"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        @error('first_name')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Last Name -->
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700">
                            Last Name *
                        </label>
                        <input type="text" id="last_name" wire:model="last_name"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        @error('last_name')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">
                            Username *
                        </label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <span
                                class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                @
                            </span>
                            <input type="text" id="username" wire:model="username"
                                class="block w-full rounded-none rounded-r-md px-3 py-2 border border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        @error('username')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                        @if ($mode === 'create' && empty($username))
                            <p class="mt-1 text-xs text-gray-500">
                                Will be auto-generated if left empty
                            </p>
                        @endif
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Email Address *
                        </label>
                        <input type="email" id="email" wire:model="email"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        @error('email')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Role -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700">
                            Role *
                        </label>
                        <select id="role" wire:model="role"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Select Role</option>
                            @foreach ($availableRoles as $roleValue)
                                <option value="{{ $roleValue }}">
                                    {{ \App\Enums\Role::tryFrom($roleValue)?->label() ?? ucfirst($roleValue) }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Password (Create mode only) -->
            @if ($mode === 'create')
                <div class="space-y-6">
                    <h3 class="text-lg font-medium text-gray-900">Account Security</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">
                                Password *
                            </label>
                            <div class="relative">
                                <input type="password" id="password" wire:model="password"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                {{-- <button type="button" 
                                        onclick="togglePassword('password')"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-eye"></i>
                                </button> --}}
                            </div>
                            @error('password')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">
                                Must be at least 8 characters
                            </p>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                                Confirm Password *
                            </label>
                            <div class="relative">
                                <input type="password" id="password_confirmation" wire:model="password_confirmation"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                {{-- <button type="button" 
                                        onclick="togglePassword('password_confirmation')"
                                        class="absolute inset-y inf-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-eye"></i>
                                </button> --}}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-100">
                <a href="{{ route('users.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </a>

                <button type="submit" wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                    <i class="fas fa-save mr-2"></i>
                    <span wire:loading.remove>{{ $mode === 'create' ? 'Create User' : 'Update User' }}</span>
                    <span wire:loading>
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Saving...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    <script>
        // Password toggle function
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
            field.setAttribute('type', type);

            // Toggle eye icon
            const icon = field.parentNode.querySelector('i');
            if (type === 'text') {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Handle redirect after toast
        document.addEventListener('livewire:initialized', () => {
            // Listen for redirect event
            Livewire.on('redirect-after-toast', ({
                url
            }) => {
                // Wait 1.5 seconds to show toast, then redirect
                setTimeout(() => {
                    window.location.href = url;
                }, 1500);
            });
        });
    </script>
@endpush
