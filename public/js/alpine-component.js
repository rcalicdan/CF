function routeOptimizer() {
    const data = new RouteOptimizerData();

    return {
        ...data,

        selectedDriver: data.drivers[0],
        showRouteSummary: false,

        init() {
            console.log('RouteOptimizer component initializing...');
            console.log('Initial selected date:', this.selectedDate);
            console.log('Orders for selected date:', this.orders.length);

            this.$watch('loading', (value) => {
                console.log('Loading state changed:', value);
            });

            this.$watch('optimizationResult', (value) => {
                console.log('Optimization result changed:', !!value);
                if (value) {
                    console.log('Auto-showing summary...');
                    this.showRouteSummary = true;
                }
            });

            this.$watch('selectedDate', (value) => {
                console.log('Selected date changed:', value);
                this.setSelectedDate(value);
            });

            this.$nextTick(() => {
                setTimeout(() => {
                    console.log('Initializing map...');
                    const mapManager = new MapManager(this);
                    const optimizerService = new RouteOptimizerService(this);

                    window.mapManager = mapManager;
                    window.optimizerService = optimizerService;
                    window.routeData = this;

                    mapManager.init();
                }, 100);
            });

            console.log('RouteOptimizer component initialized');
        },

        async optimizeRoutes() {
            console.log('optimizeRoutes called with driver:', this.selectedDriver);
            console.log('Orders available for', this.selectedDate, ':', this.orders.length);

            if (this.orders.length === 0) {
                alert(`No orders available for ${this.formattedSelectedDate}`);
                return;
            }

            if (!window.optimizerService || !window.optimizerService.canOptimize()) {
                console.warn('Cannot optimize routes - missing service or requirements');
                return;
            }

            this.loading = true;
            this.optimizationError = null;

            try {
                await window.optimizerService.optimizeRoutes();
                setTimeout(() => {
                    this.showRouteSummary = true;
                    console.log('Summary should now be visible:', this.showRouteSummary);
                }, 100);
            } catch (error) {
                console.error('Route optimization failed:', error);
                this.optimizationError = error.message;
            } finally {
                this.loading = false;
            }
        },

        onDateChange(event) {
            const newDate = event.target.value;
            if (newDate) {
                this.setSelectedDate(newDate);
            }
        },

        // Add methods for date management
        getMinDate() {
            // Allow planning from today onwards
            return this.getTodayDate();
        },

        getMaxDate() {
            // Allow planning up to 30 days in advance
            const maxDate = new Date();
            maxDate.setDate(maxDate.getDate() + 30);
            return maxDate.toISOString().split('T')[0];
        },

        getDateStatusClass() {
            const status = this.dateStatus;
            const classes = {
                today: 'bg-green-100 text-green-800 border-green-200',
                past: 'bg-gray-100 text-gray-500 border-gray-200',
                future: 'bg-blue-100 text-blue-800 border-blue-200'
            };
            return classes[status] || classes.future;
        },

        getDateStatusText() {
            const status = this.dateStatus;
            const texts = {
                today: 'Today\'s Deliveries',
                past: 'Past Deliveries',
                future: 'Scheduled Deliveries'
            };
            return texts[status] || 'Deliveries';
        },

        // Keep all existing methods...
        get executiveSummary() {
            if (!this.optimizationResult) {
                console.log('No optimization result for executive summary');
                return null;
            }

            console.log('Generating executive summary...');
            const result = this.optimizationResult;
            const savings = result.savings || 0;

            const summary = {
                totalStops: result.total_orders || 0,
                totalDistance: `${result.total_distance || 0} km`,
                totalTime: this.formatTime(result.total_time || 0),
                savings: `${savings} km`,
                startTime: '08:00',
                firstDelivery: '09:30',
                lastDelivery: result.route_steps?.length ? result.route_steps[result.route_steps.length - 1]?.estimated_arrival : '16:45',
                returnTime: this.calculateReturnTime(result.total_time || 0),
                deliveryDate: this.formattedSelectedDate
            };

            console.log('Executive summary generated:', summary);
            return summary;
        },

        get priorityBreakdown() {
            const breakdown = {
                high: { count: 0, value: 0, colorClass: 'bg-red-500' },
                medium: { count: 0, value: 0, colorClass: 'bg-yellow-500' },
                low: { count: 0, value: 0, colorClass: 'bg-green-500' }
            };

            this.orders.forEach(order => {
                if (breakdown[order.priority]) {
                    breakdown[order.priority].count++;
                    breakdown[order.priority].value += order.total_amount;
                }
            });

            return Object.entries(breakdown).map(([level, data]) => ({
                level,
                ...data
            }));
        },

        get totalOrderValue() {
            return this.orders.reduce((sum, order) => sum + order.total_amount, 0);
        },

        toggleSummary() {
            this.showRouteSummary = !this.showRouteSummary;
            console.log('Summary toggled to:', this.showRouteSummary);
        },

        selectDriver(driver) {
            console.log('Driver selected:', driver);
            this.selectedDriver = driver;
        },

        focusOnOrder(orderId) {
            if (window.mapManager) {
                window.mapManager.focusOnOrder(orderId);
            }
        },

        resetOptimization() {
            console.log('Resetting optimization...');
            this.optimizationResult = null;
            this.optimizationError = null;
            this.loading = false;
            this.showRouteSummary = false;

            if (window.mapManager) {
                window.mapManager.clearRoute();
            }
        },

        refreshMap() {
            if (window.mapManager && this.mapInitialized) {
                window.mapManager.refreshMarkers();
            }
        },

        exportSummary() {
            if (!this.optimizationResult) {
                console.warn('No optimization result to export');
                return;
            }

            const summary = this.executiveSummary;
            const exportData = {
                optimization_date: new Date().toLocaleDateString(),
                delivery_date: this.selectedDate,
                driver: this.selectedDriver.full_name,
                vehicle: this.selectedDriver.vehicle_details,
                summary: {
                    totalStops: summary.totalStops,
                    totalDistance: summary.totalDistance,
                    totalTime: summary.totalTime,
                    savings: summary.savings,
                    timeline: {
                        startTime: summary.startTime,
                        firstDelivery: summary.firstDelivery,
                        lastDelivery: summary.lastDelivery,
                        returnTime: summary.returnTime
                    }
                },
                priority_breakdown: this.priorityBreakdown,
                route_details: this.optimizationResult.route_steps,
                orders: this.orders.map(order => ({
                    id: order.id,
                    client: order.client_name,
                    address: order.address,
                    value: order.total_amount,
                    priority: order.priority,
                    delivery_date: order.delivery_date
                }))
            };

            const dataStr = JSON.stringify(exportData, null, 2);
            const dataBlob = new Blob([dataStr], { type: 'application/json' });
            const url = URL.createObjectURL(dataBlob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `route-summary-${this.selectedDate}.json`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);

            console.log('Route summary exported for', this.selectedDate);
        },

        calculateReturnTime(totalMinutes) {
            const startTime = new Date();
            startTime.setHours(8, 0, 0, 0);
            const returnTime = new Date(startTime.getTime() + (totalMinutes * 60000));
            return returnTime.toLocaleTimeString('pl-PL', {
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        formatCurrency(amount) {
            return `zł${amount.toLocaleString('pl-PL')}`;
        },

        formatTime(minutes) {
            const hours = Math.floor(minutes / 60);
            const mins = minutes % 60;
            return hours > 0 ? `${hours}h ${mins}m` : `${mins}m`;
        },

        formatDistance(meters) {
            return meters >= 1000
                ? `${Math.round(meters / 1000)} km`
                : `${meters} m`;
        },

        debugSummaryState() {
            console.log('=== Summary Debug Info ===');
            console.log('showRouteSummary:', this.showRouteSummary);
            console.log('optimizationResult exists:', !!this.optimizationResult);
            console.log('executiveSummary:', this.executiveSummary);
            console.log('priorityBreakdown:', this.priorityBreakdown);
            console.log('selectedDate:', this.selectedDate);
            console.log('orders count:', this.orders.length);
            console.log('========================');
        }
    };
}

window.routeOptimizer = routeOptimizer;

document.addEventListener('DOMContentLoaded', function () {
    console.log('DOM loaded, RouteOptimizer ready for Alpine.js initialization');
});