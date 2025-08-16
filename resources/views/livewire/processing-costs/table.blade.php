<div>
    <x-flash-session />
    <x-partials.dashboard.content-header :title="__('Processing Costs Management')" />
    
    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-md border border-gray-200 mb-6 overflow-visible">
        <div class="bg-gradient-to-r from-green-600 to-teal-600 px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-7 h-7 bg-white/20 rounded-md flex items-center justify-center">
                        <i class="fas fa-filter text-white text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-white">{{ __('Filter Processing Costs') }}</h3>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="text-xs text-green-100 bg-white/10 px-2 py-1 rounded">
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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-3 overflow-visible">
                <!-- Name Filter -->
                <div class="space-y-1 lg:col-span-2">
                    <label for="selectedName" class="block text-xs font-medium text-gray-600">
                        <i class="fas fa-tag mr-1 text-blue-500"></i>{{ __('Name') }}
                    </label>
                    <input type="text" wire:model.live.debounce.300ms="selectedName" id="selectedName"
                        placeholder="{{ __('Search by name...') }}"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:border-blue-500 focus:ring-0 transition-colors hover:border-gray-400">
                </div>

                <!-- Type Filter -->
                <div class="space-y-1 lg:col-span-1">
                    <label for="selectedType" class="block text-xs font-medium text-gray-600">
                        <i class="fas fa-layer-group mr-1 text-purple-500"></i>{{ __('Type') }}
                    </label>
                    <select wire:model.live="selectedType" id="selectedType"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:border-purple-500 focus:ring-0 transition-colors hover:border-gray-400">
                        <option value="">{{ __('All Types') }}</option>
                        @foreach ($availableTypes as $typeValue => $typeLabel)
                            <option value="{{ $typeValue }}">{{ $typeLabel }}</option>
                        @endforeach
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
                        <i class="fas fa-calendar-alt mr-1 text-red-500"></i>{{ __('From') }}
                    </label>
                    <input type="date" wire:model.live="dateFrom" id="dateFrom"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:border-red-500 focus:ring-0 transition-colors hover:border-gray-400">
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

            <!-- Month Filter Row -->
            <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="space-y-1">
                    <label for="selectedMonth" class="block text-xs font-medium text-gray-600">
                        <i class="fas fa-calendar mr-1 text-indigo-500"></i>{{ __('Month') }}
                    </label>
                    <input type="month" wire:model.live="selectedMonth" id="selectedMonth"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:border-indigo-500 focus:ring-0 transition-colors hover:border-gray-400">
                </div>
            </div>
            
            <!-- Compact Quick Filters & Active Filters Row -->
            <div class="mt-3 pt-3 border-t border-gray-200">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <!-- Quick Filter Buttons -->
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-medium text-gray-600">{{ __('Quick:') }}</span>
                        <div class="flex items-center gap-1">
                            <button wire:click="setToday"
                                class="inline-flex items-center px-2.5 py-1 bg-blue-500 hover:bg-blue-600 text-white text-xs rounded-md transition-colors">
                                <i class="fas fa-calendar-day mr-1"></i>{{ __('Today') }}
                            </button>
                            <button wire:click="setThisWeek"
                                class="inline-flex items-center px-2.5 py-1 bg-green-500 hover:bg-green-600 text-white text-xs rounded-md transition-colors">
                                <i class="fas fa-calendar-week mr-1"></i>{{ __('Week') }}
                            </button>
                            <button wire:click="setThisMonth"
                                class="inline-flex items-center px-2.5 py-1 bg-purple-500 hover:bg-purple-600 text-white text-xs rounded-md transition-colors">
                                <i class="fas fa-calendar mr-1"></i>{{ __('Month') }}
                            </button>
                        </div>
                    </div>
                    
                    <!-- Active Filters -->
                    @if ($selectedName || $selectedType || $selectedDate || $dateFrom || $dateTo || $selectedMonth)
                        <div class="flex items-center gap-1 flex-wrap">
                            <span class="text-xs font-medium text-gray-600">{{ __('Active:') }}</span>
                            
                            @if ($selectedName)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ __('Name:') }} {{ Str::limit($selectedName, 10) }}
                                    <button wire:click="$set('selectedName', '')" class="ml-1 hover:bg-blue-200 rounded-full">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </span>
                            @endif
                            
                            @if ($selectedType)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                    {{ $availableTypes[$selectedType] }}
                                    <button wire:click="$set('selectedType', '')" class="ml-1 hover:bg-purple-200 rounded-full">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </span>
                            @endif
                            
                            @if ($selectedDate)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                    {{ \Carbon\Carbon::parse($selectedDate)->format('M d') }}
                                    <button wire:click="$set('selectedDate', '')" class="ml-1 hover:bg-green-200 rounded-full">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </span>
                            @endif
                            
                            @if ($dateFrom)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                    {{ __('From:') }} {{ \Carbon\Carbon::parse($dateFrom)->format('M d') }}
                                    <button wire:click="$set('dateFrom', '')" class="ml-1 hover:bg-red-200 rounded-full">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </span>
                            @endif
                            
                            @if ($dateTo)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">
                                    {{ __('To:') }} {{ \Carbon\Carbon::parse($dateTo)->format('M d') }}
                                    <button wire:click="$set('dateTo', '')" class="ml-1 hover:bg-orange-200 rounded-full">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </span>
                            @endif
                            
                            @if ($selectedMonth)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                    {{ \Carbon\Carbon::parse($selectedMonth)->format('M Y') }}
                                    <button wire:click="$set('selectedMonth', '')" class="ml-1 hover:bg-indigo-200 rounded-full">
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
    <x-data-table 
        :data="$this->rows" 
        :headers="$dataTable['headers']"
        :showActions="$dataTable['showActions']" 
        :showSearch="$dataTable['showSearch']"
        :showCreate="$dataTable['showCreate']" 
        :createRoute="$dataTable['createRoute']"
        :createButtonName="$dataTable['createButtonName']" 
        :editRoute="$dataTable['editRoute']"
        :viewRoute="$dataTable['viewRoute']" 
        :deleteAction="$dataTable['deleteAction']"
        :searchPlaceholder="$dataTable['searchPlaceholder']" 
        :emptyMessage="$dataTable['emptyMessage']"
        :searchQuery="$search"
        :sortColumn="$sortColumn"
        :sortDirection="$sortDirection" 
        :showBulkActions="$dataTable['showBulkActions']"
        :bulkDeleteAction="$dataTable['bulkDeleteAction']" 
        :selectedRowsCount="$selectedRowsCount"
        :selectAll="$selectAll" 
        :selectPage="$selectPage" 
        :selectedRows="$selectedRows" />
</div>