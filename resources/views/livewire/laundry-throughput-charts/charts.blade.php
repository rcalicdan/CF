<div class="space-y-6" 
     x-data="chartComponent()"
     x-init="$watch('chartData', () => updateChart())"
     wire:ignore>
    {{-- Chart Controls --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <h2 class="text-xl font-semibold text-gray-900">Analytics Dashboard</h2>

            <div class="flex flex-col sm:flex-row gap-3">
                {{-- Metric Selector --}}
                <div class="min-w-0">
                    <select wire:model.live="selectedMetric"
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        @foreach ($availableMetrics as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Chart Type Selector --}}
                <div class="min-w-0">
                    <select wire:model.live="chartType"
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        @foreach ($availableChartTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Date Range Selector --}}
                <div class="min-w-0">
                    <select wire:model.live="dateRange"
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        @foreach ($availableDateRanges as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart Container --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div x-ref="chartContainer" class="w-full" style="min-height: 400px;">
            {{-- Loading State --}}
            <div x-show="loading" class="flex items-center justify-center h-96">
                <div class="flex items-center space-x-3">
                    <svg class="animate-spin h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span class="text-gray-600">Loading chart...</span>
                </div>
            </div>

            {{-- Chart Element --}}
            <div x-show="!loading" x-ref="chartElement" class="w-full" style="min-height: 400px;"></div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @php
            $stats = $this->getQuickStats();
        @endphp

        @foreach ($stats as $stat)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-{{ $stat['color'] }}-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-{{ $stat['color'] }}-600" fill="currentColor" viewBox="0 0 20 20">
                                {!! $stat['icon'] !!}
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">{{ $stat['label'] }}</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stat['value'] }}</p>
                        @if (isset($stat['change']))
                            <p class="text-xs text-{{ $stat['change'] >= 0 ? 'green' : 'red' }}-600">
                                {{ $stat['change'] >= 0 ? '+' : '' }}{{ $stat['change'] }}%
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@latest"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('chartComponent', () => ({
                chart: null,
                loading: false,
                chartData: @js($chartData),

                init() {
                    // Watch for Livewire updates
                    this.$wire.$watch('chartData', (newData) => {
                        this.chartData = newData;
                        this.updateChart();
                    });
                    
                    // Initial chart render
                    this.$nextTick(() => {
                        this.renderChart();
                    });
                },

                destroy() {
                    if (this.chart) {
                        this.chart.destroy();
                        this.chart = null;
                    }
                },

                updateChart() {
                    this.loading = true;
                    
                    if (this.chart) {
                        this.chart.destroy();
                        this.chart = null;
                    }
                
                    setTimeout(() => {
                        this.renderChart();
                        this.loading = false;
                    }, 100);
                },

                renderChart() {
                    if (!this.$refs.chartElement) {
                        console.error('Chart container not found');
                        return;
                    }

                    const options = this.getChartOptions();
                    
                    if (this.chart) {
                        this.chart.destroy();
                    }
                    
                    try {
                        this.chart = new ApexCharts(this.$refs.chartElement, options);
                        this.chart.render();
                    } catch (error) {
                        console.error('Error rendering chart:', error);
                    }
                },

                getChartOptions() {
                    const baseOptions = {
                        chart: {
                            type: this.getApexChartType(),
                            height: 400,
                            background: 'transparent',
                            fontFamily: 'Inter, ui-sans-serif, system-ui',
                            toolbar: {
                                show: true,
                                offsetX: 0,
                                offsetY: 0,
                                tools: {
                                    download: true,
                                    selection: false,
                                    zoom: true,
                                    zoomin: true,
                                    zoomout: true,
                                    pan: false,
                                    reset: true
                                },
                                export: {
                                    csv: {
                                        filename: 'chart-data'
                                    },
                                    svg: {
                                        filename: 'chart'
                                    },
                                    png: {
                                        filename: 'chart'
                                    }
                                }
                            },
                            animations: {
                                enabled: true,
                                easing: 'easeinout',
                                speed: 800
                            }
                        },
                        theme: {
                            mode: 'light',
                            palette: 'palette1'
                        },
                        grid: {
                            show: true,
                            borderColor: '#f1f5f9',
                            strokeDashArray: 3,
                            position: 'back',
                            xaxis: {
                                lines: {
                                    show: false
                                }
                            },
                            yaxis: {
                                lines: {
                                    show: true
                                }
                            }
                        },
                        responsive: [{
                                breakpoint: 768,
                                options: {
                                    chart: {
                                        height: 300
                                    },
                                    legend: {
                                        position: 'bottom'
                                    }
                                }
                            },
                            {
                                breakpoint: 480,
                                options: {
                                    chart: {
                                        height: 250
                                    },
                                    legend: {
                                        show: false
                                    }
                                }
                            }
                        ]
                    };

                    if (['pie', 'donut'].includes(this.chartData.type)) {
                        return {
                            ...baseOptions,
                            series: this.chartData.series,
                            labels: this.chartData.labels,
                            colors: this.chartData.colors,
                            legend: {
                                position: 'bottom',
                                horizontalAlign: 'center',
                                fontSize: '14px',
                                markers: {
                                    width: 12,
                                    height: 12,
                                    radius: 6
                                }
                            },
                            plotOptions: {
                                pie: {
                                    donut: {
                                        size: this.chartData.type === 'donut' ? '60%' : '0%',
                                        labels: {
                                            show: true,
                                            total: {
                                                show: true,
                                                fontSize: '16px',
                                                fontWeight: 600,
                                                color: '#374151'
                                            }
                                        }
                                    }
                                }
                            },
                            dataLabels: {
                                enabled: true,
                                formatter: function(val) {
                                    return Math.round(val) + '%';
                                },
                                style: {
                                    fontSize: '12px',
                                    fontWeight: '500',
                                    colors: ['#ffffff']
                                },
                                dropShadow: {
                                    enabled: false
                                }
                            },
                            tooltip: {
                                enabled: true,
                                style: {
                                    fontSize: '12px'
                                }
                            }
                        };
                    }

                    return {
                        ...baseOptions,
                        series: this.chartData.series,
                        xaxis: {
                            categories: this.chartData.categories,
                            labels: {
                                style: {
                                    fontSize: '12px',
                                    colors: '#6b7280'
                                },
                                rotate: -45,
                                rotateAlways: false,
                                maxHeight: 120
                            },
                            axisBorder: {
                                show: false
                            },
                            axisTicks: {
                                show: false
                            }
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    fontSize: '12px',
                                    colors: '#6b7280'
                                },
                                formatter: function(val) {
                                    if (val >= 1000000) {
                                        return (val / 1000000).toFixed(1) + 'M';
                                    }
                                    if (val >= 1000) {
                                        return (val / 1000).toFixed(1) + 'K';
                                    }
                                    return Math.round(val);
                                }
                            }
                        },
                        legend: {
                            position: 'top',
                            horizontalAlign: 'right',
                            fontSize: '14px',
                            markers: {
                                width: 12,
                                height: 12,
                                radius: 6
                            }
                        },
                        plotOptions: {
                            bar: {
                                borderRadius: 4,
                                columnWidth: '60%',
                                dataLabels: {
                                    position: 'top'
                                }
                            }
                        },
                        fill: {
                            type: this.chartData.type === 'area' ? 'gradient' : 'solid',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.7,
                                opacityTo: 0.9,
                                stops: [0, 90, 100]
                            }
                        },
                        stroke: {
                            curve: 'smooth',
                            width: this.chartData.type === 'line' ? 3 : 0
                        },
                        dataLabels: {
                            enabled: false
                        },
                        tooltip: {
                            shared: true,
                            intersect: false,
                            style: {
                                fontSize: '12px'
                            },
                            y: {
                                formatter: function(val) {
                                    if (val >= 1000000) {
                                        return (val / 1000000).toFixed(2) + 'M';
                                    }
                                    if (val >= 1000) {
                                        return (val / 1000).toFixed(1) + 'K';
                                    }
                                    return val;
                                }
                            }
                        }
                    };
                },

                getApexChartType() {
                    const typeMapping = {
                        'bar': 'bar',
                        'line': 'line',
                        'area': 'area',
                        'pie': 'pie',
                        'donut': 'donut'
                    };
                    return typeMapping[this.chartData.type] || 'bar';
                }
            }));
        });
    </script>
@endpush