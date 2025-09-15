class RouteOptimizerData {
    constructor() {
        this.dataService = new RouteDataService();

        this.drivers = [];
        this.allOrders = [];

        this.selectedDate = this.getTodayDate();
        this.selectedDriver = null;
        this.orders = [];

        this.loading = false; // Start as false - Alpine component will manage this
        this.dataLoaded = false;
        this.loadingError = null;

        this.optimizationResult = null;
        this.optimizationError = null;
        this.showRouteSummary = false;
        this.map = null;
        this.markers = [];
        this.routingControl = null;
        this.mapInitialized = false;

        // Remove automatic loading - let Alpine component handle it
        // this.loadInitialData();
    }

    /**
     * Load initial data from API (called by Alpine component)
     */
    async loadInitialData() {
        console.log("📥 Starting loadInitialData...");

        try {
            this.loading = true;
            this.loadingError = null;

            console.log('Loading initial data from server...');

            console.log('Loading drivers...');
            this.drivers = await this.dataService.getDrivers();
            console.log(`✅ Loaded ${this.drivers.length} drivers:`, this.drivers);

            console.log('Loading orders...');
            const endDate = new Date();
            endDate.setDate(endDate.getDate() + 30);

            this.allOrders = await this.dataService.getAllOrdersForDateRange(
                this.getTodayDate(),
                endDate.toISOString().split('T')[0]
            );

            console.log(`✅ Loaded ${this.allOrders.length} orders:`, this.allOrders);

            this.dataLoaded = true;
            console.log('✅ Data loading completed successfully');

            // Add a small delay to ensure Alpine detects the change
            await new Promise(resolve => setTimeout(resolve, 100));

        } catch (error) {
            console.error('❌ Failed to load initial data:', error);
            this.loadingError = 'Failed to load data from server: ' + error.message;
        } finally {
            console.log('🔄 Setting loading to false on data instance');
            this.loading = false;
            console.log('Final loading state:', this.loading);
        }
    }

    /**
     * Refresh orders for current driver and date from API
     */
    async refreshOrdersFromAPI(forceRefresh = false) {
        if (!this.selectedDriver || !this.selectedDate) {
            return;
        }

        try {
            console.log(`Refreshing orders for driver ${this.selectedDriver.id} on ${this.selectedDate}`);

            if (forceRefresh) {
                this.dataService.clearCacheByPattern(`orders_${this.selectedDriver.id}_${this.selectedDate}`);
            }

            const orders = await this.dataService.getOrdersForDriverAndDate(
                this.selectedDriver.id,
                this.selectedDate
            );

            this.allOrders = this.allOrders.filter(
                order => !(order.driver_id === this.selectedDriver.id && order.delivery_date === this.selectedDate)
            );

            this.allOrders.push(...orders);

            console.log(`✅ Refreshed ${orders.length} orders from API`);

            return orders;

        } catch (error) {
            console.error('❌ Failed to refresh orders:', error);
            throw error;
        }
    }

    /**
     * Get orders for driver and date from local cache
     */
    getOrdersForDriverAndDate(driverId, date) {
        if (!driverId || !date) {
            return [];
        }

        return this.allOrders.filter(
            (order) =>
                order.driver_id === driverId && order.delivery_date === date
        );
    }

    /**
     * Get statistics
     */
    async getStatistics(driverId = null, date = null) {
        try {
            return await this.dataService.getRouteStatistics(driverId, date);
        } catch (error) {
            console.error('Failed to get statistics:', error);
            throw error;
        }
    }

    /**
     * Trigger geocoding process
     */
    async triggerGeocoding() {
        try {
            return await this.dataService.triggerGeocoding();
        } catch (error) {
            console.error('Failed to trigger geocoding:', error);
            throw error;
        }
    }

    getTodayDate() {
        const today = new Date();
        return today.toISOString().split('T')[0];
    }

    setSelectedDate(date) {
        console.log('Setting selected date to:', date);
        this.selectedDate = date;

        this.optimizationResult = null;
        this.optimizationError = null;
        this.showRouteSummary = false;

        if (this.mapInitialized && window.mapManager) {
            window.mapManager.refreshMarkers();
            window.mapManager.clearRoute();
        }
    }

    get totalOrders() {
        return this.orders.length;
    }

    get totalValue() {
        return this.orders.reduce((sum, order) => sum + order.total_amount, 0);
    }

    get pendingOrders() {
        return this.orders.filter(order => order.status === 'pending');
    }

    get highPriorityOrders() {
        return this.orders.filter(order => order.priority === 'high');
    }

    get mediumPriorityOrders() {
        return this.orders.filter(order => order.priority === 'medium');
    }

    get lowPriorityOrders() {
        return this.orders.filter(order => order.priority === 'low');
    }
}