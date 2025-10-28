<div class="space-y-6 sm:space-y-8">
    <!-- Complaint Statistics Box -->
    <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
        <!-- Heading -->
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Statystyki reklamacji</h2>

        <!-- Header with Period Selector and Download Button -->
        <div class="flex items-center space-x-4 flex-wrap gap-4 mb-6">
            <!-- Period Type Toggle -->
            <div class="flex items-center space-x-2">
                <label class="text-sm font-medium text-gray-700">Typ okresu:</label>
                <div class="flex bg-gray-100 rounded-lg p-1">
                    <button wire:click="$set('periodType', 'days')"
                        class="px-3 py-1 text-sm rounded-md transition-colors {{ $periodType === 'days' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-600 hover:text-gray-800' }}">
                        Dni
                    </button>
                    <button wire:click="$set('periodType', 'month')"
                        class="px-3 py-1 text-sm rounded-md transition-colors {{ $periodType === 'month' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-600 hover:text-gray-800' }}">
                        Miesiąc
                    </button>
                </div>
            </div>

            @if ($periodType === 'days')
                <!-- Days Period Selector -->
                <div class="flex items-center space-x-2">
                    <label for="period" class="text-sm font-medium text-gray-700">Okres:</label>
                    <select wire:model.live="selectedPeriod" id="period"
                        class="text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="7">Ostatnie 7 dni</option>
                        <option value="30">Ostatnie 30 dni</option>
                        <option value="90">Ostatnie 3 miesiące</option>
                    </select>
                </div>
            @else
                <!-- Month Selector -->
                <div class="flex items-center space-x-2">
                    <label for="month" class="text-sm font-medium text-gray-700">Miesiąc:</label>
                    <select wire:model.live="selectedMonth" id="month"
                        class="text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Wybierz miesiąc</option>
                        @foreach ($this->availableMonths as $month)
                            <option value="{{ $month['value'] }}">{{ $month['label'] }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

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
                            @if ($periodType === 'month' && $selectedMonth)
                                <span
                                    class="text-xs text-gray-500">({{ $this->getPolishMonth(Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)) }})</span>
                            @endif
                        </button>
                        <button wire:click="generateCsvReport"
                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Szczegółowy CSV
                            @if ($periodType === 'month' && $selectedMonth)
                                <span
                                    class="text-xs text-gray-500">({{ $this->getPolishMonth(Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)) }})</span>
                            @endif
                        </button>
                        <button wire:click="generateSummaryCsvReport"
                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Statystyki CSV
                            @if ($periodType === 'month' && $selectedMonth)
                                <span
                                    class="text-xs text-gray-500">({{ $this->getPolishMonth(Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)) }})</span>
                            @endif
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

        <!-- Overview Cards -->
        @include('livewire.complaints.partials.overview-cards')

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 sm:gap-8 mt-6">
            <!-- Recent Order Complaints List -->
            @include('livewire.complaints.partials.recent-complaints')

            <!-- Order Status Summary -->
            @include('livewire.complaints.partials.order-status-summary')
        </div>

        <!-- Action Items for Order Complaints -->
        @include('livewire.complaints.partials.action-items')
    </div>

    <!-- Include simplified assets -->
    @include('livewire.complaints.assets')
</div>
