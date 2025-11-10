<div class="bg-white rounded-2xl shadow-sm border border-gray-100" x-data="{ showModal: false }"
    @open-modal.window="showModal = true" @keydown.escape.window="showModal = false">
    <div class="p-4 sm:p-8 border-b border-gray-100">
        <h3 class="text-lg sm:text-xl font-bold text-gray-900">Wymagane działania</h3>
        <p class="text-sm text-gray-500 mt-1">Zamówienia wymagające natychmiastowej uwagi</p>
    </div>
    <div class="p-4 sm:p-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
            <!-- High Priority Items (Pending Orders) -->
            <button wire:click="showPriorityOrders('high')"
                class="bg-gradient-to-br from-red-50 to-red-100 p-4 sm:p-6 rounded-xl border border-red-200 hover:shadow-lg transition-all duration-200 text-left w-full transform hover:scale-105">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-red-500 p-2 rounded-lg">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z">
                            </path>
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-red-700 bg-red-200 px-2 py-1 rounded-full">PILNE</span>
                </div>
                <h4 class="font-semibold text-red-900 mb-2">Wysokiej ważności</h4>
                <p class="text-xl sm:text-2xl font-bold text-red-900 mb-1">{{ $complaintStats['open'] }}</p>
                <p class="text-xs sm:text-sm text-red-600">Oczekujące zamówienia wymagają działania w ciągu 2h</p>
            </button>

            <!-- Medium Priority Items (Processing Orders) -->
            <button wire:click="showPriorityOrders('medium')"
                class="bg-gradient-to-br from-yellow-50 to-yellow-100 p-4 sm:p-6 rounded-xl border border-yellow-200 hover:shadow-lg transition-all duration-200 text-left w-full transform hover:scale-105">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-yellow-500 p-2 rounded-lg">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-yellow-700 bg-yellow-200 px-2 py-1 rounded-full">ŚREDNIE</span>
                </div>
                <h4 class="font-semibold text-yellow-900 mb-2">Średniej ważności</h4>
                <p class="text-xl sm:text-2xl font-bold text-yellow-900 mb-1">{{ $complaintStats['in_progress'] }}</p>
                <p class="text-xs sm:text-sm text-yellow-600">W trakcie realizacji - wymagają monitorowania</p>
            </button>

            <!-- Undelivered Orders -->
            <button wire:click="showPriorityOrders('undelivered')"
                class="bg-gradient-to-br from-orange-50 to-orange-100 p-4 sm:p-6 rounded-xl border border-orange-200 hover:shadow-lg transition-all duration-200 text-left w-full transform hover:scale-105">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-orange-500 p-2 rounded-lg">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-orange-700 bg-orange-200 px-2 py-1 rounded-full">PROBLEMY</span>
                </div>
                <h4 class="font-semibold text-orange-900 mb-2">Niedostarczone</h4>
                <p class="text-xl sm:text-2xl font-bold text-orange-900 mb-1">
                    {{ collect($recentComplaints)->where('status', 'undelivered')->count() }}
                </p>
                <p class="text-xs sm:text-sm text-orange-600">Zamówienia wymagające ponownej dostawy</p>
            </button>
        </div>
    </div>

    <!-- Modal with Alpine.js -->
    @include('livewire.complaints.partials.action-modal')
</div>
