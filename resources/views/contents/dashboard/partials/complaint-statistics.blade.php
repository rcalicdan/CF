<!-- Complaint Statistics Section -->
<div class="space-y-8">
    <!-- Complaint Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Complaints -->
        <div
            class="bg-gradient-to-br from-red-50 to-red-100 rounded-2xl shadow-sm border border-red-200 p-6 hover:shadow-lg transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="space-y-2">
                    <p class="text-sm font-medium text-red-700">Całkowite skargi</p>
                    <p class="text-3xl font-bold text-red-900">23</p>
                    <div class="flex items-center space-x-2">
                        <div class="flex items-center bg-orange-100 px-2 py-1 rounded-full">
                            <svg class="w-3 h-3 text-orange-600 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-xs font-semibold text-orange-700">-2</span>
                        </div>
                        <span class="text-xs text-red-600">od zeszłego tygodnia</span>
                    </div>
                </div>
                <div class="bg-red-500 p-3 rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Active Complaints -->
        <div
            class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-2xl shadow-sm border border-yellow-200 p-6 hover:shadow-lg transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="space-y-2">
                    <p class="text-sm font-medium text-yellow-700">Aktywne skargi</p>
                    <p class="text-3xl font-bold text-yellow-900">8</p>
                    <div class="flex items-center space-x-2">
                        <div class="flex items-center bg-red-100 px-2 py-1 rounded-full">
                            <svg class="w-3 h-3 text-red-600 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-xs font-semibold text-red-700">Pilne</span>
                        </div>
                        <span class="text-xs text-yellow-600">wymagają działania</span>
                    </div>
                </div>
                <div class="bg-yellow-500 p-3 rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Resolved Complaints -->
        <div
            class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl shadow-sm border border-green-200 p-6 hover:shadow-lg transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="space-y-2">
                    <p class="text-sm font-medium text-green-700">Rozwiązane skargi</p>
                    <p class="text-3xl font-bold text-green-900">15</p>
                    <div class="flex items-center space-x-2">
                        <div class="flex items-center bg-green-100 px-2 py-1 rounded-full">
                            <svg class="w-3 h-3 text-green-600 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-xs font-semibold text-green-700">+5</span>
                        </div>
                        <span class="text-xs text-green-600">w tym tygodniu</span>
                    </div>
                </div>
                <div class="bg-green-500 p-3 rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Resolution Rate -->
        <div
            class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-2xl shadow-sm border border-indigo-200 p-6 hover:shadow-lg transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="space-y-2">
                    <p class="text-sm font-medium text-indigo-700">Wskaźnik rozwiązań</p>
                    <p class="text-3xl font-bold text-indigo-900">85.2%</p>
                    <div class="flex items-center space-x-2">
                        <div class="flex items-center bg-green-100 px-2 py-1 rounded-full">
                            <svg class="w-3 h-3 text-green-600 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-xs font-semibold text-green-700">+3.2%</span>
                        </div>
                        <span class="text-xs text-indigo-600">od zeszłego miesiąca</span>
                    </div>
                </div>
                <div class="bg-indigo-500 p-3 rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Complaint Content -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        <!-- Recent Complaints List -->
        <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-8 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Najnowsze skargi</h3>
                        <p class="text-sm text-gray-500 mt-1">Ostatnie zgłoszenia od klientów</p>
                    </div>
                    <button
                        class="text-sm font-medium text-red-600 hover:text-red-700 px-4 py-2 rounded-lg hover:bg-red-50 transition-colors">
                        Zobacz wszystkie
                    </button>
                </div>
            </div>
            <div class="divide-y divide-gray-50">
                <!-- Complaint Item 1 -->
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4">
                            <div
                                class="bg-gradient-to-br from-red-500 to-red-600 p-3 rounded-xl shadow-lg flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z">
                                    </path>
                                </svg>
                            </div>
                            <div class="space-y-2 flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="text-base font-semibold text-gray-900">Dywan uszkodzony podczas prania
                                    </p>
                                    <span class="text-xs text-gray-400">2 godziny temu</span>
                                </div>
                                <p class="text-sm text-gray-600">Maria Kowalska • Zamówienie #LD-1247 • Kod:
                                    ORD-1247-3421</p>
                                <p class="text-sm text-gray-500 line-clamp-2">Dywan perski został uszkodzony podczas
                                    procesu prania. Widoczne są przebarwienia i rozdarcie w dolnej części...</p>
                                <div class="flex items-center space-x-3 mt-3">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Nowa</span>
                                    <span class="text-xs text-gray-500">Priorytet: Wysoki</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Complaint Item 2 -->
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4">
                            <div
                                class="bg-gradient-to-br from-yellow-500 to-yellow-600 p-3 rounded-xl shadow-lg flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="space-y-2 flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="text-base font-semibold text-gray-900">Opóźnienie w dostawie</p>
                                    <span class="text-xs text-gray-400">5 godzin temu</span>
                                </div>
                                <p class="text-sm text-gray-600">Jan Nowak • Zamówienie #LD-1245 • Kod: ORD-1245-7892
                                </p>
                                <p class="text-sm text-gray-500 line-clamp-2">Zamówiony dywan miał być dostarczony
                                    wczoraj o 14:00. Do tej pory nie otrzymałem informacji o statusie...</p>
                                <div class="flex items-center space-x-3 mt-3">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">W
                                        trakcie</span>
                                    <span class="text-xs text-gray-500">Priorytet: Średni</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Complaint Item 3 -->
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4">
                            <div
                                class="bg-gradient-to-br from-green-500 to-green-600 p-3 rounded-xl shadow-lg flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="space-y-2 flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="text-base font-semibold text-gray-900">Niezadowolenie z jakości prania
                                    </p>
                                    <span class="text-xs text-gray-400">1 dzień temu</span>
                                </div>
                                <p class="text-sm text-gray-600">Anna Wiśniewska • Zamówienie #LD-1243 • Kod:
                                    ORD-1243-5614</p>
                                <p class="text-sm text-gray-500 line-clamp-2">Po praniu dywan nadal ma plamy i
                                    nieprzyjemny zapach. Oczekuję ponownego prania lub zwrotu kosztów...</p>
                                <div class="flex items-center space-x-3 mt-3">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Rozwiązana</span>
                                    <span class="text-xs text-gray-500">Priorytet: Niski</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Complaint Item 4 -->
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4">
                            <div
                                class="bg-gradient-to-br from-orange-500 to-orange-600 p-3 rounded-xl shadow-lg flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                    </path>
                                </svg>
                            </div>
                            <div class="space-y-2 flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="text-base font-semibold text-gray-900">Problem z komunikacją</p>
                                    <span class="text-xs text-gray-400">2 dni temu</span>
                                </div>
                                <p class="text-sm text-gray-600">Piotr Zieliński • Zamówienie #LD-1241 • Kod:
                                    ORD-1241-9876</p>
                                <p class="text-sm text-gray-500 line-clamp-2">Brak informacji o statusie zamówienia.
                                    Próbowałem dzwonić kilka razy, ale nikt nie odbiera telefonu...</p>
                                <div class="flex items-center space-x-3 mt-3">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Oczekuje</span>
                                    <span class="text-xs text-gray-500">Priorytet: Średni</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Complaint Analytics -->
        <div class="space-y-6">
            <!-- Complaint Status Distribution -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900">Status skarg</h3>
                    <p class="text-sm text-gray-500 mt-1">Rozkład według statusu</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Nowe</span>
                                <span class="text-sm font-bold text-red-600">5</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="bg-gradient-to-r from-red-500 to-red-600 h-2 rounded-full"
                                    style="width: 21.7%"></div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">W trakcie</span>
                                <span class="text-sm font-bold text-yellow-600">3</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 h-2 rounded-full"
                                    style="width: 13%"></div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Rozwiązane</span>
                                <span class="text-sm font-bold text-green-600">15</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="bg-gradient-to-r from-green-500 to-green-600 h-2 rounded-full"
                                    style="width: 65.3%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Complaint Categories -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900">Kategorie skarg</h3>
                    <p class="text-sm text-gray-500 mt-1">Najczęstsze problemy</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                <span class="text-sm font-medium text-gray-700">Uszkodzenia</span>
                            </div>
                            <span class="text-sm font-bold text-gray-900">8</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                <span class="text-sm font-medium text-gray-700">Opóźnienia</span>
                            </div>
                            <span class="text-sm font-bold text-gray-900">6</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                                <span class="text-sm font-medium text-gray-700">Jakość prania</span>
                            </div>
                            <span class="text-sm font-bold text-gray-900">5</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-purple-500 rounded-full"></div>
                                <span class="text-sm font-medium text-gray-700">Komunikacja</span>
                            </div>
                            <span class="text-sm font-bold text-gray-900">3</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                <span class="text-sm font-medium text-gray-700">Inne</span>
                            </div>
                            <span class="text-sm font-bold text-gray-900">1</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Response Time Stats -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900">Czas reakcji</h3>
                    <p class="text-sm text-gray-500 mt-1">Średnie czasy</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Pierwsza odpowiedź</span>
                                <span class="text-sm font-bold text-blue-600">2.3h</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Czas rozwiązania</span>
                                <span class="text-sm font-bold text-green-600">18.5h</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Satysfakcja klienta</span>
                                <span class="text-sm font-bold text-purple-600">4.2/5</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Trend Chart -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="p-8 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">Trend tygodniowy skarg</h3>
                    <p class="text-sm text-gray-500 mt-1">Ostatnie 7 dni</p>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                        <span class="text-sm text-gray-600">Nowe skargi</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <span class="text-sm text-gray-600">Rozwiązane</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="p-8">
            <div class="relative">
                <!-- Simple Bar Chart -->
                <div class="flex items-end justify-between space-x-2 h-40">
                    <!-- Monday -->
                    <div class="flex flex-col items-center space-y-2 flex-1">
                        <div class="w-full flex items-end space-x-1">
                            <div class="bg-red-500 rounded-t-sm flex-1" style="height: 60px;"></div>
                            <div class="bg-green-500 rounded-t-sm flex-1" style="height: 40px;"></div>
                        </div>
                        <span class="text-xs text-gray-500">Pon</span>
                    </div>
                    <!-- Tuesday -->
                    <div class="flex flex-col items-center space-y-2 flex-1">
                        <div class="w-full flex items-end space-x-1">
                            <div class="bg-red-500 rounded-t-sm flex-1" style="height: 45px;"></div>
                            <div class="bg-green-500 rounded-t-sm flex-1" style="height: 50px;"></div>
                        </div>
                        <span class="text-xs text-gray-500">Wt</span>
                    </div>
                    <!-- Wednesday -->
                    <div class="flex flex-col items-center space-y-2 flex-1">
                        <div class="w-full flex items-end space-x-1">
                            <div class="bg-red-500 rounded-t-sm flex-1" style="height: 30px;"></div>
                            <div class="bg-green-500 rounded-t-sm flex-1" style="height: 35px;"></div>
                        </div>
                        <span class="text-xs text-gray-500">Śr</span>
                    </div>
                    <!-- Thursday -->
                    <div class="flex flex-col items-center space-y-2 flex-1">
                        <div class="w-full flex items-end space-x-1">
                            <div class="bg-red-500 rounded-t-sm flex-1" style="height: 40px;"></div>
                            <div class="bg-green-500 rounded-t-sm flex-1" style="height: 55px;"></div>
                        </div>
                        <span class="text-xs text-gray-500">Czw</span>
                    </div>
                    <!-- Friday -->
                    <div class="flex flex-col items-center space-y-2 flex-1">
                        <div class="w-full flex items-end space-x-1">
                            <div class="bg-green-500 rounded-t-sm flex-1" style="height: 45px;"></div>
                        </div>
                        <span class="text-xs text-gray-500">Pt</span>
                    </div>
                    <!-- Saturday -->
                    <div class="flex flex-col items-center space-y-2 flex-1">
                        <div class="w-full flex items-end space-x-1">
                            <div class="bg-red-500 rounded-t-sm flex-1" style="height: 35px;"></div>
                            <div class="bg-green-500 rounded-t-sm flex-1" style="height: 40px;"></div>
                        </div>
                        <span class="text-xs text-gray-500">Sob</span>
                    </div>
                    <!-- Sunday -->
                    <div class="flex flex-col items-center space-y-2 flex-1">
                        <div class="w-full flex items-end space-x-1">
                            <div class="bg-red-500 rounded-t-sm flex-1" style="height: 25px;"></div>
                            <div class="bg-green-500 rounded-t-sm flex-1" style="height: 30px;"></div>
                        </div>
                        <span class="text-xs text-gray-500">Nie</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Items -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="p-8 border-b border-gray-100">
            <h3 class="text-xl font-bold text-gray-900">Wymagane działania</h3>
            <p class="text-sm text-gray-500 mt-1">Skargi wymagające natychmiastowej uwagi</p>
        </div>
        <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- High Priority Items -->
                <div class="bg-gradient-to-br from-red-50 to-red-100 p-6 rounded-xl border border-red-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-red-500 p-2 rounded-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z">
                                </path>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-red-700 bg-red-200 px-2 py-1 rounded-full">PILNE</span>
                    </div>
                    <h4 class="font-semibold text-red-900 mb-2">Wysokiej ważności</h4>
                    <p class="text-2xl font-bold text-red-900 mb-1">3</p>
                    <p class="text-sm text-red-600">Wymagają działania w ciągu 2h</p>
                </div>

                <!-- Medium Priority Items -->
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 p-6 rounded-xl border border-yellow-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-yellow-500 p-2 rounded-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span
                            class="text-xs font-bold text-yellow-700 bg-yellow-200 px-2 py-1 rounded-full">ŚREDNIE</span>
                    </div>
                    <h4 class="font-semibold text-yellow-900 mb-2">Średniej ważności</h4>
                    <p class="text-2xl font-bold text-yellow-900 mb-1">5</p>
                    <p class="text-sm text-yellow-600">Wymagają działania w ciągu 24h</p>
                </div>

                <!-- Follow-up Items -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-xl border border-blue-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-blue-500 p-2 rounded-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                </path>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-blue-700 bg-blue-200 px-2 py-1 rounded-full">KONTAKT</span>
                    </div>
                    <h4 class="font-semibold text-blue-900 mb-2">Wymagają kontaktu</h4>
                    <p class="text-2xl font-bold text-blue-900 mb-1">7</p>
                    <p class="text-sm text-blue-600">Czekają na odpowiedź klienta</p>
                </div>
            </div>
        </div>
    </div>
</div>
