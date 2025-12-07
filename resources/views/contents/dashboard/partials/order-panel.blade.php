<div class="max-w-[1920px] mx-auto px-6 sm:px-8 lg:px-10 py-8">
    <div class="mb-8">
        <!-- Order Status Summary Cards -->
        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Pending Orders Card -->
                <div
                    class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-2xl shadow-sm border border-yellow-200 p-6 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-start justify-between">
                        <div class="space-y-2">
                            <p class="text-sm font-medium text-yellow-700">Oczekujące</p>
                            <p class="text-3xl font-bold text-yellow-900">1</p>
                            <div class="flex items-center space-x-2">
                                <div class="flex items-center bg-yellow-200 px-2 py-1 rounded-full">
                                    <svg class="w-3 h-3 text-yellow-700 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-xs font-semibold text-yellow-800">Nowe</span>
                                </div>
                                <span class="text-xs text-yellow-600">zamówienia</span>
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

                <!-- In Progress Orders Card -->
                <div
                    class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl shadow-sm border border-blue-200 p-6 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-start justify-between">
                        <div class="space-y-2">
                            <p class="text-sm font-medium text-blue-700">W realizacji</p>
                            <p class="text-3xl font-bold text-blue-900">0</p>
                            <div class="flex items-center space-x-2">
                                <div class="flex items-center bg-blue-200 px-2 py-1 rounded-full">
                                    <svg class="w-3 h-3 text-blue-700 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-xs font-semibold text-blue-800">Aktywne</span>
                                </div>
                                <span class="text-xs text-blue-600">w trakcie</span>
                            </div>
                        </div>
                        <div class="bg-blue-500 p-3 rounded-xl shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Completed Orders Card -->
                <div
                    class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl shadow-sm border border-green-200 p-6 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-start justify-between">
                        <div class="space-y-2">
                            <p class="text-sm font-medium text-green-700">Zrealizowane zamówienia</p>
                            <p class="text-3xl font-bold text-green-900">0</p>
                            <div class="flex items-center space-x-2">
                                <div class="flex items-center bg-green-200 px-2 py-1 rounded-full">
                                    <svg class="w-3 h-3 text-green-700 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-xs font-semibold text-green-800">Gotowe</span>
                                </div>
                                <span class="text-xs text-green-600">zakończone</span>
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
            </div>
        </div>
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <a href="/orders/create"
                class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 w-full lg:w-auto">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Dodaj zamówienie
            </a>

            <div class="flex flex-col sm:flex-row gap-4 w-full lg:w-auto">
                <div class="relative">
                    <select
                        class="appearance-none w-full sm:w-auto px-4 py-3 pr-10 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                        <option value="3">Ostatnie 3 dni</option>
                        <option value="7" selected>Ostatnie 7 dni</option>
                        <option value="30">Ostatnie 30 dni</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </div>
                </div>

                <div class="relative flex-1 lg:min-w-[400px]">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text"
                        class="w-full pl-11 pr-4 py-3 bg-white border border-gray-200 rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                        placeholder="Nr zamówienia, Imię i Nazwisko, Adres lub Miasto">
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-2xl shadow-sm border border-yellow-200">
            <div class="p-6 border-b border-yellow-200 bg-white/50 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 p-2.5 rounded-xl shadow-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Oczekujące</h3>
                            <p class="text-xs text-gray-600">Nowe zamówienia</p>
                        </div>
                    </div>
                    <span
                        class="bg-yellow-500 text-white text-sm font-bold px-3 py-1.5 rounded-full shadow-sm">4</span>
                </div>
            </div>

            <div class="p-4 space-y-4 max-h-[calc(100vh-280px)] overflow-y-auto">
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200">
                    <div class="p-5">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <div class="flex items-center space-x-2 mb-1">
                                    <span class="text-sm font-bold text-gray-900">Nr zam: 3183</span>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Oczekujące
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500">5 grudnia 2025 16:02</p>
                            </div>
                        </div>

                        <div class="mb-4 pb-4 border-b border-gray-100">
                            <div class="flex items-center space-x-2 text-sm text-gray-700 mb-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <span class="font-medium">Produkty:</span>
                            </div>
                            <div class="space-y-2 ml-6">
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-gray-700">Pranie obuwia</span>
                                        <div class="relative group">
                                            <svg class="w-4 h-4 text-gray-400 cursor-help" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                            <div
                                                class="absolute left-0 bottom-full mb-2 hidden group-hover:block w-48 p-2 bg-gray-900 text-white text-xs rounded-lg shadow-lg z-10">
                                                Dodatkowe usługi: Impregnacja
                                            </div>
                                        </div>
                                    </div>
                                    <span class="text-gray-600">1 szt</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-700">Oznownienie</span>
                                    <span class="text-gray-600">1 szt</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 pb-4 border-b border-gray-100">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Do zapłaty (brutto):</span>
                                <span class="text-lg font-bold text-gray-900">65.00 PLN</span>
                            </div>
                            <div class="flex items-center space-x-2 mt-2">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Opłacone
                                </span>
                                <span class="text-xs text-gray-500">Gotówka</span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="flex items-center space-x-2 text-sm mb-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                    </path>
                                </svg>
                                <span class="font-medium text-gray-900">Klient</span>
                            </div>
                            <div class="ml-6 space-y-1">
                                <p class="text-sm text-gray-700">siedziba przyjmutna</p>
                                <p class="text-sm font-semibold text-gray-900">Bednorz</p>
                                <p class="text-xs text-gray-500">662394940</p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="flex items-center space-x-2 text-sm mb-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                <span class="font-medium text-gray-900">Kierowca:</span>
                            </div>
                            <div class="ml-6">
                                <p class="text-sm font-semibold text-indigo-600">Andrzej Śledź</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            <button
                                class="flex-1 px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-200">
                                Szczegóły
                            </button>
                            <button
                                class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-all duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z">
                                    </path>
                                </svg>
                            </button>
                        </div>

                        <div class="mt-3 pt-3 border-t border-gray-100">
                            <p class="text-xs text-gray-500">Przyjęcie przez: <span
                                    class="font-medium text-gray-700">Monika</span></p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200">
                    <div class="p-5">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <div class="flex items-center space-x-2 mb-1">
                                    <span class="text-sm font-bold text-gray-900">Nr zam: 3185</span>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Oczekujące
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500">5 grudnia 2025 17:15</p>
                            </div>
                        </div>

                        <div class="mb-4 pb-4 border-b border-gray-100">
                            <div class="flex items-center space-x-2 text-sm text-gray-700 mb-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <span class="font-medium">Produkty:</span>
                            </div>
                            <div class="space-y-2 ml-6">
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-gray-700">Pranie dywanu</span>
                                        <div class="relative group">
                                            <svg class="w-4 h-4 text-gray-400 cursor-help" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                            <div
                                                class="absolute left-0 bottom-full mb-2 hidden group-hover:block w-48 p-2 bg-gray-900 text-white text-xs rounded-lg shadow-lg z-10">
                                                Dodatkowe usługi: Usuwanie plam, Odświeżanie
                                            </div>
                                        </div>
                                    </div>
                                    <span class="text-gray-600">2 szt</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 pb-4 border-b border-gray-100">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Do zapłaty (brutto):</span>
                                <span class="text-lg font-bold text-gray-900">180.00 PLN</span>
                            </div>
                            <div class="flex items-center space-x-2 mt-2">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-orange-100 text-orange-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Oczekuje
                                </span>
                                <span class="text-xs text-gray-500">Przelew</span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="flex items-center space-x-2 text-sm mb-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                    </path>
                                </svg>
                                <span class="font-medium text-gray-900">Klient</span>
                            </div>
                            <div class="ml-6 space-y-1">
                                <p class="text-sm text-gray-700">Centrum Handlowe</p>
                                <p class="text-sm font-semibold text-gray-900">Kowalski Jan</p>
                                <p class="text-xs text-gray-500">501234567</p>
                            </div>
                        </div>

                        <div class="mb-4 p-3 bg-orange-50 border border-orange-200 rounded-lg">
                            <div class="flex items-center space-x-2 text-sm text-orange-700">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                <span class="font-medium">Brak przypisanego kierowcy</span>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            <button
                                class="flex-1 px-4 py-2.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-200">
                                Dodaj kierowcę
                            </button>
                            <button
                                class="px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-200">
                                Szczegóły
                            </button>
                        </div>

                        <div class="mt-3 pt-3 border-t border-gray-100">
                            <p class="text-xs text-gray-500">Przyjęcie przez: <span
                                    class="font-medium text-gray-700">Anna</span></p>
                        </div>
                    </div>
                </div>

                <div class="hidden text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                        </path>
                    </svg>
                    <p class="text-gray-500 font-medium">Brak oczekujących zamówień</p>
                    <p class="text-sm text-gray-400 mt-1">Nowe zamówienia pojawią się tutaj</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl shadow-sm border border-blue-200">
            <div class="p-6 border-b border-blue-200 bg-white/50 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-2.5 rounded-xl shadow-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">W realizacji</h3>
                            <p class="text-xs text-gray-600">Zamówienia w trakcie</p>
                        </div>
                    </div>
                    <span class="bg-blue-500 text-white text-sm font-bold px-3 py-1.5 rounded-full shadow-sm">2</span>
                </div>
            </div>

            <div class="p-4 space-y-4 max-h-[calc(100vh-280px)] overflow-y-auto">
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200">
                    <div class="p-5">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <div class="flex items-center space-x-2 mb-1">
                                    <span class="text-sm font-bold text-gray-900">Nr zam: 3180</span>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        W realizacji
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500">4 grudnia 2025 14:30</p>
                            </div>
                        </div>

                        <div class="mb-4 pb-4 border-b border-gray-100">
                            <div class="flex items-center space-x-2 text-sm text-gray-700 mb-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <span class="font-medium">Produkty:</span>
                            </div>
                            <div class="space-y-2 ml-6">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-700">Pranie tapicerki</span>
                                    <span class="text-gray-600">3 szt</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 pb-4 border-b border-gray-100">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Do zapłaty (brutto):</span>
                                <span class="text-lg font-bold text-gray-900">240.00 PLN</span>
                            </div>
                            <div class="flex items-center space-x-2 mt-2">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Opłacone
                                </span>
                                <span class="text-xs text-gray-500">Karta</span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="flex items-center space-x-2 text-sm mb-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                    </path>
                                </svg>
                                <span class="font-medium text-gray-900">Klient</span>
                            </div>
                            <div class="ml-6 space-y-1">
                                <p class="text-sm text-gray-700">Hotel Centrum</p>
                                <p class="text-sm font-semibold text-gray-900">Nowak Maria</p>
                                <p class="text-xs text-gray-500">600111222</p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="flex items-center space-x-2 text-sm mb-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                <span class="font-medium text-gray-900">Kierowca:</span>
                            </div>
                            <div class="ml-6">
                                <p class="text-sm font-semibold text-indigo-600">Piotr Kowalczyk</p>
                            </div>
                        </div>

                        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-semibold text-blue-700">Status realizacji</span>
                                <span class="text-xs font-bold text-blue-700">60%</span>
                            </div>
                            <div class="w-full bg-blue-100 rounded-full h-2">
                                <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full transition-all duration-300"
                                    style="width: 60%"></div>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            <button
                                class="flex-1 px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-200">
                                Szczegóły
                            </button>
                            <button
                                class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-all duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z">
                                    </path>
                                </svg>
                            </button>
                        </div>

                        <div class="mt-3 pt-3 border-t border-gray-100">
                            <p class="text-xs text-gray-500">Przyjęcie przez: <span
                                    class="font-medium text-gray-700">Katarzyna</span></p>
                        </div>
                    </div>
                </div>

                <div class="hidden text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <p class="text-gray-500 font-medium">Brak zamówień w realizacji</p>
                    <p class="text-sm text-gray-400 mt-1">Przyjęte zamówienia pojawią się tutaj</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl shadow-sm border border-green-200">
            <div class="p-6 border-b border-green-200 bg-white/50 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="bg-gradient-to-br from-green-500 to-green-600 p-2.5 rounded-xl shadow-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Zrealizowane</h3>
                            <p class="text-xs text-gray-600">Zakończone zamówienia</p>
                        </div>
                    </div>
                    <span class="bg-green-500 text-white text-sm font-bold px-3 py-1.5 rounded-full shadow-sm">6</span>
                </div>
            </div>

            <div class="p-4 space-y-4 max-h-[calc(100vh-280px)] overflow-y-auto">
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200">
                    <div class="p-5">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <div class="flex items-center space-x-2 mb-1">
                                    <span class="text-sm font-bold text-gray-900">Nr zam: 3175</span>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Zrealizowane
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500">3 grudnia 2025 10:00</p>
                            </div>
                        </div>

                        <div class="mb-4 pb-4 border-b border-gray-100">
                            <div class="flex items-center space-x-2 text-sm text-gray-700 mb-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <span class="font-medium">Produkty:</span>
                            </div>
                            <div class="space-y-2 ml-6">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-700">Pranie dywanu</span>
                                    <span class="text-gray-600">1 szt</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-700">Czyszczenie kanapy</span>
                                    <span class="text-gray-600">1 szt</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 pb-4 border-b border-gray-100">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Do zapłaty (brutto):</span>
                                <span class="text-lg font-bold text-gray-900">320.00 PLN</span>
                            </div>
                            <div class="flex items-center space-x-2 mt-2">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Opłacone
                                </span>
                                <span class="text-xs text-gray-500">Gotówka</span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="flex items-center space-x-2 text-sm mb-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                    </path>
                                </svg>
                                <span class="font-medium text-gray-900">Klient</span>
                            </div>
                            <div class="ml-6 space-y-1">
                                <p class="text-sm text-gray-700">Osiedle Słoneczne</p>
                                <p class="text-sm font-semibold text-gray-900">Wiśniewski Tomasz</p>
                                <p class="text-xs text-gray-500">509876543</p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="flex items-center space-x-2 text-sm mb-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                <span class="font-medium text-gray-900">Kierowca:</span>
                            </div>
                            <div class="ml-6">
                                <p class="text-sm font-semibold text-indigo-600">Andrzej Śledź</p>
                            </div>
                        </div>

                        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex items-center space-x-2 text-sm text-green-700">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                <span class="font-medium">Zakończono: 5 grudnia 2025, 16:45</span>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            <button
                                class="flex-1 px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-200">
                                Szczegóły
                            </button>
                            <button
                                class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-all duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z">
                                    </path>
                                </svg>
                            </button>
                        </div>

                        <div class="mt-3 pt-3 border-t border-gray-100">
                            <p class="text-xs text-gray-500">Przyjęcie przez: <span
                                    class="font-medium text-gray-700">Monika</span></p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200">
                    <div class="p-5">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <div class="flex items-center space-x-2 mb-1">
                                    <span class="text-sm font-bold text-gray-900">Nr zam: 3172</span>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Zrealizowane
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500">2 grudnia 2025 09:20</p>
                            </div>
                        </div>

                        <div class="mb-4 pb-4 border-b border-gray-100">
                            <div class="flex items-center space-x-2 text-sm text-gray-700 mb-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <span class="font-medium">Produkty:</span>
                            </div>
                            <div class="space-y-2 ml-6">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-700">Pranie firan</span>
                                    <span class="text-gray-600">5 szt</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 pb-4 border-b border-gray-100">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Do zapłaty (brutto):</span>
                                <span class="text-lg font-bold text-gray-900">150.00 PLN</span>
                            </div>
                            <div class="flex items-center space-x-2 mt-2">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Opłacone
                                </span>
                                <span class="text-xs text-gray-500">Przelew</span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="flex items-center space-x-2 text-sm mb-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                    </path>
                                </svg>
                                <span class="font-medium text-gray-900">Klient</span>
                            </div>
                            <div class="ml-6 space-y-1">
                                <p class="text-sm text-gray-700">Restauracja pod Kogutem</p>
                                <p class="text-sm font-semibold text-gray-900">Zielińska Anna</p>
                                <p class="text-xs text-gray-500">512333444</p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="flex items-center space-x-2 text-sm mb-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                <span class="font-medium text-gray-900">Kierowca:</span>
                            </div>
                            <div class="ml-6">
                                <p class="text-sm font-semibold text-indigo-600">Piotr Kowalczyk</p>
                            </div>
                        </div>

                        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex items-center space-x-2 text-sm text-green-700">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                <span class="font-medium">Zakończono: 4 grudnia 2025, 14:30</span>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            <button
                                class="flex-1 px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-200">
                                Szczegóły
                            </button>
                            <button
                                class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-all duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z">
                                    </path>
                                </svg>
                            </button>
                        </div>

                        <div class="mt-3 pt-3 border-t border-gray-100">
                            <p class="text-xs text-gray-500">Przyjęcie przez: <span
                                    class="font-medium text-gray-700">Anna</span></p>
                        </div>
                    </div>
                </div>

                <div class="hidden text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-gray-500 font-medium">Brak zrealizowanych zamówień</p>
                    <p class="text-sm text-gray-400 mt-1">Zakończone zamówienia pojawią się tutaj</p>
                </div>
            </div>
        </div>
    </div>
</div>
