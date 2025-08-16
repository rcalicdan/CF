<div id="qr-code-print-template" class="hidden">
    <div class="print-item">
        <div class="qr-container">
            <img src="{{ $orderCarpet->qr_code_url }}" alt="QR Code" />
        </div>
        <div class="carpet-info">
            <h3>{{ __('Carpet #') }}{{ $orderCarpet->id }}</h3>
            <p>{{ $orderCarpet->order->client->full_name ?? 'N/A' }}</p>
            <p>{{ $orderCarpet->created_at->format('M d, Y') }}</p>
        </div>
    </div>
</div>
