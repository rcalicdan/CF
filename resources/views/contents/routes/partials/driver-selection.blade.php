<!-- Driver Selection -->
<div class="bg-white rounded-xl custom-shadow p-4 lg:p-6">
    <h2 class="text-lg lg:text-xl font-semibold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-users text-primary mr-2"></i>
        Select Driver
    </h2>
    <div class="space-y-3">
        <template x-for="driver in drivers" :key="driver.id">
            <div @click="selectedDriver = driver"
                :class="selectedDriver.id === driver.id ? 'ring-2 ring-primary bg-blue-50' :
                    'hover:bg-gray-50'"
                class="p-3 lg:p-4 rounded-lg border cursor-pointer transition-all">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="font-medium text-gray-800 truncate" x-text="driver.full_name">
                        </div>
                        <div class="text-sm text-gray-500 truncate" x-text="driver.vehicle_details">
                        </div>
                    </div>
                    <div class="text-right ml-2 flex-shrink-0">
                        <div class="text-xs text-gray-400">License</div>
                        <div class="text-xs lg:text-sm font-mono" x-text="driver.license_number">
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>