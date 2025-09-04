<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
    <!-- Total Order Complaints -->
    <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-2xl shadow-sm border border-red-200 p-4 sm:p-6 hover:shadow-lg transition-all duration-300">
        <div class="flex items-start justify-between">
            <div class="space-y-2">
                <p class="text-sm font-medium text-red-700">Zamówienia ze skargami</p>
                <p class="text-2xl sm:text-3xl font-bold text-red-900">{{ $complaintStats['total'] }}</p>
                <div class="flex items-center space-x-2">
                    <div class="flex items-center {{ $complaintStats['weekly_change'] >= 0 ? 'bg-red-100' : 'bg-green-100' }} px-2 py-1 rounded-full">
                        <svg class="w-3 h-3 {{ $complaintStats['weekly_change'] >= 0 ? 'text-red-600' : 'text-green-600' }} mr-1" fill="currentColor" viewBox="0 0 20 20">
                            @if ($complaintStats['weekly_change'] >= 0)
                                <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 8.707a1 1 0 01-1.414-1.414z" clip-rule="evenodd"></path>
                            @else
                                <path fill-rule="evenodd" d="M14.707 12.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            @endif
                        </svg>
                        <span class="text-xs font-semibold {{ $complaintStats['weekly_change'] >= 0 ? 'text-red-700' : 'text-green-700' }}">
                            {{ $complaintStats['weekly_change'] >= 0 ? '+' : '' }}{{ $complaintStats['weekly_change'] }}
                        </span>
                    </div>
                    <span class="text-xs text-red-600">od ostatniego okresu</span>
                </div>
            </div>
            <div class="bg-red-500 p-3 rounded-xl shadow-lg">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Pending Orders (Open Complaints) -->
    <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-2xl shadow-sm border border-yellow-200 p-4 sm:p-6 hover:shadow-lg transition-all duration-300">
        <div class="flex items-start justify-between">
            <div class="space-y-2">
                <p class="text-sm font-medium text-yellow-700">Oczekujące zamówienia</p>
                <p class="text-2xl sm:text-3xl font-bold text-yellow-900">{{ $complaintStats['open'] }}</p>
                <div class="flex items-center space-x-2">
                    <div class="flex items-center bg-red-100 px-2 py-1 rounded-full">
                        <svg class="w-3 h-3 text-red-600 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-xs font-semibold text-red-700">Pilne</span>
                    </div>
                    <span class="text-xs text-yellow-600">wymagają działania</span>
                </div>
            </div>
            <div class="bg-yellow-500 p-3 rounded-xl shadow-lg">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Completed/Delivered Orders -->
    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl shadow-sm border border-green-200 p-4 sm:p-6 hover:shadow-lg transition-all duration-300">
        <div class="flex items-start justify-between">
            <div class="space-y-2">
                <p class="text-sm font-medium text-green-700">Zrealizowane zamówienia</p>
                <p class="text-2xl sm:text-3xl font-bold text-green-900">{{ $complaintStats['resolved'] }}</p>
                <div class="flex items-center space-x-2">
                    <div class="flex items-center bg-green-100 px-2 py-1 rounded-full">
                        <svg class="w-3 h-3 text-green-600 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-xs font-semibold text-green-700">+{{ $complaintStats['resolved'] }}</span>
                    </div>
                    <span class="text-xs text-green-600">w tym okresie</span>
                </div>
            </div>
            <div class="bg-green-500 p-3 rounded-xl shadow-lg">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Resolution Rate -->
    <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-2xl shadow-sm border border-indigo-200 p-4 sm:p-6 hover:shadow-lg transition-all duration-300">
        <div class="flex items-start justify-between">
            <div class="space-y-2">
                <p class="text-sm font-medium text-indigo-700">Wskaźnik realizacji</p>
                <p class="text-2xl sm:text-3xl font-bold text-indigo-900">{{ $complaintStats['resolution_rate'] }}%</p>
                <div class="flex items-center space-x-2">
                    <div class="flex items-center {{ $complaintStats['resolution_rate'] >= 80 ? 'bg-green-100' : 'bg-yellow-100' }} px-2 py-1 rounded-full">
                        <svg class="w-3 h-3 {{ $complaintStats['resolution_rate'] >= 80 ? 'text-green-600' : 'text-yellow-600' }} mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-xs font-semibold {{ $complaintStats['resolution_rate'] >= 80 ? 'text-green-700' : 'text-yellow-700' }}">
                            {{ $complaintStats['resolution_rate'] >= 80 ? 'Dobry' : 'Średni' }}
                        </span>
                    </div>
                    <span class="text-xs text-indigo-600">poziom</span>
                </div>
            </div>
            <div class="bg-indigo-500 p-3 rounded-xl shadow-lg">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>