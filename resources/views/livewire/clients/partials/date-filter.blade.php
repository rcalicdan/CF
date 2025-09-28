<!-- Date Filter Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <div class="p-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">{{ __('Filter & Reports') }}</h3>
            <button wire:click="toggleDateFilter"
                class="inline-flex items-center px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors duration-200 text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-4 8V9m-6 4v6a2 2 0 002 2h12a2 2 0 002-2v-6M8 11H4" />
                </svg>
                {{ $showDateFilter ? __('Hide Date Filter') : __('Show Date Filter') }}
            </button>
        </div>
    </div>

    @if ($showDateFilter)
        <div class="p-4 bg-gray-50">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <!-- Date From -->
                <div>
                    <label for="dateFrom" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('From Date') }}
                    </label>
                    <input type="date" wire:model.live="dateFrom" id="dateFrom"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>

                <!-- Date To -->
                <div>
                    <label for="dateTo" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('To Date') }}
                    </label>
                    <input type="date" wire:model.live="dateTo" id="dateTo"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>

                <!-- Actions -->
                <div class="flex space-x-2">
                    <button wire:click="applyDateFilter"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        {{ __('Apply Filter') }}
                    </button>

                    @if ($dateFrom || $dateTo)
                        <button wire:click="clearDateFilter"
                            class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors duration-200 text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            {{ __('Clear') }}
                        </button>
                    @endif
                </div>

                <!-- Generate Report -->
                <div class="flex justify-end">
                    <button wire:click="generatePdfReport"
                        class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors duration-200 text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        @if ($dateFrom || $dateTo)
                            {{ __('Generate Filtered Report') }}
                        @else
                            {{ __('Generate Full Report') }}
                        @endif
                    </button>
                </div>
            </div>

            <!-- Active Filters Display -->
            @if ($dateFrom || $dateTo)
                <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm font-medium text-blue-800">{{ __('Active Date Filter') }}:</span>
                        <span class="text-sm text-blue-700 ml-2">
                            @if ($dateFrom && $dateTo)
                                {{ Carbon\Carbon::parse($dateFrom)->format('d M Y') }} -
                                {{ Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                            @elseif($dateFrom)
                                {{ __('From') }} {{ Carbon\Carbon::parse($dateFrom)->format('d M Y') }}
                            @else
                                {{ __('Until') }} {{ Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                            @endif
                        </span>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>