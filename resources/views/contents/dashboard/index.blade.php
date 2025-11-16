<x-layouts.app title="Aladynos - Panel główny">
    <x-partials.dashboard.content-header title="Panel główny" />

    <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-10 py-8 space-y-8">
        @livewire('complaints.complaint-statistics')
        @livewire('laundry-throughput.charts')
        @livewire('processing-costs.charts')
    </div>
</x-layouts.app>
