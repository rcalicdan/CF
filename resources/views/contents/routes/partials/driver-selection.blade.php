<div class="bg-white rounded-xl shadow-md border border-slate-200 p-4 lg:p-6" x-data="{ open: false }">
    <!-- Nagłówek -->
    <h2 class="text-xl font-bold text-slate-800 mb-5 flex items-center">
        <i class="fas fa-users text-indigo-600 mr-3"></i>
        Przypisanie kierowcy
    </h2>

    <!-- Stan ładowania -->
    <div x-show="loading" class="mb-4 p-3 bg-yellow-100 border border-yellow-300 rounded-lg">
        <div class="text-sm text-yellow-800">
            <i class="fas fa-spinner fa-spin mr-2"></i>
            Ładowanie kierowców...
            <span class="text-xs" x-text="`(${drivers.length} kierowców załadowanych)`"></span>
        </div>
    </div>

    <!-- Brak kierowców -->
    <div x-show="!loading && dataLoaded && (!drivers || drivers.length === 0)"
        class="mb-4 p-3 bg-red-100 border border-red-300 rounded-lg">
        <div class="text-sm text-red-800">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Nie znaleziono kierowców z zakończonymi lub niedostarczonymi zleceniami
        </div>
    </div>

    <!-- Karta wybranego kierowcy -->
    <div x-show="!loading && selectedDriver && selectedDriver.id"
        class="p-4 rounded-lg border border-indigo-200 bg-indigo-50 mb-4">
        <div class="flex items-center justify-between">
            <div class="min-w-0 flex-1">
                <div class="font-semibold text-slate-800 truncate flex items-center">
                    <i class="fas fa-user-check text-indigo-600 mr-2"></i>
                    <span x-text="selectedDriver?.full_name || 'Ładowanie...'"></span>
                </div>
                <div class="text-xs text-indigo-800 font-medium mt-2">
                    Prawo jazdy: <span x-text="selectedDriver?.license_number || 'Brak'"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Placeholder braku wybranego kierowcy -->
    <div x-show="!loading && dataLoaded && (!selectedDriver || !selectedDriver.id)"
        class="p-6 rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 mb-4 text-center">
        <div class="text-slate-500">
            <i class="fas fa-user-plus text-3xl text-slate-400 mb-3"></i>
            <div class="font-semibold text-slate-700">Nie wybrano kierowcy</div>
            <div class="text-sm text-slate-500 mt-1">Wybierz kierowcę dla tej trasy</div>
        </div>
    </div>

    <!-- Przycisk akcji -->
    <button @click="open = true" :disabled="loading || !dataLoaded || !drivers || drivers.length === 0"
        :class="(loading || !dataLoaded || !drivers || drivers.length === 0) ?
        'bg-gray-400 cursor-not-allowed' :
        'bg-indigo-600 hover:bg-indigo-700 hover:shadow-md'"
        class="w-full text-white py-3 px-4 rounded-lg font-semibold focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-300 flex items-center justify-center shadow-sm">
        <i class="fas fa-user-cog mr-2"></i>
        <span x-show="loading">Ładowanie...</span>
        <span x-show="!loading"
            x-text="(selectedDriver && selectedDriver.id) ? 'Zmień kierowcę' : 'Wybierz kierowcę'"></span>
        <span x-show="!loading && drivers && drivers.length > 0"
            class="ml-auto bg-indigo-500 text-white px-2 py-1 rounded-full text-xs font-bold"
            x-text="(drivers?.length || 0) + ' dostępnych'"></span>
    </button>

    <!-- Modal -->
    <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4"
        style="z-index: 999998 !important;" @click="open = false">

        <!-- Zawartość modala -->
        <div @click.stop x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="bg-white rounded-xl shadow-2xl max-w-lg w-full max-h-[80vh] overflow-hidden border border-slate-300 flex flex-col">

            <!-- Nagłówek modala -->
            <div class="bg-slate-50 p-4 border-b border-slate-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-800">Wybierz kierowcę</h3>
                    <button @click="open = false"
                        class="text-slate-500 hover:text-slate-800 hover:bg-slate-200 p-1 rounded-full w-7 h-7 flex items-center justify-center transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Lista kierowców -->
            <div class="p-3 overflow-y-auto space-y-2 flex-grow">
                <template x-for="driver in (drivers || [])" :key="driver.id">
                    <div @click="selectDriver(driver); open = false;"
                        :class="(selectedDriver && selectedDriver.id === driver.id) ?
                        'ring-2 ring-indigo-500 bg-indigo-50 border-indigo-300' :
                        'hover:bg-slate-100 border-slate-200 hover:border-indigo-400'"
                        class="p-4 rounded-lg border cursor-pointer transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="font-medium text-slate-800 truncate"
                                    x-text="driver?.full_name || 'Nieznany kierowca'"></div>
                                <div class="text-xs text-slate-400 font-mono mt-1"
                                    x-text="driver?.license_number || 'Brak prawa jazdy'"></div>
                            </div>
                            <div x-show="selectedDriver && selectedDriver.id === driver.id"
                                class="text-indigo-600 ml-3">
                                <i class="fas fa-check-circle text-xl"></i>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Stan pusty -->
                <div x-show="!loading && (!drivers || drivers.length === 0)" class="p-6 text-center text-slate-500">
                    <i class="fas fa-user-slash text-3xl mb-3"></i>
                    <div class="font-medium">Brak dostępnych kierowców</div>
                    <div class="text-sm mt-1">Żaden kierowca nie ma zakończonych lub niedostarczonych zleceń</div>
                </div>

                <!-- Stan ładowania w modalu -->
                <div x-show="loading" class="p-6 text-center text-slate-500">
                    <i class="fas fa-spinner fa-spin text-3xl mb-3"></i>
                    <div class="font-medium">Ładowanie kierowców...</div>
                    <div class="text-sm mt-1">Proszę czekać, trwa pobieranie dostępnych kierowców</div>
                </div>
            </div>

            <!-- Stopka modala -->
            <div class="bg-slate-50 p-3 border-t border-slate-200">
                <button @click="open = false"
                    class="w-full bg-white text-slate-700 py-2 px-4 rounded-lg hover:bg-slate-100 transition-colors border border-slate-300 font-semibold focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Zamknij
                </button>
            </div>
        </div>
    </div>
</div>
