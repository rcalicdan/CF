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
        <div class="relative w-full max-w-md hidden md:block">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3"><svg class="h-5 w-5 text-gray-400"
                    viewBox="0 0 24 24" fill="none">
                    <path
                        d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg></span>
            <!--<input
                class="themed-input w-full pl-10 pr-4 py-2 border rounded-xl text-sm text-gray-700 placeholder-gray-400 bg-gray-100 focus:outline-none focus:ring-2 focus:border-transparent"
                type="text" placeholder="Search laundries, orders...">-->
        </div>
        <div class="relative">
            <button @click="dropdownOpen = !dropdownOpen; isMobileMenuOpen = false"
                class="relative block h-8 w-8 rounded-full overflow-hidden shadow focus:outline-none">
                <img class="h-full w-full object-cover"
                    src="https://images.unsplash.com/photo-1528892952291-009c663ce843?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=296&q=80"
                    alt="Your avatar">
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
