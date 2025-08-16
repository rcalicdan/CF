<div class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6">
            <div class="block lg:hidden space-y-4">
                <div class="flex items-center justify-between">
                    <x-utils.link-button href="{{ route('orders.index') }}" buttonText="{{ __('Back to Orders') }}"
                        bgColor="bg-white" textColor="text-gray-600" hoverColor="hover:bg-gray-50"
                        focusRing="focus:ring-blue-500" spacing=""
                        class="border border-gray-300 shadow-sm transition-all duration-200 hover:shadow-md">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </x-utils.link-button>

                    @can('update', $order)
                        <x-utils.update-button route="{{ route('orders.edit', $order) }}"
                            class="transition-all duration-200 hover:shadow-md" />
                    @endcan
                </div>

                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                        {{ __('Order #:id', ['id' => $order->id]) }}</h1>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ __('Created on :date', ['date' => $order->created_at->format('M d, Y \a\t g:i A')]) }}</p>
                </div>

                <div class="flex justify-start">
                    <span
                        class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold shadow-sm
                    @if ($order->status === 'pending') bg-gradient-to-r from-yellow-400 to-yellow-500 text-white
                    @elseif($order->status === 'in_progress') bg-gradient-to-r from-blue-500 to-blue-600 text-white
                    @elseif($order->status === 'completed') bg-gradient-to-r from-green-500 to-green-600 text-white
                    @elseif($order->status === 'cancelled') bg-gradient-to-r from-red-500 to-red-600 text-white
                    @else bg-gradient-to-r from-gray-400 to-gray-500 text-white @endif">
                        <div class="w-2 h-2 rounded-full bg-white opacity-75 mr-2 animate-pulse"></div>
                        {{ __($order->status_label) }}
                    </span>
                </div>
            </div>

            <div class="hidden lg:flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <x-utils.link-button href="{{ route('orders.index') }}" buttonText="{{ __('Back to Orders') }}"
                        bgColor="bg-white" textColor="text-gray-600" hoverColor="hover:bg-gray-50"
                        focusRing="focus:ring-blue-500" spacing=""
                        class="border border-gray-300 shadow-sm transition-all duration-200 hover:shadow-md">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </x-utils.link-button>

                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ __('Order #:id', ['id' => $order->id]) }}</h1>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ __('Created on :date', ['date' => $order->created_at->format('M d, Y \a\t g:i A')]) }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    @can('update', $order)
                        <x-utils.update-button route="{{ route('orders.edit', $order) }}"
                            class="transition-all duration-200 hover:shadow-md" />
                    @endcan

                    <div class="relative">
                        <span
                            class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold shadow-sm
                        @if ($order->status === 'pending') bg-gradient-to-r from-yellow-400 to-yellow-500 text-white
                        @elseif($order->status === 'in_progress') bg-gradient-to-r from-blue-500 to-blue-600 text-white
                        @elseif($order->status === 'completed') bg-gradient-to-r from-green-500 to-green-600 text-white
                        @elseif($order->status === 'cancelled') bg-gradient-to-r from-red-500 to-red-600 text-white
                        @else bg-gradient-to-r from-gray-400 to-gray-500 text-white @endif">
                            <div class="w-2 h-2 rounded-full bg-white opacity-75 mr-2 animate-pulse"></div>
                            {{ __($order->status_label) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
