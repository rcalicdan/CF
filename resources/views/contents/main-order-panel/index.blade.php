<x-layouts.app title="Aladynos - Panel główny">
    <x-partials.dashboard.content-header title="Panel główny" />
    <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-10 pt-1 pb-6 space-y-6">
        @livewire('dashboard.order-panel')
    </div>
</x-layouts.app>
