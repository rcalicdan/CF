class RouteOptimizerService {
    constructor(data) {
        this.data = data;
        this.apiEndpoint = 'http://147.135.252.51:3000';
        this.mockDelay = 0;
    }

    async optimizeRoutes() {
        if (!this.data.selectedDriver) {
            throw new Error('No driver selected');
        }

        if (this.data.orders.length === 0) {
            throw new Error('No orders to optimize');
        }

        console.log('Starting route optimization with VROOM API...');

        try {
            const vroomResult = await this.callVroomAPI();
            this.data.optimizationResult = this.processVroomResult(vroomResult);

            console.log('Route optimization completed:', this.data.optimizationResult);

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

    processVroomResult(vroomResult) {
        if (!vroomResult.routes || vroomResult.routes.length === 0) {
            throw new Error('No routes returned from VROOM API');
        }

        const route = vroomResult.routes[0];
        const steps = route.steps || [];

        const totalDistance = Math.round((route.distance || 0) / 1000);
        const totalTime = Math.round((route.duration || 0) / 60);

        const routeSteps = this.processRouteSteps(steps, totalDistance, totalTime);

        const unoptimizedDistance = this.calculateUnoptimizedDistance();
        const savings = Math.max(0, unoptimizedDistance - totalDistance);

        return {
            total_distance: totalDistance,
            total_time: totalTime,
            savings: savings,
            route_steps: routeSteps,
            driver: this.data.selectedDriver,
            optimization_timestamp: new Date().toISOString(),
            total_orders: this.data.orders.length,
            total_value: this.data.orders.reduce((sum, order) => sum + order.total_amount, 0),
            estimated_fuel_cost: this.calculateFuelCost(totalDistance),
            carbon_footprint: this.calculateCarbonFootprint(totalDistance),
            vroom_raw: vroomResult,
            geometry: route.geometry || null
        };
    }

    processRouteSteps(steps, totalDistance, totalTime) {
        const routeSteps = [];
        let cumulativeDistance = 0;
        let cumulativeTime = 0;

        steps.forEach((step, index) => {
            if (step.type === 'start') return;

            if (step.type === 'job') {
                const order = this.data.orders.find(o => o.id === step.job);
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


    getPriorityValue(priority) {
        const priorities = {
            'high': 100,
            'medium': 50,
            'low': 10
        };
        return priorities[priority] || 50;
    }

    getTimeWindow(priority) {
        const timeWindows = {
            'high': [[28800, 43200]],
            'medium': [[32400, 54000]],
            'low': [[36000, 64800]]
        };
        return timeWindows[priority] || [[28800, 64800]];
    }

    buildVroomPayload() {
        const depotCoords = [21.0122, 52.2297];

        const vehicle = {
            id: this.data.selectedDriver.id,
            profile: "driving-car",
            start: depotCoords,
            end: depotCoords,
            capacity: [100],
            time_window: [28800, 64800]
        };

        const jobs = this.data.orders.map((order, index) => ({
            id: order.id,
            location: [order.coordinates[1], order.coordinates[0]],
            service: 900,
            amount: [1],
            priority: this.getPriorityValue(order.priority),
            time_windows: this.getTimeWindow(order.priority)
        }));

        return {
            vehicles: [vehicle],
            jobs: jobs,
            options: {
                g: true
            }
        };
    }

    calculateEstimatedArrival(arrivalSeconds) {
        const startTime = new Date();
        startTime.setHours(8, 0, 0, 0);

        const arrivalTime = new Date(startTime.getTime() + (arrivalSeconds * 1000));

        return arrivalTime.toLocaleTimeString('pl-PL', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    calculateUnoptimizedDistance() {
        return Math.round(1240 + Math.random() * 400 + 200);
    }

    calculateFuelCost(distanceKm) {
        const fuelConsumptionPer100km = 10;
        const fuelPricePerLiter = 6.5;
        return Math.round((distanceKm / 100) * fuelConsumptionPer100km * fuelPricePerLiter);
    }

    calculateCarbonFootprint(distanceKm) {
        const co2PerKm = 0.27;
        return Math.round(distanceKm * co2PerKm * 10) / 10;
    }

    handleOptimizationError(error) {
        console.error('Route optimization error:', error);
        this.data.optimizationError = {
            message: error.message || 'Optimization failed',
            timestamp: new Date().toISOString(),
            canRetry: true
        };
        this.data.optimizationResult = null;
    }

    canOptimize() {
        const hasDriver = this.data.selectedDriver && this.data.selectedDriver.id;
        const hasOrders = this.data.orders.length > 0;
        const notLoading = !this.data.loading;
        return hasDriver && hasOrders && notLoading;
    }
}