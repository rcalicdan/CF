<!-- Delivery Orders -->
<div class="bg-white rounded-xl custom-shadow p-4 lg:p-6">
    <h2 class="text-lg lg:text-xl font-semibold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-box text-primary mr-2"></i>
        <span x-text="getDateStatusText()"></span>
        <span class="ml-auto bg-primary text-white px-2 py-1 rounded-full text-sm" x-text="orders.length"></span>
    </h2>

    <!-- Orders List -->
    <div x-show="orders.length > 0" class="space-y-3 max-h-48 lg:max-h-64 overflow-y-auto">
        <template x-for="order in orders" :key="order.id">
            <div class="p-3 lg:p-4 border rounded-lg hover:bg-gray-50 transition-colors"
                :class="!order.has_coordinates ? 'border-red-300 bg-red-50' : ''">
                <div class="flex justify-between items-start">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                            <div class="font-medium text-gray-800" x-text="'Zamówienie #' + order.id">
                            </div>
                            <div :class="'priority-' + order.priority" class="priority-indicator">
                            </div>
                            <!-- No Coordinates Badge -->
                            <div x-show="!order.has_coordinates"
                                class="flex items-center gap-1 bg-red-500 text-white px-2 py-0.5 rounded-full text-xs font-medium"
                                title="Brak współrzędnych geograficznych">
                                <i class="fas fa-map-marker-alt-slash"></i>
                                <span>Brak GPS</span>
                            </div>
                        </div>
                        <div class="text-sm text-gray-600 truncate" x-text="order.client_name">
                        </div>
                        <div class="text-sm font-medium truncate"
                            :class="!order.has_coordinates ? 'text-red-600' : 'text-primary'" x-text="order.address">
                        </div>
                        <!-- Warning message for missing coordinates -->
                        <div x-show="!order.has_coordinates" class="text-xs text-red-600 mt-1">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Nie można dodać do optymalizacji trasy</span>
                        </div>
                    </div>
                    <div class="text-right ml-2 flex-shrink-0">
                        <div class="text-base lg:text-lg font-semibold text-green-600"
                            x-text="'zł' + order.total_amount"></div>
                        <div :class="order.status === 'pending' ? 'text-orange-500' : 'text-green-500'"
                            class="text-xs font-medium uppercase" x-text="order.status"></div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Empty State -->
    <div x-show="orders.length === 0" class="orders-empty-state">
        <div class="text-gray-500 mb-2">
            <i class="fas fa-calendar-times text-4xl mb-3"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-700 mb-2">Brak Zaplanowanych Zamówień</h3>
        <p class="text-sm text-gray-500">Nie znaleziono zamówień dostawy dla <span
                x-text="formattedSelectedDate"></span></p>
        <p class="text-xs text-gray-400 mt-2">Spróbuj wybrać inną datę</p>
    </div>

    <!-- Summary Statistics -->
    <div x-show="orders.length > 0" class="mt-4 pt-4 border-t border-gray-200">
        <div class="grid grid-cols-2 gap-3 text-sm">
            <div class="flex items-center justify-between">
                <span class="text-gray-600">Zamówień z GPS:</span>
                <span class="font-semibold text-green-600" x-text="orders.filter(o => o.has_coordinates).length"></span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-gray-600">Bez GPS:</span>
                <span class="font-semibold text-red-600" x-text="orders.filter(o => !o.has_coordinates).length"></span>
            </div>
        </div>
    </div>
</div>
