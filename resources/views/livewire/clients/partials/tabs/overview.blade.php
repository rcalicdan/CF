<div class="p-6">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Client Information -->
        <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                {{ __('Client Information') }}
            </h3>
            <div class="space-y-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
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
                        <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
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
                    <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
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
                    <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
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
                    <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-4 8V9m-6 4v6a2 2 0 002 2h12a2 2 0 002-2v-6M8 11H4" />
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ __('Client Since') }}</p>
                        <p class="text-gray-600">{{ $client->created_at->format('d M Y') }}</p>
                    </div>
                </div>

                {{-- Added Remarks Section --}}
                @if ($client->remarks)
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ __('Remarks') }}</p>
                            <p class="text-gray-600 whitespace-pre-wrap">{{ $client->remarks }}</p>
                        </div>
                    </div>
                @endif

            </div>
        </div>

        <!-- Recent Activity -->
        <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                {{ __('Recent Orders') }}
            </h3>
            <div class="space-y-4">
                @forelse($orders->take(5) as $order)
                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <span class="text-blue-600 font-semibold text-sm">#{{ $order->id }}</span>
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
                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
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
