<div x-show="optimizationResult" class="bg-white rounded-xl custom-shadow p-4 lg:p-6">
    <h2 class="text-lg lg:text-xl font-semibold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-edit text-primary mr-2"></i>
        Manual Route Editor
        <div class="ml-auto flex items-center gap-2">
            <span x-show="manualEditMode"
                class="text-xs bg-orange-100 text-orange-600 px-2 py-1 rounded-full font-medium">
                <i class="fas fa-edit mr-1"></i>EDIT MODE
            </span>
            <span x-show="isDrawingRoute"
                class="text-xs bg-purple-100 text-purple-600 px-2 py-1 rounded-full font-medium">
                <i class="fas fa-pencil-alt mr-1"></i>DRAWING
            </span>
        </div>
    </h2>

    <!-- Quick Stats -->
    <div class="grid grid-cols-3 gap-4 mb-4">
        <div class="text-center p-3 bg-gray-50 rounded-lg">
            <div class="text-lg font-bold text-gray-700" x-text="orders.length"></div>
            <div class="text-xs text-gray-500">Total Stops</div>
        </div>
        <div class="text-center p-3 bg-gray-50 rounded-lg">
            <div class="text-lg font-bold text-blue-600" x-text="orders.filter(o => o.isCustom).length"></div>
            <div class="text-xs text-gray-500">Custom Stops</div>
        </div>
        <div class="text-center p-3 bg-gray-50 rounded-lg">
            <div class="text-lg font-bold text-green-600" x-text="customRoutePoints.length"></div>
            <div class="text-xs text-gray-500">Route Points</div>
        </div>
    </div>

    <!-- Control Buttons -->
    <div class="space-y-4">
        <!-- Primary Actions -->
        <div class="flex flex-wrap gap-2">
            <button @click="toggleManualEdit()"
                :class="manualEditMode ? 'bg-orange-500 text-white shadow-lg' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                class="px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center">
                <i class="fas fa-edit mr-2"></i>
                <span x-text="manualEditMode ? 'Exit Edit Mode' : 'Edit Stops'"></span>
            </button>

            <button @click="toggleRouteDrawing()"
                :class="isDrawingRoute ? 'bg-purple-500 text-white shadow-lg' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                class="px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center">
                <i class="fas fa-pencil-alt mr-2"></i>
                <span x-text="isDrawingRoute ? 'Exit Drawing' : 'Draw Route'"></span>
            </button>

            <button @click="window.mapManager?.clearRoute(); window.mapManager?.clearCustomRoute();"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors flex items-center">
                <i class="fas fa-eraser mr-2"></i>
                Clear Route
            </button>
        </div>

        <!-- Instructions Panel -->
        <div x-show="manualEditMode || isDrawingRoute" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform -translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0" class="border rounded-lg overflow-hidden">

            <!-- Edit Mode Instructions -->
            <div x-show="manualEditMode && !isDrawingRoute" class="bg-orange-50 border-l-4 border-orange-400 p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-orange-400"></i>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-orange-800">Edit Mode Instructions</h4>
                        <div class="mt-2 text-sm text-orange-700">
                            <ul class="list-disc list-inside space-y-1">
                                <li><strong>Drag markers</strong> on the map to reposition stops</li>
                                <li><strong>Click empty areas</strong> on the map to add custom stops</li>
                                <li><strong>Right-click markers</strong> for context menu options</li>
                                <li><strong>Use the route list</strong> below to reorder stops</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Drawing Mode Instructions -->
            <div x-show="isDrawingRoute" class="bg-purple-50 border-l-4 border-purple-400 p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-purple-400"></i>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-purple-800">Route Drawing Instructions</h4>
                        <div class="mt-2 text-sm text-purple-700">
                            <ul class="list-disc list-inside space-y-1">
                                <li><strong>Click points</strong> on the map to draw your custom route</li>
                                <li><strong>Right-click</strong> to finish drawing the route</li>
                                <li><strong>Custom route</strong> will override automatic optimization</li>
                                <li><strong>Points connect</strong> in the order you click them</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save/Reset Actions -->
        <div x-show="manualEditMode || isDrawingRoute || customRoutePoints.length > 0"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" class="flex flex-wrap gap-2 pt-2 border-t">

            <button @click="saveManualChanges()"
                class="bg-green-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-green-600 transition-colors flex items-center shadow-sm">
                <i class="fas fa-save mr-2"></i>Save Changes
            </button>

            <button @click="resetToOptimized()"
                class="bg-red-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-red-600 transition-colors flex items-center shadow-sm">
                <i class="fas fa-undo mr-2"></i>Reset to Optimized
            </button>

            <button @click="exportManualRoute()"
                class="bg-blue-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-600 transition-colors flex items-center shadow-sm">
                <i class="fas fa-download mr-2"></i>Export Route
            </button>
        </div>

        <!-- Route Statistics -->
        <div x-show="manualEditMode && orders.length > 0" x-transition:enter="transition ease-out duration-200"
            class="bg-gray-50 rounded-lg p-3">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Current Route Analysis</h4>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 text-center">
                <div>
                    <div class="text-sm font-semibold text-red-600"
                        x-text="orders.filter(o => o.priority === 'high').length"></div>
                    <div class="text-xs text-gray-500">High Priority</div>
                </div>
                <div>
                    <div class="text-sm font-semibold text-yellow-600"
                        x-text="orders.filter(o => o.priority === 'medium').length"></div>
                    <div class="text-xs text-gray-500">Medium Priority</div>
                </div>
                <div>
                    <div class="text-sm font-semibold text-green-600"
                        x-text="orders.filter(o => o.priority === 'low').length"></div>
                    <div class="text-xs text-gray-500">Low Priority</div>
                </div>
                <div>
                    <div class="text-sm font-semibold text-purple-600"
                        x-text="'zł' + totalOrderValue.toLocaleString('pl-PL')"></div>
                    <div class="text-xs text-gray-500">Total Value</div>
                </div>
            </div>
        </div>
    </div>
</div>
