<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 md:p-8" x-data="laundryChartsComponent()"
    x-init="initCharts()" @update-charts.window="updateCharts($event.detail)">

    <!-- Header Section -->
    <div class="mb-6 sm:mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex-1">
                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 leading-tight">Wydajność pralni</h3>
                <p class="text-sm sm:text-base text-gray-500 mt-1">Analiza przepustowości i efektywności przetwarzania
                    dywanów</p>
            </div>
            <div class="flex gap-2 sm:gap-3 w-full sm:w-auto">
                <button @click="$wire.refreshData()"
                    class="flex-1 sm:flex-none flex items-center justify-center px-3 py-2 sm:px-4 sm:py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors duration-200 whitespace-nowrap">
                    <svg class="w-4 h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                    <span class="truncate">Odśwież</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <div class="bg-gradient-to-r from-emerald-50 to-emerald-100 rounded-xl p-4 sm:p-6 border border-emerald-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm font-medium text-emerald-600">Bieżący miesiąc</p>
                    <p class="text-lg sm:text-2xl font-bold text-emerald-900">{{ number_format($totalCurrentMonth) }}
                    </p>
                    <p class="text-xs text-emerald-700">dywanów przetworzonych</p>
                </div>
                <div class="p-2 sm:p-3 bg-emerald-200 rounded-full">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-emerald-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-4 sm:p-6 border border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm font-medium text-blue-600">Poprzedni miesiąc</p>
                    <p class="text-lg sm:text-2xl font-bold text-blue-900">{{ number_format($totalPreviousMonth) }}</p>
                    <p class="text-xs text-blue-700">dywanów przetworzonych</p>
                </div>
                <div class="p-2 sm:p-3 bg-blue-200 rounded-full">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl p-4 sm:p-6 border border-purple-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm font-medium text-purple-600">Zmiana</p>
                    <p class="text-lg sm:text-2xl font-bold text-purple-900">
                        {{ $percentageChange >= 0 ? '+' : '' }}{{ number_format($percentageChange, 1) }}%
                    </p>
                    <p class="text-xs text-purple-700">w stosunku do poprzedniego</p>
                </div>
                <div class="p-2 sm:p-3 bg-purple-200 rounded-full">
                    @if ($percentageChange >= 0)
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-purple-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                        </svg>
                    @else
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-purple-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                        </svg>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Tabs -->
    <div class="border-b border-gray-200 mb-6 sm:mb-8">
        <nav class="flex space-x-1 bg-gray-50 rounded-lg p-1">
            <button @click="switchTab('weekly')"
                :class="activeTab === 'weekly' ? 'bg-white text-emerald-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                class="flex-1 py-2 px-3 sm:py-3 sm:px-4 text-xs sm:text-sm font-semibold rounded-lg transition-all duration-200">
                <div class="flex items-center justify-center">
                    <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    <span class="truncate">Tygodniowe</span>
                </div>
            </button>
            <button @click="switchTab('monthly')"
                :class="activeTab === 'monthly' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                class="flex-1 py-2 px-3 sm:py-3 sm:px-4 text-xs sm:text-sm font-semibold rounded-lg transition-all duration-200">
                <div class="flex items-center justify-center">
                    <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                        </path>
                    </svg>
                    <span class="truncate">Miesięczne</span>
                </div>
            </button>
            <button @click="switchTab('yearly')"
                :class="activeTab === 'yearly' ? 'bg-white text-purple-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                class="flex-1 py-2 px-3 sm:py-3 sm:px-4 text-xs sm:text-sm font-semibold rounded-lg transition-all duration-200">
                <div class="flex items-center justify-center">
                    <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    <span class="truncate">Roczne</span>
                </div>
            </button>
        </nav>
    </div>

    <!-- Dynamic Charts Container -->
    <div class="space-y-6 sm:space-y-8">
        <!-- Main Chart -->
        <div
            class="bg-gradient-to-br from-gray-50 to-white rounded-2xl p-4 sm:p-6 md:p-8 border border-gray-200 shadow-lg">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 sm:mb-6">
                <h4 class="text-lg sm:text-xl font-bold text-gray-900" x-text="getChartTitle()"></h4>
                <div class="flex items-center space-x-2 sm:space-x-3">
                    <span class="text-xs font-medium px-2 py-1 sm:px-3 sm:py-1.5 rounded-full"
                        :class="getTrendBadgeClass()" x-text="getTrendText()"></span>
                    <div class="flex space-x-1" x-data="{ showTooltip: false, tooltipType: '' }">
                        <!-- Chart Type Buttons with Tooltips -->
                        <div class="relative">
                            <button @click="toggleChartType('line')"
                                @mouseenter="showTooltip = true; tooltipType = 'line'"
                                @mouseleave="showTooltip = false"
                                :class="chartType === 'line' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600'"
                                class="p-1.5 sm:p-2 rounded-lg transition-colors duration-200 hover:scale-105">
                                <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </button>
                            <!-- Tooltip for Line Chart -->
                            <div x-show="showTooltip && tooltipType === 'line'"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform scale-95"
                                x-transition:enter-end="opacity-100 transform scale-100"
                                class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap z-10">
                                Wykres liniowy
                            </div>
                        </div>

                        <div class="relative">
                            <button @click="toggleChartType('bar')"
                                @mouseenter="showTooltip = true; tooltipType = 'bar'"
                                @mouseleave="showTooltip = false"
                                :class="chartType === 'bar' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600'"
                                class="p-1.5 sm:p-2 rounded-lg transition-colors duration-200 hover:scale-105">
                                <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                    </path>
                                </svg>
                            </button>
                            <!-- Tooltip for Bar Chart -->
                            <div x-show="showTooltip && tooltipType === 'bar'"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform scale-95"
                                x-transition:enter-end="opacity-100 transform scale-100"
                                class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap z-10">
                                Wykres słupkowy
                            </div>
                        </div>

                        <div class="relative">
                            <button @click="toggleChartType('area')"
                                @mouseenter="showTooltip = true; tooltipType = 'area'"
                                @mouseleave="showTooltip = false"
                                :class="chartType === 'area' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600'"
                                class="p-1.5 sm:p-2 rounded-lg transition-colors duration-200 hover:scale-105">
                                <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z">
                                    </path>
                                </svg>
                            </button>
                            <!-- Tooltip for Area Chart -->
                            <div x-show="showTooltip && tooltipType === 'area'"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform scale-95"
                                x-transition:enter-end="opacity-100 transform scale-100"
                                class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap z-10">
                                Wykres powierzchniowy
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="laundry-throughput-main-chart" class="h-64 sm:h-80 md:h-96"></div>
        </div>

        <!-- Status Breakdown Chart (when available) -->
        <div x-show="activeTab === 'monthly'" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl p-4 sm:p-6 md:p-8 border border-indigo-200 shadow-lg">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 sm:mb-6">
                <h4 class="text-lg sm:text-xl font-bold text-gray-900">Status dywanów</h4>
                <span
                    class="text-xs font-medium px-2 py-1 sm:px-3 sm:py-1.5 rounded-full bg-indigo-100 text-indigo-800">
                    Bieżący miesiąc
                </span>
            </div>
            <div class="grid grid-cols-1 gap-6">
                <div id="laundry-throughput-status-breakdown-chart" class="h-64 sm:h-80"></div>
                <div class="space-y-3 sm:space-y-4">
                    <h5 class="text-base sm:text-lg font-semibold text-gray-800">Szczegóły statusów</h5>
                    <template x-for="(item, index) in statusBreakdownData" :key="index">
                        <div
                            class="flex items-center justify-between p-3 sm:p-4 bg-white rounded-lg border border-gray-200">
                            <div class="flex items-center">
                                <div class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full mr-2 sm:mr-3"
                                    :style="`background-color: ${getStatusColor(index)}`"></div>
                                <div>
                                    <p class="text-sm sm:font-medium text-gray-900 truncate" x-text="item.label"></p>
                                    <p class="text-xs sm:text-sm text-gray-500"
                                        x-text="`Średnia powierzchnia: ${item.avg_area} m²`"></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm sm:font-bold text-gray-900" x-text="`${item.value} dywanów`"></p>
                                <p class="text-xs sm:text-sm text-gray-500" x-text="`${item.percentage}%`"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Completion Rate Chart -->
            <div
                class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-4 sm:p-6 border border-green-200 shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-bold text-gray-900">Wskaźnik ukończenia</h4>
                    <span class="text-xs font-medium px-2 py-1 rounded-full bg-green-100 text-green-800"
                        x-text="getCompletionRateText()"></span>
                </div>
                <div id="laundry-throughput-completion-rate-chart" class="h-48 sm:h-64"></div>
            </div>

            <!-- Average Area Chart -->
            <div
                class="bg-gradient-to-br from-orange-50 to-amber-50 rounded-2xl p-4 sm:p-6 border border-orange-200 shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-bold text-gray-900">Średnia powierzchnia</h4>
                    <span class="text-xs font-medium px-2 py-1 rounded-full bg-orange-100 text-orange-800">m²</span>
                </div>
                <div id="laundry-throughput-average-area-chart" class="h-48 sm:h-64"></div>
            </div>
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
                weeklyData: @json($chartData['weekly'] ?? []),
                monthlyData: @json($chartData['monthly'] ?? []),
                yearlyData: @json($chartData['yearly'] ?? []),
                statusBreakdownData: @json($chartData['statusBreakdown'] ?? []),

                initCharts() {
                    this.$nextTick(() => {
                        this.createMainChart();
                        this.createStatusBreakdownChart();
                        this.createCompletionRateChart();
                        this.createAverageAreaChart();

                        window.addEventListener('resize', () => {
                            this.recreateCharts();
                        });
                    });
                },

                switchTab(tab) {
                    this.$wire.set('activeTab', tab).then(() => {
                        this.$nextTick(() => {
                            this.recreateCharts();
                        });
                    }).catch(error => {
                        console.error("Error switching tab:", error);
                    });
                },

                toggleChartType(type) {
                    this.chartType = type;
                    this.updateMainChart();
                },

                updateCharts(eventData) {
                    if (eventData && eventData.data) {
                        this.weeklyData = eventData.data.weekly || [];
                        this.monthlyData = eventData.data.monthly || [];
                        this.yearlyData = eventData.data.yearly || [];
                        this.statusBreakdownData = eventData.data.statusBreakdown || [];
                        this.$nextTick(() => this.recreateCharts());
                    } else {
                        console.warn("Received update-charts event with no valid data:", eventData);
                        this.recreateCharts();
                    }
                },

                getCurrentData() {
                    switch (this.activeTab) {
                        case 'weekly':
                            return this.weeklyData && Array.isArray(this.weeklyData) ? this.weeklyData : [];
                        case 'monthly':
                            return this.monthlyData && Array.isArray(this.monthlyData) ? this.monthlyData : [];
                        case 'yearly':
                            return this.yearlyData && Array.isArray(this.yearlyData) ? this.yearlyData : [];
                        default:
                            return [];
                    }
                },

                getChartTitle() {
                    const titles = {
                        'weekly': 'Przepustowość tygodniowa',
                        'monthly': 'Przepustowość miesięczna',
                        'yearly': 'Przepustowość roczna'
                    };
                    return titles[this.activeTab] || 'Przepustowość';
                },

                getTrendText() {
                    const trends = {
                        'weekly': '{{ $weeklyTrend }}',
                        'monthly': '{{ $monthlyTrend }}',
                        'yearly': '{{ $yearlyChange }}'
                    };
                    return trends[this.activeTab] || 'Brak danych';
                },

                getTrendBadgeClass() {
                    const trend = this.getTrendText().toLowerCase();
                    if (trend.includes('rosnący') || trend.includes('wzrost') || trend.includes('+'))
                    return 'bg-green-100 text-green-800';
                    if (trend.includes('malejący') || trend.includes('spadek') || trend.includes('-'))
                    return 'bg-red-100 text-red-800';
                    return 'bg-blue-100 text-blue-800';
                },

                getCompletionRateText() {
                    const data = this.getCurrentData();
                    if (data.length === 0) return 'Brak danych';
                    const latestData = data[data.length - 1];
                    const rate = latestData && latestData.completion_rate !== undefined ? latestData.completion_rate : 0;
                    return `${rate}%`;
                },

                getStatusColor(index) {
                    const colors = ['#10B981', '#3B82F6', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4', '#84CC16', '#F97316'];
                    return colors[index % colors.length];
                },

                destroyChart(chartName) {
                    if (this[chartName]) {
                        try {
                            this[chartName].destroy();
                        } catch (e) {
                            console.warn(`Error destroying ${chartName}:`, e);
                        }
                        this[chartName] = null;
                    }
                },

                destroyCharts() {
                    this.destroyChart('mainChart');
                    this.destroyChart('statusBreakdownChart');
                    this.destroyChart('completionRateChart');
                    this.destroyChart('averageAreaChart');
                },

                recreateCharts() {
                    this.destroyCharts();
                    this.$nextTick(() => {
                        this.createMainChart();
                        this.createStatusBreakdownChart();
                        this.createCompletionRateChart();
                        this.createAverageAreaChart();
                    });
                },

                getChartConfig() {
                    const data = this.getCurrentData();
                    if (!data || !Array.isArray(data) || data.length === 0) {
                        return this.getEmptyChartConfig('Brak danych do wyświetlenia');
                    }

                    const colors = {
                        'weekly': ['#10B981'],
                        'monthly': ['#3B82F6'],
                        'yearly': ['#8B5CF6']
                    };

                    const baseConfig = {
                        chart: {
                            type: this.chartType,
                            height: '100%',
                            toolbar: {
                                show: false
                            },
                            foreColor: '#6B7280',
                            fontFamily: 'Inter, sans-serif',
                            animations: {
                                enabled: true,
                                easing: 'easeinout',
                                speed: 800
                            },
                            dropShadow: {
                                enabled: true,
                                color: colors[this.activeTab][0],
                                top: 18,
                                left: 7,
                                blur: 10,
                                opacity: 0.1
                            },
                            responsive: [{
                                breakpoint: 768,
                                options: {
                                    chart: {
                                        height: 240
                                    },
                                    xaxis: {
                                        labels: {
                                            style: {
                                                fontSize: '10px'
                                            }
                                        }
                                    },
                                    yaxis: {
                                        labels: {
                                            style: {
                                                fontSize: '10px'
                                            }
                                        }
                                    },
                                    markers: {
                                        size: 3,
                                        hover: {
                                            size: 5
                                        }
                                    }
                                }
                            }]
                        },
                        series: [{
                            name: 'Przetworzone dywany',
                            data: data.map(item => parseInt(item.value, 10) || 0)
                        }],
                        xaxis: {
                            categories: data.map(item => item.label || ''),
                            axisBorder: {
                                show: false
                            },
                            axisTicks: {
                                show: false
                            },
                            labels: {
                                style: {
                                    colors: '#9CA3AF',
                                    fontSize: '12px',
                                    fontWeight: 500
                                },
                                rotate: -45,
                                rotateAlways: window.innerWidth < 768
                            }
                        },
                        yaxis: {
                            labels: {
                                formatter: (value) => Math.round(value).toString(),
                                style: {
                                    colors: '#9CA3AF',
                                    fontSize: '12px'
                                }
                            },
                            title: {
                                text: 'Liczba dywanów',
                                style: {
                                    color: '#6B7280',
                                    fontSize: '12px',
                                    fontWeight: 500
                                }
                            }
                        },
                        colors: colors[this.activeTab],
                        grid: {
                            borderColor: '#F3F4F6',
                            strokeDashArray: 5,
                            padding: {
                                left: 10
                            }
                        },
                        tooltip: {
                            theme: 'light',
                            style: {
                                fontSize: '12px',
                                fontFamily: 'Inter, sans-serif'
                            },
                            y: {
                                formatter: (value, {
                                    dataPointIndex
                                }) => {
                                    const item = data[dataPointIndex];
                                    if (!item) return `${value} dywanów przetworzonych`;
                                    let tooltipText = `${value} dywanów przetworzonych`;
                                    if (item.completed_count !== undefined) tooltipText +=
                                        `<br/>Ukończonych: ${item.completed_count}`;
                                    if (item.completion_rate !== undefined) tooltipText +=
                                        `<br/>Wskaźnik ukończenia: ${item.completion_rate}%`;
                                    if (item.avg_area !== undefined) tooltipText +=
                                        `<br/>Średnia powierzchnia: ${item.avg_area} m²`;
                                    if (item.total_area !== undefined) tooltipText +=
                                        `<br/>Całkowita powierzchnia: ${item.total_area} m²`;
                                    return tooltipText;
                                }
                            },
                            x: {
                                formatter: (value, {
                                    dataPointIndex
                                }) => data[dataPointIndex]?.full_name || value
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        noData: {
                            text: 'Brak danych do wyświetlenia',
                            align: 'center',
                            verticalAlign: 'middle'
                        }
                    };

                    if (this.chartType === 'line') {
                        baseConfig.stroke = {
                            curve: 'smooth',
                            width: 3,
                            lineCap: 'round'
                        };
                        baseConfig.markers = {
                            size: 4,
                            colors: ['#fff'],
                            strokeColors: colors[this.activeTab][0],
                            strokeWidth: 2,
                            hover: {
                                size: 6
                            }
                        };
                    } else if (this.chartType === 'bar') {
                        baseConfig.plotOptions = {
                            bar: {
                                borderRadius: 6,
                                columnWidth: '60%',
                                borderRadiusApplication: 'end'
                            }
                        };
                    } else if (this.chartType === 'area') {
                        baseConfig.stroke = {
                            curve: 'smooth',
                            width: 2
                        };
                        baseConfig.fill = {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.6,
                                opacityTo: 0.1,
                                stops: [0, 100]
                            }
                        };
                    }

                    return baseConfig;
                },

                getEmptyChartConfig(message) {
                    return {
                        chart: {
                            type: 'line',
                            height: '100%',
                            toolbar: {
                                show: false
                            }
                        },
                        series: [],
                        xaxis: {
                            categories: []
                        },
                        noData: {
                            text: message,
                            align: 'center',
                            verticalAlign: 'middle',
                            style: {
                                color: '#6B7280',
                                fontSize: '14px',
                                fontFamily: 'Inter, sans-serif'
                            }
                        }
                    };
                },

                createMainChart() {
                    const element = document.querySelector("#laundry-throughput-main-chart");
                    if (element) {
                        this.destroyChart('mainChart');
                        this.mainChart = new ApexCharts(element, this.getChartConfig());
                        this.mainChart.render().catch(error => {
                            console.error("Error rendering main chart:", error);
                            element.innerHTML =
                                '<div class="h-full flex items-center justify-center text-gray-500">Błąd ładowania wykresu</div>';
                        });
                    } else {
                        console.warn("Chart container element (#laundry-throughput-main-chart) not found.");
                    }
                },

                updateMainChart() {
                    if (this.mainChart) {
                        this.mainChart.updateOptions(this.getChartConfig(), false, true, true).catch(error => {
                            console.error("Error updating main chart:", error);
                        });
                    } else {
                        this.createMainChart();
                    }
                },

                createStatusBreakdownChart() {
                    if (this.activeTab !== 'monthly') {
                        this.destroyChart('statusBreakdownChart');
                        return;
                    }
                    const element = document.querySelector("#laundry-throughput-status-breakdown-chart");
                    if (!element) return;

                    if (!this.statusBreakdownData || this.statusBreakdownData.length === 0) {
                        this.destroyChart('statusBreakdownChart');
                        const emptyConfig = this.getEmptyChartConfig('Brak danych statusu');
                        emptyConfig.chart.type = 'donut';
                        this.statusBreakdownChart = new ApexCharts(element, emptyConfig);
                        this.statusBreakdownChart.render();
                        return;
                    }

                    const config = {
                        chart: {
                            type: 'donut',
                            height: '100%',
                            fontFamily: 'Inter, sans-serif'
                        },
                        series: this.statusBreakdownData.map(item => parseInt(item.value, 10) || 0),
                        labels: this.statusBreakdownData.map(item => item.label || 'Nieznany'),
                        colors: this.statusBreakdownData.map((_, index) => this.getStatusColor(index)),
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '65%',
                                    labels: {
                                        show: true,
                                        total: {
                                            show: true,
                                            label: 'Łącznie',
                                            formatter: () =>
                                                `${this.statusBreakdownData.reduce((sum, item) => sum + (parseInt(item.value, 10) || 0), 0)} dywanów`,
                                            style: {
                                                fontSize: '14px',
                                                fontWeight: 600
                                            }
                                        }
                                    }
                                }
                            }
                        },
                        legend: {
                            show: true,
                            position: 'right',
                            fontSize: '12px',
                            markers: {
                                width: 10,
                                height: 10
                            }
                        },
                        tooltip: {
                            y: {
                                formatter: (value, {
                                    dataPointIndex
                                }) => {
                                    const item = this.statusBreakdownData[dataPointIndex];
                                    let tooltip = `${value} dywanów`;
                                    if (item?.avg_area) tooltip += `<br/>Średnia powierzchnia: ${item.avg_area} m²`;
                                    return tooltip;
                                }
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            formatter: (val) => `${val.toFixed(1)}%`,
                            style: {
                                fontSize: '10px',
                                fontWeight: 600
                            }
                        },
                        responsive: [{
                            breakpoint: 768,
                            options: {
                                chart: {
                                    height: 240
                                },
                                legend: {
                                    position: 'bottom'
                                },
                                plotOptions: {
                                    pie: {
                                        donut: {
                                            labels: {
                                                total: {
                                                    style: {
                                                        fontSize: '12px'
                                                    }
                                                }
                                            }
                                        }
                                    }
                                },
                                dataLabels: {
                                    style: {
                                        fontSize: '9px'
                                    }
                                }
                            }
                        }]
                    };

                    this.destroyChart('statusBreakdownChart');
                    this.statusBreakdownChart = new ApexCharts(element, config);
                    this.statusBreakdownChart.render().catch(error => console.error(
                        "Error rendering status breakdown chart:", error));
                },

                createCompletionRateChart() {
                    const element = document.querySelector("#laundry-throughput-completion-rate-chart");
                    if (!element) return;
                    const data = this.getCurrentData();
                    if (!data || data.length === 0) {
                        this.destroyChart('completionRateChart');
                        this.completionRateChart = new ApexCharts(element, this.getEmptyChartConfig(
                            'Brak danych wskaźnika'));
                        this.completionRateChart.render();
                        return;
                    }

                    const config = {
                        chart: {
                            type: 'line',
                            height: '100%',
                            toolbar: {
                                show: false
                            },
                            fontFamily: 'Inter, sans-serif'
                        },
                        series: [{
                            name: 'Wskaźnik ukończenia',
                            data: data.map(item => Math.max(0, Math.min(100, parseFloat(item.completion_rate) ||
                                0)))
                        }],
                        xaxis: {
                            categories: data.map(item => item.label || ''),
                            labels: {
                                style: {
                                    fontSize: '10px'
                                }
                            }
                        },
                        yaxis: {
                            min: 0,
                            max: 100,
                            labels: {
                                formatter: (value) => `${Math.round(value)}%`,
                                style: {
                                    fontSize: '10px'
                                }
                            },
                            title: {
                                text: 'Procent (%)',
                                style: {
                                    color: '#6B7280',
                                    fontSize: '10px',
                                    fontWeight: 500
                                }
                            }
                        },
                        colors: ['#10B981'],
                        stroke: {
                            curve: 'smooth',
                            width: 3
                        },
                        markers: {
                            size: 4,
                            colors: ['#fff'],
                            strokeColors: '#10B981',
                            strokeWidth: 2
                        },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.3,
                                opacityTo: 0.1,
                                stops: [0, 100]
                            }
                        },
                        grid: {
                            borderColor: '#F3F4F6',
                            strokeDashArray: 5
                        },
                        tooltip: {
                            y: {
                                formatter: (value) => `${Math.round(value)}%`
                            }
                        },
                        responsive: [{
                            breakpoint: 768,
                            options: {
                                chart: {
                                    height: 200
                                },
                                xaxis: {
                                    labels: {
                                        style: {
                                            fontSize: '9px'
                                        }
                                    }
                                },
                                yaxis: {
                                    labels: {
                                        style: {
                                            fontSize: '9px'
                                        }
                                    },
                                    title: {
                                        style: {
                                            fontSize: '9px'
                                        }
                                    }
                                },
                                markers: {
                                    size: 3
                                }
                            }
                        }]
                    };

                    this.destroyChart('completionRateChart');
                    this.completionRateChart = new ApexCharts(element, config);
                    this.completionRateChart.render().catch(error => console.error("Error rendering completion rate chart:",
                        error));
                },

                createAverageAreaChart() {
                    const element = document.querySelector("#laundry-throughput-average-area-chart");
                    if (!element) return;
                    const data = this.getCurrentData();
                    if (!data || data.length === 0) {
                        this.destroyChart('averageAreaChart');
                        const emptyConfig = this.getEmptyChartConfig('Brak danych powierzchni');
                        emptyConfig.chart.type = 'bar';
                        this.averageAreaChart = new ApexCharts(element, emptyConfig);
                        this.averageAreaChart.render();
                        return;
                    }

                    const config = {
                        chart: {
                            type: 'bar',
                            height: '100%',
                            toolbar: {
                                show: false
                            },
                            fontFamily: 'Inter, sans-serif'
                        },
                        series: [{
                            name: 'Średnia powierzchnia',
                            data: data.map(item => parseFloat(item.avg_area) || 0)
                        }],
                        xaxis: {
                            categories: data.map(item => item.label || ''),
                            labels: {
                                style: {
                                    fontSize: '10px'
                                }
                            }
                        },
                        yaxis: {
                            labels: {
                                formatter: (value) => `${value.toFixed(2)} m²`,
                                style: {
                                    fontSize: '10px'
                                }
                            },
                            title: {
                                text: 'Powierzchnia (m²)',
                                style: {
                                    color: '#6B7280',
                                    fontSize: '10px',
                                    fontWeight: 500
                                }
                            }
                        },
                        colors: ['#F59E0B'],
                        plotOptions: {
                            bar: {
                                borderRadius: 4,
                                columnWidth: '60%',
                                borderRadiusApplication: 'end'
                            }
                        },
                        grid: {
                            borderColor: '#F3F4F6',
                            strokeDashArray: 5
                        },
                        tooltip: {
                            y: {
                                formatter: (value) => `${value.toFixed(2)} m²`
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        responsive: [{
                            breakpoint: 768,
                            options: {
                                chart: {
                                    height: 200
                                },
                                xaxis: {
                                    labels: {
                                        style: {
                                            fontSize: '9px'
                                        }
                                    }
                                },
                                yaxis: {
                                    labels: {
                                        style: {
                                            fontSize: '9px'
                                        }
                                    },
                                    title: {
                                        style: {
                                            fontSize: '9px'
                                        }
                                    }
                                }
                            }
                        }]
                    };

                    this.destroyChart('averageAreaChart');
                    this.averageAreaChart = new ApexCharts(element, config);
                    this.averageAreaChart.render().catch(error => console.error("Error rendering average area chart:",
                        error));
                }
            }
        }
    </script>
@endpush
