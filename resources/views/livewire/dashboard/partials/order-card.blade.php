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
    class="bg-white rounded-lg shadow-sm border border-gray-200 {{ $config['border'] }} hover:shadow-md transition-all duration-200 h-full flex flex-col">
    
    <div class="p-4 flex flex-col h-full">
        
        <div>
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-2">
                    <span class="text-xs font-bold text-gray-900">Nr zam: {{ $order->id }}</span>
                    <span class="text-xs font-semibold {{ $config['labelColor'] }}">{{ $config['label'] }}</span>
                </div>
                <span class="text-xs text-gray-500">{{ $order->schedule_date->format('d.m.Y') }}</span>
            </div>

            <div class="mb-3 border border-gray-100 rounded-lg overflow-hidden">
                <div class="overflow-y-auto max-h-40 scrollbar-thin scrollbar-thumb-gray-200 scrollbar-track-gray-50">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="sticky top-0 bg-white z-10 text-left py-2 px-2 font-semibold text-gray-700 shadow-sm">Produkt</th>
                                <th class="sticky top-0 bg-white z-10 text-center py-2 px-2 font-semibold text-gray-700 shadow-sm">Ilość</th>
                                <th class="sticky top-0 bg-white z-10 text-right py-2 px-2 font-semibold text-gray-700 shadow-sm">Brutto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($order->orderServices as $orderService)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="py-2 px-2 text-gray-700">{{ $orderService->service->name ?? 'N/A' }}</td>
                                    <td class="py-2 px-2 text-center text-gray-600">{{ $orderService->quantity }} szt</td>
                                    <td class="py-2 px-2 text-right text-gray-900">
                                        {{ number_format($orderService->total_price, 2) }} PLN</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-3 text-center text-gray-500 text-xs italic">Brak usług</td>
                                </tr>
                            @endforelse

                            @foreach ($order->orderCarpets as $carpet)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="py-2 px-2 text-gray-700">
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
                                                        class="fixed ml-4 hidden group-hover:block z-50 w-48 bg-gray-900 text-white text-xs rounded-lg py-2 px-3 shadow-xl pointer-events-none">
                                                        <p class="font-semibold mb-1">Usługi dywanu:</p>
                                                        @foreach ($carpet->services as $service)
                                                            <p>• {{ $service->name }}</p>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-2 px-2 text-center text-gray-600">1 szt</td>
                                    <td class="py-2 px-2 text-right text-gray-900">{{ number_format($carpet->total_price, 2) }}
                                        PLN</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

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

            <div class="grid grid-cols-2 gap-4 mb-3 text-xs">
                <div class="space-y-1">
                    <p class="text-gray-500">Klient:</p>
                    <p class="font-semibold text-gray-900 truncate" title="{{ $order->client->full_name ?? '' }}">
                        {{ $order->client->full_name ?? 'N/A' }}
                    </p>
                    <p class="text-gray-600 truncate" title="{{ $order->client->city ?? '' }}">
                        {{ $order->client->city ?? '' }}
                    </p>
                    @if($order->client && $order->client->phone_number)
                        <a href="tel:{{ $order->client->phone_number }}" 
                        class="flex items-center text-gray-600 hover:text-indigo-600 transition-colors group mt-1">
                            <svg class="w-3 h-3 mr-1.5 text-gray-400 group-hover:text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <span class="truncate">{{ $order->client->phone_number }}</span>
                        </a>
                    @endif
                    @if($order->client && $order->client->email)
                        <a href="mailto:{{ $order->client->email }}" 
                        class="flex items-center text-gray-600 hover:text-indigo-600 transition-colors group">
                            <svg class="w-3 h-3 mr-1.5 text-gray-400 group-hover:text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span class="truncate" title="{{ $order->client->email }}">{{ $order->client->email }}</span>
                        </a>
                    @endif
                </div>

                <div class="space-y-1">
                    <p class="text-gray-500">Kierowca:</p>
                    @if ($order->driver)
                        <p class="font-semibold text-indigo-600 truncate" title="{{ $order->driver_full_name }}">
                            {{ $order->driver_full_name }}
                        </p>
                    @else
                        <p class="text-xs text-orange-600 font-medium">Nie przypisano</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-auto">
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

            <div class="mt-3 pt-2 border-t border-gray-100">
                <p class="text-xs text-gray-500">Przyjęcie przez: <span
                        class="font-medium text-gray-700">{{ $order->user->name ?? 'N/A' }}</span></p>
            </div>
        </div>
    </div>
</div>
