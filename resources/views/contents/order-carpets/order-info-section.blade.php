<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ __('Order Information') }}
        </h2>
    </div>
    <div class="p-6 space-y-4">
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-500">{{ __('Order ID') }}</span>
            <a href="{{ route('orders.show', $orderCarpet->order) }}"
                class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition-colors duration-200">
                #{{ $orderCarpet->order->id }}
            </a>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-500">{{ __('Created') }}</span>
            <span class="text-sm text-gray-900">{{ $orderCarpet->created_at->format('M d, Y') }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-500">{{ __('Time') }}</span>
            <span class="text-sm text-gray-900">{{ $orderCarpet->created_at->format('h:i A') }}</span>
        </div>
    </div>
</div>
