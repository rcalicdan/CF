<div>
    <x-flash-session />
    <x-partials.dashboard.content-header :title="__('Clients Management')" />

    <!-- Monthly Report Generation Section -->
    <div class="bg-white rounded-lg shadow-sm mb-6 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('Generowanie raportów miesięcznych') }}</h3>
                <p class="text-sm text-gray-600">
                    {{ __('Generuj kompleksowe raporty dla wszystkich klientów z zamówieniami w wybranym miesiącu') }}
                </p>
            </div>

            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                <div class="flex space-x-2">
                    <select wire:model.live="selectedMonth"
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('Wybierz miesiąc') }}</option>
                        @foreach (range(1, 12) as $month)
                            <option value="{{ $month }}">
                                {{ \Carbon\Carbon::create()->month($month)->locale('pl')->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>

                    <select wire:model.live="selectedYear"
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('Wybierz rok') }}</option>
                        @foreach (range(date('Y'), date('Y') - 5) as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex space-x-2">
                    <button wire:click="generateMonthlyPdfReport"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        {{ __('Raport PDF') }}
                    </button>

                    <button wire:click="generateMonthlyCsvReport"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        {{ __('Raport CSV') }}
                    </button>
                </div>
            </div>
        </div>

        @if ($selectedMonth && $selectedYear)
            <div class="mt-4 p-3 bg-blue-50 rounded-md">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm font-medium text-blue-800">
                        {{ __('Wybrany okres') }}:
                        {{ \Carbon\Carbon::create($selectedYear, $selectedMonth)->locale('pl')->translatedFormat('F Y') }}
                        @if ($clientsCount > 0)
                            ({{ $clientsCount }} {{ __('klientów z zamówieniami') }})
                        @else
                            ({{ __('Brak klientów z zamówieniami') }})
                        @endif
                    </span>
                </div>
            </div>
        @endif
    </div>


    <x-data-table :data="$this->rows" :headers="$dataTable['headers']" :showActions="$dataTable['showActions']" :showSearch="$dataTable['showSearch']" :showCreate="$dataTable['showCreate']"
        :createRoute="$dataTable['createRoute']" :createButtonName="$dataTable['createButtonName']" :editRoute="$dataTable['editRoute']" :viewRoute="$dataTable['viewRoute']" :deleteAction="$dataTable['deleteAction']" :searchPlaceholder="$dataTable['searchPlaceholder']"
        :emptyMessage="$dataTable['emptyMessage']" :searchQuery="$search" :sortColumn="$sortColumn" :sortDirection="$sortDirection" :showBulkActions="$dataTable['showBulkActions']"
        :bulkDeleteAction="$dataTable['bulkDeleteAction']" :selectedRowsCount="$selectedRowsCount" :selectAll="$selectAll" :selectPage="$selectPage" :selectedRows="$selectedRows" />
</div>
