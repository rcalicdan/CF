<header class="flex items-center justify-between h-16 p-4 bg-white border-b border-gray-200">
    <button @click="isMobileMenuOpen = !isMobileMenuOpen; dropdownOpen = false"
        class="text-gray-500 focus:outline-none md:hidden">
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M4 6H20M4 12H20M4 18H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" />
        </svg>
    </button>

    <div class="flex-1">{{ $header ?? '' }}</div>

    <div class="flex items-center space-x-4">
        <div class="relative">
            <button @click="dropdownOpen = !dropdownOpen; isMobileMenuOpen = false"
                class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-400">
                <div class="flex items-center justify-center h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 text-white font-bold text-base shadow">
                    {{ strtoupper(substr(Auth::user()->full_name, 0, 1) . (strpos(Auth::user()->full_name, ' ') !== false ? substr(Auth::user()->full_name, strpos(Auth::user()->full_name, ' ') + 1, 1) : '')) }}
                </div>
                <div class="flex flex-col items-start">
                    <span class="text-sm font-semibold text-gray-800">{{ Auth::user()->full_name }}</span>
                </div>
            </button>
            <div x-show="dropdownOpen" @click.away="dropdownOpen = false"
                class="absolute right-0 mt-2 w-48 bg-white rounded-md overflow-hidden shadow-xl z-10"
                style="display: none;">
                <!--<a href="#" class="dropdown-link block px-4 py-2 text-sm text-gray-700">{{ __('Profile') }}</a>
                <a href="#" class="dropdown-link block px-4 py-2 text-sm text-gray-700">{{ __('Settings') }}</a>-->
                <livewire:auth.logout />
            </div>
        </div>
    </div>
</header>
