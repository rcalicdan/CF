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
            <div class="p-3 lg:p-4 border rounded-lg hover:bg-gray-50 transition-colors">
                <div class="flex justify-between items-start">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="font-medium text-gray-800" x-text="'Order #' + order.id">
                            </div>
                            <div :class="'priority-' + order.priority" class="priority-indicator">
                            </div>
                        </div>
                        <div class="text-sm text-gray-600 truncate" x-text="order.client_name">
                        </div>
                        <div class="text-sm text-primary font-medium truncate" x-text="order.address"></div>
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
        <h3 class="text-lg font-medium text-gray-700 mb-2">No Orders Scheduled</h3>
        <p class="text-sm text-gray-500">No delivery orders found for <span x-text="formattedSelectedDate"></span></p>
        <p class="text-xs text-gray-400 mt-2">Try selecting a different date</p>
    </div>
</div>
