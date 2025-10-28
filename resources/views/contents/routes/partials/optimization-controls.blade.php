<!-- Sterowanie optymalizacją -->
<div class="bg-white rounded-xl custom-shadow p-4 lg:p-6">
    <h2 class="text-lg lg:text-xl font-semibold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-cogs text-primary mr-2"></i>
        Optymalizacja trasy
    </h2>
    <div class="space-y-4">
        <!-- Informacje o braku zamówień -->
        <div x-show="orders.length === 0" class="text-center p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
            <div class="text-sm text-yellow-700">
                <i class="fas fa-info-circle mr-1"></i>
                Wybierz datę z dostępnymi zleceniami, aby zoptymalizować trasy
            </div>
        </div>

        <!-- Ostrzeżenie o brakujących współrzędnych -->
        <div x-show="!canOptimizeRoute && orders.length > 0" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-sm">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-semibold text-red-800 mb-2">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Nie można zoptymalizować trasy
                    </h3>
                    <div class="text-xs text-red-700 space-y-1">
                        <p x-show="coordinateValidationSummary.missing > 0" class="flex items-center">
                            <i class="fas fa-map-marker-times mr-2 text-red-500"></i>
                            <span>
                                <strong x-text="coordinateValidationSummary.missing"></strong>
                                <span
                                    x-text="coordinateValidationSummary.missing === 1 ? 'zamówienie bez' : 'zamówień bez'"></span>
                                współrzędnych
                            </span>
                        </p>
                        <p x-show="coordinateValidationSummary.invalid > 0" class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-2 text-red-500"></i>
                            <span>
                                <strong x-text="coordinateValidationSummary.invalid"></strong>
                                <span
                                    x-text="coordinateValidationSummary.invalid === 1 ? 'zamówienie z nieprawidłowymi' : 'zamówień z nieprawidłowymi'"></span>
                                współrzędnymi
                            </span>
                        </p>
                        <div class="mt-3 pt-3 border-t border-red-200">
                            <p class="font-medium text-red-800 flex items-center">
                                <i class="fas fa-lightbulb mr-2"></i>
                                Proszę uzupełnić brakujące dane przed optymalizacją
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ostrzeżenie o częściowych danych (niektóre zamówienia OK) -->
        <div x-show="coordinateValidationSummary.valid > 0 && (coordinateValidationSummary.invalid > 0 || coordinateValidationSummary.missing > 0)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded-lg">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-2 flex-1">
                    <p class="text-xs text-yellow-800">
                        <strong x-text="coordinateValidationSummary.valid"></strong> z
                        <strong x-text="coordinateValidationSummary.total"></strong>
                        zamówień ma prawidłowe współrzędne
                    </p>
                </div>
            </div>
        </div>

        <!-- Komunikat o błędzie optymalizacji -->
        <div x-show="optimizationError" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            class="bg-red-100 border border-red-300 p-4 rounded-lg">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-semibold text-red-800 mb-1">
                        Błąd optymalizacji
                    </h3>
                    <div class="text-xs text-red-700 whitespace-pre-line" x-text="optimizationError"></div>
                    <button @click="optimizationError = null"
                        class="mt-2 text-xs text-red-600 hover:text-red-800 font-medium underline">
                        Zamknij
                    </button>
                </div>
            </div>
        </div>

        <!-- Szybkie statystyki -->
        <div x-show="optimizationResult" class="grid grid-cols-2 gap-3">
            <div class="bg-green-50 p-3 rounded-lg text-center">
                <div class="text-lg lg:text-2xl font-bold text-green-600"
                    x-text="Math.round((optimizationResult?.total_distance || 0)) + ' km'"></div>
                <div class="text-xs text-green-700 uppercase">Łączna odległość</div>
            </div>
            <div class="bg-blue-50 p-3 rounded-lg text-center">
                <div class="text-lg lg:text-2xl font-bold text-blue-600"
                    x-text="Math.round((optimizationResult?.total_time || 0) / 60) + 'h ' + Math.round((optimizationResult?.total_time || 0) % 60) + 'm'">
                </div>
                <div class="text-xs text-blue-700 uppercase">Łączny czas</div>
            </div>
        </div>

        <!-- Akcje podsumowania -->
        <div x-show="optimizationResult" class="space-y-2">
            <button @click="showRouteSummary = !showRouteSummary"
                class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                <span x-show="!showRouteSummary">
                    <i class="fas fa-chart-bar mr-2"></i>Wyświetl podsumowanie
                </span>
                <span x-show="showRouteSummary">
                    <i class="fas fa-eye-slash mr-2"></i>Ukryj podsumowanie
                </span>
            </button>
            <button @click="resetOptimization()"
                class="w-full bg-red-50 hover:bg-red-100 text-red-600 py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                <i class="fas fa-redo mr-2"></i>Resetuj trasę
            </button>
        </div>
    </div>
