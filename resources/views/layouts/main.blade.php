<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>METCO | Banking App</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('images/metco_logo.png') }}" />

    <!-- Add Alpine.js CDN -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    {{-- <script defer src="{{ asset('js/alpine3.15.5.min.js') }}"></script> --}}

    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- <script src="{{ asset('js/tailwindcss.js') }}"></script> --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    {{-- <link rel="stylesheet" src="{{ asset('css/all.min.css') }}"> --}}

    <!-- Meta tags for SEO -->
    <meta name="description" content="METCO - Your trusted digital banking partner">
    <meta name="keywords" content="banking, finance, digital bank, secure, transactions">

    <style>
        /* Professional input styling */
        .professional-input {
            @apply transition-all duration-200 ease-in-out;
        }

        .professional-input:focus {
            @apply ring-2 ring-blue-500 ring-opacity-50 border-blue-500 shadow-sm;
        }

        .professional-input:disabled {
            @apply bg-gray-50 cursor-not-allowed opacity-70;
        }

        /* Modern select styling */
        .modern-select {
            @apply transition-all duration-200 ease-in-out appearance-none bg-white;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }

        .modern-select:focus {
            @apply ring-2 ring-blue-500 ring-opacity-50 border-blue-500 shadow-sm;
        }

        /* Button styling */
        .modern-button {
            @apply transition-all duration-200 ease-in-out transform hover:scale-105 active:scale-95;
        }

        .modern-button:focus {
            @apply ring-2 ring-offset-2 ring-blue-500 outline-none;
        }

        /* Card styling */
        .modern-card {
            @apply transition-all duration-300 ease-in-out hover:shadow-xl;
        }

        /* Search results dropdown */
        .search-results-container {
            @apply absolute z-50 w-full bg-white shadow-2xl rounded-lg border border-gray-200 mt-1 max-h-96 overflow-y-auto;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        /* Animation for selected items */
        .selected-item {
            @apply transform transition-all duration-300 ease-in-out;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.5);
            }

            50% {
                box-shadow: 0 0 0 10px rgba(59, 130, 246, 0);
            }
        }
    </style>

</head>

