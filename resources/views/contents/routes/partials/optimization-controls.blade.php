<!-- Optimization Controls -->
<div class="bg-white rounded-xl custom-shadow p-4 lg:p-6">
    <h2 class="text-lg lg:text-xl font-semibold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-cogs text-primary mr-2"></i>
        Route Optimization
    </h2>
    <div class="space-y-4">
        <button @click="optimizeRoutes()" :disabled="loading || orders.length === 0"
            class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 px-4 rounded-lg font-semibold hover:shadow-lg transition-all transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
            <span x-show="!loading" class="flex items-center justify-center">
                <i class="fas fa-route mr-2"></i>
                Optimize Routes for <span x-text="formattedSelectedDate" class="ml-1 font-normal text-sm"></span>
            </span>
            <span x-show="loading" class="flex items-center justify-center">
                <i class="fas fa-spinner fa-spin mr-2"></i>
                Optimizing...
            </span>
        </button>

        <!-- Optimization Info -->
        <div x-show="orders.length === 0" class="text-center p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
            <div class="text-sm text-yellow-700">
                <i class="fas fa-info-circle mr-1"></i>
                Select a date with available orders to optimize routes
            </div>
        </div>

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
