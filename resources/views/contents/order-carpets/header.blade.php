<div class="mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Carpet Details') }}</h1>
            <p class="mt-2 text-gray-600">{{ __('Carpet') }} #{{ $orderCarpet->id }}</p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <span
                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        {{ $orderCarpet->status === 'pending' ? 'bg-amber-100 text-amber-800 border border-amber-200' : '' }}
                        {{ $orderCarpet->status === 'measured' ? 'bg-blue-100 text-blue-800 border border-blue-200' : '' }}
                        {{ $orderCarpet->status === 'completed' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : '' }}
                        {{ $orderCarpet->status === 'cancelled' ? 'bg-red-100 text-red-800 border border-red-200' : '' }}">
                <!--<span
                    class="w-2 h-2 mr-2 rounded-full
                            {{ $orderCarpet->status === 'pending' ? 'bg-amber-400' : '' }}
                            {{ $orderCarpet->status === 'measured' ? 'bg-blue-400' : '' }}
                            {{ $orderCarpet->status === 'completed' ? 'bg-emerald-400' : '' }}
                            {{ $orderCarpet->status === 'cancelled' ? 'bg-red-400' : '' }}"></span>
                {{"Status:  " . $orderCarpet->status_label }}
            </span>-->
        </div>
    </div>
</div>
