<aside
    :class="{
        'translate-x-0': isMobileMenuOpen,
        '-translate-x-full': !isMobileMenuOpen,
        'md:w-64': !isDesktopSidebarCollapsed,
        'md:w-20': isDesktopSidebarCollapsed
    }"
    class="fixed inset-y-0 left-0 z-30 flex flex-col w-64 bg-white shadow-lg transform transition-all duration-300 ease-in-out md:translate-x-0">

    <div class="flex flex-col items-center justify-center h-24 border-b border-gray-200 flex-shrink-0">

        <svg class="mx-auto mb-1 text-primary" style="height:2em;width:2em;" viewBox="0 0 512 512" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path d="M380.9 144.3l41.8-72.4c2.7-4.7 1.1-10.7-3.6-13.4-4.7-2.7-10.7-1.1-13.4 3.6l-42.2 73.1c-32.2-13.7-68.2-21.2-106.5-21.2s-74.3 7.5-106.5 21.2l-42.2-73.1c-2.7-4.7-8.7-6.3-13.4-3.6-4.7 2.7-6.3 8.7-3.6 13.4l41.8 72.4C70.7 176.2 32 234.2 32 301.3c0 90.5 86.1 164 192 164s192-73.5 192-164c0-67.1-38.7-125.1-97.1-157zM128 352c-17.7 0-32-14.3-32-32s14.3-32 32-32 32 14.3 32 32-14.3 32-32 32zm256 0c-17.7 0-32-14.3-32-32s14.3-32 32-32 32 14.3 32 32-14.3 32-32 32z"/>
        </svg>
        <span class="block text-xl font-bold text-primary mt-1">Aladynos</span>
    </div>

    <nav class="flex-grow mt-4 overflow-y-auto">
        <x-dashboard.sidebar-link href="{{ route('dashboard') }}" icon="fas fa-home" :active="request()->routeIs('dashboard')"
            :label="__('Dashboard')" />

        @can('viewAny', App\Models\Client::class)
            <x-dashboard.sidebar-link href="{{ route('clients.index') }}" icon="fas fa-user-friends" :active="request()->routeIs('clients.*')"
                :label="__('Clients')" />
        @endcan

        @can('viewAny', App\Models\Order::class)
            <div class="space-y-1">
                <x-dashboard.sidebar-link href="{{ route('orders.index') }}" icon="fas fa-clipboard-list" :active="request()->routeIs('orders.*', 'order-carpets.*')"
                    :label="__('All Orders')" />
                <x-dashboard.sidebar-link href="{{ route('by-driver') }}" icon="fas fa-user-check" :active="request()->routeIs('by-driver*')"
                    :label="__('Orders by Driver')" />
            </div>
        @endcan

        <x-dashboard.sidebar-link href="{{ route('routes') }}" icon="fas fa-route" :active="request()->routeIs('routes')"
            :label="__('Route Optimization')" />

        @can('viewAny', App\Models\User::class)
            <x-dashboard.sidebar-link href="{{ route('users.index') }}" icon="fas fa-users" :active="request()->routeIs('users.*')"
                :label="__('Users')" />
        @endcan

        @can('viewAny', App\Models\PriceList::class)
            <x-dashboard.sidebar-link href="{{ route('price-lists.index') }}" icon="fas fa-tags" :active="request()->routeIs('price-lists.*')"
                :label="__('Price Lists')" />
        @endcan

        @can('viewAny', App\Models\ServicePriceList::class)
            <x-dashboard.sidebar-link href="{{ route('service-price-lists.index') }}" icon="fas fa-list-ul"
                :active="request()->routeIs('service-price-lists.*')" :label="__('Service Price Lists')" />
        @endcan

        @can('viewAny', App\Models\ProcessingCost::class)
            <x-dashboard.sidebar-link href="{{ route('processing-costs.index') }}" icon="fas fa-hand-holding-usd"
                :active="request()->routeIs('processing-costs.*')" :label="__('Processing Costs Management')" />
        @endcan

        <x-dashboard.sidebar-link href="{{ route('qr-codes.index') }}" icon="fas fa-qrcode" :active="request()->routeIs('qr-codes.*')"
            :label="__('QR Code Management')" />
    </nav>

    <div class="hidden md:block p-4 border-t border-gray-200 flex-shrink-0">
        <button @click="isDesktopSidebarCollapsed = !isDesktopSidebarCollapsed"
            class="w-full flex items-center justify-center text-gray-500 hover:text-gray-700 rounded-md p-2">
            <i x-show="!isDesktopSidebarCollapsed" class="fas fa-chevron-left text-xl"></i>
            <i x-show="isDesktopSidebarCollapsed" class="fas fa-chevron-right text-xl"></i>
        </button>
    </div>
    <livewire:livewire-placeholder />
</aside>
