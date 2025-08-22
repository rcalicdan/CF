<div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50">
    <!-- Header Section -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('clients.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        {{ __('Back to Clients') }}
                    </a>
                    <div class="h-8 w-px bg-gray-300"></div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ $client->full_name }}</h1>
                        <p class="text-gray-500 mt-1">{{ __('Client ID') }}: #{{ $client->id }}</p>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('clients.edit', $client) }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        {{ __('Edit Client') }}
                    </a>
                    <a href="{{ route('orders.create') }}?client_id={{ $client->id }}"
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('New Order') }}
                    </a>
                    <button wire:click="generatePdfReport"
                        class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        {{ __('Generate PDF Report') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">{{ __('Total Orders') }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_orders'] }}</p>
                    </div>
                </div>
            </div>

            <div
                class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">{{ __('Completed Orders') }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['completed_orders'] }}</p>
                    </div>
                </div>
            </div>

            <div
                class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">{{ __('Total Carpets') }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_carpets'] }}</p>
                    </div>
                </div>
            </div>

            <div
                class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">{{ __('Total Spent') }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_spent'], 2) }}
                            {{ __('PLN') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <button wire:click="setActiveTab('overview')"
                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200
                               {{ $activeTab === 'overview'
                                   ? 'border-blue-500 text-blue-600'
                                   : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    {{ __('Overview') }}
                </button>
                <button wire:click="setActiveTab('orders')"
                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200
                               {{ $activeTab === 'orders'
                                   ? 'border-blue-500 text-blue-600'
                                   : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    {{ __('Orders') }} ({{ $stats['total_orders'] }})
                </button>
                <button wire:click="setActiveTab('carpets')"
                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200
                               {{ $activeTab === 'carpets'
                                   ? 'border-blue-500 text-blue-600'
                                   : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    {{ __('Carpets') }} ({{ $stats['total_carpets'] }})
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            @if ($activeTab === 'overview')
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Client Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ __('Client Information') }}
                            </h3>
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ __('Full Name') }}</p>
                                        <p class="text-gray-600">{{ $client->full_name }}</p>
                                    </div>
                                </div>

                                @if ($client->email)
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ __('Email') }}</p>
                                            <a href="mailto:{{ $client->email }}"
                                                class="text-blue-600 hover:text-blue-800">{{ $client->email }}</a>
                                        </div>
                                    </div>
                                @endif

                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ __('Phone Number') }}</p>
                                        <a href="tel:{{ $client->phone_number }}"
                                            class="text-blue-600 hover:text-blue-800">{{ $client->phone_number }}</a>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ __('Address') }}</p>
                                        <p class="text-gray-600">{{ $client->full_address }}</p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-4 8V9m-6 4v6a2 2 0 002 2h12a2 2 0 002-2v-6M8 11H4" />
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ __('Client Since') }}</p>
                                        <p class="text-gray-600">{{ $client->created_at->format('d M Y') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                {{ __('Recent Orders') }}
                            </h3>
                            <div class="space-y-4">
                                @forelse($orders->take(5) as $order)
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                        <div
                                            class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <span
                                                class="text-blue-600 font-semibold text-sm">#{{ $order->id }}</span>
                                        </div>
                                        <div class="ml-4 flex-grow">
                                            <div class="flex items-center justify-between">
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ __('Order') }} #{{ $order->id }}
                                                </p>
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                       {{ $order->status === 'completed'
                                                           ? 'bg-green-100 text-green-800'
                                                           : ($order->status === 'pending'
                                                               ? 'bg-yellow-100 text-yellow-800'
                                                               : 'bg-blue-100 text-blue-800') }}">
                                                    {{ ucfirst($order->status ?? __('Pending')) }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ $order->created_at->format('d M Y') }} •
                                                {{ number_format($order->total_amount, 2) }} {{ __('PLN') }}
                                            </p>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-8">
                                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('No orders yet') }}
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-500">
                                            {{ __('This client hasn\'t placed any orders.') }}</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($activeTab === 'orders')
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Order History') }}</h3>
                        <a href="{{ route('orders.create') }}?client_id={{ $client->id }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            {{ __('New Order') }}
                        </a>
                    </div>

                    <div class="space-y-4">
                        @forelse($orders as $order)
                            <div
                                class="border border-gray-200 rounded-lg hover:shadow-md transition-shadow duration-200">
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center space-x-4">
                                            <div
                                                class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                                <span class="text-blue-600 font-bold">#{{ $order->id }}</span>
                                            </div>
                                            <div>
                                                <h4 class="text-lg font-semibold text-gray-900">{{ __('Order') }}
                                                    #{{ $order->id }}</h4>
                                                <p class="text-sm text-gray-500">
                                                    {{ $order->created_at->format('d M Y \o H:i') }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                                   {{ $order->status === 'completed'
                                                       ? 'bg-green-100 text-green-800'
                                                       : ($order->status === 'pending'
                                                           ? 'bg-yellow-100 text-yellow-800'
                                                           : 'bg-blue-100 text-blue-800') }}">
                                                {{ ucfirst($order->status ?? __('Pending')) }}
                                            </span>
                                            <button wire:click="selectOrder({{ $order->id }})"
                                                class="text-blue-600 hover:text-blue-800">
                                                {{ $selectedOrderId === $order->id ? __('Hide Details') : __('View Details') }}
                                            </button>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                        <div class="flex items-center text-sm text-gray-600">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-4 8V9" />
                                            </svg>
                                            {{ __('Driver') }}: {{ $order->driver_name }}
                                        </div>
                                        <div class="flex items-center text-sm text-gray-600">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                            </svg>
                                            {{ __('Total') }}: {{ number_format($order->total_amount, 2) }}
                                            {{ __('PLN') }}
                                        </div>
                                        <div class="flex items-center text-sm text-gray-600">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                            </svg>
                                            {{ __('Carpets') }}: {{ $order->orderCarpets->count() }}
                                        </div>
                                    </div>

                                    @if ($selectedOrderId === $order->id && $order->orderCarpets->count() > 0)
                                        <div class="border-t border-gray-200 pt-4">
                                            <h5 class="text-sm font-medium text-gray-900 mb-3">
                                                {{ __('Order Carpets') }}</h5>
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                @foreach ($order->orderCarpets as $carpet)
                                                    <div class="bg-gray-50 rounded-lg p-4">
                                                        <div class="flex items-center justify-between mb-2">
                                                            <span
                                                                class="text-xs font-medium text-gray-500">{{ $carpet->qr_code }}</span>
                                                            <span
                                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                          {{ $carpet->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                                {{ $carpet->status_label }}
                                                            </span>
                                                        </div>
                                                        <div class="space-y-1 text-sm">
                                                            <p class="text-gray-900">
                                                                {{ $carpet->width }}x{{ $carpet->height }}m</p>
                                                            <p class="text-gray-600">{{ __('Area') }}:
                                                                {{ $carpet->total_area }}m²</p>
                                                            <p class="text-gray-600">{{ __('Services') }}:
                                                                {{ $carpet->services_count }}</p>
                                                            <p class="text-gray-900 font-medium">
                                                                {{ number_format($carpet->total_price, 2) }}
                                                                {{ __('PLN') }}</p>
                                                        </div>
                                                        <div class="mt-3 flex space-x-2">
                                                            <a href="{{ route('order-carpets.show', $carpet) }}"
                                                                class="text-xs text-blue-600 hover:text-blue-800">{{ __('View Details') }}</a>
                                                            @if ($carpet->hasValidQrCode())
                                                                <span class="text-gray-300">•</span>
                                                                <a href="{{ $carpet->qr_code_url }}" target="_blank"
                                                                    class="text-xs text-green-600 hover:text-green-800">{{ __('View QR') }}</a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <div class="flex justify-end pt-4">
                                        <a href="{{ route('orders.show', $order) }}"
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition-colors duration-200">
                                            {{ __('View Full Order') }}
                                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('No orders found') }}</h3>
                                <p class="mt-2 text-gray-500">{{ __('This client hasn\'t placed any orders yet.') }}
                                </p>
                                <div class="mt-6">
                                    <a href="{{ route('orders.create') }}?client_id={{ $client->id }}"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                        {{ __('Create First Order') }}
                                    </a>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    @if ($orders->hasPages())
                        <div class="mt-8">
                            {{ $orders->links() }}
                        </div>
                    @endif
                </div>
            @elseif($activeTab === 'carpets')
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('All Carpets') }}</h3>
                        <div class="flex items-center space-x-2 text-sm text-gray-500">
                            <span>{{ __('Total') }}: {{ $stats['total_carpets'] }} {{ __('carpets') }}</span>
                        </div>
                    </div>

                    @if ($carpets->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            @foreach ($carpets as $carpet)
                                <div
                                    class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden">
                                    <!-- Carpet Header -->
                                    <div
                                        class="bg-gradient-to-r from-blue-50 to-indigo-50 px-4 py-3 border-b border-gray-100">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-2">
                                                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                                <span
                                                    class="text-xs font-medium text-gray-900">{{ $carpet->reference_code }}</span>
                                            </div>
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                              {{ $carpet->status === 'completed'
                                                  ? 'bg-green-100 text-green-800'
                                                  : ($carpet->status === 'in_progress'
                                                      ? 'bg-blue-100 text-blue-800'
                                                      : ($carpet->status === 'picked_up'
                                                          ? 'bg-yellow-100 text-yellow-800'
                                                          : 'bg-gray-100 text-gray-800')) }}">
                                                {{ $carpet->status_label }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Carpet Body -->
                                    <div class="p-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <h4 class="text-sm font-semibold text-gray-900">
                                                {{ __('Order') }} #{{ $carpet->order->id }}
                                            </h4>
                                            <span
                                                class="text-lg font-bold text-gray-900">{{ number_format($carpet->total_price, 2) }}
                                                {{ __('PLN') }}</span>
                                        </div>

                                        <div class="space-y-2 mb-4">
                                            <div class="flex items-center text-xs text-gray-600">
                                                <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                                                </svg>
                                                {{ __('Dimensions') }}:
                                                {{ $carpet->width }}×{{ $carpet->height }}m
                                            </div>
                                            <div class="flex items-center text-xs text-gray-600">
                                                <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z" />
                                                </svg>
                                                {{ __('Area') }}: {{ $carpet->total_area }}m²
                                            </div>
                                            <div class="flex items-center text-xs text-gray-600">
                                                <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                {{ __('Services') }}: {{ $carpet->services_count }}
                                            </div>
                                            @if ($carpet->measured_at)
                                                <div class="flex items-center text-xs text-gray-600">
                                                    <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-4 8V9" />
                                                    </svg>
                                                    {{ __('Measured') }}:
                                                    {{ $carpet->measured_at->format('d M Y') }}
                                                </div>
                                            @endif
                                        </div>

                                        @if ($carpet->remarks)
                                            <div class="mb-4">
                                                <p class="text-xs text-gray-500 bg-gray-50 rounded p-2">
                                                    <span class="font-medium">{{ __('Note') }}:</span>
                                                    {{ Str::limit($carpet->remarks, 60) }}
                                                </p>
                                            </div>
                                        @endif

                                        <!-- Action Buttons -->
                                        <div class="flex space-x-2">
                                            <a href="{{ route('order-carpets.show', $carpet) }}"
                                                class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-colors duration-200">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                {{ __('View') }}
                                            </a>
                                            @if ($carpet->hasValidQrCode())
                                                <a href="{{ $carpet->qr_code_url }}" target="_blank"
                                                    class="inline-flex items-center justify-center px-3 py-2 bg-green-100 hover:bg-green-200 text-green-700 text-xs font-medium rounded-lg transition-colors duration-200">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if ($carpets->hasPages())
                            <div class="mt-8">
                                {{ $carpets->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('No carpets found') }}</h3>
                            <p class="mt-2 text-gray-500">{{ __('This client doesn\'t have any carpets yet.') }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

@include('livewire.clients.assets')
