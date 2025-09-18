class RouteOptimizerService {
    constructor(routeComponent) {
        this.routeComponent = routeComponent;
        this.vroomEndpoint = 'http://147.135.252.51:3000';
        this.serverEndpoint = window.location.origin;
        this.mockDelay = 0;
        this.debugMode = true;
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

    async optimizeRoutes(skipChecks = false) {
        if (!skipChecks) {
            if (this.routeComponent.orders.length === 0) {
                alert(`Brak dostępnych zamówień dla ${this.routeComponent.formattedSelectedDate}`);
                return;
            }

            if (!this.canOptimize()) {
                return;
            }

            this.routeComponent.loading = true;
            this.routeComponent.optimizationError = null;
        }

        try {
            const vroomResult = await this.callVroomAPI();
            const optimizationResult = this.processVroomResult(vroomResult);

            this.routeComponent.optimizationResult = optimizationResult;

            await this.saveOptimizationToServer();

            if (window.mapManager) {
                setTimeout(() => {
                    window.mapManager.visualizeOptimizedRoute();
                }, 100);
            }

        } catch (error) {
            this.routeComponent.optimizationError = error.message;
            throw error;
        } finally {
            if (!skipChecks) {
                this.routeComponent.loading = false;
            }
        }
    }

    async callVroomAPI() {
        const vroomPayload = this.buildVroomPayload();

        try {
            const response = await fetch(`${this.vroomEndpoint}/`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(vroomPayload)
            });

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`Błąd VROOM API: ${response.status} ${response.statusText} - ${errorText}`);
            }

            const result = await response.json();
            return result;

        } catch (error) {
            if (error.name === 'TypeError' && error.message.includes('fetch')) {
                throw new Error('Nie można połączyć się z serwerem VROOM. Sprawdź, czy usługa działa.');
            }
            throw error;
        }
    }

    buildVroomPayload() {
        const depotCoords = [21.0122, 52.2297];

        const vehicle = {
            id: this.routeComponent.selectedDriver.id,
            profile: "driving-car",
            start: depotCoords,
            end: depotCoords,
            capacity: [99999]
        };

        const validOrders = this.routeComponent.orders.filter(order =>
            order.coordinates &&
            Array.isArray(order.coordinates) &&
            order.coordinates.length === 2 &&
            !isNaN(order.coordinates[0]) &&
            !isNaN(order.coordinates[1])
        );

        if (validOrders.length === 0) {
            throw new Error('Żadne zamówienia nie mają prawidłowych współrzędnych do optymalizacji');
        }

        const jobs = validOrders.map((order) => {
            return {
                id: order.id,
                location: [order.coordinates[1], order.coordinates[0]],
                service: 600,
                amount: [1],
                priority: this.getPriorityValue(order.priority)
            };
        });

        const payload = {
            vehicles: [vehicle],
            jobs: jobs,
            options: {
                g: true,
                c: true,
                t: 3
            }
        };

        return payload;
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

            const token = localStorage.getItem('auth_token') ||
                document.querySelector('meta[name="token"]')?.content;

            const response = await fetch(`${this.serverEndpoint}/api/route-data/save-optimization`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': token ? `Bearer ${token}` : '',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(optimizationData)
            });

            if (!response.ok) {
                const responseText = await response.text();
                throw new Error(`Nie udało się zapisać optymalizacji: ${response.statusText}`);
            }

        } catch (error) {
        }
    }

    processVroomResult(vroomResult) {
        if (!vroomResult.routes || vroomResult.routes.length === 0) {
            throw new Error('Brak tras zwróconych z VROOM API');
        }

        const route = vroomResult.routes[0];
        const steps = route.steps || [];

        const totalDistance = Math.round((route.distance || 0) / 1000);
        const totalTime = Math.round((route.duration || 0) / 60);

        const routeSteps = this.processRouteSteps(steps);

        const unoptimizedDistance = this.calculateUnoptimizedDistance();
        const savings = Math.max(0, unoptimizedDistance - totalDistance);

        this.updateOrderSequence(steps);

        const result = {
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

        return result;
    }

    processRouteSteps(steps) {
        const routeSteps = [];
        let cumulativeDistance = 0;
        let cumulativeTime = 0;

        steps.forEach((step) => {
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
                        description: `Dostawa do ${order.client_name}`,
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
        const jobSequence = [];
        steps.forEach(step => {
            if (step.type === 'job') {
                jobSequence.push(step.job);
            }
        });

        const orderedOrders = [];
        const remainingOrders = [...this.routeComponent.orders];

        jobSequence.forEach(jobId => {
            const orderIndex = remainingOrders.findIndex(order => order.id === jobId);
            if (orderIndex !== -1) {
                orderedOrders.push(remainingOrders.splice(orderIndex, 1)[0]);
            }
        });

        orderedOrders.push(...remainingOrders);

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
        this.routeComponent.optimizationError = {
            message: error.message || 'Optymalizacja nie powiodła się',
            timestamp: new Date().toISOString(),
            canRetry: true
        };
        this.routeComponent.optimizationResult = null;
    }

    calculateDistance(coords1, coords2) {
        const R = 6371;
        const dLat = this.toRad(coords2[0] - coords1[0]);
        const dLon = this.toRad(coords2[1] - coords1[1]);
        const lat1 = this.toRad(coords1[0]);
        const lat2 = this.toRad(coords2[0]);

        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.sin(dLon / 2) * Math.sin(dLon / 2) * Math.cos(lat1) * Math.cos(lat2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    toRad(deg) {
        return deg * (Math.PI / 180);
    }
}

window.RouteOptimizerService = RouteOptimizerService;