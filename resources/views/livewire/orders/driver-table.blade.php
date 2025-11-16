<div>
    <x-flash-session />
    <x-partials.dashboard.content-header :title="__('Orders Management by Driver')" />
    <div class="bg-white rounded-lg shadow-md border border-gray-200 mb-6 overflow-visible">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-7 h-7 bg-white/20 rounded-md flex items-center justify-center">
                        <i class="fas fa-filter text-white text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-white">{{ __('Filter Orders') }}</h3>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="text-xs text-blue-100 bg-white/10 px-2 py-1 rounded">
                        {{ $this->rows->total() }} {{ __('results') }}
                    </div>
                    <button wire:click="clearFilters"
                        class="px-3 py-1 bg-white/20 hover:bg-white/30 text-white rounded text-xs font-medium transition-all duration-200">
                        <i class="fas fa-times mr-1"></i>{{ __('Clear') }}
                    </button>
                </div>
            </div>
        </div>
        <div class="p-4 overflow-visible">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-7 gap-3 overflow-visible">
                <div class="space-y-1 relative overflow-visible lg:col-span-2" x-data="{
                    open: false,
                    init() {
                        this.$watch('$wire.showDriverDropdown', value => {
                            this.open = value;
                        });
                    }
                }">
                    <label class="block text-xs font-medium text-gray-600">
                        <i class="fas fa-user-tie mr-1 text-blue-500"></i>{{ __('Driver') }}
                    </label>
                    @if (auth()->user()->isDriver())
                        <div
                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-gray-100 text-gray-600">
                            {{ $selectedDriverName }}
                        </div>
                    @else
                        @if ($selectedDriverName)
                            <div
                                class="flex items-center w-full px-3 py-2 text-sm rounded-lg border border-blue-300 bg-blue-50">
                                <span class="flex-1 text-gray-900">{{ $selectedDriverName }}</span>
                                <button wire:click="clearDriverSelection" class="ml-2 text-gray-400 hover:text-red-500">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>
                        @else
                            <div class="relative">
                                <input type="text" wire:model.live.debounce.300ms="driverSearch"
                                    placeholder="{{ __('Search drivers...') }}"
                                    class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:border-blue-500 focus:ring-0 transition-colors hover:border-gray-400"
                                    @focus="open = true; $wire.set('showDriverDropdown', true)"
                                    @input="open = true; $wire.set('showDriverDropdown', true)">
                            </div>
                        @endif
                        <!-- Dropdown -->
                        <div x-show="open && !@js($selectedDriverName)"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            @click.away.outside="
                        if (!$el.contains($event.target) && !$el.previousElementSibling.contains($event.target)) {
                            open = false;
                            $wire.set('showDriverDropdown', false);
                        }
                    "
                            class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-xl max-h-60 overflow-y-auto">
                            @if (empty($driverSearch))
                                @if ($recentDrivers->count() > 0)
                                    <div class="px-3 py-2 text-xs font-medium text-gray-500 bg-gray-50 border-b">
                                        {{ __('Recent Drivers') }}
                                    </div>
                                    @foreach ($recentDrivers as $driver)
                                        <button type="button"
                                            wire:click="selectDriver({{ $driver->id }}, '{{ $driver->user?->full_name ?? '' }}')"
                                            @click="open = false"
                                            class="w-full px-3 py-2 text-left text-sm hover:bg-blue-50 focus:bg-blue-50 focus:outline-none flex items-center">
                                            <i class="fas fa-user-circle mr-2 text-gray-400"></i>
                                            {{ $driver->user?->full_name ?? '-' }}
                                        </button>
                                    @endforeach
                                @else
                                    <div class="px-3 py-2 text-sm text-gray-500">
                                        {{ __('Start typing to search drivers...') }}
                                    </div>
                                @endif
                            @else
                                @if ($filteredDrivers->count() > 0)
                                    @foreach ($filteredDrivers as $driver)
                                        <button type="button"
                                            wire:click="selectDriver({{ $driver->id }}, '{{ $driver->user->full_name }}')"
                                            @click="open = false"
                                            class="w-full px-3 py-2 text-left text-sm hover:bg-blue-50 focus:bg-blue-50 focus:outline-none flex items-center">
                                            <i class="fas fa-user-circle mr-2 text-gray-400"></i>
                                            {{ $driver->user->full_name }}
                                        </button>
                                    @endforeach
                                @else
                                    <div class="px-3 py-2 text-sm text-gray-500">
                                        {{ __('No drivers found matching :search', ['search' => $driverSearch]) }}
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endif
                </div>
                <!-- Status Filter -->
                <div class="space-y-1 lg:col-span-1">
                    <label for="selectedStatus" class="block text-xs font-medium text-gray-600">
                        <i class="fas fa-flag mr-1 text-red-500"></i>{{ __('Order Status') }}
                    </label>
                    <select wire:model.live="selectedStatus" id="selectedStatus"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:border-red-500 focus:ring-0 transition-colors hover:border-gray-400">
                        <option value="">{{ __('All Statuses') }}</option>
                        @foreach ($availableStatuses as $statusValue => $statusLabel)
                            <option value="{{ $statusValue }}">{{ __($statusLabel) }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Driver Status Filter -->
                <div class="space-y-1 lg:col-span-1">
                    <label for="driverStatus" class="block text-xs font-medium text-gray-600">
                        <i class="fas fa-user-slash mr-1 text-yellow-500"></i>{{ __('Driver Status') }}
                    </label>
                    <select wire:model.live="driverStatus" id="driverStatus"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:border-yellow-500 focus:ring-0 transition-colors hover:border-gray-400">
                        <option value="">{{ __('All') }}</option>
                        <option value="active">{{ __('Active') }}</option>
                        <option value="inactive">{{ __('Inactive') }}</option>
                    </select>
                </div>
                <!-- Specific Date -->
                <div class="space-y-1 lg:col-span-1">
                    <label for="selectedDate" class="block text-xs font-medium text-gray-600">
                        <i class="fas fa-calendar-day mr-1 text-green-500"></i>{{ __('Specific Date') }}
                    </label>
                    <input type="date" wire:model.live="selectedDate" id="selectedDate"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:border-green-500 focus:ring-0 transition-colors hover:border-gray-400">
                </div>
                <!-- Date From -->
                <div class="space-y-1 lg:col-span-1">
                    <label for="dateFrom" class="block text-xs font-medium text-gray-600">
                        <i class="fas fa-calendar-alt mr-1 text-purple-500"></i>{{ __('From') }}
                    </label>
                    <input type="date" wire:model.live="dateFrom" id="dateFrom"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:border-purple-500 focus:ring-0 transition-colors hover:border-gray-400">
                </div>
                <!-- Date To -->
                <div class="space-y-1 lg:col-span-1">
                    <label for="dateTo" class="block text-xs font-medium text-gray-600">
                        <i class="fas fa-calendar-check mr-1 text-orange-500"></i>{{ __('To') }}
                    </label>
                    <input type="date" wire:model.live="dateTo" id="dateTo"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:border-orange-500 focus:ring-0 transition-colors hover:border-gray-400">
                </div>
            </div>
            <!-- Compact Quick Filters & Active Filters Row -->
            <div class="mt-3 pt-3 border-t border-gray-200">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <!-- Quick Filter Buttons -->
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-medium text-gray-600">{{ __('Quick:') }}</span>
                        <!-- Date Quick Filters -->
                        <div class="flex items-center gap-1">
                            <button wire:click="setToday"
                                class="inline-flex items-center px-2.5 py-1 bg-blue-500 hover:bg-blue-600 text-white text-xs rounded-md transition-colors">
                                <i class="fas fa-calendar-day mr-1"></i>{{ __('Today') }}
                            </button>
                            <button wire:click="setThisWeek"
                                class="inline-flex items-center px-2.5 py-1 bg-green-500 hover:bg-green-600 text-white text-xs rounded-md transition-colors">
                                <i class="fas fa-calendar-week mr-1"></i>{{ __('Week') }}
                            </button>
                        </div>
                    </div>
                    <!-- Active Filters -->
                    @if ($selectedDriverId || $selectedDate || $dateFrom || $dateTo || $selectedStatus || $driverStatus)
                        <div class="flex items-center gap-1 flex-wrap">
                            <span class="text-xs font-medium text-gray-600">{{ __('Active:') }}</span>
                            @if ($selectedDriverId && $selectedDriverName)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ Str::limit($selectedDriverName, 15) }}
                                    <button wire:click="clearDriverSelection"
                                        class="ml-1 hover:bg-blue-200 rounded-full">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </span>
                            @endif
                            @if ($selectedStatus)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                    {{ __($availableStatuses[$selectedStatus]) }}
                                    <button wire:click="$set('selectedStatus', '')"
                                        class="ml-1 hover:bg-red-200 rounded-full">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </span>
                            @endif
                            @if ($driverStatus)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                    {{ ucfirst($driverStatus) }} {{__('Driver')}}
                                    <button wire:click="$set('driverStatus', '')"
                                        class="ml-1 hover:bg-yellow-200 rounded-full">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </span>
                            @endif
                            @if ($selectedDate)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                    {{ \Carbon\Carbon::parse($selectedDate)->locale('pl')->isoFormat('D MMM') }}
                                    <button wire:click="$set('selectedDate', '')"
                                        class="ml-1 hover:bg-green-200 rounded-full">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </span>
                            @endif
                            @if ($dateFrom)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                    {{ __('From:') }} {{ \Carbon\Carbon::parse($dateFrom)->locale('pl')->isoFormat('D MMM') }}
                                    <button wire:click="$set('dateFrom', '')"
                                        class="ml-1 hover:bg-purple-200 rounded-full">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </span>
                            @endif
                            @if ($dateTo)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">
                                    {{ __('To:') }} {{ \Carbon\Carbon::parse($dateTo)->locale('pl')->isoFormat('D MMM') }}
                                    <button wire:click="$set('dateTo', '')"
                                        class="ml-1 hover:bg-orange-200 rounded-full">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- Data Table -->
    <x-data-table :data="$this->rows" :headers="$dataTable['headers']" :showActions="$dataTable['showActions']" :showSearch="$dataTable['showSearch']" :showCreate="$dataTable['showCreate']"
        :createRoute="$dataTable['createRoute']" :createButtonName="$dataTable['createButtonName']" :editRoute="$dataTable['editRoute']" :viewRoute="$dataTable['viewRoute']" :deleteAction="$dataTable['deleteAction']" :searchPlaceholder="$dataTable['searchPlaceholder']"
        :emptyMessage="$dataTable['emptyMessage']" :searchQuery="$search" :sortColumn="$sortColumn" :sortDirection="$sortDirection" :showBulkActions="$dataTable['showBulkActions']"
        :bulkDeleteAction="$dataTable['bulkDeleteAction']" :selectedRowsCount="$selectedRowsCount" :selectAll="$selectAll" :selectPage="$selectPage" :selectedRows="$selectedRows" />
</div>