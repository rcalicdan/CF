<x-layouts.app>
    <div x-data="routeOptimizer()" x-cloak class="min-h-screen">
        <div class="w-full max-w-full">
            <div class="grid grid-cols-1 xl:grid-cols-4 gap-4 lg:gap-6">
                <div class="xl:col-span-1 space-y-4 lg:space-y-6">
                    <div class="bg-white rounded-xl custom-shadow p-4 lg:p-6">
                        <h2 class="text-lg lg:text-xl font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-users text-primary mr-2"></i>
                            Select Driver
                        </h2>
                        <div class="space-y-3">
                            <template x-for="driver in drivers" :key="driver.id">
                                <div @click="selectedDriver = driver"
                                    :class="selectedDriver.id === driver.id ? 'ring-2 ring-primary bg-blue-50' :
                                        'hover:bg-gray-50'"
                                    class="p-3 lg:p-4 rounded-lg border cursor-pointer transition-all">
                                    <div class="flex items-center justify-between">
                                        <div class="min-w-0 flex-1">
                                            <div class="font-medium text-gray-800 truncate" x-text="driver.full_name">
                                            </div>
                                            <div class="text-sm text-gray-500 truncate" x-text="driver.vehicle_details">
                                            </div>
                                        </div>
                                        <div class="text-right ml-2 flex-shrink-0">
                                            <div class="text-xs text-gray-400">License</div>
                                            <div class="text-xs lg:text-sm font-mono" x-text="driver.license_number">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Delivery Orders -->
                    <div class="bg-white rounded-xl custom-shadow p-4 lg:p-6">
                        <h2 class="text-lg lg:text-xl font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-box text-primary mr-2"></i>
                            Today's Deliveries
                            <span class="ml-auto bg-primary text-white px-2 py-1 rounded-full text-sm"
                                x-text="orders.length"></span>
                        </h2>
                        <div class="space-y-3 max-h-48 lg:max-h-64 overflow-y-auto">
                            <template x-for="order in orders" :key="order.id">
                                <div class="p-3 lg:p-4 border rounded-lg hover:bg-gray-50 transition-colors">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1 min-w-0">
                                            <div class="font-medium text-gray-800" x-text="'Order #' + order.id"></div>
                                            <div class="text-sm text-gray-600 truncate" x-text="order.client_name">
                                            </div>
                                            <div class="text-sm text-primary font-medium truncate"
                                                x-text="order.address"></div>
                                        </div>
                                        <div class="text-right ml-2 flex-shrink-0">
                                            <div class="text-base lg:text-lg font-semibold text-green-600"
                                                x-text="'zł' + order.total_amount"></div>
                                            <div :class="order.status === 'pending' ? 'text-orange-500' : 'text-green-500'"
                                                class="text-xs font-medium uppercase" x-text="order.status"></div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Optimization Controls -->
                    <div class="bg-white rounded-xl custom-shadow p-4 lg:p-6">
                        <h2 class="text-lg lg:text-xl font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-cogs text-primary mr-2"></i>
                            Route Optimization
                        </h2>
                        <div class="space-y-4">
                            <button @click="optimizeRoutes()" :disabled="loading"
                                class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 px-4 rounded-lg font-semibold hover:shadow-lg transition-all transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                                <span x-show="!loading" class="flex items-center justify-center">
                                    <i class="fas fa-route mr-2"></i>
                                    Optimize Routes
                                </span>
                                <span x-show="loading" class="flex items-center justify-center">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>
                                    Optimizing...
                                </span>
                            </button>

                            <!-- Quick Stats -->
                            <div x-show="optimizationResult" class="grid grid-cols-2 gap-3">
                                <div class="bg-green-50 p-3 rounded-lg text-center">
                                    <div class="text-lg lg:text-2xl font-bold text-green-600"
                                        x-text="Math.round((optimizationResult?.total_distance || 0)) + ' km'"></div>
                                    <div class="text-xs text-green-700 uppercase">Total Distance</div>
                                </div>
                                <div class="bg-blue-50 p-3 rounded-lg text-center">
                                    <div class="text-lg lg:text-2xl font-bold text-blue-600"
                                        x-text="Math.round((optimizationResult?.total_time || 0) / 60) + 'h ' + Math.round((optimizationResult?.total_time || 0) % 60) + 'm'">
                                    </div>
                                    <div class="text-xs text-blue-700 uppercase">Total Time</div>
                                </div>
                            </div>

                            <!-- Summary Actions -->
                            <div x-show="optimizationResult" class="space-y-2">
                                <button @click="showRouteSummary = !showRouteSummary"
                                    class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                                    <span x-show="!showRouteSummary">
                                        <i class="fas fa-chart-bar mr-2"></i>View Summary
                                    </span>
                                    <span x-show="showRouteSummary">
                                        <i class="fas fa-eye-slash mr-2"></i>Hide Summary
                                    </span>
                                </button>
                                <button @click="resetOptimization()"
                                    class="w-full bg-red-50 hover:bg-red-100 text-red-600 py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                                    <i class="fas fa-redo mr-2"></i>Reset Route
                                </button>

                                <button @click="debugSummaryState()"
                                    class="w-full bg-yellow-50 hover:bg-yellow-100 text-yellow-600 py-1 px-4 rounded text-xs">
                                    Debug Summary
                                </button>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="xl:col-span-3 space-y-4 lg:space-y-6">
                    <!-- Map -->
                    <div class="bg-white rounded-xl custom-shadow overflow-hidden">
                        <div class="p-4 border-b bg-gray-50">
                            <h2 class="text-lg lg:text-xl font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-map text-primary mr-2"></i>
                                Live Route Map
                                <span x-show="selectedDriver" class="ml-auto text-sm text-gray-600">
                                    Driver: <span class="font-medium" x-text="selectedDriver.full_name"></span>
                                </span>
                            </h2>
                        </div>
                        <div id="map" class="h-64 sm:h-80 lg:h-96 xl:h-[32rem] w-full"></div>
                    </div>

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
                            <button @click="exportSummary()"
                                class="ml-auto mt-2 sm:mt-0 text-sm bg-primary text-white px-3 py-1 rounded-lg hover:bg-opacity-90">
                                <i class="fas fa-download mr-1"></i>Export
                            </button>
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

                            <!-- Add this in your control panel -->
                            <div x-show="optimizationResult" class="bg-white rounded-xl custom-shadow p-6">
                                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-route text-primary mr-2"></i>
                                    Route Controls
                                </h2>
                                <div class="grid grid-cols-2 gap-3">
                                    <button
                                        @click="window.mapManager?.clearRoute(); window.mapManager?.visualizeOptimizedRoute()"
                                        class="bg-blue-50 hover:bg-blue-100 text-blue-600 py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                                        <i class="fas fa-redo mr-2"></i>Redraw Route
                                    </button>
                                    <button @click="window.mapManager?.fitMapToRoute()"
                                        class="bg-green-50 hover:bg-green-100 text-green-600 py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                                        <i class="fas fa-search-plus mr-2"></i>Fit to Route
                                    </button>
                                </div>

                                <!-- Route Quality Indicator -->
                                <div x-show="optimizationResult?.actual_route_distance"
                                    class="mt-4 p-3 bg-gray-50 rounded-lg">
                                    <div class="text-sm text-gray-600 mb-1">Route Quality:</div>
                                    <div class="flex items-center justify-between text-sm">
                                        <span>VROOM Optimized:</span>
                                        <span class="font-medium"
                                            x-text="optimizationResult?.total_distance + ' km'"></span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm">
                                        <span>Actual Route:</span>
                                        <span class="font-medium"
                                            x-text="optimizationResult?.actual_route_distance + ' km'"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Timeline Summary -->
                            <div class="bg-gray-50 p-4 lg:p-5 rounded-xl">
                                <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-clock mr-2"></i>Timeline Summary
                                </h3>
                                <div class="space-y-3">
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
                    </div>

                    <!-- Route Details -->
                    <div x-show="optimizationResult" class="bg-white rounded-xl custom-shadow p-4 lg:p-6">
                        <h2 class="text-lg lg:text-xl font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-list-ol text-primary mr-2"></i>
                            Optimized Route Details
                        </h2>
                        <div class="space-y-4">
                            <div class="space-y-3">
                                <template x-for="(step, index) in (optimizationResult?.route_steps || [])"
                                    :key="index">
                                    <div
                                        class="route-card flex items-center p-3 lg:p-4 border rounded-lg hover:shadow-md transition-shadow">
                                        <div class="flex-shrink-0 w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center font-semibold text-sm"
                                            x-text="index + 1"></div>
                                        <div class="ml-4 flex-1 min-w-0">
                                            <div class="font-medium text-gray-800 truncate" x-text="step.location">
                                            </div>
                                            <div class="text-sm text-gray-600 truncate" x-text="step.description">
                                            </div>
                                            <div class="text-xs text-primary mt-1"
                                                x-text="'ETA: ' + step.estimated_arrival"></div>
                                        </div>
                                        <div class="text-right ml-2 flex-shrink-0">
                                            <div class="text-sm font-semibold text-gray-700" x-text="step.distance">
                                            </div>
                                            <div class="text-xs text-gray-500" x-text="step.duration"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading Overlay -->
        <!-- Loading Overlay -->
        <div x-show="loading" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="loading-overlay fixed inset-0 bg-white/20 backdrop-blur-sm flex items-center justify-center"
            style="z-index: 9999 !important;">

            <div
                class="loading-glass-effect rounded-2xl p-6 lg:p-8 text-center max-w-sm mx-4 relative transform bg-white/90 backdrop-blur-md border border-white/30 shadow-2xl">
                <div class="relative mx-auto mb-6">
                    <div
                        class="animate-spin rounded-full h-16 lg:h-20 w-16 lg:w-20 border-4 border-blue-600 border-t-transparent mx-auto">
                    </div>
                    <div class="absolute inset-0 rounded-full border-4 border-blue-600/20"></div>
                </div>

                <h3 class="text-lg lg:text-xl font-bold text-gray-800 mb-3">Optimizing Routes</h3>
                <p class="text-sm lg:text-base text-gray-600 mb-4">Calculating the best delivery path using advanced
                    algorithms...</p>

                <div class="space-y-2">
                    <div class="flex items-center justify-center text-xs lg:text-sm text-gray-500">
                        <i class="fas fa-map-marked-alt mr-2 text-blue-600"></i>
                        Analyzing delivery locations
                    </div>
                    <div class="flex items-center justify-center text-xs lg:text-sm text-gray-500">
                        <i class="fas fa-route mr-2 text-blue-600"></i>
                        Computing optimal path
                    </div>
                    <div class="flex items-center justify-center text-xs lg:text-sm text-gray-500">
                        <i class="fas fa-clock mr-2 text-blue-600"></i>
                        Estimating delivery times
                    </div>
                </div>

                <div class="mt-6">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-gradient-to-r from-blue-500 to-purple-600 h-2 rounded-full animate-pulse"
                            style="width: 100%; animation: loading-progress 2s ease-in-out infinite;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
