<!-- Wybór daty -->
<div class="bg-white rounded-xl custom-shadow p-4 lg:p-6">
    <h2 class="text-lg lg:text-xl font-semibold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-calendar-alt text-primary mr-2"></i>
        Data dostawy
    </h2>

    <div class="space-y-4">
        <!-- Wybór daty -->
        <div class="date-picker-container">
            <!--
                THE ONLY CHANGE IS HERE:
                The `:min="getMinDate()"` attribute has been removed to allow selecting past dates.
            -->
            <input type="date" x-model="selectedDate" :max="getMaxDate()" @change="onDateChange($event)"
                class="date-picker-input w-full" id="deliveryDate" />
        </div>

        <!-- Karta informacji o dacie -->
        <div class="date-info-card rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center">
                    <i class="fas fa-calendar-day text-gray-500 mr-2"></i>
                    <span class="font-medium text-gray-700">Wybrana data</span>
                </div>
                <div :class="getDateStatusClass()" class="date-status-badge">
                    <span x-text="getDateStatusText()"></span>
                </div>
            </div>

            <div class="text-sm font-semibold text-gray-800 mb-2" x-text="formattedSelectedDate">
            </div>

            <div class="grid grid-cols-2 gap-3 text-center">
                <div class="bg-white rounded-lg p-2 border">
                    <div class="text-lg font-bold text-blue-600" x-text="orders.length"></div>
                    <div class="text-xs text-gray-600">Zlecenia</div>
                </div>
                <div class="bg-white rounded-lg p-2 border">
                    <div class="text-lg font-bold text-green-600"
                        x-text="'zł' + totalOrderValue.toLocaleString('pl-PL')"></div>
                    <div class="text-xs text-gray-600">Wartość</div>
                </div>
            </div>
        </div>

        <!-- Szybka nawigacja po dacie  -->
        <div class="date-navigation">
            <button @click="selectedDate = getTodayDate(); setSelectedDate(selectedDate);"
                :class="selectedDate === getTodayDate() ? 'bg-blue-100 border-blue-300 text-blue-700' :
                    'bg-white hover:bg-gray-50'"
                class="date-nav-btn text-xs px-3 py-2">Dzisiaj</button>

            <button
                @click="(() => { const tomorrow = new Date(); tomorrow.setDate(tomorrow.getDate() + 1); const tomorrowStr = tomorrow.toISOString().split('T')[0]; selectedDate = tomorrowStr; setSelectedDate(tomorrowStr); })()"
                class="date-nav-btn text-xs px-3 py-2 bg-white hover:bg-gray-50">Jutro</button>

            <button
                @click="(() => { const nextWeek = new Date(); nextWeek.setDate(nextWeek.getDate() + 7); const nextWeekStr = nextWeek.toISOString().split('T')[0]; selectedDate = nextWeekStr; setSelectedDate(nextWeekStr); })()"
                class="date-nav-btn text-xs px-3 py-2 bg-white hover:bg-gray-50">+7 dni</button>
        </div>
    </div>
</div>
