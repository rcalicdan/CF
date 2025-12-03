<!-- Delivery Orders -->
<div class="bg-white rounded-xl custom-shadow p-4 lg:p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg lg:text-xl font-semibold text-gray-800 flex items-center">
            <i class="fas fa-box text-primary mr-2"></i>
            <span x-text="getDateStatusText()"></span>
        </h2>
        <div class="flex items-center gap-2">
            <span class="bg-primary text-white px-3 py-1 rounded-full text-sm font-medium" x-text="orders.length"></span>
            <span x-show="newOrdersCount > 0"
                class="bg-yellow-500 text-white px-3 py-1 rounded-full text-sm font-medium animate-pulse"
                x-text="'+' + newOrdersCount">
            </span>
        </div>
    </div>

    <!-- Orders List -->
    <div x-show="orders.length > 0" class="space-y-3 max-h-48 lg:max-h-64 overflow-y-auto">
        <template x-for="order in orders" :key="order.id">
            <div class="p-3 lg:p-4 border rounded-lg transition-all duration-200 relative"
                :class="{
                    'border-red-300 bg-red-50': (!order.has_coordinates && !order.isCustom),
                    'border-yellow-400 bg-yellow-50 shadow-md border-l-4': order.isNewOrder,
                    'hover:bg-gray-50': !order.isNewOrder,
                    'hover:bg-yellow-100': order.isNewOrder
                }">

                <!-- ✨ NEW: New Order Badge -->
                <div x-show="order.isNewOrder" class="absolute -top-2 -right-2 z-10">
                    <span
                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-yellow-500 text-white shadow-lg animate-pulse">
                        <i class="fas fa-star mr-1"></i>
                        NOWE
                    </span>
                </div>

                <div class="flex justify-between items-start">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                            <div class="font-medium text-gray-800"
                                x-text="order.isCustom ? 'Własny przystanek' : 'Zamówienie #' + order.id">
                            </div>
                            <div :class="'priority-' + order.priority" class="priority-indicator"></div>

                            <!-- Custom Stop Badge -->
                            <div x-show="order.isCustom"
                                class="flex items-center gap-1 bg-purple-500 text-white px-2 py-0.5 rounded-full text-xs font-medium"
                                title="Własny przystanek">
                                <i class="fas fa-map-pin"></i>
                                <span>Własny</span>
                            </div>

                            <!-- No Coordinates Badge (only for non-custom stops) -->
                            <div x-show="!order.has_coordinates && !order.isCustom"
                                class="flex items-center gap-1 bg-red-500 text-white px-2 py-0.5 rounded-full text-xs font-medium"
                                title="Brak współrzędnych geograficznych">
                                <i class="fas fa-map-marker-alt-slash"></i>
                                <span>Brak GPS</span>
                            </div>

                            <!-- ✨ NEW: New Order Inline Badge -->
                            <div x-show="order.isNewOrder"
                                class="flex items-center gap-1 bg-yellow-600 text-white px-2 py-0.5 rounded-full text-xs font-medium">
                                <i class="fas fa-plus-circle"></i>
                                <span>Nowe</span>
                            </div>
                        </div>

                        <div class="text-sm text-gray-600 truncate" x-text="order.client_name"></div>
                        <div class="text-sm font-medium truncate"
                            :class="(!order.has_coordinates && !order.isCustom) ? 'text-red-600' : 'text-primary'"
                            x-text="order.address">
                        </div>

                        <!-- Warning message for missing coordinates (only for non-custom stops) -->
                        <div x-show="!order.has_coordinates && !order.isCustom" class="text-xs text-red-600 mt-1">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Nie można dodać do optymalizacji trasy</span>
                        </div>

                        <!-- ✨ NEW: New order notice -->
                        <div x-show="order.isNewOrder" class="text-xs text-yellow-700 mt-1 font-medium">
                            <i class="fas fa-info-circle"></i>
                            <span>Dodane po ostatniej optymalizacji</span>
                        </div>
                    </div>

                    <div class="text-right ml-2 flex-shrink-0">
                        <div class="text-base lg:text-lg font-semibold text-green-600"
                            x-text="'zł' + order.total_amount"></div>
                        <div :class="{
                            'text-orange-500': order.status === 'pending',
                            'text-purple-500': order.status === 'custom',
                            'text-green-500': order.status !== 'pending' && order.status !== 'custom'
                        }"
                            class="text-xs font-medium uppercase"
                            x-text="order.status === 'custom' ? 'WŁASNY' : order.status">
                        </div>
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
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 text-xs lg:text-sm">
            <div class="flex flex-col items-center p-2 bg-green-50 rounded-lg">
                <span class="text-gray-600 mb-1">Z GPS</span>
                <span class="font-semibold text-green-600 text-lg"
                    x-text="orders.filter(o => o.has_coordinates && !o.isCustom).length"></span>
            </div>
            <div class="flex flex-col items-center p-2 bg-red-50 rounded-lg">
                <span class="text-gray-600 mb-1">Bez GPS</span>
                <span class="font-semibold text-red-600 text-lg"
                    x-text="orders.filter(o => !o.has_coordinates && !o.isCustom).length"></span>
            </div>
            <div class="flex flex-col items-center p-2 bg-purple-50 rounded-lg">
                <span class="text-gray-600 mb-1">Własne</span>
                <span class="font-semibold text-purple-600 text-lg"
                    x-text="orders.filter(o => o.isCustom).length"></span>
            </div>
            <!-- ✨ NEW: New orders stat -->
            <div x-show="newOrdersCount > 0"
                class="flex flex-col items-center p-2 bg-yellow-50 rounded-lg border border-yellow-300">
                <span class="text-gray-600 mb-1">Nowe</span>
                <span class="font-semibold text-yellow-600 text-lg" x-text="newOrdersCount"></span>
            </div>
        </div>
    </div>
</div>
