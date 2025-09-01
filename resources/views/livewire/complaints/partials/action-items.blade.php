<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="p-4 sm:p-8 border-b border-gray-100">
        <h3 class="text-lg sm:text-xl font-bold text-gray-900">Wymagane działania</h3>
        <p class="text-sm text-gray-500 mt-1">Zamówienia-skargi wymagające natychmiastowej uwagi</p>
    </div>
    <div class="p-4 sm:p-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
            <!-- High Priority Items (Pending Complaints) -->
            <div class="bg-gradient-to-br from-red-50 to-red-100 p-4 sm:p-6 rounded-xl border border-red-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-red-500 p-2 rounded-lg">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-red-700 bg-red-200 px-2 py-1 rounded-full">PILNE</span>
                </div>
                <h4 class="font-semibold text-red-900 mb-2">Wysokiej ważności</h4>
                <p class="text-xl sm:text-2xl font-bold text-red-900 mb-1">{{ $complaintStats['pending'] }}</p>
                <p class="text-xs sm:text-sm text-red-600">Oczekujące skargi wymagają działania w ciągu 2h</p>
            </div>

            <!-- Medium Priority Items (Processing Complaints) -->
            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 p-4 sm:p-6 rounded-xl border border-yellow-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-yellow-500 p-2 rounded-lg">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-yellow-700 bg-yellow-200 px-2 py-1 rounded-full">ŚREDNIE</span>
                </div>
                <h4 class="font-semibold text-yellow-900 mb-2">Średniej ważności</h4>
                <p class="text-xl sm:text-2xl font-bold text-yellow-900 mb-1">{{ $complaintStats['processing'] }}</p>
                <p class="text-xs sm:text-sm text-yellow-600">W trakcie realizacji - wymagają monitorowania</p>
            </div>

            <!-- Canceled/Resolved Items -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 sm:p-6 rounded-xl border border-blue-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-blue-500 p-2 rounded-lg">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-blue-700 bg-blue-200 px-2 py-1 rounded-full">ANULOWANE</span>
                </div>
                <h4 class="font-semibold text-blue-900 mb-2">Anulowane skargi</h4>
                <p class="text-xl sm:text-2xl font-bold text-blue-900 mb-1">{{ $complaintStats['canceled'] }}</p>
                <p class="text-xs sm:text-sm text-blue-600">Wymagają analizy przyczyn</p>
            </div>
        </div>
    </div>
</div>