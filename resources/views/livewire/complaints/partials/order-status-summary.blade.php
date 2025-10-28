<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="p-4 sm:p-6 border-b border-gray-100">
        <h3 class="text-base sm:text-lg font-bold text-gray-900">Podsumowanie statusów zamówień</h3>
        <p class="text-sm text-gray-500 mt-1">Podział zamówień ze skargami według statusu</p>
    </div>
    <div class="p-4 sm:p-6">
        <div class="space-y-4">
            <!-- Pending Orders -->
            <button 
                wire:click="viewOrdersByStatus('{{ App\Enums\OrderStatus::PENDING->value }}')"
                class="w-full flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-200 hover:bg-red-100 hover:border-red-300 transition-all duration-200 cursor-pointer group">
                <div class="flex items-center space-x-3">
                    <div class="w-3 h-3 bg-red-500 rounded-full group-hover:scale-110 transition-transform"></div>
                    <span class="text-sm font-medium text-red-700 group-hover:text-red-800">Oczekujące</span>
                    <svg class="w-4 h-4 text-red-400 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
                <span class="text-lg font-bold text-red-900 group-hover:text-red-950">{{ $complaintStats['open'] }}</span>
            </button>

            <!-- In Progress Orders -->
            <button 
                wire:click="viewOrdersByStatus('in_progress')"
                class="w-full flex items-center justify-between p-3 bg-yellow-50 rounded-lg border border-yellow-200 hover:bg-yellow-100 hover:border-yellow-300 transition-all duration-200 cursor-pointer group">
                <div class="flex items-center space-x-3">
                    <div class="w-3 h-3 bg-yellow-500 rounded-full group-hover:scale-110 transition-transform"></div>
                    <span class="text-sm font-medium text-yellow-700 group-hover:text-yellow-800">W realizacji</span>
                    <svg class="w-4 h-4 text-yellow-400 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
                <span class="text-lg font-bold text-yellow-900 group-hover:text-yellow-950">{{ $complaintStats['in_progress'] }}</span>
            </button>

            <!-- Completed Orders -->
            <button 
                wire:click="viewOrdersByStatus('completed')"
                class="w-full flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200 hover:bg-green-100 hover:border-green-300 transition-all duration-200 cursor-pointer group">
                <div class="flex items-center space-x-3">
                    <div class="w-3 h-3 bg-green-500 rounded-full group-hover:scale-110 transition-transform"></div>
                    <span class="text-sm font-medium text-green-700 group-hover:text-green-800">Ukończone</span>
                    <svg class="w-4 h-4 text-green-400 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
                <span class="text-lg font-bold text-green-900 group-hover:text-green-950">{{ $complaintStats['resolved'] }}</span>
            </button>

            @if(isset($complaintStats['canceled']) && $complaintStats['canceled'] > 0)
            <!-- Canceled Orders -->
            <button 
                wire:click="viewOrdersByStatus('{{ App\Enums\OrderStatus::CANCELED->value }}')"
                class="w-full flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 hover:border-gray-300 transition-all duration-200 cursor-pointer group">
                <div class="flex items-center space-x-3">
                    <div class="w-3 h-3 bg-gray-500 rounded-full group-hover:scale-110 transition-transform"></div>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-gray-800">Anulowane</span>
                    <svg class="w-4 h-4 text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
                <span class="text-lg font-bold text-gray-900 group-hover:text-gray-950">{{ $complaintStats['canceled'] }}</span>
            </button>
            @endif
        </div>
    </div>
</div>