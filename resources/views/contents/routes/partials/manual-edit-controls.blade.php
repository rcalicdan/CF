<div x-show="optimizationResult || orders.length > 0"
    class="bg-white rounded-xl shadow-md border border-slate-200 p-4 lg:p-6">
    <h2 class="text-xl font-bold text-slate-800 mb-5 flex items-center">
        <i class="fas fa-edit text-indigo-600 mr-3"></i>
        Ręczny Edytor Trasy
        <div class="ml-auto flex items-center gap-2">
            <span x-show="manualEditMode"
                class="text-xs bg-orange-100 text-orange-800 px-2.5 py-1 rounded-full font-semibold tracking-wide">
                <i class="fas fa-edit mr-1"></i>TRYB EDYCJI
            </span>
        </div>
    </h2>

    <div class="grid grid-cols-2 gap-3 mb-5">
        <div class="text-center p-3 bg-slate-50 rounded-lg border border-slate-200">
            <div class="text-xl font-bold text-slate-700" x-text="orders.length"></div>
            <div class="text-xs text-slate-500 font-medium">Wszystkie Przystanki</div>
        </div>
        <div class="text-center p-3 bg-slate-50 rounded-lg border border-slate-200">
            <div class="text-xl font-bold text-indigo-600" x-text="orders.filter(o => o.isCustom).length"></div>
            <div class="text-xs text-slate-500 font-medium">Własne Przystanki</div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="flex flex-wrap gap-3">
            <button @click="toggleManualEdit()"
                :class="manualEditMode ? 'bg-indigo-600 text-white shadow-md' :
                    'bg-white border-slate-300 text-slate-700 hover:bg-slate-50'"
                class="px-4 py-2 rounded-lg font-semibold transition-all duration-200 flex items-center border">
                <i class="fas fa-edit mr-2"></i>
                <span x-text="manualEditMode ? 'Wyjdź z Trybu Edycji' : 'Wejdź w Tryb Edycji'"></span>
            </button>

            <button @click="window.mapManager?.clearRoute()"
                class="px-4 py-2 bg-white border-slate-300 text-slate-700 rounded-lg font-semibold hover:bg-slate-50 transition-colors flex items-center border">
                <i class="fas fa-eraser mr-2"></i>
                Wyczyść Trasę
            </button>
        </div>

        <div x-show="manualEditMode" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform -translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0" class="rounded-lg overflow-hidden">

            <div x-show="manualEditMode" class="bg-orange-50 border-l-4 border-orange-400 p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0 pt-0.5">
                        <i class="fas fa-info-circle text-orange-500"></i>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-base font-semibold text-orange-800">Instrukcje Trybu Edycji</h4>
                        <div class="mt-2 text-sm text-orange-700">
                            <ul class="list-disc list-inside space-y-1.5">
                                <li><strong>Przeciągaj znaczniki</strong> na mapie, aby zmienić pozycję przystanków.
                                </li>
                                <li><strong>Kliknij puste obszary</strong> na mapie, aby dodać nowe własne przystanki.
                                </li>
                                <li><strong>Kliknij prawym przyciskiem myszy na znacznik</strong>, aby zobaczyć więcej
                                    opcji.</li>
                                <li><strong>Użyj listy trasy</strong> poniżej, aby przeciągać i zmieniać kolejność
                                    przystanków.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="manualEditMode" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            class="flex flex-wrap gap-3 pt-4 border-t border-slate-200">

            <button @click="saveManualChanges()"
                class="bg-emerald-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors flex items-center shadow-sm">
                <i class="fas fa-save mr-2"></i>Zapisz Zmiany
            </button>

            <button @click="resetToOptimized()"
                class="bg-rose-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-rose-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-colors flex items-center shadow-sm">
                <i class="fas fa-undo mr-2"></i>Przywróć Zoptymalizowaną
            </button>

            <button @click="exportManualRoute()"
                class="bg-sky-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-sky-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 transition-colors flex items-center shadow-sm">
                <i class="fas fa-download mr-2"></i>Eksportuj Trasę
            </button>
        </div>

        <div x-show="manualEditMode && orders.length > 0" x-transition:enter="transition ease-out duration-200"
            class="bg-slate-50 rounded-lg p-4 border border-slate-200">
            <h4 class="text-base font-semibold text-slate-700 mb-3">Analiza Obecnej Trasy</h4>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 text-center">
                <div class="p-2">
                    <div class="text-lg font-bold text-red-600"
                        x-text="orders.filter(o => o.priority === 'high').length"></div>
                    <div class="text-xs text-slate-500 font-medium">Wysoki Priorytet</div>
                </div>
                <div class="p-2">
                    <div class="text-lg font-bold text-amber-600"
                        x-text="orders.filter(o => o.priority === 'medium').length"></div>
                    <div class="text-xs text-slate-500 font-medium">Średni Priorytet</div>
                </div>
                <div class="p-2">
                    <div class="text-lg font-bold text-green-600"
                        x-text="orders.filter(o => o.priority === 'low').length"></div>
                    <div class="text-xs text-slate-500 font-medium">Niski Priorytet</div>
                </div>
                <div class="p-2">
                    <div class="text-lg font-bold text-slate-800"
                        x-text="'zł' + totalOrderValue.toLocaleString('pl-PL')"></div>
                    <div class="text-xs text-slate-500 font-medium">Całkowita Wartość</div>
                </div>
            </div>
        </div>
    </div>
</div>
