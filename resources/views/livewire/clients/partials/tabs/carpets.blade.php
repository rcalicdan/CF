<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900">{{ __('All Carpets') }}</h3>
        <div class="flex items-center space-x-2 text-sm text-gray-500">
            <span>{{ __('Total') }}: {{ $stats['total_carpets'] }} {{ __('carpets') }}</span>
        </div>
    </div>

    @if ($carpets->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach ($carpets as $carpet)
                <div
                    class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden">
                    <!-- Carpet Header -->
                    <div
                        class="bg-gradient-to-r from-blue-50 to-indigo-50 px-4 py-3 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                <span
                                    class="text-xs font-medium text-gray-900">{{ $carpet->reference_code }}</span>
                            </div>
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                              {{ $carpet->status === 'completed'
                                  ? 'bg-green-100 text-green-800'
                                  : ($carpet->status === 'in_progress'
                                      ? 'bg-blue-100 text-blue-800'
                                      : ($carpet->status === 'picked_up'
                                          ? 'bg-yellow-100 text-yellow-800'
                                          : 'bg-gray-100 text-gray-800')) }}">
                                {{ $carpet->status_label }}
                            </span>
                        </div>
                    </div>

                    <!-- Carpet Body -->
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-semibold text-gray-900">
                                {{ __('Order') }} #{{ $carpet->order->id }}
                            </h4>
                            <span
                                class="text-lg font-bold text-gray-900">{{ number_format($carpet->total_price, 2) }}
                                {{ __('PLN') }}</span>
                        </div>

                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-xs text-gray-600">
                                <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                                </svg>
                                {{ __('Dimensions') }}:
                                {{ $carpet->width }}×{{ $carpet->height }}m
                            </div>
                            <div class="flex items-center text-xs text-gray-600">
                                <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z" />
                                </svg>
                                {{ __('Area') }}: {{ $carpet->total_area }}m²
                            </div>
                            <div class="flex items-center text-xs text-gray-600">
                                <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                {{ __('Services') }}: {{ $carpet->services_count }}
                            </div>
                            @if ($carpet->measured_at)
                                <div class="flex items-center text-xs text-gray-600">
                                    <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-4 8V9" />
                                    </svg>
                                    {{ __('Measured') }}:
                                    {{ $carpet->measured_at->format('d M Y') }}
                                </div>
                            @endif
                        </div>

                        @if ($carpet->remarks)
                            <div class="mb-4">
                                <p class="text-xs text-gray-500 bg-gray-50 rounded p-2">
                                    <span class="font-medium">{{ __('Note') }}:</span>
                                    {{ Str::limit($carpet->remarks, 60) }}
                                </p>
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="flex space-x-2">
                            <a href="{{ route('order-carpets.show', $carpet) }}"
                                class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-colors duration-200">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                {{ __('View') }}
                            </a>
                            @if ($carpet->hasValidQrCode())
                                <a href="{{ $carpet->qr_code_url }}" target="_blank"
                                    class="inline-flex items-center justify-center px-3 py-2 bg-green-100 hover:bg-green-200 text-green-700 text-xs font-medium rounded-lg transition-colors duration-200">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($carpets->hasPages())
            <div class="mt-8">
                {{ $carpets->links() }}
            </div>
        @endif
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('No carpets found') }}</h3>
            <p class="mt-2 text-gray-500">{{ __('This client doesn\'t have any carpets yet.') }}</p>
        </div>
    @endif
</div>