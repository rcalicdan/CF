<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <div
        class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-100 hover:shadow-xl transition-all duration-300">
        <div class="bg-gradient-to-r from-emerald-50 to-teal-50 px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-semibold text-gray-900 flex items-center">
                <div
                    class="w-8 h-8 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                        </path>
                    </svg>
                </div>
                {{ __('Payment Information') }}
            </h3>
        </div>
        <div class="px-6 py-6">
            @if ($order->orderPayment)
                <div class="space-y-6">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <dt class="text-sm font-medium text-gray-500">{{ __('Payment Status') }}</dt>
                            <dd class="mt-1">
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold shadow-sm
                                   @if ($order->orderPayment->status === 'paid') bg-gradient-to-r from-green-400 to-green-500 text-white
                                   @elseif($order->orderPayment->status === 'pending') bg-gradient-to-r from-yellow-400 to-yellow-500 text-white
                                   @else bg-gradient-to-r from-red-400 to-red-500 text-white @endif">
                                    <div class="w-2 h-2 rounded-full bg-white opacity-75 mr-2"></div>
                                    {{ __($order->orderPayment->status_label) }}
                                </span>
                            </dd>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <dt class="text-sm font-medium text-gray-500">{{ __('Payment Method') }}</dt>
                            <dd class="mt-1 text-base font-semibold text-gray-900">
                                {{ $order->orderPayment->payment_method ?? __('Not specified') }}</dd>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <dt class="text-sm font-medium text-gray-500">{{ __('Amount Paid') }}</dt>
                            <dd class="mt-1 text-xl font-bold text-green-600">
                                {{ number_format($order->total_amount, 2) }} PLN</dd>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <dt class="text-sm font-medium text-gray-500">{{ __('Payment Date') }}</dt>
                            <dd class="mt-1 text-base text-gray-900">
                                {{ $order->orderPayment->paid_at ? $order->orderPayment->paid_at->format('d.m.Y H:i') : __('Not paid yet') }}
                            </dd>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                            </path>
                        </svg>
                    </div>
                    <p class="text-lg font-medium text-gray-500">{{ __('No Payment Information') }}</p>
                    <p class="text-sm text-gray-400 mt-1">{{ __('Payment details will appear here once processed') }}
                    </p>
                </div>
            @endif
        </div>
    </div>

    <div
        class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-100 hover:shadow-xl transition-all duration-300">
        <div class="bg-gradient-to-r from-orange-50 to-amber-50 px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-semibold text-gray-900 flex items-center">
                <div
                    class="w-8 h-8 bg-gradient-to-br from-orange-500 to-amber-600 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8l6 6 10-10"></path>
                    </svg>
                </div>
                {{ __('Delivery Confirmation') }}
            </h3>
        </div>
        <div class="px-6 py-6">
            @if ($order->orderDeliveryConfirmation)
                <div class="space-y-6">
                    {{-- Delivery Status --}}
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <dt class="text-sm font-medium text-gray-500">{{ __('Delivery Status') }}</dt>
                            <dd class="mt-1">
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-gradient-to-r from-green-400 to-green-500 text-white shadow-sm">
                                    <div class="w-2 h-2 rounded-full bg-white opacity-75 mr-2"></div>
                                    {{ __('Delivered') }}
                                </span>
                            </dd>
                        </div>
                    </div>

                    {{-- Delivered Date --}}
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <dt class="text-sm font-medium text-gray-500">{{ __('Delivered Date') }}</dt>
                            <dd class="mt-1 text-base font-semibold text-gray-900">
                                {{ isset($order->orderDeliveryConfirmation->delivered_at) ? $order->orderDeliveryConfirmation->delivered_at->format('d.m.Y H:i') : $order->updated_at->format('d.m.Y H:i') }}
                            </dd>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <dt class="text-sm font-medium text-gray-500">{{ __('Confirmation Type') }}</dt>
                            <dd class="mt-1 text-base font-semibold text-gray-900">
                                {{ $order->orderDeliveryConfirmation->confirmation_type_label ?? __('Unknown') }}
                            </dd>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                                @if ($order->orderDeliveryConfirmation->confirmation_type === 'signature')
                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                        </path>
                                    </svg>
                                @elseif($order->orderDeliveryConfirmation->confirmation_type === 'data')
                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                @endif
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <dt class="text-sm font-medium text-gray-500">
                                @if ($order->orderDeliveryConfirmation->confirmation_type === 'signature')
                                    {{ __('Signature') }}
                                @elseif($order->orderDeliveryConfirmation->confirmation_type === 'data')
                                    {{ __('Confirmation Data') }}
                                @else
                                    {{ __('Details') }}
                                @endif
                            </dt>
                            <dd class="mt-1 text-base text-gray-900">
                                @if (
                                    $order->orderDeliveryConfirmation->confirmation_type === 'signature' &&
                                        $order->orderDeliveryConfirmation->signature_url)
                                    <div class="mt-2">
                                        <img src="{{ asset($order->orderDeliveryConfirmation->signature_url) }}"
                                            alt="{{ __('Delivery Signature') }}"
                                            class="max-w-full h-auto border rounded-lg shadow-sm">
                                    </div>
                                @elseif($order->orderDeliveryConfirmation->confirmation_type === 'data')
                                    @if ($order->orderDeliveryConfirmation->confirmation_data)
                                        <div
                                            class="bg-gray-50 rounded-lg p-3 border text-sm font-mono whitespace-pre-wrap break-words">
                                            {{ $order->orderDeliveryConfirmation->confirmation_data }}
                                        </div>
                                    @else
                                        <span
                                            class="text-gray-400 italic">{{ __('No confirmation data provided') }}</span>
                                    @endif
                                @else
                                    <span
                                        class="text-gray-400 italic">{{ __('No confirmation details available') }}</span>
                                @endif
                            </dd>
                        </div>
                    </div>

                    {{-- Delivery Notes --}}
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <dt class="text-sm font-medium text-gray-500">{{ __('Delivery Notes') }}</dt>
                            <dd class="mt-1 text-base text-gray-900">
                                @if ($order->orderDeliveryConfirmation->notes)
                                    <div class="bg-gray-50 rounded-lg p-3 border">
                                        {{ $order->orderDeliveryConfirmation->notes }}
                                    </div>
                                @else
                                    <span class="text-gray-400 italic">{{ __('No additional notes') }}</span>
                                @endif
                            </dd>
                        </div>
                    </div>
                </div>
            @else
                {{-- Pending Delivery --}}
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414a1 1 0 00-.707-.293H8">
                            </path>
                        </svg>
                    </div>
                    <p class="text-lg font-medium text-gray-500">{{ __('Pending Delivery') }}</p>
                    <p class="text-sm text-gray-400 mt-1">
                        {{ __('Delivery confirmation will appear here once completed') }}
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
