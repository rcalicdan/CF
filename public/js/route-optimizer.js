class RouteOptimizerData {
    constructor() {
        this.dataService = new RouteDataService();

        this.drivers = [];
        this.allOrders = [];

        this.selectedDate = this.getTodayDate();
        this.selectedDriver = null;
        this.orders = [];

        this.loading = false; 
        this.dataLoaded = false;
        this.loadingError = null;

        this.optimizationResult = null;
        this.optimizationError = null;
        this.showRouteSummary = false;
        this.map = null;
        this.markers = [];
        this.routingControl = null;
        this.mapInitialized = false;
    }

    /**
     * Load initial data from API (called by Alpine component)
     */
    async loadInitialData() {
        try {
            this.loading = true;
            this.loadingError = null; 

            this.drivers = await this.dataService.getDrivers();

            const endDate = new Date();
            endDate.setDate(endDate.getDate() + 30);

            this.allOrders = await this.dataService.getAllOrdersForDateRange(
                this.getTodayDate(),
                endDate.toISOString().split('T')[0]
            );

            this.dataLoaded = true;

            await new Promise(resolve => setTimeout(resolve, 100));
        } catch (error) {
            this.loadingError = 'Nie udało się załadować danych z serwera: ' + error.message;
        } finally {
            this.loading = false;
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

            return orders;

        } catch (error) {
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
            throw error;
        }
    }

    getTodayDate() {
        const today = new Date();
        return today.toISOString().split('T')[0];
    }

    setSelectedDate(date) {
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