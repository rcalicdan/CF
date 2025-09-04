<aside
    :class="{
        'translate-x-0': isMobileMenuOpen,
        '-translate-x-full': !isMobileMenuOpen,
        'md:w-64': !isDesktopSidebarCollapsed,
        'md:w-20': isDesktopSidebarCollapsed
    }"
    class="fixed inset-y-0 left-0 z-30 flex flex-col w-64 bg-white shadow-lg transform transition-all duration-300 ease-in-out md:translate-x-0">

    <div class="flex items-center justify-center h-16 border-b border-gray-200 flex-shrink-0">
        <h1 class="text-2xl font-bold px-4 text-primary" x-show="!isDesktopSidebarCollapsed" x-transition.opacity>
            {{ __(auth()->user()->role) }}
        </h1>
        <i x-show="isDesktopSidebarCollapsed" class="fas fa-tshirt h-8 w-8 text-primary text-2xl"
            x-transition.opacity></i>
    </div>

    <nav class="flex-grow mt-4 overflow-y-auto">
        <x-dashboard.sidebar-link href="{{ route('dashboard') }}" icon="fas fa-home" :active="request()->routeIs('dashboard')"
            :label="__('Dashboard')" />
        @can('viewAny', App\Models\User::class)
            <x-dashboard.sidebar-link href="{{ route('users.index') }}" icon="fas fa-users" :active="request()->routeIs('users.*')"
                :label="__('Users')" />
        @endcan

        @can('viewAny', App\Models\Client::class)
            <x-dashboard.sidebar-link href="{{ route('clients.index') }}" icon="fas fa-user-friends" :active="request()->routeIs('clients.*')"
                :label="__('Clients')" />
        @endcan

        @can('viewAny', App\Models\Service::class)
            <!--<x-dashboard.sidebar-link href="{{ route('services.index') }}" icon="fas fa-tshirt" :active="request()->routeIs('services.*')"
                    :label="__('Laundry Services')" />-->
        @endcan

        @can('viewAny', App\Models\PriceList::class)
            <x-dashboard.sidebar-link href="{{ route('price-lists.index') }}" icon="fas fa-tags" :active="request()->routeIs('price-lists.*')"
                :label="__('Price Lists')" />
        @endcan

        @can('viewAny', App\Models\ServicePriceList::class)
            <x-dashboard.sidebar-link href="{{ route('service-price-lists.index') }}" icon="fas fa-list-ul"
                :active="request()->routeIs('service-price-lists.*')" :label="__('Service Price Lists')" />
        @endcan

        @can('viewAny', App\Models\Order::class)
            <div class="space-y-1">
                <x-dashboard.sidebar-link href="{{ route('orders.index') }}" icon="fas fa-clipboard-list" :active="request()->routeIs('orders.*', 'order-carpets.*')"
                    :label="__('All Orders')" />
                <x-dashboard.sidebar-link href="{{ route('by-driver') }}" icon="fas fa-user-check" :active="request()->routeIs('by-driver*')"
                    :label="__('Orders by Driver')" />
            </div>
        @endcan

        {{-- @can('viewAny', App\Models\Complaint::class)
            <x-dashboard.sidebar-link href="{{ route('complaints.index') }}" icon="fas fa-exclamation-triangle"
                :active="request()->routeIs('complaints.*')" :label="__('Complaints')" />
        @endcan --}}

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
