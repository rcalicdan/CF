<!-- Route Summary Dashboard -->
<div x-show="optimizationResult && showRouteSummary"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform translate-y-4"
    x-transition:enter-end="opacity-100 transform translate-y-0"
    class="bg-white rounded-xl custom-shadow p-4 lg:p-6">

    <h2
        class="text-lg lg:text-xl font-semibold text-gray-800 mb-4 lg:mb-6 flex items-center flex-wrap">
        <i class="fas fa-chart-bar text-primary mr-2"></i>
        Route Summary & Analytics
        <div class="flex items-center gap-2 ml-auto">
            <span class="text-sm text-gray-600 bg-gray-100 px-3 py-1 rounded-full">
                <i class="fas fa-calendar mr-1"></i>
                <span x-text="executiveSummary?.deliveryDate || formattedSelectedDate"></span>
            </span>
            <button @click="exportSummary()"
                class="text-sm bg-primary text-white px-3 py-1 rounded-lg hover:bg-opacity-90">
                <i class="fas fa-download mr-1"></i>Export
            </button>
        </div>
    </h2>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4 lg:mb-6">
        <div class="text-center p-4 bg-blue-50 rounded-xl border border-blue-200">
            <div class="text-2xl lg:text-3xl font-bold text-blue-600 mb-1"
                x-text="executiveSummary?.totalStops || '0'"></div>
            <div class="text-xs text-blue-700 uppercase font-medium">Total Stops</div>
            <div class="text-xs text-blue-600 mt-1">+ Depot Return</div>
        </div>
        <div class="text-center p-4 bg-green-50 rounded-xl border border-green-200">
            <div class="text-2xl lg:text-3xl font-bold text-green-600 mb-1"
                x-text="executiveSummary?.totalDistance || '0 km'"></div>
            <div class="text-xs text-green-700 uppercase font-medium">Total Distance</div>
            <div class="text-xs text-green-600 mt-1"
                x-text="(executiveSummary?.savings || '0 km') + ' saved'"></div>
        </div>
        <div class="text-center p-4 bg-purple-50 rounded-xl border border-purple-200">
            <div class="text-2xl lg:text-3xl font-bold text-purple-600 mb-1"
                x-text="executiveSummary?.totalTime || '0h'"></div>
            <div class="text-xs text-purple-700 uppercase font-medium">Total Time</div>
            <div class="text-xs text-purple-600 mt-1">Including stops</div>
        </div>
    </div>

    <!-- Priority & Timeline Analysis - Responsive -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
        <div class="bg-gray-50 p-4 lg:p-5 rounded-xl">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-flag mr-2"></i>Priority Breakdown
            </h3>
            <div class="space-y-2">
                <template x-for="priority in priorityBreakdown" :key="priority.level">
                    <div class="flex items-center justify-between py-2">
                        <div class="flex items-center min-w-0 flex-1">
                            <div :class="priority.colorClass"
                                class="w-3 h-3 rounded-full mr-3 flex-shrink-0"></div>
                            <span class="text-sm capitalize truncate"
                                x-text="priority.level + ' Priority'"></span>
                        </div>
                        <div class="flex items-center space-x-2 lg:space-x-4 ml-2">
                            <span class="text-sm font-medium whitespace-nowrap"
                                x-text="priority.count + ' orders'"></span>
                            <span class="text-sm font-bold text-green-600 whitespace-nowrap"
                                x-text="formatCurrency(priority.value)"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Timeline Summary -->
        <div class="bg-gray-50 p-4 lg:p-5 rounded-xl">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-clock mr-2"></i>Timeline Summary
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Delivery Date:</span>
                    <span class="font-medium text-primary" x-text="selectedDate"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Start Time:</span>
                    <span class="font-medium"
                        x-text="executiveSummary?.startTime || '08:00'"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">First Delivery:</span>
                    <span class="font-medium"
                        x-text="executiveSummary?.firstDelivery || '09:30'"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Last Delivery:</span>
                    <span class="font-medium"
                        x-text="executiveSummary?.lastDelivery || '16:45'"></span>
                </div>
                <div class="flex justify-between items-center border-t pt-2">
                    <span class="text-sm font-medium text-gray-700">Return to Depot:</span>
                    <span class="font-bold text-primary"
                        x-text="executiveSummary?.returnTime || '18:00'"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Route Controls -->
    <div class="mt-6 bg-white border rounded-xl p-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-route text-primary mr-2"></i>
            Route Controls
        </h3>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <button
                @click="window.mapManager?.clearRoute(); window.mapManager?.visualizeOptimizedRoute()"
                class="bg-blue-50 hover:bg-blue-100 text-blue-600 py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                <i class="fas fa-redo mr-2"></i>Redraw Route
            </button>
            <button @click="window.mapManager?.fitMapToRoute()"
                class="bg-green-50 hover:bg-green-100 text-green-600 py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                <i class="fas fa-search-plus mr-2"></i>Fit to Route
            </button>
            <button @click="exportSummary()"
                class="bg-purple-50 hover:bg-purple-100 text-purple-600 py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                <i class="fas fa-file-export mr-2"></i>Export Data
            </button>
            <button @click="window.print()"
                class="bg-gray-50 hover:bg-gray-100 text-gray-600 py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                <i class="fas fa-print mr-2"></i>Print
            </button>
        </div>

        <!-- Route Quality Indicator -->
        <div x-show="optimizationResult?.actual_route_distance"
            class="mt-4 p-3 bg-gray-50 rounded-lg">
            <div class="text-sm text-gray-600 mb-2">Route Quality Analysis:</div>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div class="flex justify-between">
                    <span>VROOM Optimized:</span>
                    <span class="font-medium"
                        x-text="optimizationResult?.total_distance + ' km'"></span>
                </div>
                <div class="flex justify-between">
                    <span>Actual Route:</span>
                    <span class="font-medium"
                        x-text="optimizationResult?.actual_route_distance + ' km'"></span>
                </div>
            </div>
        </div>
    </div>
</div>