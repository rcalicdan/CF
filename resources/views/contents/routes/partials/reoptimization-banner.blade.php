<!-- Re-optimization Warning Banner -->
<div x-show="routeNeedsReoptimization" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform -translate-y-2"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="bg-gradient-to-r from-yellow-50 to-orange-50 border-l-4 border-yellow-500 p-4 mb-4 lg:mb-6 rounded-r-lg shadow-lg"
     style="display: none;">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <div class="flex items-center justify-center w-10 h-10 lg:w-12 lg:h-12 rounded-full bg-yellow-100">
                <i class="fas fa-exclamation-triangle text-yellow-600 text-xl lg:text-2xl"></i>
            </div>
        </div>
        <div class="ml-3 lg:ml-4 flex-1">
            <h3 class="text-sm lg:text-base font-bold text-yellow-900 mb-1">
                <i class="fas fa-route mr-1"></i>
                Wykryto nowe zamówienia - Trasa wymaga aktualizacji
            </h3>
            <div class="text-xs lg:text-sm text-yellow-800">
                <p class="mb-2">
                    Znaleziono <span class="font-bold text-yellow-900" x-text="newOrdersCount"></span> 
                    <span x-text="newOrdersCount === 1 ? 'nowe zamówienie' : newOrdersCount < 5 ? 'nowe zamówienia' : 'nowych zamówień'"></span>, 
                    <span class="font-semibold">które nie zostały uwzględnione w ostatniej optymalizacji</span>.
                </p>
                <p class="text-yellow-700">
                    <i class="fas fa-info-circle mr-1"></i>
                    Nowe zamówienia zostały dodane na końcu trasy. Zalecamy ponowną optymalizację dla najlepszej wydajności.
                </p>
            </div>
            <div class="mt-3 lg:mt-4 flex flex-col sm:flex-row gap-2 lg:gap-3">
                <button @click="optimizeRoutes()" 
                        :disabled="loading"
                        class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-md hover:shadow-lg">
                    <i class="fas fa-sync-alt mr-2" :class="{ 'animate-spin': loading }"></i>
                    <span x-show="!loading">Optymalizuj trasę ponownie</span>
                    <span x-show="loading">Optymalizacja w toku...</span>
                </button>
                <button @click="dismissReoptimizationWarning()" 
                        class="inline-flex items-center justify-center px-4 py-2 border border-yellow-300 text-sm font-medium rounded-lg text-yellow-800 bg-white hover:bg-yellow-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-all duration-200">
                    <i class="fas fa-times-circle mr-2"></i>
                    Kontynuuj bez optymalizacji
                </button>
            </div>
        </div>
        <div class="ml-auto pl-3">
            <button @click="dismissReoptimizationWarning()" 
                    class="inline-flex items-center justify-center w-8 h-8 text-yellow-400 hover:text-yellow-600 hover:bg-yellow-100 rounded-full focus:outline-none transition-colors"
                    title="Zamknij">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
    </div>
</div>