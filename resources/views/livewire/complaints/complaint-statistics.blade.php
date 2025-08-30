<div class="space-y-6 sm:space-y-8">
    <!-- Header with Period Selector and Download Button -->
    <div class="flex items-center space-x-4">
        <div class="flex items-center space-x-2">
            <label for="period" class="text-sm font-medium text-gray-700">Okres:</label>
            <select wire:model.live="selectedPeriod" id="period"
                class="text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                <option value="7">Ostatnie 7 dni</option>
                <option value="30">Ostatnie 30 dni</option>
                <option value="90">Ostatnie 3 miesiące</option>
            </select>
        </div>

        <!-- Dropdown for export options -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" type="button"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Eksportuj Raport
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="open" @click.away="open = false" x-transition
                class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-10">
                <div class="py-1">
                    <button wire:click="generatePdfReport"
                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        Raport PDF
                    </button>
                    <button wire:click="generateCsvReport"
                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Szczegółowy CSV
                    </button>
                    <button wire:click="generateSummaryCsvReport"
                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Statystyki CSV
                    </button>
                    <button wire:click="generateWeeklyTrendCsv"
                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        Trend Tygodniowy CSV
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Rest of your existing content -->
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
