<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 8h10M7 12h4m-7 8h16a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2-2v16a2 2 0 002 2z"></path>
            </svg>
            {{ __('Remarks') }}
        </h2>
    </div>
    <div class="p-6">
        @if ($orderCarpet->remarks)
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                <p class="text-gray-700 leading-relaxed">{{ $orderCarpet->remarks }}</p>
            </div>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 8h10M7 12h4m-7 8h16a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2-2v16a2 2 0 002 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('No remarks') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('No additional remarks have been added for this carpet.') }}</p>
            </div>
        @endif
    </div>
</div>