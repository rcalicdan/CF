<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Header Section -->
        <div class="mb-8">
            <!-- Header Card -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-8 text-white">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div class="flex-1">
                            <h1 class="text-3xl font-bold mb-2">{{ $priceList->name }}</h1>
                            <div class="flex items-center text-blue-100">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span class="text-lg">{{ $priceList->location_postal_code }}</span>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-3 mt-6 md:mt-0">
                            <a href="{{ route('price-lists.index') }}"
                                class="inline-flex items-center justify-center px-6 py-3 bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl text-white hover:bg-white/30 transition-all duration-200 font-medium">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                {{ __('Back') }}
                            </a>
                            <a href="{{ route('price-lists.edit', $priceList) }}"
                                class="inline-flex items-center justify-center px-6 py-3 bg-white text-blue-600 rounded-xl hover:bg-gray-50 transition-all duration-200 font-medium shadow-lg">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                    </path>
                                </svg>
                                {{ __('Edit Price List') }}
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Stats Section -->
                <div class="p-6 bg-gray-50 border-b border-gray-200">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-white rounded-xl p-4 text-center shadow-sm">
                            <div class="text-2xl font-bold text-gray-900">{{ $this->totalServices }}</div>
                            <div class="text-sm text-gray-500 mt-1">{{ __('Total Services') }}</div>
                        </div>
                        <div class="bg-white rounded-xl p-4 text-center shadow-sm">
                            <div class="text-2xl font-bold text-green-600">{{ number_format($this->totalValue, 2) }} PLN
                            </div>
                            <div class="text-sm text-gray-500 mt-1">{{ __('Total Value') }}</div>
                        </div>
                        <div class="bg-white rounded-xl p-4 text-center shadow-sm">
                            <div class="text-2xl font-bold text-blue-600">{{ number_format($this->averagePrice, 2) }}
                                PLN
                            </div>
                            <div class="text-sm text-gray-500 mt-1">{{ __('Average Price') }}</div>
                        </div>
                        <div class="bg-white rounded-xl p-4 text-center shadow-sm">
                            @php $stats = $this->priceDifferenceStats; @endphp
                            <div
                                class="text-2xl font-bold {{ $stats['total_difference'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $stats['total_difference'] >= 0 ? '+' : '' }}{{ number_format($stats['percentage'], 1) }}%
                            </div>
                            <div class="text-sm text-gray-500 mt-1">{{ __('Price Variance') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Services Section -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <!-- Services Header -->
            <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <h2 class="text-xl font-semibold text-gray-900">{{ __('Service Prices') }}</h2>
                    <!-- Search and View Toggle -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input wire:model.live.debounce.300ms="searchTerm" type="text"
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="{{ __('Search services...') }}">
                        </div>
                        <button wire:click="$toggle('showMobileView')"
                            class="sm:hidden inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                            {{ $showMobileView ? __('Hide Details') : __('Show Details') }}
                        </button>
                    </div>
                </div>
            </div>
            @if ($this->filteredServices->count() > 0)
                <!-- Desktop Table View -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th wire:click="sortBy('name')"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors">
                                    <div class="flex items-center space-x-1">
                                        <span>{{ __('Service Name') }}</span>
                                        @if ($sortField === 'name')
                                            <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? '' : 'transform rotate-180' }}"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7"></path>
                                            </svg>
                                        @endif
                                    </div>
                                </th>
                                <th wire:click="sortBy('base_price')"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors">
                                    <div class="flex items-center space-x-1">
                                        <span>{{ __('Base Price') }}</span>
                                        @if ($sortField === 'base_price')
                                            <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? '' : 'transform rotate-180' }}"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7"></path>
                                            </svg>
                                        @endif
                                    </div>
                                </th>
                                <th wire:click="sortBy('pivot.price')"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors">
                                    <div class="flex items-center space-x-1">
                                        <span>{{ __('List Price') }}</span>
                                        @if ($sortField === 'pivot.price')
                                            <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? '' : 'transform rotate-180' }}"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7"></path>
                                            </svg>
                                        @endif
                                    </div>
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Difference') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Status') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($this->filteredServices as $service)
                                @php
                                    $difference = $service->pivot->price - $service->base_price;
                                    $percentage =
                                        $service->base_price > 0 ? ($difference / $service->base_price) * 100 : 0;
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div
                                                    class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                                                    <span
                                                        class="text-white font-semibold text-sm">{{ substr($service->name, 0, 2) }}</span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $service->name }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 font-medium">
                                            {{ number_format($service->base_price, 2) }} PLN</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-900">
                                            {{ number_format($service->pivot->price, 2) }} PLN</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="text-sm {{ $difference >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">
                                            {{ $difference >= 0 ? '+' : '' }}{{ number_format($difference, 2) }} PLN
                                            <span class="text-xs block">
                                                ({{ $percentage >= 0 ? '+' : '' }}{{ number_format($percentage, 1) }}%)
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($difference > 0)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ __('Premium') }}
                                            </span>
                                        @elseif($difference < 0)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                {{ __('Discounted') }}
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ __('Base Price') }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- Mobile Card View -->
                <div class="md:hidden">
                    <div class="divide-y divide-gray-200">
                        @foreach ($this->filteredServices as $service)
                            @php
                                $difference = $service->pivot->price - $service->base_price;
                                $percentage = $service->base_price > 0 ? ($difference / $service->base_price) * 100 : 0;
                            @endphp
                            <div class="p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center flex-1">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div
                                                class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                                                <span
                                                    class="text-white font-semibold text-sm">{{ substr($service->name, 0, 2) }}</span>
                                            </div>
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <div class="text-sm font-medium text-gray-900 mb-1">{{ $service->name }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ __('Base') }}: {{ number_format($service->base_price, 2) }} PLN
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-bold text-gray-900">
                                            {{ number_format($service->pivot->price, 2) }} PLN</div>
                                        <div
                                            class="text-xs {{ $difference >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">
                                            {{ $difference >= 0 ? '+' : '' }}{{ number_format($difference, 2) }} PLN
                                            ({{ $percentage >= 0 ? '+' : '' }}{{ number_format($percentage, 1) }}%)
                                        </div>
                                    </div>
                                </div>
                                @if ($showMobileView)
                                    <div class="mt-3 pt-3 border-t border-gray-100">
                                        <div class="flex justify-between items-center">
                                            <span class="text-xs text-gray-500">{{ __('Status') }}:</span>
                                            @if ($difference > 0)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    {{ __('Premium') }}
                                                </span>
                                            @elseif($difference < 0)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    {{ __('Discounted') }}
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ __('Base Price') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v11a2 2 0 002 2h5.586a1 1 0 00.707-.293l5.414-5.414a1 1 0 00.293-.707V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                        </path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('No services found') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        @if ($searchTerm)
                            {{ __('No services match your search criteria.') }}
                        @else
                            {{ __('This price list does not have any services yet.') }}
                        @endif
                    </p>
                    @if ($searchTerm)
                        <div class="mt-6">
                            <button wire:click="$set('searchTerm', '')"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                {{ __('Clear Search') }}
                            </button>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
