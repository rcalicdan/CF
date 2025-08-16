<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 md:p-8" x-data="chartsComponent()"
    x-init="initCharts()" @update-charts.window="updateCharts($event.detail)">

    <!-- Header Section -->
    <div class="mb-6 sm:mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex-1">
                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 leading-tight">Analiza kosztów przetwarzania</h3>
                <p class="text-sm sm:text-base text-gray-500 mt-1">Szczegółowe podsumowanie kosztów w różnych okresach
                </p>
            </div>
            <div class="flex gap-2 sm:gap-3 w-full sm:w-auto">
                <button @click="$wire.refreshData()" :disabled="loading"
                    class="flex-1 sm:flex-none flex items-center justify-center px-3 py-2 sm:px-4 sm:py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors duration-200 whitespace-nowrap">
                    <svg x-show="!loading" class="w-4 h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                    <svg x-show="loading" class="animate-spin w-4 h-4 mr-1 sm:mr-2" fill="none" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"
                            class="opacity-25"></circle>
                        <path fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            class="opacity-75"></path>
                    </svg>
                    <span x-text="loading ? 'Odswiezanie...' : 'Odswież'" class="truncate"></span>
                </button>

                <!-- PDF Download Button -->
                <button @click="$wire.generatePdf()"
                    class="flex items-center justify-center px-3 py-2 sm:px-4 sm:py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors duration-200 whitespace-nowrap flex-1 sm:flex-none">
                    <svg class="w-4 h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                    </svg>
                    <span class="truncate hidden xs:inline">PDF</span>
                    <span class="truncate inline xs:hidden">PDF</span>
                </button>

                <!-- CSV Download Button -->
                <button @click="$wire.generateCsv()"
                    class="flex items-center justify-center px-3 py-2 sm:px-4 sm:py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition-colors duration-200 whitespace-nowrap flex-1 sm:flex-none">
                    <svg class="w-4 h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span class="truncate hidden xs:inline">CSV</span>
                    <span class="truncate inline xs:hidden">CSV</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-4 sm:p-6 border border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm font-medium text-blue-600">Bieżący miesiąc</p>
                    <p class="text-lg sm:text-2xl font-bold text-blue-900">{{ number_format($totalCurrentMonth, 2) }} zł
                    </p>
                </div>
                <div class="p-2 sm:p-3 bg-blue-200 rounded-full">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-4 sm:p-6 border border-green-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm font-medium text-green-600">Poprzedni miesiąc</p>
                    <p class="text-lg sm:text-2xl font-bold text-green-900">{{ number_format($totalPreviousMonth, 2) }}
                        zł</p>
                </div>
                <div class="p-2 sm:p-3 bg-green-200 rounded-full">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600" fill="none" stroke="currentColor"
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
                :class="activeTab === 'weekly' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
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
                :class="activeTab === 'monthly' ? 'bg-white text-green-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
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
                    <div class="flex space-x-1">
                        <button @click="toggleChartType('line')"
                            :class="chartType === 'line' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600'"
                            class="p-1.5 sm:p-2 rounded-lg transition-colors duration-200">
                            <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </button>
                        <button @click="toggleChartType('bar')"
                            :class="chartType === 'bar' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600'"
                            class="p-1.5 sm:p-2 rounded-lg transition-colors duration-200">
                            <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <div id="main-chart" class="h-64 sm:h-80 md:h-96"></div>
        </div>

        <!-- Cost Types Chart (when available) -->
        <div x-show="activeTab === 'monthly'" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl p-4 sm:p-6 md:p-8 border border-indigo-200 shadow-lg">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 sm:mb-6">
                <h4 class="text-lg sm:text-xl font-bold text-gray-900">Koszty według typu</h4>
                <span
                    class="text-xs font-medium px-2 py-1 sm:px-3 sm:py-1.5 rounded-full bg-indigo-100 text-indigo-800">
                    Bieżący miesiąc
                </span>
            </div>
            <div class="grid grid-cols-1 gap-6">
                <div id="cost-types-chart" class="h-64 sm:h-80"></div>
                <div class="space-y-3 sm:space-y-4">
                    <h5 class="text-base sm:text-lg font-semibold text-gray-800">Szczegóły typów kosztów</h5>
                    <template x-for="(item, index) in costTypesData" :key="index">
                        <div
                            class="flex items-center justify-between p-3 sm:p-4 bg-white rounded-lg border border-gray-200">
                            <div class="flex items-center">
                                <div class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full mr-2 sm:mr-3"
                                    :style="`background-color: ${getCostTypeColor(index)}`"></div>
                                <div>
                                    <p class="text-sm sm:font-medium text-gray-900 truncate" x-text="item.label"></p>
                                    <p class="text-xs sm:text-sm text-gray-500" x-text="`${item.count} transakcji`">
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm sm:font-bold text-gray-900" x-text="`${item.value.toFixed(2)} zł`">
                                </p>
                                <p class="text-xs sm:text-sm text-gray-500" x-text="`${item.percentage}%`"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        function chartsComponent() {
            return {
                activeTab: @entangle('activeTab'),
                loading: false,
                chartType: 'line',
                mainChart: null,
                costTypesChart: null,
                weeklyData: @json($chartData['weekly'] ?? [], JSON_UNESCAPED_UNICODE),
                monthlyData: @json($chartData['monthly'] ?? [], JSON_UNESCAPED_UNICODE),
                yearlyData: @json($chartData['yearly'] ?? [], JSON_UNESCAPED_UNICODE),
                costTypesData: @json($chartData['costTypes'] ?? [], JSON_UNESCAPED_UNICODE),

                initCharts() {
                    this.$nextTick(() => {
                        this.createMainChart();
                        this.createCostTypesChart();
                        window.addEventListener('resize', () => {
                            this.recreateCharts();
                        });
                    });
                },

                switchTab(tab) {
                    this.loading = true;
                    this.$wire.set('activeTab', tab).then(() => {
                        this.loading = false;
                        this.$nextTick(() => {
                            this.recreateCharts();
                        });
                    });
                },

                toggleChartType(type) {
                    this.chartType = type;
                    this.updateMainChart();
                },

                updateCharts(data) {
                    if (data && data.data) {
                        this.weeklyData = data.data.weekly || [];
                        this.monthlyData = data.data.monthly || [];
                        this.yearlyData = data.data.yearly || [];
                        this.costTypesData = data.data.costTypes || [];
                        this.$nextTick(() => {
                            this.recreateCharts();
                        });
                    }
                },

                recreateCharts() {
                    this.destroyCharts();
                    this.$nextTick(() => {
                        this.createMainChart();
                        this.createCostTypesChart();
                    });
                },

                destroyCharts() {
                    if (this.mainChart) {
                        this.mainChart.destroy();
                        this.mainChart = null;
                    }
                    if (this.costTypesChart) {
                        this.costTypesChart.destroy();
                        this.costTypesChart = null;
                    }
                },

                getCurrentData() {
                    switch (this.activeTab) {
                        case 'weekly':
                            return this.weeklyData || [];
                        case 'monthly':
                            return this.monthlyData || [];
                        case 'yearly':
                            return this.yearlyData || [];
                        default:
                            return [];
                    }
                },

                getChartTitle() {
                    const titles = {
                        'weekly': 'Koszty tygodniowe',
                        'monthly': 'Koszty miesieczne',
                        'yearly': 'Koszty roczne'
                    };
                    return titles[this.activeTab] || 'Koszty';
                },

                getTrendText() {
                    const trends = {
                        'weekly': '{{ $weeklyTrend ?? 'Stabilne' }}',
                        'monthly': '{{ $monthlyTrend ?? 'Stabilne' }}',
                        'yearly': '{{ $yearlyChange ?? '0%' }}'
                    };
                    return trends[this.activeTab] || 'Brak danych';
                },

                getTrendBadgeClass() {
                    const trend = this.getTrendText();
                    if (trend.includes('rosnacy') || trend.includes('+')) {
                        return 'bg-green-100 text-green-800';
                    } else if (trend.includes('malejacy') || trend.includes('-')) {
                        return 'bg-red-100 text-red-800';
                    }
                    return 'bg-blue-100 text-blue-800';
                },

                getCostTypeColor(index) {
                    const colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4'];
                    return colors[index % colors.length];
                },

                getChartConfig() {
                    const data = this.getCurrentData();
                    if (!data || data.length === 0) {
                        return this.getEmptyChartConfig();
                    }

                    const colors = {
                        'weekly': ['#3B82F6'],
                        'monthly': ['#10B981'],
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
                                    }
                                }
                            }]
                        },
                        series: [{
                            name: 'Koszty',
                            data: data.map(item => item.value || 0)
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
                                rotateAlways: true
                            }
                        },
                        yaxis: {
                            labels: {
                                formatter: (value) => this.formatCurrency(value),
                                style: {
                                    colors: '#9CA3AF',
                                    fontSize: '12px'
                                }
                            }
                        },
                        colors: colors[this.activeTab],
                        grid: {
                            borderColor: '#F3F4F6',
                            strokeDashArray: 5,
                            padding: {
                                top: 0,
                                right: 0,
                                bottom: 0,
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
                                    series,
                                    seriesIndex,
                                    dataPointIndex,
                                    w
                                }) => {
                                    let tooltipText = this.formatCurrency(value);
                                    const item = data[dataPointIndex];
                                    if (item && item.processed_count !== undefined && item.avg_cost_per_carpet !==
                                        undefined) {
                                        tooltipText += `<br/>Liczba dywanów: ${item.processed_count}`;
                                        tooltipText +=
                                            `<br/>Średni koszt/dywan: ${this.formatCurrency(item.avg_cost_per_carpet)}`;
                                    }
                                    return tooltipText;
                                }
                            },
                            x: {
                                formatter: (value, {
                                    dataPointIndex
                                }) => {
                                    const item = data[dataPointIndex];
                                    return item ? item.full_name : value;
                                }
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        legend: {
                            position: 'top',
                            horizontalAlign: 'right',
                            fontSize: '12px',
                            onItemClick: {
                                toggleDataSeries: false
                            }
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
                                columnWidth: '50%',
                                borderRadiusApplication: 'end'
                            }
                        };
                    }

                    if (this.activeTab === 'yearly' && this.chartType === 'line') {
                        baseConfig.chart.type = 'area';
                        baseConfig.fill = {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.4,
                                opacityTo: 0.1,
                                stops: [0, 100]
                            }
                        };
                    }

                    return baseConfig;
                },

                getEmptyChartConfig() {
                    return {
                        chart: {
                            type: 'line',
                            height: '100%',
                            toolbar: {
                                show: false
                            }
                        },
                        series: [{
                            name: 'Koszty',
                            data: []
                        }],
                        xaxis: {
                            categories: []
                        },
                        noData: {
                            text: 'Brak danych do wyswietlenia',
                            align: 'center',
                            verticalAlign: 'middle',
                            offsetX: 0,
                            offsetY: 0,
                            style: {
                                color: '#6B7280',
                                fontSize: '14px',
                                fontFamily: 'Inter, sans-serif'
                            }
                        }
                    };
                },

                createMainChart() {
                    const element = document.querySelector("#main-chart");
                    if (element) {
                        this.mainChart = new ApexCharts(element, this.getChartConfig());
                        this.mainChart.render();
                    }
                },

                updateMainChart() {
                    if (this.mainChart) {
                        const config = this.getChartConfig();
                        this.mainChart.updateOptions(config, true, true);
                    } else {
                        this.createMainChart();
                    }
                },

                createCostTypesChart() {
                    if (!this.costTypesData || this.costTypesData.length === 0) return;
                    const config = {
                        chart: {
                            type: 'donut',
                            height: '100%',
                            fontFamily: 'Inter, sans-serif',
                            responsive: [{
                                breakpoint: 768,
                                options: {
                                    chart: {
                                        height: 240
                                    },
                                    legend: {
                                        position: 'bottom'
                                    }
                                }
                            }]
                        },
                        series: this.costTypesData.map(item => item.value || 0),
                        labels: this.costTypesData.map(item => item.label || 'Nieznany'),
                        colors: this.costTypesData.map((_, index) => this.getCostTypeColor(index)),
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '70%',
                                    labels: {
                                        show: true,
                                        total: {
                                            show: true,
                                            label: 'Lacznie',
                                            formatter: () => this.formatCurrency(
                                                this.costTypesData.reduce((sum, item) => sum + (item.value || 0), 0)
                                            )
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
                                formatter: (value) => this.formatCurrency(value)
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            formatter: (val) => `${val.toFixed(1)}%`,
                            style: {
                                fontSize: '10px'
                            }
                        }
                    };
                    const element = document.querySelector("#cost-types-chart");
                    if (element) {
                        this.costTypesChart = new ApexCharts(element, config);
                        this.costTypesChart.render();
                    }
                },

                formatCurrency(value) {
                    if (!value || isNaN(value)) return '0 zl';
                    return new Intl.NumberFormat('pl-PL', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(value) + ' zl';
                }
            }
        }

        document.addEventListener('livewire:init', function() {
            Livewire.on('download-pdf', (data) => {
                const filename = data[0].filename;
                const link = document.createElement('a');
                link.href = '/storage/' + filename;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        });

        document.addEventListener('livewire:init', function() {
            Livewire.on('download-csv', (data) => {
                const filename = data[0].filename;
                const link = document.createElement('a');
                link.href = '/storage/' + filename;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        });
    </script>
@endpush
