<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4">
                </path>
            </svg>
            {{ __('Measurements') }}
        </h2>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-2 gap-4">
            <div class="text-center p-4 bg-gray-50 rounded-lg border border-gray-100">
                <div class="text-2xl font-bold text-gray-900">{{ $orderCarpet->height ?? '–' }}</div>
                <div class="text-sm text-gray-500 mt-1">{{ __('Height (m)') }}</div>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-lg border border-gray-100">
                <div class="text-2xl font-bold text-gray-900">{{ $orderCarpet->width ?? '–' }}</div>
                <div class="text-sm text-gray-500 mt-1">{{ __('Width (m)') }}</div>
            </div>
        </div>
        <div class="mt-4 p-4 bg-indigo-50 rounded-lg border border-indigo-200">
            <div class="text-center">
                <div class="text-3xl font-bold text-indigo-600">
                    {{ $orderCarpet->total_area ?? (number_format($orderCarpet->height * $orderCarpet->width, 2) ?? '–') }}
                </div>
                <div class="text-sm text-indigo-700 mt-1">{{ __('Total Area (m²)') }}</div>
            </div>
        </div>
        <div class="mt-4 p-4 bg-green-50 rounded-lg border border-green-200">
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600">{{ $orderCarpet->services_count }}</div>
                <div class="text-sm text-green-700 mt-1">{{ __('Services Applied') }}</div>
            </div>
        </div>
    </div>
</div>