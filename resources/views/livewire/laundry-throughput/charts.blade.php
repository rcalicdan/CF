<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 md:p-8" x-data="laundryChartsComponent()"
    x-init="initCharts()" @update-charts.window="updateCharts($event.detail)">

    <!-- Header Section -->
    <div class="mb-6 sm:mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex-1">
                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 leading-tight">Analityka Pralni</h3>
                <p class="text-sm sm:text-base text-gray-500 mt-1">Kompleksowa analiza wydajności, przychodów i operacji</p>
            </div>
            <div class="flex gap-2 sm:gap-3 w-full sm:w-auto">
                <button @click="$wire.refreshData()"
                    class="flex-1 sm:flex-none flex items-center justify-center px-3 py-2 sm:px-4 sm:py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors duration-200 whitespace-nowrap">
                    <svg class="w-4 h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span class="truncate">Odśwież</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <div class="bg-gradient-to-r from-emerald-50 to-emerald-100 rounded-xl p-4 sm:p-5 border border-emerald-200">
            <p class="text-sm font-medium text-emerald-600">Dywany (bież. miesiąc)</p>
            <p class="text-2xl font-bold text-emerald-900">{{ number_format($totalCurrentMonthCarpets) }}</p>
            <p class="text-xs text-emerald-700">
                <span class="{{ $percentageChangeCarpets >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $percentageChangeCarpets >= 0 ? '▲' : '▼' }} {{ number_format($percentageChangeCarpets, 1) }}%
                </span>
                vs poprzedni
            </p>
        </div>
        <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-4 sm:p-5 border border-blue-200">
            <p class="text-sm font-medium text-blue-600">Przychód (bież. miesiąc)</p>
            <p class="text-2xl font-bold text-blue-900">{{ number_format($totalRevenueCurrentMonth, 2) }} zł</p>
            <p class="text-xs text-blue-700">
                <span class="{{ $percentageChangeRevenue >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $percentageChangeRevenue >= 0 ? '▲' : '▼' }} {{ number_format($percentageChangeRevenue, 1) }}%
                </span>
                vs poprzedni
            </p>
        </div>
        <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl p-4 sm:p-5 border border-purple-200">
             <p class="text-sm font-medium text-purple-600">Średnia wartość zam.</p>
             <p class="text-2xl font-bold text-purple-900">{{ number_format($avgOrderValue, 2) }} zł</p>
             <p class="text-xs text-purple-700">w bieżącym miesiącu</p>
        </div>
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-4 sm:p-5 border border-gray-200">
            <p class="text-sm font-medium text-gray-600">Nowi klienci</p>
            <p class="text-2xl font-bold text-gray-900">12</p>
            <p class="text-xs text-gray-700">w bieżącym miesiącu</p>
        </div>
    </div>

    <!-- Throughput Section -->
    <div class="border-t border-gray-200 pt-6 sm:pt-8">
        <div class="pb-2 border-b border-gray-200 mb-6">
            <h3 class="text-lg leading-6 font-semibold text-gray-900">
                Analiza przepustowości dywanów
            </h3>
        </div>
        
        <!-- Tabs for Throughput -->
        <div class="border-b border-gray-200 mb-6 sm:mb-8">
            <nav class="flex space-x-1 bg-gray-50 rounded-lg p-1">
                <button @click="switchTab('weekly')"
                    :class="activeTab === 'weekly' ? 'bg-white text-emerald-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                    class="flex-1 py-2 px-3 sm:py-3 sm:px-4 text-xs sm:text-sm font-semibold rounded-lg transition-all duration-200">
                    <div class="flex items-center justify-center">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span class="truncate">Tygodniowe</span>
                    </div>
                </button>
                <button @click="switchTab('monthly')"
                    :class="activeTab === 'monthly' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                    class="flex-1 py-2 px-3 sm:py-3 sm:px-4 text-xs sm:text-sm font-semibold rounded-lg transition-all duration-200">
                    <div class="flex items-center justify-center">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        <span class="truncate">Miesięczne</span>
                    </div>
                </button>
                <button @click="switchTab('yearly')"
                    :class="activeTab === 'yearly' ? 'bg-white text-purple-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                    class="flex-1 py-2 px-3 sm:py-3 sm:px-4 text-xs sm:text-sm font-semibold rounded-lg transition-all duration-200">
                    <div class="flex items-center justify-center">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        <span class="truncate">Roczne</span>
                    </div>
                </button>
            </nav>
        </div>

        <div class="space-y-6 sm:space-y-8">
            <div class="bg-gradient-to-br from-gray-50 to-white rounded-2xl p-4 sm:p-6 md:p-8 border border-gray-200 shadow-lg">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 sm:mb-6">
                    <h4 class="text-lg sm:text-xl font-bold text-gray-900" x-text="getChartTitle()"></h4>
                    <div class="flex items-center space-x-2 sm:space-x-3">
                        <span class="text-xs font-medium px-2 py-1 sm:px-3 sm:py-1.5 rounded-full" :class="getTrendBadgeClass()" x-text="getTrendText()"></span>
                        <div class="flex space-x-1" x-data="{ showTooltip: false, tooltipType: '' }">
                            <div class="relative">
                                <button @click="toggleChartType('line')" @mouseenter="showTooltip = true; tooltipType = 'line'" @mouseleave="showTooltip = false" :class="chartType === 'line' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600'" class="p-1.5 sm:p-2 rounded-lg transition-colors duration-200 hover:scale-105">
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                </button>
                                <div x-show="showTooltip && tooltipType === 'line'" x-transition class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap z-10">Wykres liniowy</div>
                            </div>
                            <div class="relative">
                                <button @click="toggleChartType('bar')" @mouseenter="showTooltip = true; tooltipType = 'bar'" @mouseleave="showTooltip = false" :class="chartType === 'bar' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600'" class="p-1.5 sm:p-2 rounded-lg transition-colors duration-200 hover:scale-105">
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                </button>
                                <div x-show="showTooltip && tooltipType === 'bar'" x-transition class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap z-10">Wykres słupkowy</div>
                            </div>
                            <div class="relative">
                                <button @click="toggleChartType('area')" @mouseenter="showTooltip = true; tooltipType = 'area'" @mouseleave="showTooltip = false" :class="chartType === 'area' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600'" class="p-1.5 sm:p-2 rounded-lg transition-colors duration-200 hover:scale-105">
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                                </button>
                                <div x-show="showTooltip && tooltipType === 'area'" x-transition class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap z-10">Wykres powierzchniowy</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="laundry-throughput-main-chart" class="h-64 sm:h-80 md:h-96"></div>
            </div>

            <div x-show="activeTab === 'monthly'" x-transition class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl p-4 sm:p-6 md:p-8 border border-indigo-200 shadow-lg">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 sm:mb-6">
                    <h4 class="text-lg sm:text-xl font-bold text-gray-900">Status dywanów</h4>
                    <span class="text-xs font-medium px-2 py-1 sm:px-3 sm:py-1.5 rounded-full bg-indigo-100 text-indigo-800">Bieżący miesiąc</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                    <div id="laundry-throughput-status-breakdown-chart" class="h-64 sm:h-80"></div>
                    <div class="space-y-3 sm:space-y-4">
                        <h5 class="text-base sm:text-lg font-semibold text-gray-800">Szczegóły statusów</h5>
                        <template x-for="(item, index) in chartData.statusBreakdown" :key="index">
                            <div class="flex items-center justify-between p-3 sm:p-4 bg-white rounded-lg border border-gray-200">
                                <div class="flex items-center overflow-hidden">
                                    <div class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full mr-2 sm:mr-3 flex-shrink-0" :style="`background-color: getStatusColor(index)`"></div>
                                    <div class="flex-1">
                                        <p class="text-sm sm:font-medium text-gray-900 truncate" x-text="item.label"></p>
                                        <p class="text-xs sm:text-sm text-gray-500" x-text="`Średnia powierzchnia: ${item.avg_area} m²`"></p>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0 ml-2">
                                    <p class="text-sm sm:font-bold text-gray-900" x-text="`${item.value} dywanów`"></p>
                                    <p class="text-xs sm:text-sm text-gray-500" x-text="`${item.percentage}%`"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-4 sm:p-6 border border-green-200 shadow-lg">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-lg font-bold text-gray-900">Wskaźnik ukończenia</h4>
                        <span class="text-xs font-medium px-2 py-1 rounded-full bg-green-100 text-green-800" x-text="getCompletionRateText()"></span>
                    </div>
                    <div id="laundry-throughput-completion-rate-chart" class="h-48 sm:h-64"></div>
                </div>
                <div class="bg-gradient-to-br from-orange-50 to-amber-50 rounded-2xl p-4 sm:p-6 border border-orange-200 shadow-lg">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-lg font-bold text-gray-900">Średnia powierzchnia</h4>
                        <span class="text-xs font-medium px-2 py-1 rounded-full bg-orange-100 text-orange-800">m²</span>
                    </div>
                    <div id="laundry-throughput-average-area-chart" class="h-48 sm:h-64"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Analytics Section -->
    <div class="space-y-6 sm:space-y-8 mt-6 sm:mt-8 border-t border-gray-200 pt-6 sm:pt-8">
        <div class="pb-2 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-semibold text-gray-900">
                Szczegółowa analityka zamówień
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                Analiza przychodów, statusów, kierowców i popularności usług w bieżącym miesiącu.
            </p>
        </div>

        <div class="bg-gradient-to-br from-gray-50 to-white rounded-2xl p-4 sm:p-6 md:p-8 border border-gray-200 shadow-lg">
            <h4 class="text-lg sm:text-xl font-bold text-gray-900 mb-4">Przychody w czasie (wg daty dostarczenia)</h4>
            <div id="laundry-throughput-revenue-chart" class="h-64 sm:h-80"></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-gradient-to-br from-cyan-50 to-sky-50 rounded-2xl p-4 sm:p-6 border border-cyan-200 shadow-lg">
                <h4 class="text-lg font-bold text-gray-900 mb-4">Statusy zamówień</h4>
                <div id="laundry-throughput-order-status-chart" class="h-64 sm:h-80"></div>
            </div>
            <div class="bg-gradient-to-br from-rose-50 to-pink-50 rounded-2xl p-4 sm:p-6 border border-rose-200 shadow-lg">
                <h4 class="text-lg font-bold text-gray-900 mb-4">Najlepsi kierowcy</h4>
                <div id="laundry-throughput-driver-performance-chart" class="h-64 sm:h-80"></div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-violet-50 to-fuchsia-50 rounded-2xl p-4 sm:p-6 border border-violet-200 shadow-lg">
            <h4 class="text-lg font-bold text-gray-900 mb-4">Najpopularniejsze usługi</h4>
            <div id="laundry-throughput-top-services-chart" class="h-64 sm:h-80"></div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function laundryChartsComponent() {
            return {
                activeTab: @entangle('activeTab'),
                chartType: 'bar',
                mainChart: null,
                statusBreakdownChart: null,
                completionRateChart: null,
                averageAreaChart: null,
                revenueChart: null,
                orderStatusChart: null,
                driverPerformanceChart: null,
                topServicesChart: null,
                chartData: @json($chartData),

                initCharts() {
                    this.$nextTick(() => {
                        this.createAllCharts();
                        window.addEventListener('resize', () => this.recreateAllCharts());
                    });
                },

                switchTab(tab) {
                    this.activeTab = tab;
                    this.recreateAllCharts();
                },

                toggleChartType(type) {
                    this.chartType = type;
                    this.updateMainChart();
                },
                
                updateCharts(event) {
                    this.chartData = event.detail.data;
                    this.$nextTick(() => this.recreateAllCharts());
                },

                getCurrentData(dataType) {
                    const dataSet = this.chartData[dataType] || {};
                    return dataSet[this.activeTab] || [];
                },
                
                getChartTitle() {
                    const titles = { 'weekly': 'Przepustowość tygodniowa', 'monthly': 'Przepustowość miesięczna', 'yearly': 'Przepustowość roczna' };
                    return titles[this.activeTab] || 'Przepustowość';
                },

                getTrendText() {
                    const trends = { 'weekly': '{{ $weeklyTrend }}', 'monthly': '{{ $monthlyTrend }}', 'yearly': '{{ $yearlyChange }}' };
                    return trends[this.activeTab] || 'Brak danych';
                },

                getTrendBadgeClass() {
                    const trend = this.getTrendText().toLowerCase();
                    if (trend.includes('rosnący') || trend.includes('wzrost') || trend.includes('+')) return 'bg-green-100 text-green-800';
                    if (trend.includes('malejący') || trend.includes('spadek') || trend.includes('-')) return 'bg-red-100 text-red-800';
                    return 'bg-blue-100 text-blue-800';
                },

                getCompletionRateText() {
                    const data = this.getCurrentData('throughput');
                    if (!data || data.length === 0) return 'Brak danych';
                    const rate = data[data.length - 1]?.completion_rate ?? 0;
                    return `${rate}%`;
                },

                getStatusColor(index) {
                    const colors = ['#10B981', '#3B82F6', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4', '#84CC16', '#F97316'];
                    return colors[index % colors.length];
                },

                getEmptyChartConfig(message) {
                    return {
                        chart: { type: 'line', height: '100%', toolbar: { show: false } },
                        series: [],
                        xaxis: { categories: [] },
                        noData: { text: message, align: 'center', verticalAlign: 'middle', style: { color: '#6B7280', fontSize: '14px', fontFamily: 'Inter, sans-serif' } }
                    };
                },

                destroyAllCharts() {
                    ['mainChart', 'statusBreakdownChart', 'completionRateChart', 'averageAreaChart', 'revenueChart', 'orderStatusChart', 'driverPerformanceChart', 'topServicesChart']
                    .forEach(chartName => {
                        if (this[chartName]) {
                            try { this[chartName].destroy(); } catch (e) {}
                            this[chartName] = null;
                        }
                    });
                },

                recreateAllCharts() {
                    this.destroyAllCharts();
                    this.createAllCharts();
                },
                
                createAllCharts() {
                    this.createMainChart();
                    this.createStatusBreakdownChart();
                    this.createCompletionRateChart();
                    this.createAverageAreaChart();
                    this.createRevenueChart();
                    this.createOrderStatusChart();
                    this.createDriverPerformanceChart();
                    this.createTopServicesChart();
                },

                updateMainChart() {
                    if (this.mainChart) {
                        this.mainChart.updateOptions(this.getChartConfig(), false, true, true).catch(e => console.error(e));
                    } else {
                        this.createMainChart();
                    }
                },

                getChartConfig() {
                    const data = this.getCurrentData('throughput');
                    if (!data || data.length === 0) return this.getEmptyChartConfig('Brak danych do wyświetlenia');
                    
                    const colors = { 'weekly': ['#10B981'], 'monthly': ['#3B82F6'], 'yearly': ['#8B5CF6'] };
                    const baseConfig = {
                        chart: { type: this.chartType, height: '100%', toolbar: { show: false }, foreColor: '#6B7280', fontFamily: 'Inter, sans-serif' },
                        series: [{ name: 'Przetworzone dywany', data: data.map(item => parseInt(item.value, 10) || 0) }],
                        xaxis: { categories: data.map(item => item.label || ''), axisBorder: { show: false }, axisTicks: { show: false }, labels: { style: { colors: '#9CA3AF', fontSize: '12px' } } },
                        yaxis: { labels: { formatter: (value) => Math.round(value).toString(), style: { fontSize: '12px' } }, title: { text: 'Liczba dywanów', style: { color: '#6B7280', fontSize: '12px' } } },
                        colors: colors[this.activeTab],
                        grid: { borderColor: '#F3F4F6', strokeDashArray: 5 },
                        tooltip: { theme: 'light', y: { formatter: (value, { dataPointIndex }) => { const item = data[dataPointIndex]; if (!item) return `${value}`; let text = `${value} dywanów`; if (item.completion_rate) text += `<br/>Ukończone: ${item.completion_rate}%`; if (item.avg_area) text += `<br/>Śr. pow.: ${item.avg_area} m²`; return text; } } },
                        dataLabels: { enabled: false },
                        responsive: [{ breakpoint: 768, options: { chart: { height: 250 }, yaxis: { labels: { style: { fontSize: '10px' } } }, xaxis: { labels: { style: { fontSize: '10px' } } } } }]
                    };

                    if (this.chartType === 'line') {
                        baseConfig.stroke = { curve: 'smooth', width: 3 };
                        baseConfig.markers = { size: 4, strokeWidth: 2, hover: { size: 6 } };
                    } else if (this.chartType === 'bar') {
                        baseConfig.plotOptions = { bar: { borderRadius: 6, columnWidth: '60%' } };
                    } else if (this.chartType === 'area') {
                        baseConfig.stroke = { curve: 'smooth', width: 2 };
                        baseConfig.fill = { type: 'gradient', gradient: { opacityFrom: 0.6, opacityTo: 0.1 } };
                    }
                    return baseConfig;
                },

                createMainChart() {
                    const element = document.querySelector("#laundry-throughput-main-chart");
                    if (!element) return;
                    const config = this.getChartConfig();
                    this.mainChart = new ApexCharts(element, config);
                    this.mainChart.render();
                },

                createStatusBreakdownChart() {
                    const element = document.querySelector("#laundry-throughput-status-breakdown-chart");
                    if (!element || this.activeTab !== 'monthly') { if(element) element.innerHTML = ''; return; }

                    const seriesData = this.chartData.statusBreakdown || [];
                    if (seriesData.length === 0) { element.innerHTML = '<div class="h-full flex items-center justify-center text-gray-500">Brak danych</div>'; return; }
                    
                    const config = {
                        chart: { type: 'donut', height: '100%', fontFamily: 'Inter, sans-serif' },
                        series: seriesData.map(d => d.value),
                        labels: seriesData.map(d => d.label),
                        colors: seriesData.map((_, index) => this.getStatusColor(index)),
                        plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Łącznie' } } } } },
                        legend: { show: false },
                        dataLabels: { formatter: (val) => `${val.toFixed(1)}%` },
                        responsive: [{ breakpoint: 768, options: { chart: { height: 250 }, legend: { show: true, position: 'bottom' } } }]
                    };
                    this.statusBreakdownChart = new ApexCharts(element, config);
                    this.statusBreakdownChart.render();
                },

                createCompletionRateChart() {
                    const element = document.querySelector("#laundry-throughput-completion-rate-chart");
                    if (!element) return;
                    
                    const data = this.getCurrentData('throughput');
                    if (!data || data.length === 0) { element.innerHTML = '<div class="h-full flex items-center justify-center text-gray-500">Brak danych</div>'; return; }
                    
                    const config = {
                        chart: { type: 'line', height: '100%', toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
                        series: [{ name: 'Wskaźnik', data: data.map(item => item.completion_rate || 0) }],
                        xaxis: { categories: data.map(item => item.label || ''), labels: { style: { fontSize: '10px' } } },
                        yaxis: { min: 0, max: 100, labels: { formatter: (value) => `${Math.round(value)}%` } },
                        colors: ['#10B981'],
                        stroke: { curve: 'smooth', width: 3 },
                        markers: { size: 4 },
                        grid: { borderColor: '#F3F4F6', strokeDashArray: 5 },
                        tooltip: { y: { formatter: (value) => `${Math.round(value)}%` } },
                        responsive: [{ breakpoint: 768, options: { chart: { height: 200 } } }]
                    };
                    this.completionRateChart = new ApexCharts(element, config);
                    this.completionRateChart.render();
                },

                createAverageAreaChart() {
                    const element = document.querySelector("#laundry-throughput-average-area-chart");
                    if (!element) return;

                    const data = this.getCurrentData('throughput');
                    if (!data || data.length === 0) { element.innerHTML = '<div class="h-full flex items-center justify-center text-gray-500">Brak danych</div>'; return; }

                    const config = {
                        chart: { type: 'bar', height: '100%', toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
                        series: [{ name: 'Śr. pow.', data: data.map(item => item.avg_area || 0) }],
                        xaxis: { categories: data.map(item => item.label || ''), labels: { style: { fontSize: '10px' } } },
                        yaxis: { labels: { formatter: (value) => `${value.toFixed(2)} m²` } },
                        colors: ['#F59E0B'],
                        plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
                        grid: { borderColor: '#F3F4F6', strokeDashArray: 5 },
                        tooltip: { y: { formatter: (value) => `${value.toFixed(2)} m²` } },
                        dataLabels: { enabled: false },
                        responsive: [{ breakpoint: 768, options: { chart: { height: 200 } } }]
                    };
                    this.averageAreaChart = new ApexCharts(element, config);
                    this.averageAreaChart.render();
                },

                createRevenueChart() {
                    const element = document.querySelector("#laundry-throughput-revenue-chart");
                    if (!element) return;

                    const seriesData = this.getCurrentData('revenue');
                    if (!seriesData || seriesData.length === 0) { element.innerHTML = '<div class="h-full flex items-center justify-center text-gray-500">Brak danych</div>'; return; }

                    const config = {
                        chart: { type: 'area', height: '100%', toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
                        series: [{ name: 'Przychód', data: seriesData.map(d => d.value) }],
                        xaxis: { categories: seriesData.map(d => d.label) },
                        yaxis: { labels: { formatter: (value) => `${(value / 1000).toFixed(1)}k zł` } },
                        colors: ['#3B82F6'],
                        stroke: { curve: 'smooth', width: 2 },
                        fill: { type: 'gradient', gradient: { opacityFrom: 0.6, opacityTo: 0.1 } },
                        tooltip: { y: { formatter: (value) => `${value.toFixed(2)} zł` } },
                        dataLabels: { enabled: false },
                        responsive: [{ breakpoint: 768, options: { chart: { height: 250 }, yaxis: { labels: { style: { fontSize: '10px' } } }, xaxis: { labels: { style: { fontSize: '10px' } } } } }]
                    };
                    this.revenueChart = new ApexCharts(element, config);
                    this.revenueChart.render();
                },

                createOrderStatusChart() {
                    const element = document.querySelector("#laundry-throughput-order-status-chart");
                    if (!element) return;

                    const seriesData = this.chartData.orderStatus || [];
                    if (seriesData.length === 0) { element.innerHTML = '<div class="h-full flex items-center justify-center text-gray-500">Brak danych</div>'; return; }
                    
                    const config = {
                        chart: { type: 'donut', height: '100%', fontFamily: 'Inter, sans-serif' },
                        series: seriesData.map(d => d.value),
                        labels: seriesData.map(d => d.label),
                        dataLabels: { enabled: true, formatter: (val, { seriesIndex }) => `${seriesData[seriesIndex].value}` },
                        plotOptions: { pie: { donut: { labels: { show: true, total: { show: true, label: 'Wszystkie Zam.' } } } } },
                        legend: { position: 'bottom' },
                        tooltip: { y: { formatter: (val) => `${val} zamówień` } },
                        responsive: [{ breakpoint: 768, options: { chart: { height: 250 }, legend: { fontSize: '12px' } } }]
                    };
                    this.orderStatusChart = new ApexCharts(element, config);
                    this.orderStatusChart.render();
                },

                createDriverPerformanceChart() {
                    const element = document.querySelector("#laundry-throughput-driver-performance-chart");
                    if (!element) return;

                    const seriesData = this.chartData.driverPerformance || [];
                    if (seriesData.length === 0) { element.innerHTML = '<div class="h-full flex items-center justify-center text-gray-500">Brak danych</div>'; return; }

                    const config = {
                        chart: { type: 'bar', height: '100%', toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
                        series: [{ name: 'Zlecenia', data: seriesData.map(d => d.value) }],
                        xaxis: { categories: seriesData.map(d => d.label) },
                        colors: ['#E11D48'],
                        plotOptions: { bar: { borderRadius: 4, horizontal: false, columnWidth: '50%' } },
                        dataLabels: { enabled: false },
                        responsive: [{ breakpoint: 768, options: { chart: { height: 250 }, xaxis: { labels: { style: { fontSize: '10px' } } } } }]
                    };
                    this.driverPerformanceChart = new ApexCharts(element, config);
                    this.driverPerformanceChart.render();
                },

                createTopServicesChart() {
                    const element = document.querySelector("#laundry-throughput-top-services-chart");
                    if (!element) return;

                    const seriesData = this.chartData.topServices || [];
                    if (seriesData.length === 0) { element.innerHTML = '<div class="h-full flex items-center justify-center text-gray-500">Brak danych</div>'; return; }
                    
                    const config = {
                        chart: { type: 'bar', height: '100%', toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
                        series: [{ name: 'Ilość', data: seriesData.map(d => d.value) }],
                        xaxis: { categories: seriesData.map(d => d.label) },
                        colors: ['#8B5CF6'],
                        plotOptions: { bar: { borderRadius: 4, horizontal: true, barHeight: '60%' } },
                        dataLabels: { enabled: true, textAnchor: 'start', offsetX: 10, style: { fontSize: '12px', colors: ['#fff'] }, formatter: (val) => val },
                        responsive: [{ breakpoint: 768, options: { chart: { height: 250 }, dataLabels: { style: { fontSize: '10px' } } } }]
                    };
                    this.topServicesChart = new ApexCharts(element, config);
                    this.topServicesChart.render();
                }
            }
        }
    </script>
@endpush