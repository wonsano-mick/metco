<nav class="bg-white shadow-sm border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        <div class="text-xl font-bold text-blue-600">
                            <i class="fas fa-bank mr-2"></i>METCO
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