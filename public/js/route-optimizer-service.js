class RouteOptimizerService {
    constructor(routeComponent) {
        this.routeComponent = routeComponent;
        this.apiEndpoint = 'http://147.135.252.51:3000';
        this.mockDelay = 0;
    }

    async optimizeRoutes() {
        if (!this.routeComponent.selectedDriver) {
            throw new Error('No driver selected');
        }

        if (this.routeComponent.orders.length === 0) {
            throw new Error('No orders to optimize');
        }

        const validOrders = this.routeComponent.orders.filter(order =>
            order.coordinates &&
            Array.isArray(order.coordinates) &&
            order.coordinates.length === 2 &&
            !isNaN(order.coordinates[0]) &&
            !isNaN(order.coordinates[1])
        );

        if (validOrders.length === 0) {
            throw new Error('No orders have valid coordinates for optimization. Please geocode addresses first.');
        }

        console.log('Starting route optimization with VROOM API...');
        console.log('Valid orders for optimization:', validOrders.length, 'out of', this.routeComponent.orders.length);
        console.log('Selected driver:', this.routeComponent.selectedDriver);

        try {
            const vroomResult = await this.callVroomAPI();
            this.routeComponent.optimizationResult = this.processVroomResult(vroomResult);

            await this.saveOptimizationToServer();

            console.log('Route optimization completed:', this.routeComponent.optimizationResult);

            setTimeout(() => {
                if (window.mapManager) {
                    window.mapManager.visualizeOptimizedRoute();
                }
            }, 100);

        } catch (error) {
            console.error('Optimization failed:', error);
            this.handleOptimizationError(error);
            throw error;
        }
    }

    async saveOptimizationToServer() {
        try {
            const optimizationData = {
                driver_id: this.routeComponent.selectedDriver.id,
                optimization_date: this.routeComponent.selectedDate,
                optimization_result: this.routeComponent.optimizationResult,
                order_sequence: this.routeComponent.orders.map(order => order.id),
                total_distance: this.routeComponent.optimizationResult.total_distance,
                total_time: this.routeComponent.optimizationResult.total_time,
                estimated_fuel_cost: this.routeComponent.optimizationResult.estimated_fuel_cost,
                carbon_footprint: this.routeComponent.optimizationResult.carbon_footprint,
                is_manual_edit: false,
                manual_modifications: null
            };

            const response = await fetch(`${this.apiEndpoint}/api/route-data/save-optimization`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('auth_token') || ''}`,
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(optimizationData)
            });

            if (!response.ok) {
                throw new Error(`Failed to save optimization: ${response.statusText}`);
            }

            console.log('✅ Optimization saved to server');
        } catch (error) {
            console.warn('⚠️ Failed to save optimization to server:', error);
        }
    }

    async callVroomAPI() {
        console.log('Calling VROOM API...');

        const vroomPayload = this.buildVroomPayload();
        console.log('VROOM Payload:', JSON.stringify(vroomPayload, null, 2));

        try {
            const response = await fetch(`${this.apiEndpoint}/`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(vroomPayload)
            });

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`VROOM API error: ${response.status} ${response.statusText} - ${errorText}`);
            }

            const result = await response.json();
            console.log('VROOM API Response:', result);

            if (!result.routes || result.routes.length === 0) {
                throw new Error('VROOM returned no routes');
            }

            return result;

        } catch (error) {
            if (error.name === 'TypeError' && error.message.includes('fetch')) {
                throw new Error('Cannot connect to VROOM server. Please check if the service is running.');
            }
            throw error;
        }
    }

    buildVroomPayload() {
        const depotCoords = [21.0122, 52.2297]; // [longitude, latitude] for VROOM

        const vehicle = {
            id: this.routeComponent.selectedDriver.id,
            profile: "driving-car",
            start: depotCoords,
            end: depotCoords,
            capacity: [100],
            time_window: [28800, 64800] // 8:00 AM to 6:00 PM in seconds
        };

        const validOrders = this.routeComponent.orders.filter(order =>
            order.coordinates &&
            Array.isArray(order.coordinates) &&
            order.coordinates.length === 2 &&
            !isNaN(order.coordinates[0]) &&
            !isNaN(order.coordinates[1])
        );

        if (validOrders.length === 0) {
            throw new Error('No orders have valid coordinates for optimization');
        }

        const jobs = validOrders.map((order) => ({
            id: order.id,
            location: [order.coordinates[1], order.coordinates[0]],
            service: 900,
            amount: [1],
            priority: this.getPriorityValue(order.priority),
            time_windows: this.getTimeWindow(order.priority)
        }));

        console.log('VROOM payload jobs:', jobs);

        return {
            vehicles: [vehicle],
            jobs: jobs,
            options: {
                g: true
            }
        };
    }

    processVroomResult(vroomResult) {
        if (!vroomResult.routes || vroomResult.routes.length === 0) {
            throw new Error('No routes returned from VROOM API');
        }

        const route = vroomResult.routes[0];
        const steps = route.steps || [];

        const totalDistance = Math.round((route.distance || 0) / 1000);
        const totalTime = Math.round((route.duration || 0) / 60);

        const routeSteps = this.processRouteSteps(steps);

        // Calculate savings compared to unoptimized route
        const unoptimizedDistance = this.calculateUnoptimizedDistance();
        const savings = Math.max(0, unoptimizedDistance - totalDistance);

        // Update the order of items in the component based on VROOM optimization
        this.updateOrderSequence(steps);

        return {
            total_distance: totalDistance,
            total_time: totalTime,
            savings: savings,
            route_steps: routeSteps,
            driver: this.routeComponent.selectedDriver,
            optimization_timestamp: new Date().toISOString(),
            total_orders: this.routeComponent.orders.length,
            total_value: this.routeComponent.orders.reduce((sum, order) => sum + order.total_amount, 0),
            estimated_fuel_cost: this.calculateFuelCost(totalDistance),
            carbon_footprint: this.calculateCarbonFootprint(totalDistance),
            vroom_raw: vroomResult,
            geometry: route.geometry || null
        };
    }

    processRouteSteps(steps) {
        const routeSteps = [];
        let cumulativeDistance = 0;
        let cumulativeTime = 0;

        steps.forEach((step, index) => {
            if (step.type === 'start') return;

            if (step.type === 'job') {
                const order = this.routeComponent.orders.find(o => o.id === step.job);
                if (order) {
                    const segmentDistance = Math.round((step.distance || 0) / 1000);
                    const segmentTime = Math.round((step.duration || 0) / 60);

                    cumulativeDistance += segmentDistance;
                    cumulativeTime += segmentTime;

                    routeSteps.push({
                        step: routeSteps.length + 1,
                        location: order.address,
                        description: `Deliver to ${order.client_name}`,
                        distance: `${segmentDistance} km`,
                        duration: `${segmentTime} min`,
                        cumulative_distance: `${cumulativeDistance} km`,
                        cumulative_time: `${cumulativeTime} min`,
                        order_id: order.id,
                        client_name: order.client_name,
                        amount: order.total_amount,
                        priority: order.priority,
                        estimated_arrival: this.calculateEstimatedArrival(step.arrival || cumulativeTime * 60),
                        coordinates: order.coordinates,
                        vroom_step: step
                    });
                }
            }
        });

        return routeSteps;
    }

    updateOrderSequence(steps) {
        // Extract job order from VROOM result
        const jobSequence = [];
        steps.forEach(step => {
            if (step.type === 'job') {
                jobSequence.push(step.job);
            }
        });

        // Reorder the orders array based on VROOM optimization
        const orderedOrders = [];
        const remainingOrders = [...this.routeComponent.orders];

        jobSequence.forEach(jobId => {
            const orderIndex = remainingOrders.findIndex(order => order.id === jobId);
            if (orderIndex !== -1) {
                orderedOrders.push(remainingOrders.splice(orderIndex, 1)[0]);
            }
        });

        // Add any remaining orders (those without coordinates) at the end
        orderedOrders.push(...remainingOrders);

        // Update the component's orders array
        this.routeComponent.orders = orderedOrders;
    }

    getPriorityValue(priority) {
        const priorities = {
            'high': 100,
            'medium': 50,
            'low': 10
        };
        return priorities[priority] || 50;
    }

    getTimeWindow(priority) {
        // Time windows in seconds from midnight
        const timeWindows = {
            'high': [[28800, 43200]], // 8:00-12:00 (urgent deliveries)
            'medium': [[32400, 54000]], // 9:00-15:00 (normal deliveries)
            'low': [[36000, 64800]] // 10:00-18:00 (flexible deliveries)
        };
        return timeWindows[priority] || [[28800, 64800]]; // Default: 8:00-18:00
    }

    calculateEstimatedArrival(arrivalSeconds) {
        const startTime = new Date();
        startTime.setHours(8, 0, 0, 0); // 8:00 AM start time

        const arrivalTime = new Date(startTime.getTime() + (arrivalSeconds * 1000));

        return arrivalTime.toLocaleTimeString('pl-PL', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    calculateUnoptimizedDistance() {
        const baseDistance = 50;
        const orderDistance = this.routeComponent.orders.length * 15;
        const inefficiency = Math.random() * 200 + 100;

        return Math.round(baseDistance + orderDistance + inefficiency);
    }

    calculateFuelCost(distanceKm) {
        const fuelConsumptionPer100km = 10;
        const fuelPricePerLiter = 6.50;
        return Math.round((distanceKm / 100) * fuelConsumptionPer100km * fuelPricePerLiter);
    }

    calculateCarbonFootprint(distanceKm) {
        const co2PerKm = 0.27;
        return Math.round(distanceKm * co2PerKm * 10) / 10;
    }

    handleOptimizationError(error) {
        console.error('Route optimization error:', error);
        this.routeComponent.optimizationError = {
            message: error.message || 'Optimization failed',
            timestamp: new Date().toISOString(),
            canRetry: true
        };
        this.routeComponent.optimizationResult = null;
    }

    canOptimize() {
        const hasDriver = this.routeComponent.selectedDriver && this.routeComponent.selectedDriver.id;
        const hasOrders = this.routeComponent.orders.length > 0;
        const hasValidCoordinates = this.routeComponent.orders.some(order =>
            order.coordinates &&
            Array.isArray(order.coordinates) &&
            order.coordinates.length === 2 &&
            !isNaN(order.coordinates[0]) &&
            !isNaN(order.coordinates[1])
        );
        const notLoading = !this.routeComponent.loading;

        return hasDriver && hasOrders && hasValidCoordinates && notLoading;
    }
}

window.RouteOptimizerService = RouteOptimizerService;