</div>

<!-- Kontener pływającego przycisku optymalizacji - Najwyższy priorytet nakładki -->
<div class="fixed inset-0 pointer-events-none" style="z-index: 999999 !important;">

    <!-- Główny pływający przycisk -->
    <div x-show="orders.length > 0" x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="opacity-0 scale-75 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-75 translate-y-4"
        class="absolute bottom-6 right-6 pointer-events-auto">

        <button @click="optimizeRoutes()" :disabled="loading || orders.length === 0 || !canOptimizeRoute"
            :class="{
                'animate-pulse': loading,
                'hover:scale-110 hover:shadow-2xl': !loading && canOptimizeRoute,
                'opacity-50 cursor-not-allowed': !canOptimizeRoute,
                'bg-gradient-to-r from-red-600 via-red-700 to-red-600': !canOptimizeRoute,
                'bg-gradient-to-r from-blue-600 via-blue-700 to-purple-600': canOptimizeRoute
            }"
            class="group relative text-white p-5 rounded-full shadow-2xl transition-all duration-300 transform hover:shadow-3xl disabled:cursor-not-allowed disabled:transform-none border-4 border-white/20 backdrop-blur-sm"
            style="box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(255, 255, 255, 0.1) inset !important;">

            <!-- Ikona główna -->
            <div class="relative">
                <i x-show="!loading && canOptimizeRoute" class="fas fa-route text-2xl drop-shadow-lg"></i>
                <i x-show="!loading && !canOptimizeRoute"
                    class="fas fa-exclamation-triangle text-2xl drop-shadow-lg"></i>
                <i x-show="loading" class="fas fa-spinner fa-spin text-2xl drop-shadow-lg"></i>
            </div>

            <!-- Wzbogacone pływające podpowiedzi -->
            <div
                class="absolute right-full mr-4 top-1/2 transform -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-all duration-200 pointer-events-none scale-95 group-hover:scale-100">
                <div class="bg-gray-900/95 backdrop-blur-sm text-white text-sm px-4 py-3 rounded-xl whitespace-nowrap shadow-2xl border border-white/10"
                    style="box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;">
                    <span x-show="!loading && canOptimizeRoute" class="flex items-center">
                        <i class="fas fa-route mr-2 text-blue-300"></i>
                        Zoptymalizuj trasy dla <span x-text="formattedSelectedDate"
                            class="font-semibold text-blue-200 mx-1"></span>
                    </span>
                    <span x-show="!loading && !canOptimizeRoute" class="flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2 text-red-300"></i>
                        <span class="max-w-xs">
                            Brak współrzędnych dla niektórych zamówień
                        </span>
                    </span>
                    <span x-show="loading" class="flex items-center">
                        <i class="fas fa-spinner fa-spin mr-2 text-purple-300"></i>
                        Optymalizacja tras...
                    </span>

                    <!-- Wzbogacona strzałka podpowiedzi -->
                    <div
                        class="absolute left-full top-1/2 transform -translate-y-1/2 w-0 h-0 border-l-8 border-l-gray-900/95 border-t-8 border-t-transparent border-b-8 border-b-transparent drop-shadow-lg">
                    </div>
                </div>
            </div>

            <!-- Wiele animowanych pierścieni (tylko gdy można optymalizować) -->
            <div x-show="!loading && !optimizationResult && canOptimizeRoute" class="absolute inset-0">
                <div
                    class="absolute inset-0 rounded-full bg-gradient-to-r from-blue-600 to-purple-600 opacity-20 animate-ping animation-delay-0">
                </div>
                <div
                    class="absolute inset-0 rounded-full bg-gradient-to-r from-blue-600 to-purple-600 opacity-10 animate-ping animation-delay-300">
                </div>
                <div
                    class="absolute inset-0 rounded-full bg-gradient-to-r from-blue-600 to-purple-600 opacity-5 animate-ping animation-delay-700">
                </div>
            </div>

            <!-- Pierścień ostrzeżenia -->
            <div x-show="!canOptimizeRoute && !loading"
                class="absolute inset-0 rounded-full border-3 border-red-400 opacity-80 shadow-lg animate-pulse"
                style="box-shadow: 0 0 20px rgba(239, 68, 68, 0.4), 0 0 40px rgba(239, 68, 68, 0.2) !important;"></div>

            <!-- Pierścień sukcesu z poświatą -->
            <div x-show="optimizationResult"
                class="absolute inset-0 rounded-full border-3 border-green-400 opacity-80 shadow-lg"
                style="box-shadow: 0 0 20px rgba(34, 197, 94, 0.4), 0 0 40px rgba(34, 197, 94, 0.2) !important;"></div>

            <!-- Wzbogacona odznaka z liczbą zleceń -->
            <div :class="{
                'bg-gradient-to-r from-red-500 to-pink-600': canOptimizeRoute,
                'bg-gradient-to-r from-gray-600 to-gray-700': !canOptimizeRoute
            }"
                class="absolute -top-3 -right-3 text-white text-sm font-bold rounded-full h-8 w-8 flex items-center justify-center shadow-xl border-2 border-white transform hover:scale-110 transition-transform duration-200"
                style="box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.4) !important;">
                <span x-text="orders.length"></span>
            </div>

            <!-- Dodatkowa odznaka z ostrzeżeniem -->
            <div x-show="!canOptimizeRoute"
                class="absolute -top-1 -left-1 bg-yellow-500 text-white text-xs font-bold rounded-full h-6 w-6 flex items-center justify-center shadow-lg border-2 border-white">
                <i class="fas fa-exclamation"></i>
            </div>

            <!-- Efekt poświaty -->
            <div
                class="absolute inset-0 rounded-full bg-gradient-to-r from-blue-600 to-purple-600 opacity-0 group-hover:opacity-20 transition-opacity duration-300 blur-md transform scale-110">
            </div>
        </button>
    </div>

    <!-- Drugorzędne pływające akcje (po optymalizacji) -->
    <div x-show="optimizationResult" x-transition:enter="transition ease-out duration-500 transform"
        x-transition:enter-start="opacity-0 scale-75 translate-x-8"
        x-transition:enter-end="opacity-100 scale-100 translate-x-0"
        x-transition:leave="transition ease-in duration-300 transform"
        x-transition:leave-start="opacity-100 scale-100 translate-x-0"
        x-transition:leave-end="opacity-0 scale-75 translate-x-8"
        class="absolute bottom-28 right-6 space-y-4 pointer-events-auto">

        <!-- Przycisk przełączania podsumowania -->
        <button @click="showRouteSummary = !showRouteSummary"
            :class="showRouteSummary ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-purple-500/25' :
                'bg-white/95 text-gray-700 shadow-gray-500/25'"
            class="group backdrop-blur-sm p-4 rounded-full shadow-2xl hover:shadow-3xl transition-all duration-300 hover:scale-110 border-2 border-white/30"
            style="box-shadow: 0 20px 40px -10px var(--tw-shadow-color) !important;">
            <i x-show="!showRouteSummary" class="fas fa-chart-bar text-xl drop-shadow-sm"></i>
            <i x-show="showRouteSummary" class="fas fa-eye-slash text-xl drop-shadow-sm"></i>

            <!-- Mini podpowiedź -->
            <div
                class="absolute right-full mr-3 top-1/2 transform -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-all duration-200 pointer-events-none scale-95 group-hover:scale-100">
                <div
                    class="bg-gray-900/95 backdrop-blur-sm text-white text-xs px-3 py-2 rounded-lg whitespace-nowrap shadow-xl border border-white/10">
                    <span x-show="!showRouteSummary">Wyświetl podsumowanie</span>
                    <span x-show="showRouteSummary">Ukryj podsumowanie</span>
                    <div
                        class="absolute left-full top-1/2 transform -translate-y-1/2 w-0 h-0 border-l-4 border-l-gray-900/95 border-t-4 border-t-transparent border-b-4 border-b-transparent">
                    </div>
                </div>
            </div>
        </button>

        <!-- Przycisk resetu -->
        <button @click="resetOptimization()"
            class="group bg-white/95 backdrop-blur-sm text-red-500 p-4 rounded-full shadow-2xl hover:shadow-3xl transition-all duration-300 hover:scale-110 border-2 border-white/30 hover:bg-red-50"
            style="box-shadow: 0 20px 40px -10px rgba(239, 68, 68, 0.25) !important;">
            <i class="fas fa-redo text-xl drop-shadow-sm"></i>

            <!-- Mini podpowiedź -->
            <div
                class="absolute right-full mr-3 top-1/2 transform -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-all duration-200 pointer-events-none scale-95 group-hover:scale-100">
                <div
                    class="bg-gray-900/95 backdrop-blur-sm text-white text-xs px-3 py-2 rounded-lg whitespace-nowrap shadow-xl border border-white/10">
                    Resetuj trasę
                    <div
                        class="absolute left-full top-1/2 transform -translate-y-1/2 w-0 h-0 border-l-4 border-l-gray-900/95 border-t-4 border-t-transparent border-b-4 border-b-transparent">
                    </div>
                </div>
            </div>
        </button>

        <!-- Przycisk eksportu -->
        <button @click="exportManualRoute && exportManualRoute()"
            class="group bg-white/95 backdrop-blur-sm text-green-600 p-4 rounded-full shadow-2xl hover:shadow-3xl transition-all duration-300 hover:scale-110 border-2 border-white/30 hover:bg-green-50"
            style="box-shadow: 0 20px 40px -10px rgba(34, 197, 94, 0.25) !important;">
            <i class="fas fa-download text-xl drop-shadow-sm"></i>

            <!-- Mini podpowiedź -->
            <div
                class="absolute right-full mr-3 top-1/2 transform -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-all duration-200 pointer-events-none scale-95 group-hover:scale-100">
                <div
                    class="bg-gray-900/95 backdrop-blur-sm text-white text-xs px-3 py-2 rounded-lg whitespace-nowrap shadow-xl border border-white/10">
                    Eksportuj trasę
                    <div
                        class="absolute left-full top-1/2 transform -translate-y-1/2 w-0 h-0 border-l-4 border-l-gray-900/95 border-t-4 border-t-transparent border-b-4 border-b-transparent">
                    </div>
                </div>
            </div>
        </button>
    </div>

    <!-- Przycisk optymalizacji mobilny (wzbogacony dla mniejszych ekranów) -->
    <div class="lg:hidden absolute bottom-6 left-1/2 transform -translate-x-1/2 pointer-events-auto"
        x-show="orders.length > 0">
        <button @click="optimizeRoutes()" :disabled="loading || orders.length === 0 || !canOptimizeRoute"
            :class="{
                'animate-pulse': loading,
                'hover:scale-105': !loading && canOptimizeRoute,
                'opacity-70 cursor-not-allowed': !canOptimizeRoute,
                'bg-gradient-to-r from-red-600 via-red-700 to-red-600': !canOptimizeRoute,
                'bg-gradient-to-r from-blue-600 via-blue-700 to-purple-600': canOptimizeRoute
            }"
            class="text-white px-8 py-4 rounded-full shadow-2xl font-semibold text-base hover:shadow-3xl transition-all duration-300 disabled:cursor-not-allowed flex items-center space-x-3 border-2 border-white/20 backdrop-blur-sm"
            style="box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(255, 255, 255, 0.1) inset !important;">
            <i x-show="!loading && canOptimizeRoute" class="fas fa-route text-xl"></i>
            <i x-show="!loading && !canOptimizeRoute" class="fas fa-exclamation-triangle text-xl"></i>
            <i x-show="loading" class="fas fa-spinner fa-spin text-xl"></i>
            <span x-show="!loading && canOptimizeRoute">Optymalizuj trasy</span>
            <span x-show="!loading && !canOptimizeRoute">Brak współrzędnych</span>
            <span x-show="loading">Optymalizacja...</span>
            <div :class="{
                'bg-gradient-to-r from-red-500 to-pink-600': canOptimizeRoute,
                'bg-gradient-to-r from-gray-600 to-gray-700': !canOptimizeRoute
            }"
                class="text-white text-sm font-bold rounded-full h-7 w-7 flex items-center justify-center shadow-lg border border-white/30">
                <span x-text="orders.length"></span>
            </div>
        </button>
    </div>
</div>
