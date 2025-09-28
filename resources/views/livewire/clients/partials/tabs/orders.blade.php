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