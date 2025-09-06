<!-- Map -->
<div class="bg-white rounded-xl custom-shadow overflow-hidden">
    <div class="p-4 border-b bg-gray-50">
        <h2 class="text-lg lg:text-xl font-semibold text-gray-800 flex items-center flex-wrap gap-2">
            <i class="fas fa-map text-primary mr-2"></i>
            Live Route Map
            <div class="flex items-center gap-4 ml-auto text-sm text-gray-600">
                <span x-show="selectedDriver">
                    Driver: <span class="font-medium" x-text="selectedDriver.full_name"></span>
                </span>
                <span class="px-2 py-1 bg-gray-200 rounded-full text-xs">
                    <i class="fas fa-calendar-day mr-1"></i>
                    <span x-text="selectedDate"></span>
                </span>
            </div>
        </h2>
    </div>
    <div id="map" class="h-64 sm:h-80 lg:h-96 xl:h-[32rem] w-full"></div>
</div>
