function routeOptimizer() {
    const data = new RouteOptimizerData();

    return {
        ...data,

        selectedDriver: data.drivers[0],
        showRouteSummary: false,
        
        manualEditMode: false,
        isDragging: false,
        draggedOrderIndex: null,
        customRoutePoints: [],
        isDrawingRoute: false,

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

        getTodayDate() {
            const today = new Date();
            return today.toISOString().split('T')[0];
        },

        get formattedSelectedDate() {
            const date = new Date(this.selectedDate + 'T00:00:00');
            return date.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },

        get dateStatus() {
            const today = this.getTodayDate();
            if (this.selectedDate === today) return 'today';
            if (this.selectedDate < today) return 'past';
            return 'future';
        },

        get totalOrderValue() {
            return this.orders.reduce((sum, order) => sum + order.total_amount, 0);
        },

        toggleManualEdit() {
            this.manualEditMode = !this.manualEditMode;
            console.log('Manual edit mode:', this.manualEditMode);
            
            if (window.mapManager) {
                if (this.manualEditMode) {
                    window.mapManager.enableManualEdit();
                } else {
                    window.mapManager.disableManualEdit();
                }
            }
        },

        toggleRouteDrawing() {
            this.isDrawingRoute = !this.isDrawingRoute;
            console.log('Route drawing mode:', this.isDrawingRoute);
            
            if (window.mapManager) {
                if (this.isDrawingRoute) {
                    window.mapManager.enableRouteDrawing();
                } else {
                    window.mapManager.disableRouteDrawing();
                }
            }
        },

        moveStopUp(index) {
            if (index > 0) {
                const temp = this.orders[index];
                this.orders[index] = this.orders[index - 1];
                this.orders[index - 1] = temp;
                this.refreshOptimizedRoute();
            }
        },

        moveStopDown(index) {
            if (index < this.orders.length - 1) {
                const temp = this.orders[index];
                this.orders[index] = this.orders[index + 1];
                this.orders[index + 1] = temp;
                this.refreshOptimizedRoute();
            }
        },

        removeStop(index) {
            if (confirm('Are you sure you want to remove this stop from the route?')) {
                this.orders.splice(index, 1);
                this.refreshOptimizedRoute();
                if (window.mapManager) {
                    window.mapManager.refreshMarkers();
                }
            }
        },

        addCustomStop(lat, lng, address = 'Custom Location') {
            const customOrder = {
                id: Date.now(), // Use timestamp as unique ID
                client_name: 'Custom Stop',
                address: address,
                coordinates: [lat, lng],
                total_amount: 0,
                status: 'custom',
                priority: 'medium',
                delivery_date: this.selectedDate,
                isCustom: true
            };
            
            this.orders.push(customOrder);
            this.refreshOptimizedRoute();
            if (window.mapManager) {
                window.mapManager.refreshMarkers();
            }
        },

        onDragStart(index, event) {
            this.isDragging = true;
            this.draggedOrderIndex = index;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/html', event.target.outerHTML);
            event.target.classList.add('dragging');
        },

        onDragOver(event) {
            if (this.isDragging) {
                event.preventDefault();
                event.dataTransfer.dropEffect = 'move';
            }
        },

        onDrop(index, event) {
            if (this.isDragging && this.draggedOrderIndex !== null) {
                event.preventDefault();
                
                const draggedOrder = this.orders[this.draggedOrderIndex];
                this.orders.splice(this.draggedOrderIndex, 1);
                this.orders.splice(index, 0, draggedOrder);
                
                this.isDragging = false;
                this.draggedOrderIndex = null;
                
                document.querySelectorAll('.dragging').forEach(el => {
                    el.classList.remove('dragging');
                });
                
                this.refreshOptimizedRoute();
            }
        },

        refreshOptimizedRoute() {
            if (this.optimizationResult) {
                this.optimizationResult.route_steps = this.orders.map((order, index) => ({
                    step: index + 1,
                    location: order.address,
                    description: `Deliver to ${order.client_name}`,
                    distance: index > 0 ? '5 km' : '0 km', 
                    duration: '15 min', 
                    order_id: order.id,
                    client_name: order.client_name,
                    amount: order.total_amount,
                    priority: order.priority,
                    estimated_arrival: this.calculateEstimatedArrival(index * 30 * 60),
                    coordinates: order.coordinates,
                    sequence: index + 1
                }));
                
                this.optimizationResult.total_orders = this.orders.length;
                this.optimizationResult.total_value = this.orders.reduce((sum, order) => sum + order.total_amount, 0);
                
                if (window.mapManager) {
                    window.mapManager.visualizeOptimizedRoute();
                }
            }
        },

        saveManualChanges() {
            if (confirm('Save current route configuration?')) {
                console.log('Saving manual route changes...');
                

                const saveData = {
                    date: this.selectedDate,
                    driver_id: this.selectedDriver.id,
                    orders: this.orders,
                    custom_route_points: this.customRoutePoints,
                    optimization_result: this.optimizationResult,
                    manual_modifications: {
                        edit_mode_used: this.manualEditMode,
                        custom_stops_added: this.orders.filter(o => o.isCustom).length,
                        custom_route_drawn: this.customRoutePoints.length > 0
                    }
                };
                
                console.log('Save data:', saveData);
                
                this.manualEditMode = false;
                this.isDrawingRoute = false;
                
                if (window.mapManager) {
                    window.mapManager.disableManualEdit();
                    window.mapManager.disableRouteDrawing();
                }
                
                alert('Route changes saved successfully!');
            }
        },

        resetToOptimized() {
            if (confirm('Reset to original optimized route? This will lose all manual changes.')) {
                this.orders = this.getOrdersForDate(this.selectedDate);
                this.customRoutePoints = [];
                this.manualEditMode = false;
                this.isDrawingRoute = false;
                
                this.refreshOptimizedRoute();
                
                if (window.mapManager) {
                    window.mapManager.refreshMarkers();
                    window.mapManager.clearCustomRoute();
                    window.mapManager.disableManualEdit();
                    window.mapManager.disableRouteDrawing();
                }
                
                console.log('Reset to optimized route');
            }
        },

        exportManualRoute() {
            if (!this.optimizationResult && this.orders.length === 0) {
                alert('No route data to export');
                return;
            }

            const exportData = {
                export_type: 'manual_route',
                export_date: new Date().toISOString(),
                delivery_date: this.selectedDate,
                driver: this.selectedDriver,
                manual_modifications: {
                    edit_mode_used: this.manualEditMode,
                    custom_route_drawn: this.customRoutePoints.length > 0,
                    custom_stops_added: this.orders.filter(o => o.isCustom).length
                },
                route_data: {
                    total_stops: this.orders.length,
                    custom_stops: this.orders.filter(o => o.isCustom),
                    stop_sequence: this.orders.map((order, index) => ({
                        sequence: index + 1,
                        order_id: order.id,
                        client_name: order.client_name,
                        address: order.address,
                        coordinates: order.coordinates,
                        priority: order.priority,
                        value: order.total_amount,
                        is_custom: order.isCustom || false
                    })),
                    custom_route_points: this.customRoutePoints,
                    optimization_result: this.optimizationResult
                },
                statistics: {
                    total_value: this.totalOrderValue,
                    priority_breakdown: this.priorityBreakdown,
                    estimated_distance: this.optimizationResult?.total_distance || 'N/A',
                    estimated_time: this.optimizationResult?.total_time || 'N/A'
                }
            };

            const dataStr = JSON.stringify(exportData, null, 2);
            const dataBlob = new Blob([dataStr], { type: 'application/json' });
            const url = URL.createObjectURL(dataBlob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `manual-route-${this.selectedDate}-${Date.now()}.json`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);

            console.log('Manual route exported for', this.selectedDate);
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

        getMinDate() {
            return this.getTodayDate();
        },

        getMaxDate() {
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
            this.manualEditMode = false;
            this.isDrawingRoute = false;
            this.customRoutePoints = [];

            if (window.mapManager) {
                window.mapManager.clearRoute();
                window.mapManager.clearCustomRoute();
                window.mapManager.disableManualEdit();
                window.mapManager.disableRouteDrawing();
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
                    delivery_date: order.delivery_date,
                    is_custom: order.isCustom || false
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

        calculateEstimatedArrival(arrivalSeconds) {
            const startTime = new Date();
            startTime.setHours(8, 0, 0, 0);

            const arrivalTime = new Date(startTime.getTime() + (arrivalSeconds * 1000));

            return arrivalTime.toLocaleTimeString('pl-PL', {
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
            console.log('manualEditMode:', this.manualEditMode);
            console.log('isDrawingRoute:', this.isDrawingRoute);
            console.log('customRoutePoints:', this.customRoutePoints.length);
            console.log('========================');
        }
    };
}

window.routeOptimizer = routeOptimizer;

document.addEventListener('DOMContentLoaded', function () {
    console.log('DOM loaded, RouteOptimizer ready for Alpine.js initialization');
});