<!-- Tab Content -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    @if ($activeTab === 'overview')
        @include('livewire.clients.partials.tabs.overview')
    @elseif($activeTab === 'orders')
        @include('livewire.clients.partials.tabs.orders')
    @elseif($activeTab === 'carpets')
        @include('livewire.clients.partials.tabs.carpets')
    @elseif($activeTab === 'location')
        @include('livewire.clients.partials.tabs.location')
    @endif
</div>