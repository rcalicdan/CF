@php
    $statusConfig = [
        'pending' => [
            'border' => 'border-l-8 border-yellow-500',
            'label' => 'Oczekujące',
            'labelColor' => 'text-yellow-700',
        ],
        'in-progress' => [
            'border' => 'border-l-8 border-blue-500',
            'label' => 'W realizacji',
            'labelColor' => 'text-blue-700',
        ],
        'completed' => [
            'border' => 'border-l-8 border-green-500',
            'label' => 'Zrealizowane',
            'labelColor' => 'text-green-700',
        ],
    ];

    $config = $statusConfig[$cardType];
    $isPaid = $order->orderPayment && $order->orderPayment->paid;
@endphp

<div
    class="bg-white rounded-lg shadow-sm border border-gray-200 {{ $config['border'] }} hover:shadow-md transition-all duration-200">
    <div class="p-4">
        <!-- Header -->
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center space-x-2">
                <span class="text-xs font-bold text-gray-900">Nr zam: {{ $order->id }}</span>
                <span class="text-xs font-semibold {{ $config['labelColor'] }}">{{ $config['label'] }}</span>
            </div>
            <span class="text-xs text-gray-500">{{ $order->schedule_date->format('d.m.Y') }}</span>
        </div>

        <!-- Services/Products Table -->
        <div class="mb-3">
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-1 font-semibold text-gray-700">Produkt</th>
                        <th class="text-center py-1 font-semibold text-gray-700">Ilość</th>
                        <th class="text-right py-1 font-semibold text-gray-700">Brutto</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->orderServices as $orderService)
                        <tr class="border-b border-gray-100">
                            <td class="py-1.5 text-gray-700">{{ $orderService->service->name ?? 'N/A' }}</td>
                            <td class="py-1.5 text-center text-gray-600">{{ $orderService->quantity }} szt</td>
                            <td class="py-1.5 text-right text-gray-900">
                                {{ number_format($orderService->total_price, 2) }} PLN</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-2 text-center text-gray-500 text-xs">Brak usług</td>
                        </tr>
                    @endforelse

                    <!-- Carpets with Services -->
                    @foreach ($order->orderCarpets as $carpet)
                        <tr class="border-b border-gray-100">
                            <td class="py-1.5 text-gray-700">
                                <div class="flex items-center space-x-1">
                                    <span>Dywan ({{ $carpet->width }}x{{ $carpet->height }}cm)</span>
                                    @if ($carpet->services->count() > 0)
                                        <div class="relative group">
                                            <svg class="w-3 h-3 text-indigo-500 cursor-help" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                            <div
                                                class="absolute left-0 bottom-full mb-2 hidden group-hover:block z-10 w-48 bg-gray-900 text-white text-xs rounded-lg py-2 px-3 shadow-lg">
                                                <p class="font-semibold mb-1">Usługi dywanu:</p>
                                                @foreach ($carpet->services as $service)
                                                    <p>• {{ $service->name }}</p>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="py-1.5 text-center text-gray-600">1 szt</td>
                            <td class="py-1.5 text-right text-gray-900">{{ number_format($carpet->total_price, 2) }}
                                PLN</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Total & Payment Status -->
        <div class="flex items-center justify-between py-2 border-t border-gray-200 mb-3">
            <div class="flex items-center space-x-2">
                <span class="text-xs font-medium text-gray-700">Do zapłaty:</span>
                <span
                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $isPaid ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        @if ($isPaid)
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"></path>
                        @else
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                clip-rule="evenodd"></path>
                        @endif
                    </svg>
                    {{ $isPaid ? 'Opłacone' : 'Oczekuje' }}
                </span>
            </div>
            <span class="text-sm font-bold text-gray-900">{{ number_format($order->total_amount, 2) }} PLN</span>
        </div>

        <!-- Progress Bar (for in-progress orders) -->
        @if ($cardType === 'in-progress')
            @php
                $totalCarpets = $order->orderCarpets->count();
                $completedCarpets = $order->orderCarpets->where('status', 'completed')->count();
                $progress = $totalCarpets > 0 ? ($completedCarpets / $totalCarpets) * 100 : 0;
            @endphp
            <div class="mb-3 p-2 bg-blue-50 border border-blue-200 rounded">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-semibold text-blue-700">Status realizacji</span>
                    <span class="text-xs font-bold text-blue-700">{{ round($progress) }}%</span>
                </div>
                <div class="w-full bg-blue-100 rounded-full h-1.5">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-1.5 rounded-full transition-all duration-300"
                        style="width: {{ $progress }}%"></div>
                </div>
            </div>
        @endif

        <!-- Completion Info (for completed orders) -->
        @if ($cardType === 'completed' && $order->orderDeliveryConfirmation)
            <div class="mb-3 p-2 bg-green-50 border border-green-200 rounded">
                <div class="flex items-center space-x-2 text-xs text-green-700">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span class="font-medium">Zakończono:
                        {{ $order->orderDeliveryConfirmation->delivered_at->format('d.m.Y, H:i') }}</span>
                </div>
            </div>
        @endif

        <!-- Client and Driver Info -->
        <div class="grid grid-cols-2 gap-3 mb-3 text-xs">
            <div>
                <p class="text-gray-500 mb-1">Klient:</p>
                <p class="font-semibold text-gray-900">{{ $order->client->full_name ?? 'N/A' }}</p>
                <p class="text-gray-600">{{ $order->client->city ?? '' }}</p>
            </div>
            <div>
                <p class="text-gray-500 mb-1">Kierowca:</p>
                @if ($order->driver)
                    <p class="font-semibold text-indigo-600">{{ $order->driver_full_name }}</p>
                @else
                    <p class="text-xs text-orange-600 font-medium">Nie przypisano</p>
                @endif
            </div>
        </div>

        <!-- Warning for missing driver -->
        @if (!$order->driver && $cardType === 'pending')
            <div class="mb-3 p-2 bg-orange-50 border border-orange-200 rounded">
                <div class="flex items-center space-x-2 text-xs text-orange-700">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span class="font-medium">Brak przypisanego kierowcy</span>
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="flex items-center space-x-2">
            @if (!$order->driver && $cardType === 'pending')
                <button wire:click="$dispatch('assign-driver', { orderId: {{ $order->id }} })"
                    class="flex-1 px-3 py-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white text-xs font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-200">
                    Dodaj kierowcę
                </button>
            @endif
            <a href="{{ route('orders.show', $order->id) }}"
                class="flex-1 px-3 py-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-xs font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-200 text-center">
                Szczegóły
            </a>
            <button
                class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z">
                    </path>
                </svg>
            </button>
        </div>

        <!-- Created By -->
        <div class="mt-3 pt-2 border-t border-gray-100">
            <p class="text-xs text-gray-500">Przyjęcie przez: <span
                    class="font-medium text-gray-700">{{ $order->user->name ?? 'N/A' }}</span></p>
        </div>
    </div>
</div>
