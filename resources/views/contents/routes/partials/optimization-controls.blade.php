<!-- Optimization Controls -->
<div class="bg-white rounded-xl custom-shadow p-4 lg:p-6">
    <h2 class="text-lg lg:text-xl font-semibold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-cogs text-primary mr-2"></i>
        Route Optimization
    </h2>
    <div class="space-y-4">
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

<!-- Floating Optimization Button Container - Highest Priority Overlay -->
<div class="fixed inset-0 pointer-events-none" style="z-index: 999999 !important;">
    
    <!-- Main Floating Button -->
    <div x-show="orders.length > 0" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 scale-75 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-75 translate-y-4"
         class="absolute bottom-6 right-6 pointer-events-auto">
        
        <button @click="optimizeRoutes()" 
                :disabled="loading || orders.length === 0"
                :class="loading ? 'animate-pulse' : 'hover:scale-110 hover:shadow-2xl'"
                class="group relative bg-gradient-to-r from-blue-600 via-blue-700 to-purple-600 text-white p-5 rounded-full shadow-2xl transition-all duration-300 transform hover:shadow-3xl disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none border-4 border-white/20 backdrop-blur-sm"
                style="box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(255, 255, 255, 0.1) inset !important;">
            
            <!-- Main Icon -->
            <div class="relative">
                <i x-show="!loading" class="fas fa-route text-2xl drop-shadow-lg"></i>
                <i x-show="loading" class="fas fa-spinner fa-spin text-2xl drop-shadow-lg"></i>
            </div>

            <!-- Enhanced Floating Tooltip -->
            <div class="absolute right-full mr-4 top-1/2 transform -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-all duration-200 pointer-events-none scale-95 group-hover:scale-100">
                <div class="bg-gray-900/95 backdrop-blur-sm text-white text-sm px-4 py-3 rounded-xl whitespace-nowrap shadow-2xl border border-white/10"
                     style="box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;">
                    <span x-show="!loading" class="flex items-center">
                        <i class="fas fa-route mr-2 text-blue-300"></i>
                        Optimize Routes for <span x-text="formattedSelectedDate" class="font-semibold text-blue-200 mx-1"></span>
                    </span>
                    <span x-show="loading" class="flex items-center">
                        <i class="fas fa-spinner fa-spin mr-2 text-purple-300"></i>
                        Optimizing routes...
                    </span>
                    
                    <!-- Enhanced Tooltip Arrow -->
                    <div class="absolute left-full top-1/2 transform -translate-y-1/2 w-0 h-0 border-l-8 border-l-gray-900/95 border-t-8 border-t-transparent border-b-8 border-b-transparent drop-shadow-lg"></div>
                </div>
            </div>

            <!-- Multiple Pulse Ring Animations -->
            <div x-show="!loading && !optimizationResult" class="absolute inset-0">
                <div class="absolute inset-0 rounded-full bg-gradient-to-r from-blue-600 to-purple-600 opacity-20 animate-ping animation-delay-0"></div>
                <div class="absolute inset-0 rounded-full bg-gradient-to-r from-blue-600 to-purple-600 opacity-10 animate-ping animation-delay-300"></div>
                <div class="absolute inset-0 rounded-full bg-gradient-to-r from-blue-600 to-purple-600 opacity-5 animate-ping animation-delay-700"></div>
            </div>
            
            <!-- Success Ring with Glow -->
            <div x-show="optimizationResult" 
                 class="absolute inset-0 rounded-full border-3 border-green-400 opacity-80 shadow-lg"
                 style="box-shadow: 0 0 20px rgba(34, 197, 94, 0.4), 0 0 40px rgba(34, 197, 94, 0.2) !important;"></div>

            <!-- Enhanced Order Count Badge -->
            <div class="absolute -top-3 -right-3 bg-gradient-to-r from-red-500 to-pink-600 text-white text-sm font-bold rounded-full h-8 w-8 flex items-center justify-center shadow-xl border-2 border-white transform hover:scale-110 transition-transform duration-200"
                 style="box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.4) !important;">
                <span x-text="orders.length"></span>
            </div>

            <!-- Glowing Ring Effect -->
            <div class="absolute inset-0 rounded-full bg-gradient-to-r from-blue-600 to-purple-600 opacity-0 group-hover:opacity-20 transition-opacity duration-300 blur-md transform scale-110"></div>
        </button>
    </div>

    <!-- Secondary Floating Actions (when optimized) -->
    <div x-show="optimizationResult" 
         x-transition:enter="transition ease-out duration-500 transform"
         x-transition:enter-start="opacity-0 scale-75 translate-x-8"
         x-transition:enter-end="opacity-100 scale-100 translate-x-0"
         x-transition:leave="transition ease-in duration-300 transform"
         x-transition:leave-start="opacity-100 scale-100 translate-x-0"
         x-transition:leave-end="opacity-0 scale-75 translate-x-8"
         class="absolute bottom-28 right-6 space-y-4 pointer-events-auto">
        
        <!-- Summary Toggle Button -->
        <button @click="showRouteSummary = !showRouteSummary"
                :class="showRouteSummary ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-purple-500/25' : 'bg-white/95 text-gray-700 shadow-gray-500/25'"
                class="group backdrop-blur-sm p-4 rounded-full shadow-2xl hover:shadow-3xl transition-all duration-300 hover:scale-110 border-2 border-white/30"
                style="box-shadow: 0 20px 40px -10px var(--tw-shadow-color) !important;">
            <i x-show="!showRouteSummary" class="fas fa-chart-bar text-xl drop-shadow-sm"></i>
            <i x-show="showRouteSummary" class="fas fa-eye-slash text-xl drop-shadow-sm"></i>
            
            <!-- Mini Tooltip -->
            <div class="absolute right-full mr-3 top-1/2 transform -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-all duration-200 pointer-events-none scale-95 group-hover:scale-100">
                <div class="bg-gray-900/95 backdrop-blur-sm text-white text-xs px-3 py-2 rounded-lg whitespace-nowrap shadow-xl border border-white/10">
                    <span x-show="!showRouteSummary">View Summary</span>
                    <span x-show="showRouteSummary">Hide Summary</span>
                    <div class="absolute left-full top-1/2 transform -translate-y-1/2 w-0 h-0 border-l-4 border-l-gray-900/95 border-t-4 border-t-transparent border-b-4 border-b-transparent"></div>
                </div>
            </div>
        </button>

        <!-- Reset Button -->
        <button @click="resetOptimization()"
                class="group bg-white/95 backdrop-blur-sm text-red-500 p-4 rounded-full shadow-2xl hover:shadow-3xl transition-all duration-300 hover:scale-110 border-2 border-white/30 hover:bg-red-50"
                style="box-shadow: 0 20px 40px -10px rgba(239, 68, 68, 0.25) !important;">
            <i class="fas fa-redo text-xl drop-shadow-sm"></i>
            
            <!-- Mini Tooltip -->
            <div class="absolute right-full mr-3 top-1/2 transform -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-all duration-200 pointer-events-none scale-95 group-hover:scale-100">
                <div class="bg-gray-900/95 backdrop-blur-sm text-white text-xs px-3 py-2 rounded-lg whitespace-nowrap shadow-xl border border-white/10">
                    Reset Route
                    <div class="absolute left-full top-1/2 transform -translate-y-1/2 w-0 h-0 border-l-4 border-l-gray-900/95 border-t-4 border-t-transparent border-b-4 border-b-transparent"></div>
                </div>
            </div>
        </button>

        <!-- Export Button -->
        <button @click="exportManualRoute && exportManualRoute()"
                class="group bg-white/95 backdrop-blur-sm text-green-600 p-4 rounded-full shadow-2xl hover:shadow-3xl transition-all duration-300 hover:scale-110 border-2 border-white/30 hover:bg-green-50"
                style="box-shadow: 0 20px 40px -10px rgba(34, 197, 94, 0.25) !important;">
            <i class="fas fa-download text-xl drop-shadow-sm"></i>
            
            <!-- Mini Tooltip -->
            <div class="absolute right-full mr-3 top-1/2 transform -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-all duration-200 pointer-events-none scale-95 group-hover:scale-100">
                <div class="bg-gray-900/95 backdrop-blur-sm text-white text-xs px-3 py-2 rounded-lg whitespace-nowrap shadow-xl border border-white/10">
                    Export Route
                    <div class="absolute left-full top-1/2 transform -translate-y-1/2 w-0 h-0 border-l-4 border-l-gray-900/95 border-t-4 border-t-transparent border-b-4 border-b-transparent"></div>
                </div>
            </div>
        </button>
    </div>

    <!-- Mobile Optimization Button (Enhanced for smaller screens) -->
    <div class="lg:hidden absolute bottom-6 left-1/2 transform -translate-x-1/2 pointer-events-auto" x-show="orders.length > 0">
        <button @click="optimizeRoutes()" 
                :disabled="loading || orders.length === 0"
                :class="loading ? 'animate-pulse' : 'hover:scale-105'"
                class="bg-gradient-to-r from-blue-600 via-blue-700 to-purple-600 text-white px-8 py-4 rounded-full shadow-2xl font-semibold text-base hover:shadow-3xl transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-3 border-2 border-white/20 backdrop-blur-sm"
                style="box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(255, 255, 255, 0.1) inset !important;">
            <i x-show="!loading" class="fas fa-route text-xl"></i>
            <i x-show="loading" class="fas fa-spinner fa-spin text-xl"></i>
            <span x-show="!loading">Optimize Routes</span>
            <span x-show="loading">Optimizing...</span>
            <div class="bg-gradient-to-r from-red-500 to-pink-600 text-white text-sm font-bold rounded-full h-7 w-7 flex items-center justify-center shadow-lg border border-white/30">
                <span x-text="orders.length"></span>
            </div>
        </button>
    </div>
</div>