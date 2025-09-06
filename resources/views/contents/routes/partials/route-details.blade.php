<!-- Route Details -->
<div x-show="optimizationResult" class="bg-white rounded-xl custom-shadow p-4 lg:p-6">
    <h2 class="text-lg lg:text-xl font-semibold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-list-ol text-primary mr-2"></i>
        Optimized Route Details
        <span class="ml-auto text-sm text-gray-600 bg-gray-100 px-3 py-1 rounded-full">
            <span x-text="selectedDate"></span>
        </span>
    </h2>
    <div class="space-y-4">
        <div class="space-y-3">
            <template x-for="(step, index) in (optimizationResult?.route_steps || [])"
                :key="index">
                <div
                    class="route-card flex items-center p-3 lg:p-4 border rounded-lg hover:shadow-md transition-shadow">
                    <div class="flex-shrink-0 w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center font-semibold text-sm"
                        x-text="index + 1"></div>
                    <div class="ml-4 flex-1 min-w-0">
                        <div class="font-medium text-gray-800 truncate" x-text="step.location">
                        </div>
                        <div class="text-sm text-gray-600 truncate" x-text="step.description">
                        </div>
                        <div class="flex items-center gap-4 text-xs mt-1">
                            <span class="text-primary">ETA: <span
                                    x-text="step.estimated_arrival"></span></span>
                            <span class="text-gray-500">Priority: <span class="capitalize"
                                    x-text="step.priority"></span></span>
                            <span class="text-green-600">Value: zł<span
                                    x-text="step.amount"></span></span>
                        </div>
                    </div>
                    <div class="text-right ml-2 flex-shrink-0">
                        <div class="text-sm font-semibold text-gray-700" x-text="step.distance">
                        </div>
                        <div class="text-xs text-gray-500" x-text="step.duration"></div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>