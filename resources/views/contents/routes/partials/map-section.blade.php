<!-- Map -->
<div class="bg-white rounded-xl custom-shadow overflow-hidden">
    <div class="p-4 border-b bg-gray-50">
        <h2 class="text-lg lg:text-xl font-semibold text-gray-800 flex items-center flex-wrap gap-2">
            <i class="fas fa-map text-primary mr-2"></i>
            Mapa Trasy na Żywo
            <div class="flex items-center gap-4 ml-auto text-sm text-gray-600">
                <span x-show="selectedDriver && selectedDriver.id">
                    Kierowca: <span class="font-medium" x-text="selectedDriver?.full_name || 'Nieznany'"></span>
                </span>
                <span class="px-2 py-1 bg-gray-200 rounded-full text-xs">
                    <i class="fas fa-calendar-day mr-1"></i>
                    <span x-text="selectedDate || 'Brak daty'"></span>
                </span>
            </div>
        </h2>
    </div>
    <div id="map" class="h-64 sm:h-80 lg:h-96 xl:h-[32rem] w-full"></div>
    <div class="bg-white rounded-lg p-4 mb-4 border-2 border-yellow-400">
        <h3 class="font-bold mb-2">🐛 DEBUG INFO</h3>
        <div class="text-sm space-y-1">
            <div>Selected Driver: <span class="font-mono" x-text="selectedDriver?.full_name || 'None'"></span></div>
            <div>Selected Date: <span class="font-mono" x-text="selectedDate"></span></div>
            <div>Orders Count: <span class="font-mono" x-text="orders.length"></span></div>
            <div>Has Optimization: <span class="font-mono" x-text="optimizationResult ? 'YES' : 'NO'"></span></div>
            <div>Route Steps: <span class="font-mono" x-text="optimizationResult?.route_steps?.length || 0"></span>
            </div>
            <div class="mt-2 border-t pt-2">
                <div class="font-bold">Orders Breakdown:</div>
                <div>- Regular: <span class="font-mono" x-text="orders.filter(o => !o.isCustom).length"></span></div>
                <div>- Custom: <span class="font-mono text-purple-600"
                        x-text="orders.filter(o => o.isCustom).length"></span></div>
            </div>
            <template x-if="orders.length > 0">
                <div class="mt-2 border-t pt-2 max-h-40 overflow-y-auto">
                    <div class="font-bold mb-1">Order List:</div>
                    <template x-for="(order, idx) in orders" :key="order.id">
                        <div class="text-xs py-1 border-b" :class="order.isCustom ? 'bg-purple-50' : 'bg-gray-50'">
                            <span x-text="idx + 1"></span>.
                            <span class="font-mono" x-text="order.id"></span> -
                            <span x-text="order.client_name"></span>
                            <span x-show="order.isCustom" class="text-purple-600 font-bold"> [CUSTOM]</span>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>
</div>
