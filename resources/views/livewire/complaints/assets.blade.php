@push('styles')
<style>
    /* resources/css/app.css - Add these custom styles */
    @media (max-width: 640px) {
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .truncate-mobile {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 200px;
        }
    }

    /* Custom scrollbar for mobile */
    .overflow-x-auto::-webkit-scrollbar {
        height: 4px;
    }

    .overflow-x-auto::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 2px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 2px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>
@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
                initializeCharts();
            });

            document.addEventListener('livewire:navigated', function() {
                initializeCharts();
            });

            Livewire.on('refreshStats', function() {
                setTimeout(() => {
                    initializeCharts();
                }, 100);
            });

            function initializeCharts() {
                initWeeklyTrendChart();
                initCategoryChart();
            }

            function initWeeklyTrendChart() {
                const chartElement = document.querySelector('#weeklyTrendChart');
                if (!chartElement) return;

                // Clear existing chart
                chartElement.innerHTML = '';

                const weeklyData = @json($weeklyTrend);

                const options = {
                    series: [{
                        name: 'Nowe skargi',
                        data: weeklyData.new_complaints,
                        color: '#ef4444'
                    }, {
                        name: 'Rozwiązane',
                        data: weeklyData.resolved_complaints,
                        color: '#22c55e'
                    }],
                    chart: {
                        type: 'bar',
                        height: 300,
                        toolbar: {
                            show: false
                        },
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            endingShape: 'rounded',
                            borderRadius: 4
                        },
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        show: true,
                        width: 2,
                        colors: ['transparent']
                    },
                    xaxis: {
                        categories: weeklyData.days,
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Liczba skarg'
                        }
                    },
                    fill: {
                        opacity: 1,
                        type: 'gradient',
                        gradient: {
                            shade: 'light',
                            type: 'vertical',
                            shadeIntensity: 0.3,
                            gradientToColors: undefined,
                            inverseColors: false,
                            opacityFrom: 0.9,
                            opacityTo: 0.7,
                            stops: [0, 100]
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return val + " skargi"
                            }
                        }
                    },
                    grid: {
                        show: true,
                        borderColor: '#f1f5f9',
                        strokeDashArray: 0,
                        position: 'back'
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right'
                    }
                };

                const chart = new ApexCharts(chartElement, options);
                chart.render();
            }

            function initCategoryChart() {
                const chartElement = document.querySelector('#categoryChart');
                if (!chartElement) return;

                // Clear existing chart
                chartElement.innerHTML = '';

                const categoryData = @json($categoryStats);
                const categories = Object.keys(categoryData);
                const values = Object.values(categoryData);

                const categoryLabels = {
                    'damage': 'Uszkodzenia',
                    'delay': 'Opóźnienia',
                    'quality': 'Jakość prania',
                    'communication': 'Komunikacja',
                    'other': 'Inne'
                };

                const options = {
                    series: values,
                    chart: {
                        type: 'donut',
                        height: 250,
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800
                        }
                    },
                    labels: categories.map(cat => categoryLabels[cat] || cat),
                    colors: ['#ef4444', '#f59e0b', '#3b82f6', '#8b5cf6', '#22c55e'],
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '65%',
                                labels: {
                                    show: true,
                                    total: {
                                        show: true,
                                        label: 'Razem',
                                        color: '#374151',
                                        formatter: function(w) {
                                            return w.globals.seriesTotals.reduce((a, b) => {
                                                return a + b
                                            }, 0)
                                        }
                                    }
                                }
                            }
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    legend: {
                        position: 'bottom',
                        offsetY: 10,
                        markers: {
                            width: 8,
                            height: 8,
                            radius: 4
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return val + " skargi"
                            }
                        }
                    }
                };

                const chart = new ApexCharts(chartElement, options);
                chart.render();
            }
</script>
@endpush