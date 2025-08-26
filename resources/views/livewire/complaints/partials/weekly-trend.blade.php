<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="p-4 sm:p-8 border-b border-gray-100">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-lg sm:text-xl font-bold text-gray-900">Trend tygodniowy skarg</h3>
                <p class="text-sm text-gray-500 mt-1">Ostatnie 7 dni</p>
            </div>
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                    <span class="text-sm text-gray-600">Nowe skargi</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    <span class="text-sm text-gray-600">Rozwiązane</span>
                </div>
            </div>
        </div>
    </div>
    <div class="p-4 sm:p-8">
        <div id="weeklyTrendChart" wire:ignore style="height: 350px;"></div>
    </div>
</div>