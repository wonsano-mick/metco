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
                    @if(auth()->user()->role === 'teller')
                    <a href="{{ route('teller.dashboard') }}"
                        class="{{ request()->routeIs('teller.dashboard') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-700 hover:text-blue-600' }} px-3 py-2 text-sm font-medium">
                        Dashboard
                    </a>
                    @else
                    <a href="{{ route('dashboard') }}"
                        class="{{ request()->routeIs('dashboard') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-700 hover:text-blue-600' }} px-3 py-2 text-sm font-medium">
                        Dashboard
                    </a>
                    @endif
                    
                    <a href="{{ route('customers.index') }}"
                        class="{{ request()->routeIs('customers.*') ? 'text-green-600 border-b-2 border-green-600' : 'text-gray-700 hover:text-blue-600' }} px-3 py-2 text-sm font-medium">
                        Customers
                    </a>
                    
                    <!-- Accounts Dropdown -->
                    <div class="relative group">
                        <button class="flex items-center space-x-1 px-4 py-2 text-sm {{ request()->routeIs('accounts*') ||request()->routeIs('fee*') ||request()->routeIs('interest*') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600' }} hover:text-blue-600 group">
                            <span>Accounts</span>
                            <i class="fas fa-chevron-down text-xs transition-transform group-hover:rotate-180"></i>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div class="absolute left-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                            <a href="{{ route('accounts.index') }}"
                                class="block px-4 py-2 text-sm {{ request()->routeIs('accounts.index') ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600' }}">
                                <i class="fas fa-wallet mr-3 w-4"></i>
                                All Accounts
                            </a>
                            <div class="border-t border-gray-100 my-2"></div>
                            <a href="{{ route('accounts.account-types') }}"
                                class="block px-4 py-2 text-sm {{ request()->routeIs('accounts.*') ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600' }}">
                                <i class="fas fa-percent mr-3 w-4"></i>
                                Account Types
                            </a>
                            @can('process monthly fees and interest')
                            <a href="{{ route('accounts.monthly-processing') }}"
                                class="block px-4 py-2 text-sm {{ request()->routeIs('accounts.*') ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600' }}">
                                <i class="fas fa-chart-line mr-3 w-4"></i>
                                Monthly Processing
                            </a>
                            @endcan
                            {{-- <a href="{{ route('fee.configurations') }}"
                                class="block px-4 py-2 text-sm {{ request()->routeIs('fee.*') ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600' }}">
                                <i class="fas fa-percent mr-3 w-4"></i>
                                Fee on Account
                            </a>
                            <a href="{{ route('interest.configurations') }}"
                                class="block px-4 py-2 text-sm {{ request()->routeIs('interest.*') ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600' }}">
                                <i class="fas fa-chart-line mr-3 w-4"></i>
                                Interest on Account
                            </a> --}}
                        </div>
                    </div>

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
                    
                    <a href="{{ route('tellers.index') }}"
                        class="{{ request()->routeIs('tellers.*') ? 'text-purple-600 border-b-2 border-purple-600' : 'text-gray-700 hover:text-blue-600' }} px-3 py-2 text-sm font-medium">
                        Tellers
                    </a>
                    
                    @if(auth()->user()->role === 'super-admin' || auth()->user()->role === 'manager')
                    <a href="{{ route('users.index') }}"
                        class="{{ request()->routeIs('users.*') ? 'text-red-600 border-b-2 border-red-400' : 'text-gray-700 hover:text-blue-600' }} px-3 py-2 text-sm font-medium">
                        Users
                    </a>
                    @endif
                </div>
            </div>

            <!-- User Menu (unchanged) -->
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
                        <a href="#"
                            class="block px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                            <i class="fas fa-shield-alt mr-3 text-gray-400"></i>
                            Security
                        </a>
                        <a href="#"
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

    <!-- Mobile menu -->
    <div class="md:hidden border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('dashboard') }}"
                    class="{{ request()->routeIs('dashboard') ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:bg-gray-50' }} px-3 py-2 text-sm font-medium rounded-md transition-colors">
                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                </a>
                <a href="{{ route('accounts.index') }}"
                    class="{{ request()->routeIs('accounts.*') ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:bg-gray-50' }} px-3 py-2 text-sm font-medium rounded-md transition-colors">
                    <i class="fas fa-wallet mr-2"></i>Accounts
                </a>
                <a href="{{ route('transactions.index') }}"
                    class="{{ request()->routeIs('transactions.*') ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:bg-gray-50' }} px-3 py-2 text-sm font-medium rounded-md transition-colors">
                    <i class="fas fa-exchange-alt mr-2"></i>Transactions
                </a>
            </div>
        </div>
    </div>
</nav>

<script>
    function toggleNotifications() {
        const dropdown = document.getElementById('notifications-dropdown');
        dropdown.classList.toggle('hidden');
    }

    function toggleUserMenu() {
        const dropdown = document.getElementById('user-dropdown');
        dropdown.classList.toggle('hidden');
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const notifications = document.getElementById('notifications-dropdown');
        const userMenu = document.getElementById('user-dropdown');
        const notificationsButton = event.target.closest('button[onclick="toggleNotifications()"]');
        const userMenuButton = event.target.closest('button[onclick="toggleUserMenu()"]');
        
        if (!notificationsButton && !notifications?.contains(event.target)) {
            notifications?.classList.add('hidden');
        }
        
        if (!userMenuButton && !userMenu?.contains(event.target)) {
            userMenu?.classList.add('hidden');
        }
    });
</script>