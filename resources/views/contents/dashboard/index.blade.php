<x-layouts.app title="LaundryManager - Panel główny">
    <x-partials.dashboard.content-header title="Analityka panelu główny" />

    <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-10 py-8 space-y-8">
        @include('contents.dashboard.partials.stats-overview')
        @include('contents.dashboard.partials.main-content')
        @include('contents.dashboard.partials.quick-actions')
        @livewire('laundry-throughput.charts')
        @livewire('processing-costs.charts')   
    </div>
</x-layouts.app>