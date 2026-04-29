<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                </path>
            </svg>
            {{ __('Services Applied') }}
        </h2>
    </div>
    <div class="p-6">
        @if ($orderCarpet->services->isNotEmpty())
            <div class="space-y-4">
                @foreach ($orderCarpet->services as $service)
                    @php
                        $quantity = $service->pivot->quantity;
                        $totalPrice = $service->pivot->total_price;
                        
                        $priceListPrice = $service->priceLists
                            ->where('id', $orderCarpet->order->price_list_id)
                            ->first();
                        $unitPrice = $priceListPrice ? $priceListPrice->pivot->price : $service->base_price;
                        
                        $hasQuantity = !is_null($quantity) && $quantity > 0;
                        $hasArea = $orderCarpet->total_area > 0;
                    @endphp
                    
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between p-4 bg-gray-50 rounded-lg border border-gray-100 hover:bg-gray-100 transition-colors duration-200 gap-4">
                        <!-- Left Section: Service Info -->
                        <div class="flex-1">
                            <div class="flex items-start">
                                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-900">{{ $service->name }}</p>
                                    
                                    <!-- Service Type Badge -->
                                    <div class="flex items-center mt-2 gap-2 flex-wrap">
                                        @if ($service->is_area_based)
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4">
                                                    </path>
                                                </svg>
                                                {{ __('Area-based') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                {{ __('One-time') }}
                                            </span>
                                        @endif
                                        
                                        @if($hasQuantity)
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-purple-100 text-purple-800">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                                </svg>
                                                {{ __('Quantity multiplier') }}
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <!-- Calculation Breakdown -->
                                    <div class="mt-3 space-y-1">
                                        <div class="text-sm text-gray-600">
                                            <span class="font-medium">{{ __('Unit price') }}:</span>
                                            <span class="ml-1">{{ number_format($unitPrice, 2, ',', ' ') }} zł</span>
                                            @if($service->is_area_based && !$hasQuantity)
                                                <span class="text-xs text-gray-500">/ m²</span>
                                            @endif
                                        </div>
                                        
                                        @if($hasQuantity)
                                            <!-- Quantity-based calculation -->
                                            <div class="text-sm text-gray-600">
                                                <span class="font-medium">{{ __('Quantity') }}:</span>
                                                <span class="ml-1">{{ number_format($quantity, 2, ',', ' ') }}</span>
                                            </div>
                                            <div class="text-xs text-indigo-600 font-medium mt-1">
                                                {{ number_format($unitPrice, 2, ',', ' ') }} zł × {{ number_format($quantity, 2, ',', ' ') }} = {{ number_format($totalPrice, 2, ',', ' ') }} zł
                                            </div>
                                        @elseif($service->is_area_based && $hasArea)
                                            <!-- Area-based calculation -->
                                            <div class="text-sm text-gray-600">
                                                <span class="font-medium">{{ __('Area') }}:</span>
                                                <span class="ml-1">{{ number_format($orderCarpet->total_area, 2, ',', ' ') }} m²</span>
                                            </div>
                                            <div class="text-xs text-indigo-600 font-medium mt-1">
                                                {{ number_format($unitPrice, 2, ',', ' ') }} zł/m² × {{ number_format($orderCarpet->total_area, 2, ',', ' ') }} m² = {{ number_format($totalPrice, 2, ',', ' ') }} zł
                                            </div>
                                        @elseif($service->is_area_based && !$hasArea)
                                            <!-- Area-based but no area yet -->
                                            <div class="text-xs text-amber-600 font-medium mt-1">
                                                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                                </svg>
                                                {{ __('Waiting for carpet measurements') }}
                                            </div>
                                        @else
                                            <!-- Fixed price (backward compatibility - treat as quantity 1) -->
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ __('Fixed price service') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Section: Total Price -->
                        <div class="text-right sm:ml-4">
                            <p class="text-xs text-gray-500 mb-1">{{ __('Total') }}</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ number_format($totalPrice, 2, ',', ' ') }} zł
                            </p>
                        </div>
                    </div>
                @endforeach

                {{-- Total Price --}}
                <div class="border-t border-gray-200 pt-4 mt-6">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold text-gray-900">{{ __('Total Service Cost') }}</span>
                        <span class="text-2xl font-bold text-indigo-600">
                            {{ number_format($orderCarpet->services->sum('pivot.total_price'), 2, ',', ' ') }} zł
                        </span>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                    </path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('No services added') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('No services have been added for this carpet yet.') }}</p>
                <div class="mt-4">
                    <a href="{{ route('order-carpets.edit', $orderCarpet) }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        {{ __('Add Services') }}
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>