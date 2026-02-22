<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>METCO | Banking App</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('images/metco_logo.png') }}" />

    <!-- Add Alpine.js CDN -->
    {{-- <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}
    <script defer src="{{ asset('js/alpine.min.js') }}"></script>

    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}
    <script src="{{ asset('js/tailwind.js') }}"></script>

    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> --}}
    <link rel="stylesheet" href="{{ asset('css/all.min.css') }}">

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
    <x-navbar />

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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
    <script src="{{ asset('js/chart.min.js') }}"></script>
    @stack('scripts')
</body>

</html>
