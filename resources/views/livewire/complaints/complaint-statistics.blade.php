<div class="space-y-6 sm:space-y-8">
    <!-- Header with Period Selector -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Statystyki Skarg</h2>
            <p class="text-sm text-gray-600 mt-1">Przegląd i analiza zgłoszonych problemów</p>
        </div>
        
        <div class="flex items-center space-x-2">
            <label for="period" class="text-sm font-medium text-gray-700">Okres:</label>
            <select wire:model.live="selectedPeriod" id="period" class="text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                <option value="7">Ostatnie 7 dni</option>
                <option value="30">Ostatnie 30 dni</option>
                <option value="90">Ostatnie 3 miesiące</option>
            </select>
        </div>
    </div>

    <!-- Overview Cards -->
    @include('livewire.complaints.partials.overview-cards')

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 sm:gap-8">
        <!-- Recent Complaints List -->
        @include('livewire.complaints.partials.recent-complaints')

        <!-- Right Sidebar Analytics -->
        <div class="space-y-6">
            <!-- Status Distribution -->
            @include('livewire.complaints.partials.status-distribution-chart')
            
            <!-- Category Distribution -->
            @include('livewire.complaints.partials.category-distribution')
        </div>
    </div>

    <!-- Weekly Trend Chart -->
    @include('livewire.complaints.partials.weekly-trend')

    <!-- Action Items -->
    @include('livewire.complaints.partials.action-items')

    <!-- Include Assets -->
    @include('livewire.complaints.assets')
</div>