<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="p-4 sm:p-6 border-b border-gray-100">
        <h3 class="text-base sm:text-lg font-bold text-gray-900">Podsumowanie statusów zamówień</h3>
        <p class="text-sm text-gray-500 mt-1">Podział zamówień ze skargami według statusu</p>
    </div>
    <div class="p-4 sm:p-6">
        <div class="space-y-4">
            <!-- Pending Orders -->
            <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-200">
                <div class="flex items-center space-x-3">
                    <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                    <span class="text-sm font-medium text-red-700">Oczekujące</span>
                </div>
                <span class="text-lg font-bold text-red-900">{{ $complaintStats['open'] }}</span>
            </div>

            <!-- In Progress Orders -->
            <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                <div class="flex items-center space-x-3">
                    <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                    <span class="text-sm font-medium text-yellow-700">W realizacji</span>
                </div>
                <span class="text-lg font-bold text-yellow-900">{{ $complaintStats['in_progress'] }}</span>
            </div>

            <!-- Completed Orders -->
            <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200">
                <div class="flex items-center space-x-3">
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    <span class="text-sm font-medium text-green-700">Ukończone</span>
                </div>
                <span class="text-lg font-bold text-green-900">{{ $complaintStats['resolved'] }}</span>
            </div>

            @if(isset($complaintStats['canceled']) && $complaintStats['canceled'] > 0)
            <!-- Canceled Orders -->
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                <div class="flex items-center space-x-3">
                    <div class="w-3 h-3 bg-gray-500 rounded-full"></div>
                    <span class="text-sm font-medium text-gray-700">Anulowane</span>
                </div>
                <span class="text-lg font-bold text-gray-900">{{ $complaintStats['canceled'] }}</span>
            </div>
            @endif
        </div>
    </div>
</div>