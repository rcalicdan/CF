<!-- Route Details -->
<div x-show="optimizationResult || orders.length > 0" class="bg-white rounded-xl custom-shadow p-4 lg:p-6">
    <h2 class="text-lg lg:text-xl font-semibold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-list-ol text-primary mr-2"></i>
        <span x-text="optimizationResult ? 'Optimized Route Details' : 'Current Route'"></span>
        <div class="ml-auto flex items-center gap-2">
            <!-- Load Saved Route Button -->
            <button @click="loadSavedRoute()" :disabled="!selectedDriver?.id || !selectedDate || loading"
                class="bg-blue-500 hover:bg-blue-600 disabled:bg-gray-400 text-white px-3 py-1 rounded text-sm font-medium transition-colors">
                <i class="fas fa-download mr-1"></i>
                Load Saved
            </button>
            <span class="text-sm text-gray-600 bg-gray-100 px-3 py-1 rounded-full">
                <i class="fas fa-calendar mr-1"></i>
                <span x-text="selectedDate"></span>
            </span>
            <span x-show="manualEditMode"
                class="text-xs bg-orange-100 text-orange-600 px-2 py-1 rounded-full font-medium">
                <i class="fas fa-edit mr-1"></i>EDIT MODE
            </span>
        </div>
    </h2>

    <!-- Route List -->
    <div class="space-y-4">
        <div x-show="orders.length === 0" class="text-center py-8 text-gray-500">
            <i class="fas fa-route text-4xl mb-3 opacity-50"></i>
            <h3 class="text-lg font-medium mb-2">No Route Available</h3>
            <p class="text-sm">Select a date with orders and optimize the route to get started.</p>
        </div>

        <div x-show="orders.length > 0" class="space-y-3">
            <!-- Depot Start -->
            <div class="route-card flex items-center p-3 lg:p-4 border-2 border-blue-200 bg-blue-50 rounded-lg">
                <div
                    class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-semibold text-sm">
                    <i class="fas fa-warehouse text-xs"></i>
                </div>
                <div class="ml-4 flex-1">
                    <div class="font-medium text-blue-800">Main Depot - START</div>
                    <div class="text-sm text-blue-600">Warsaw Distribution Center</div>
                    <div class="text-xs text-blue-500 mt-1">Departure: 08:00</div>
                </div>
            </div>

            <!-- Route Steps -->
            <template x-for="(order, index) in orders" :key="order.id">
                <div :draggable="manualEditMode" @dragstart="onDragStart(index, $event)" @dragover="onDragOver($event)"
                    @drop="onDrop(index, $event)" @dragenter="$event.preventDefault()"
                    :class="[
                        manualEditMode ? 'cursor-move border-dashed hover:border-blue-400 hover:shadow-md' : '',
                        order.isCustom ? 'border-l-4 border-l-purple-400 bg-purple-50' : ''
                    ]"
                    class="route-card flex items-center p-3 lg:p-4 border rounded-lg transition-all duration-200">

                    <!-- Drag Handle -->
                    <div x-show="manualEditMode" class="mr-2 text-gray-400 cursor-move">
                        <i class="fas fa-grip-vertical"></i>
                    </div>

                    <!-- Order Number -->
                    <div :class="order.isCustom ? 'bg-purple-600' : 'bg-primary'"
                        class="flex-shrink-0 w-8 h-8 text-white rounded-full flex items-center justify-center font-semibold text-sm"
                        x-text="index + 1"></div>

                    <!-- Order Details -->
                    <div class="ml-4 flex-1 min-w-0">
                        <div class="font-medium text-gray-800 truncate flex items-center">
                            <span x-text="order.address || order.location"></span>
                            <span x-show="order.isCustom"
                                class="ml-2 text-xs bg-purple-100 text-purple-600 px-2 py-1 rounded-full">
                                <i class="fas fa-map-pin mr-1"></i>CUSTOM
                            </span>
                        </div>
                        <div class="text-sm text-gray-600 truncate" x-text="order.client_name"></div>
                        <div class="flex items-center gap-4 text-xs mt-1">
                            <span class="text-primary">
                                <i class="fas fa-money-bill-wave mr-1"></i>
                                zł<span x-text="order.total_amount || 0"></span>
                            </span>
                            <span class="text-gray-500 capitalize">
                                <i class="fas fa-flag mr-1"></i>
                                <span x-text="order.priority"></span>
                            </span>
                            <span x-show="optimizationResult?.route_steps?.[index]?.estimated_arrival"
                                class="text-green-600">
                                <i class="fas fa-clock mr-1"></i>
                                <span
                                    x-text="optimizationResult?.route_steps?.[index]?.estimated_arrival || 'TBD'"></span>
                            </span>
                        </div>
                    </div>

                    <!-- Distance/Duration Info -->
                    <div x-show="optimizationResult?.route_steps?.[index]" class="text-right ml-2 flex-shrink-0">
                        <div class="text-sm font-semibold text-gray-700"
                            x-text="optimizationResult?.route_steps?.[index]?.distance || 'N/A'"></div>
                        <div class="text-xs text-gray-500"
                            x-text="optimizationResult?.route_steps?.[index]?.duration || 'N/A'"></div>
                    </div>

                    <!-- Manual Edit Controls -->
                    <div x-show="manualEditMode" class="ml-2 flex flex-col gap-1">
                        <button @click="moveStopUp(index)" :disabled="index === 0"
                            :class="index === 0 ? 'opacity-30 cursor-not-allowed' : 'hover:bg-blue-100 cursor-pointer'"
                            class="p-1 text-blue-600 rounded transition-colors">
                            <i class="fas fa-chevron-up text-xs"></i>
                        </button>
                        <button @click="moveStopDown(index)" :disabled="index === orders.length - 1"
                            :class="index === orders.length - 1 ? 'opacity-30 cursor-not-allowed' :
                                'hover:bg-blue-100 cursor-pointer'"
                            class="p-1 text-blue-600 rounded transition-colors">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <button x-show="order.isCustom" @click="removeStop(index)"
                            class="p-1 text-red-600 hover:bg-red-100 rounded cursor-pointer transition-colors">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </div>
                </div>
            </template>

            <!-- Depot End -->
            <div class="route-card flex items-center p-3 lg:p-4 border-2 border-green-200 bg-green-50 rounded-lg">
                <div
                    class="flex-shrink-0 w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-semibold text-sm">
                    <i class="fas fa-home text-xs"></i>
                </div>
                <div class="ml-4 flex-1">
                    <div class="font-medium text-green-800">Return to Depot - END</div>
                    <div class="text-sm text-green-600">Warsaw Distribution Center</div>
                    <div class="text-xs text-green-500 mt-1">
                        Expected Return: <span x-text="executiveSummary?.returnTime || '18:00'"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
