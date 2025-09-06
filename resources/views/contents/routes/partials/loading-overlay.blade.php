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
        <p class="text-sm lg:text-base text-gray-600 mb-4">
            Calculating the best delivery path for <span x-text="formattedSelectedDate"
                class="font-medium"></span>
        </p>

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