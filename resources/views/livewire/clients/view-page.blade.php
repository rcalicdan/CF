<div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50">
    @include('livewire.clients.partials.header')
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('livewire.clients.partials.stats-cards')
        @include('livewire.clients.partials.date-filter')
        @include('livewire.clients.partials.tab-navigation')
        @include('livewire.clients.partials.tab-content')
    </div>
</div>

@include('livewire.clients.assets')