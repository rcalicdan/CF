<div>
    <x-flash-session />
    <x-partials.dashboard.content-header :title="__('Zarządzanie zamówieniami')" />

    <div class="mb-4">
        {{-- Filter Toggle Header --}}
        <div class="flex items-center justify-between mb-3">
            <button wire:click="toggleAdvancedFilters" type="button"
                class="group inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg text-xs font-semibold text-blue-700 hover:from-blue-100 hover:to-indigo-100 hover:border-blue-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-sm hover:shadow">
                <svg class="w-4 h-4 mr-1.5 {{ $showAdvancedFilters ? 'rotate-180' : '' }} transition-transform duration-300"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                    </path>
                </svg>
                <span>{{ __('Filtry') }}</span>
                @if ($this->activeFiltersCount > 0)
                    <span
                        class="ml-1.5 inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full text-[10px] font-bold bg-blue-600 text-white">
                        {{ $this->activeFiltersCount }}
                    </span>
                @endif
            </button>

            @if ($this->activeFiltersCount > 0)
                <button wire:click="clearFilters" type="button"
                    class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-red-600 hover:text-white hover:bg-red-600 border border-red-300 hover:border-red-600 rounded-lg transition-all duration-200">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    {{ __('Wyczyść wszystko') }}
                </button>
            @endif
        </div>

        {{-- Filters Panel --}}
        <div
            class="transition-all duration-300 ease-in-out {{ $showAdvancedFilters ? 'opacity-100 max-h-[600px]' : 'opacity-0 max-h-0 overflow-hidden' }}">
            <div
                class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-xl shadow-md p-4 space-y-3">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    {{-- Creation Date Filter --}}
                    <div>
                        <label for="dateFilter" class="flex items-center text-xs font-semibold text-gray-700 mb-1">
                            <svg class="w-3 h-3 mr-1 text-blue-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ __('Data utworzenia') }}
                        </label>
                        <select wire:model.live="dateFilter" id="dateFilter"
                            class="block w-full px-2.5 py-1.5 text-xs bg-white border border-gray-300 rounded-lg shadow-sm hover:border-blue-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-medium text-gray-700 transition-all duration-200">
                            <option value="">{{ __('Cały czas') }}</option>
                            <option value="today">{{ __('Dzisiaj') }}</option>
                            <option value="yesterday">{{ __('Wczoraj') }}</option>
                            <option value="last_7_days">{{ __('Ostatnie 7 dni') }}</option>
                            <option value="last_30_days">{{ __('Ostatnie 30 dni') }}</option>
                            <option value="this_month">{{ __('Ten miesiąc') }}</option>
                            <option value="last_month">{{ __('Poprzedni miesiąc') }}</option>
                            <option value="this_year">{{ __('Ten rok') }}</option>
                            <option value="custom">{{ __('Zakres niestandardowy') }}</option>
                        </select>
                    </div>

                    {{-- Schedule Date Filter --}}
                    <div>
                        <label for="scheduleDateFilter"
                            class="flex items-center text-xs font-semibold text-gray-700 mb-1">
                            <svg class="w-3 h-3 mr-1 text-purple-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ __('Data realizacji') }}
                        </label>
                        <select wire:model.live="scheduleDateFilter" id="scheduleDateFilter"
                            class="block w-full px-2.5 py-1.5 text-xs bg-white border border-gray-300 rounded-lg shadow-sm hover:border-purple-400 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 font-medium text-gray-700 transition-all duration-200">
                            <option value="">{{ __('Cały czas') }}</option>
                            <option value="today">{{ __('Dzisiaj') }}</option>
                            <option value="yesterday">{{ __('Wczoraj') }}</option>
                            <option value="last_7_days">{{ __('Ostatnie 7 dni') }}</option>
                            <option value="last_30_days">{{ __('Ostatnie 30 dni') }}</option>
                            <option value="this_month">{{ __('Ten miesiąc') }}</option>
                            <option value="last_month">{{ __('Poprzedni miesiąc') }}</option>
                            <option value="this_year">{{ __('Ten rok') }}</option>
                            <option value="custom">{{ __('Zakres niestandardowy') }}</option>
                        </select>
                    </div>

                    {{-- Status Filter --}}
                    <div>
                        <label for="statusFilter" class="flex items-center text-xs font-semibold text-gray-700 mb-1">
                            <svg class="w-3 h-3 mr-1 text-green-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ __('Status zamówienia') }}
                        </label>
                        <select wire:model.live="statusFilter" id="statusFilter"
                            class="block w-full px-2.5 py-1.5 text-xs bg-white border border-gray-300 rounded-lg shadow-sm hover:border-green-400 focus:ring-2 focus:ring-green-500 focus:border-green-500 font-medium text-gray-700 transition-all duration-200">
                            <option value="">{{ __('Wszystkie statusy') }}</option>
                            @foreach ($orderStatuses as $status)
                                <option value="{{ $status->value }}">
                                    {{ \App\ActionService\EnumTranslationService::translate($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 mt-3">
                    {{-- Complaint Status Filter (full width) --}}
                    <div>
                        <label for="complaintStatus" class="flex items-center text-xs font-semibold text-gray-700 mb-1">
                            <svg class="w-3 h-3 mr-1 text-yellow-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            {{ __('Status reklamacji') }}
                        </label>
                        <select wire:model.live="complaintStatus" id="complaintStatus"
                            class="block w-full px-2.5 py-1.5 text-xs bg-white border border-gray-300 rounded-lg shadow-sm hover:border-yellow-400 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 font-medium text-gray-700 transition-all duration-200">
                            @foreach ($complaintStatuses as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Custom Date Ranges --}}
                @if ($dateFilter === 'custom' || $scheduleDateFilter === 'custom')
                    <div class="animate-fadeIn mt-3">
                        @if ($dateFilter === 'custom')
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-3">
                                <div class="flex items-center space-x-1.5 mb-2">
                                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span
                                        class="text-xs font-bold text-blue-800">{{ __('Zakres dat utworzenia') }}</span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label for="customStartDate"
                                            class="block text-xs font-medium text-gray-700 mb-1">
                                            {{ __('Data rozpoczęcia') }}
                                        </label>
                                        <input type="date" wire:model.live="customStartDate" id="customStartDate"
                                            class="block w-full px-2.5 py-1.5 text-xs bg-white border border-gray-300 rounded-lg shadow-sm hover:border-blue-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                    </div>
                                    <div>
                                        <label for="customEndDate"
                                            class="block text-xs font-medium text-gray-700 mb-1">
                                            {{ __('Data zakończenia') }}
                                        </label>
                                        <input type="date" wire:model.live="customEndDate" id="customEndDate"
                                            class="block w-full px-2.5 py-1.5 text-xs bg-white border border-gray-300 rounded-lg shadow-sm hover:border-blue-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($scheduleDateFilter === 'custom')
                            <div class="bg-purple-50 border border-purple-200 rounded-lg p-3">
                                <div class="flex items-center space-x-1.5 mb-2">
                                    <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span
                                        class="text-xs font-bold text-purple-800">{{ __('Zakres dat realizacji') }}</span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label for="customScheduleStartDate"
                                            class="block text-xs font-medium text-gray-700 mb-1">
                                            {{ __('Data rozpoczęcia') }}
                                        </label>
                                        <input type="date" wire:model.live="customScheduleStartDate"
                                            id="customScheduleStartDate"
                                            class="block w-full px-2.5 py-1.5 text-xs bg-white border border-gray-300 rounded-lg shadow-sm hover:border-purple-400 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
                                    </div>
                                    <div>
                                        <label for="customScheduleEndDate"
                                            class="block text-xs font-medium text-gray-700 mb-1">
                                            {{ __('Data zakończenia') }}
                                        </label>
                                        <input type="date" wire:model.live="customScheduleEndDate"
                                            id="customScheduleEndDate"
                                            class="block w-full px-2.5 py-1.5 text-xs bg-white border border-gray-300 rounded-lg shadow-sm hover:border-purple-400 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Active Filters Display --}}
                @if ($this->activeFiltersCount > 0)
                    <div class="pt-2 border-t border-gray-200">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="text-xs font-semibold text-gray-600">{{ __('Aktywne:') }}</span>

                            @if ($scheduleDateFilter)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gradient-to-r from-purple-500 to-purple-600 text-white shadow-sm">
                                    <svg class="w-2.5 h-2.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    {{ __('Realizacja:') }}
                                    @if ($scheduleDateFilter === 'custom' && ($customScheduleStartDate || $customScheduleEndDate))
                                        {{ $customScheduleStartDate ?: '...' }} →
                                        {{ $customScheduleEndDate ?: '...' }}
                                    @else
                                        {{ collect([
                                            'today' => __('Dzisiaj'),
                                            'yesterday' => __('Wczoraj'),
                                            'last_7_days' => __('Ostatnie 7 dni'),
                                            'last_30_days' => __('Ostatnie 30 dni'),
                                            'this_month' => __('Ten miesiąc'),
                                            'last_month' => __('Poprzedni miesiąc'),
                                            'this_year' => __('Ten rok'),
                                        ])->get($scheduleDateFilter) }}
                                    @endif
                                </span>
                            @endif

                            @if ($statusFilter)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gradient-to-r from-green-500 to-green-600 text-white shadow-sm">
                                    <svg class="w-2.5 h-2.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    {{ \App\ActionService\EnumTranslationService::translate(\App\Enums\OrderStatus::from($statusFilter)) }}
                                </span>
                            @endif

                            @if ($complaintStatus)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gradient-to-r from-yellow-500 to-yellow-600 text-white shadow-sm">
                                    <svg class="w-2.5 h-2.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    {{ $this->getComplaintStatusLabel() }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <x-data-table :data="$this->rows" :headers="$dataTable['headers']" :showActions="$dataTable['showActions']" :showSearch="$dataTable['showSearch']" :showCreate="$dataTable['showCreate']"
        :createRoute="$dataTable['createRoute']" :createButtonName="$dataTable['createButtonName']" :editRoute="$dataTable['editRoute']" :viewRoute="$dataTable['viewRoute']" :deleteAction="$dataTable['deleteAction']" :searchPlaceholder="$dataTable['searchPlaceholder']"
        :emptyMessage="$dataTable['emptyMessage']" :searchQuery="$search" :sortColumn="$sortColumn" :sortDirection="$sortDirection" :showBulkActions="$dataTable['showBulkActions']"
        :bulkDeleteAction="$dataTable['bulkDeleteAction']" :selectedRowsCount="$selectedRowsCount" :selectAll="$selectAll" :selectPage="$selectPage" :selectedRows="$selectedRows" />
</div>

@push('styles')
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.3s ease-out;
        }
    </style>
@endpush