<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        <div class="text-xl font-bold text-blue-600">
                            <i class="fas fa-university mr-2"></i>METCO
                        </div>
                    </a>

                    <!-- Navigation Links -->
                    <div class="ml-10 hidden md:flex space-x-4">
                        <a href="{{ route('dashboard') }}"
                            class="{{ request()->routeIs('dashboard') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-700 hover:text-blue-600' }} px-3 py-2 text-sm font-medium">
                            Dashboard
                        </a>
                        <a href="{{ route('customers.index') }}"
                            class="{{ request()->routeIs('customers.*') ? 'text-green-600 border-b-2 border-green-600' : 'text-gray-700 hover:text-blue-600' }} px-3 py-2 text-sm font-medium">
                            Customers
                        </a>
                        <a href="{{ route('accounts.index') }}"
                            class="{{ request()->routeIs('accounts.*') ? 'text-blue-800 border-b-2 border-blue-800' : 'text-gray-700 hover:text-blue-600' }} px-3 py-2 text-sm font-medium">
                            Accounts
                        </a>
                        <a href="{{ route('transactions.index') }}"
                            class="{{ request()->routeIs('transactions.*') ? 'text-yellow-600 border-b-2 border-yellow-600' : 'text-gray-700 hover:text-blue-600' }} px-3 py-2 text-sm font-medium">
                            Transactions
                        </a>
                        <a href="{{ route('loans.index') }}"
                            class="{{ request()->routeIs('loans.*') ? 'text-red-800 border-b-2 border-red-800' : 'text-gray-700 hover:text-red-800' }} px-3 py-2 text-sm font-medium">
                            Loans
                        </a>
                        <a href="{{ route('reports.index') }}"
                            class="{{ request()->routeIs('reports.*') ? 'text-pink-600 border-b-2 border-pink-800' : 'text-gray-700 hover:text-blue-600' }} px-3 py-2 text-sm font-medium">
                            Reports
                        </a>
                        <a href="{{ route('users.index') }}"
                            class="{{ request()->routeIs('users.*') ? 'text-red-600 border-b-2 border-red-400' : 'text-gray-700 hover:text-blue-600' }} px-3 py-2 text-sm font-medium">
                            User Management
                        </a>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="flex items-center space-x-4">
                    <!-- Notifications -->
                    <div class="relative">
                        <button type="button" class="p-2 text-gray-500 hover:text-blue-600 transition-colors relative"
                            onclick="toggleNotifications()">
                            <i class="fas fa-bell text-lg"></i>
                            <span
                                class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 rounded-full text-xs text-white flex items-center justify-center">3</span>
                        </button>

                        <!-- Notifications Dropdown -->
                        <div id="notifications-dropdown"
                            class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <h3 class="font-bold text-gray-800">Notifications</h3>
                            </div>
                            <div class="max-h-64 overflow-y-auto">
                                <!-- Notification items -->
                                <a href="#" class="block px-4 py-3 hover:bg-blue-50 border-b border-gray-100">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 mt-1">
                                            <div
                                                class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                                                <i class="fas fa-check text-green-600 text-sm"></i>
                                            </div>
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <p class="text-sm font-medium text-gray-800">Transfer Completed</p>
                                            <p class="text-xs text-gray-500 mt-1">Your transfer of $500 has been
                                                processed</p>
                                            <p class="text-xs text-gray-400 mt-1">2 hours ago</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="px-4 py-3 border-t border-gray-100">
                                <a href="#" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View All
                                    Notifications</a>
                            </div>
                        </div>
                    </div>

                    <!-- User Dropdown -->
                    <div class="relative">
                        <button type="button"
                            class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-50 transition-colors"
                            onclick="toggleUserMenu()">
                            <div class="text-right hidden md:block">
                                <p class="text-sm font-medium text-gray-700">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                            </div>
                            <div
                                class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                        </button>

                        <!-- User Dropdown Menu -->
                        <div id="user-dropdown"
                            class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50">
                            <a href="{{ route('profile.show') }}"
                                class="block px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                                <i class="fas fa-user-circle mr-3 text-gray-400"></i>
                                My Profile
                            </a>
                            <a href="{{-- route('security') --}}"
                                class="block px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                                <i class="fas fa-shield-alt mr-3 text-gray-400"></i>
                                Security
                            </a>
                            <a href="{{-- route('settings') --}}"
                                class="block px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                                <i class="fas fa-cog mr-3 text-gray-400"></i>
                                Settings
                            </a>
                            <div class="border-t border-gray-100 my-2"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="block w-full text-left px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-700 transition-colors">
                                    <i class="fas fa-sign-out-alt mr-3 text-gray-400"></i>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile menu button -->
        <div class="md:hidden px-4 pb-3">
            <div class="flex space-x-4">
                <a href="{{ route('dashboard') }}"
                    class="{{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-gray-700' }} px-3 py-2 text-sm font-medium">
                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                </a>
                <a href="{{-- route('accounts.index') --}}"
                    class="{{ request()->routeIs('accounts.*') ? 'text-blue-600' : 'text-gray-700' }} px-3 py-2 text-sm font-medium">
                    <i class="fas fa-wallet mr-2"></i>Accounts
                </a>
                <a href="{{-- route('transactions.index') --}}"
                    class="{{ request()->routeIs('transactions.*') ? 'text-blue-600' : 'text-gray-700' }} px-3 py-2 text-sm font-medium">
                    <i class="fas fa-exchange-alt mr-2"></i>Transactions
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        {{-- <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-gray-600 mt-2">Welcome to your METCU dashboard</p>
        </div> --}}

        <!-- Livewire Dashboard Component -->
        <x-feedback /> 
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="mt-12 border-t border-gray-200 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-lg bg-blue-500 flex items-center justify-center text-white mr-3">
                            <i class="fas fa-university"></i>
                        </div>
                        <span class="text-xl font-bold text-gray-900">METCO</span>
                    </div>
                    <p class="text-gray-500 text-sm mt-2">Digital Banking Solutions</p>
                </div>
                <div class="flex space-x-6">
                    <a href="#" class="text-gray-500 hover:text-blue-600 transition-colors">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="text-gray-500 hover:text-blue-600 transition-colors">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="text-gray-500 hover:text-blue-600 transition-colors">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="#" class="text-gray-500 hover:text-blue-600 transition-colors">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-gray-200 text-center text-gray-500 text-sm">
                <p>© {{ date('Y') }} METCO. All rights reserved. Banking services provided by METCU PLC.</p>
                <p class="mt-2">Developed by Wonsano</p>
                <p class="mt-2 text-xs">
                    <a href="#" class="text-gray-400 hover:text-gray-600">Privacy Policy</a> •
                    <a href="#" class="text-gray-400 hover:text-gray-600">Terms of Service</a> •
                    <a href="#" class="text-gray-400 hover:text-gray-600">Security</a> •
                    <a href="#" class="text-gray-400 hover:text-gray-600">Contact Us</a>
                </p>
            </div>
        </div>
    </footer>

    @livewireScripts

    <script>
        // Toggle dropdown functions
        function toggleNotifications() {
            const dropdown = document.getElementById('notifications-dropdown');
            dropdown.classList.toggle('hidden');

            // Close user dropdown if open
            const userDropdown = document.getElementById('user-dropdown');
            userDropdown.classList.add('hidden');
        }

        function toggleUserMenu() {
            const dropdown = document.getElementById('user-dropdown');
            dropdown.classList.toggle('hidden');

            // Close notifications dropdown if open
            const notificationsDropdown = document.getElementById('notifications-dropdown');
            notificationsDropdown.classList.add('hidden');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const notificationsDropdown = document.getElementById('notifications-dropdown');
            const userDropdown = document.getElementById('user-dropdown');

            if (!event.target.closest('#notifications-dropdown') && !event.target.closest(
                    'button[onclick="toggleNotifications()"]')) {
                notificationsDropdown.classList.add('hidden');
            }

            if (!event.target.closest('#user-dropdown') && !event.target.closest(
                    'button[onclick="toggleUserMenu()"]')) {
                userDropdown.classList.add('hidden');
            }
        });

        // Initialize Livewire
        document.addEventListener('DOMContentLoaded', function() {
            console.log('METCO Dashboard loaded');

            if (typeof Livewire !== 'undefined') {
                console.log('Livewire v3 active');

                // Listen for dashboard updates
                Livewire.hook('message.processed', (message, component) => {
                    if (component.name === 'dashboard') {
                        console.log('Dashboard updated successfully');
                    }
                });
            }
        });
    </script>

    @include('components.toast')

    <!-- Session Toast Component (for cross-page toasts) -->
    @include('components.session-toast')

    @stack('scripts')
</body>

</html>
