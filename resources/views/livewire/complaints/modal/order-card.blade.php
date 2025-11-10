<div class="group relative bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg p-4 transition-all duration-200">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <div class="flex items-center gap-2 mb-2">
                <span class="font-semibold text-gray-900">Zamówienie #{{ $order['order_id'] }}</span>
                
                @php
                    $statusInfo = $this->getStatusColor($order['status']);
                @endphp
                <span class="{{ $statusInfo['bg'] }} {{ $statusInfo['text'] }} px-2 py-1 rounded text-xs font-medium">
                    {{ $statusInfo['label'] }}
                </span>
                
                @php
                    $priority = $order['priority'];
                @endphp
                @if ($priority['level'] === 'high')
                    <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-medium">
                        {{ $priority['label'] }}
                    </span>
                @elseif($priority['level'] === 'medium')
                    <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-medium">
                        {{ $priority['label'] }}
                    </span>
                @else
                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">
                        {{ $priority['label'] }}
                    </span>
                @endif
            </div>
            
            <p class="text-sm text-gray-600 mb-2">{{ $order['details'] }}</p>
            
            <div class="flex flex-wrap gap-4 text-xs text-gray-500">
                <span>Klient: <strong>{{ $order['client_name'] }}</strong></span>
                <span>QR: <strong>{{ $order['carpet_qr'] }}</strong></span>
                <span>Data: <strong>{{ $order['created_at']->format('d.m.Y H:i') }}</strong></span>
                <span>Wartość: <strong>{{ number_format($order['total_amount'], 2) }} PLN</strong></span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="ml-4 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex gap-2">
            <a href="{{ route('orders.show', $order['order_id']) }}"
                class="inline-flex items-center px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                title="Zobacz szczegóły">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span class="ml-1">Zobacz</span>
            </a>
            <a href="{{ route('orders.edit', $order['order_id']) }}"
                class="inline-flex items-center px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                title="Edytuj zamówienie">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span class="ml-1">Edytuj</span>
            </a>
        </div>
    </div>
</div>