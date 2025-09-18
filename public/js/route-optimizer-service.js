class RouteOptimizerService {
    constructor(routeComponent) {
        this.routeComponent = routeComponent;
        this.vroomEndpoint = 'https://147.135.252.51:3000';
        this.serverEndpoint = window.location.origin;
        this.mockDelay = 0;
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

        return hasDriver && hasOrders && hasValidCoordinates;
    }

    async optimizeRoutes(skipChecks = false) {
        console.log('Starting route optimization:', {
            skipChecks: skipChecks,
            canOptimize: this.canOptimize(),
            ordersCount: this.routeComponent.orders.length,
            selectedDriver: this.routeComponent.selectedDriver?.id,
            selectedDate: this.routeComponent.selectedDate
        });

        if (!skipChecks) {
            if (this.routeComponent.orders.length === 0) {
                console.warn('No orders available for optimization');
                alert(`Brak dostępnych zamówień dla ${this.routeComponent.formattedSelectedDate}`);
                return;
            }

            if (!this.canOptimize()) {
                console.warn('Cannot optimize - validation failed');
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

            console.log('Route optimization completed successfully');

        } catch (error) {
            console.error('Route optimization failed:', {
                error: error,
                message: error.message,
                name: error.name,
                stack: error.stack
            });
            
            this.routeComponent.optimizationError = error.message;
            throw error;
        } finally {
            if (!skipChecks) {
                this.routeComponent.loading = false;
            }
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

            // Log the data being sent
            console.log('Save Optimization Request:', {
                endpoint: `${this.serverEndpoint}/api/route-data/save-optimization`,
                data: optimizationData
            });

            const authToken = localStorage.getItem('auth_token');
            const metaToken = document.querySelector('meta[name="token"]')?.content;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            const token = localStorage.getItem('auth_token') ||
                document.querySelector('meta[name="token"]')?.content;

            console.log('Save Optimization Auth Info:', {
                hasAuthToken: !!authToken,
                hasMetaToken: !!metaToken,
                hasCsrfToken: !!csrfToken,
                usingToken: !!token
            });

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

            // Log response details
            console.log('Save Optimization Response:', {
                status: response.status,
                statusText: response.statusText,
                ok: response.ok,
                url: response.url,
                headers: Object.fromEntries(response.headers.entries())
            });

            let responseBody;
            let responseText;
            try {
                responseText = await response.clone().text();
                console.log('Save Optimization Response Text:', responseText);

                if (responseText) {
                    try {
                        responseBody = JSON.parse(responseText);
                        console.log('Save Optimization Response JSON:', responseBody);
                    } catch (parseError) {
                        console.error('Failed to parse response JSON:', parseError);
                    }
                }
            } catch (bodyReadError) {
                console.error('Failed to read response body:', bodyReadError);
            }

            if (!response.ok) {
                console.error('Save Optimization Error:', {
                    status: response.status,
                    statusText: response.statusText,
                    responseText: responseText,
                    responseBody: responseBody
                });
                throw new Error(`Nie udało się zapisać optymalizacji: ${response.statusText}`);
            }

            console.log('Save Optimization Success');

        } catch (error) {
            console.error('Save Optimization Failed:', {
                error: error,
                message: error.message,
                name: error.name,
                stack: error.stack
            });
            
            if (error.name === 'TypeError' && error.message.includes('fetch')) {
                console.error('Network error during save optimization');
                // Ignored
            }
        }
    }

    async callVroomAPI() {
        const vroomPayload = this.buildVroomPayload();
        
        // Log the payload being sent
        console.log('VROOM API Request:', {
            endpoint: this.vroomEndpoint,
            payload: vroomPayload,
            payloadSize: JSON.stringify(vroomPayload).length
        });

        try {
            const response = await fetch(`${this.vroomEndpoint}/`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(vroomPayload)
            });

            // Log response details
            console.log('VROOM API Response:', {
                status: response.status,
                statusText: response.statusText,
                ok: response.ok,
                url: response.url,
                headers: Object.fromEntries(response.headers.entries())
            });

            if (!response.ok) {
                const errorText = await response.text();
                console.error('VROOM API Error Response:', {
                    status: response.status,
                    statusText: response.statusText,
                    errorText: errorText
                });
                throw new Error(`Błąd VROOM API: ${response.status} ${response.statusText} - ${errorText}`);
            }

            const result = await response.json();
            console.log('VROOM API Success Result:', {
                hasRoutes: !!(result.routes && result.routes.length > 0),
                routesCount: result.routes ? result.routes.length : 0,
                summary: result.summary,
                unassigned: result.unassigned
            });

            if (!result.routes || result.routes.length === 0) {
                console.error('VROOM API Error: No routes returned', result);
                throw new Error('VROOM nie zwrócił żadnych tras');
            }

            return result;

        } catch (error) {
            console.error('VROOM API Call Failed:', {
                error: error,
                message: error.message,
                name: error.name,
                stack: error.stack,
                endpoint: this.vroomEndpoint
            });
            
            if (error.name === 'TypeError' && error.message.includes('fetch')) {
                throw new Error('Nie można połączyć się z serwerem VROOM. Sprawdź, czy usługa działa.');
            }
            throw error;
        }
    }

    buildVroomPayload() {
        console.log('Building VROOM payload...');
        
        const depotCoords = [21.0122, 52.2297];
        const vehicle = {
            id: this.routeComponent.selectedDriver.id,
            profile: "driving-car",
            start: depotCoords,
            end: depotCoords,
            capacity: [100],
            time_window: [28800, 64800]
        };

        const validOrders = this.routeComponent.orders.filter(order =>
            order.coordinates &&
            Array.isArray(order.coordinates) &&
            order.coordinates.length === 2 &&
            !isNaN(order.coordinates[0]) &&
            !isNaN(order.coordinates[1])
        );

        console.log('VROOM Payload Info:', {
            totalOrders: this.routeComponent.orders.length,
            validOrders: validOrders.length,
            invalidOrders: this.routeComponent.orders.length - validOrders.length,
            vehicle: vehicle
        });

        if (validOrders.length === 0) {
            console.error('No valid orders for optimization');
            throw new Error('Żadne zamówienia nie mają prawidłowych współrzędnych do optymalizacji');
        }

        const jobs = validOrders.map((order) => ({
            id: order.id,
            location: [order.coordinates[1], order.coordinates[0]],
            service: 900,
            amount: [1],
            priority: this.getPriorityValue(order.priority),
            time_windows: this.getTimeWindow(order.priority)
        }));

        console.log('VROOM Jobs created:', jobs.length);

        return {
            vehicles: [vehicle],
            jobs: jobs,
            options: {
                g: true
            }
        };
    }

    processVroomResult(vroomResult) {
        console.log('Processing VROOM result...');
        
        if (!vroomResult.routes || vroomResult.routes.length === 0) {
            console.error('No routes in VROOM result');
            throw new Error('Brak tras zwróconych z VROOM API');
        }

        const route = vroomResult.routes[0];
        const steps = route.steps || [];

        console.log('VROOM Route Info:', {
            stepsCount: steps.length,
            distance: route.distance,
            duration: route.duration,
            hasGeometry: !!route.geometry
        });

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

        console.log('Optimization Result:', {
            totalDistance: totalDistance,
            totalTime: totalTime,
            savings: savings,
            routeStepsCount: routeSteps.length
        });

        return result;
    }

    processRouteSteps(steps) {
        console.log('Processing route steps:', steps.length);
        
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
                } else {
                    console.warn('Order not found for step job:', step.job);
                }
            }
        });

        console.log('Route steps processed:', routeSteps.length);
        return routeSteps;
    }

    updateOrderSequence(steps) {
        console.log('Updating order sequence...');
        
        const jobSequence = [];
        steps.forEach(step => {
            if (step.type === 'job') {
                jobSequence.push(step.job);
            }
        });

        console.log('Job sequence:', jobSequence);

        const orderedOrders = [];
        const remainingOrders = [...this.routeComponent.orders];

        jobSequence.forEach(jobId => {
            const orderIndex = remainingOrders.findIndex(order => order.id === jobId);
            if (orderIndex !== -1) {
                orderedOrders.push(remainingOrders.splice(orderIndex, 1)[0]);
            }
        });

        orderedOrders.push(...remainingOrders);

        console.log('Orders reordered:', {
            originalCount: this.routeComponent.orders.length,
            newCount: orderedOrders.length,
            optimizedCount: jobSequence.length
        });

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
        const timeWindows = {
            'high': [[28800, 43200]], // 8:00-12:00 
            'medium': [[32400, 54000]], // 9:00-15:00 
            'low': [[36000, 64800]] // 10:00-18:00 
        };
        return timeWindows[priority] || [[28800, 64800]]; 
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
        console.error('Handling optimization error:', error);
        
        this.routeComponent.optimizationError = {
            message: error.message || 'Optymalizacja nie powiodła się',
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

        console.log('Can optimize check:', {
            hasDriver,
            hasOrders,
            hasValidCoordinates,
            notLoading,
            result: hasDriver && hasOrders && hasValidCoordinates && notLoading
        });

        return hasDriver && hasOrders && hasValidCoordinates && notLoading;
    }
}

window.RouteOptimizerService = RouteOptimizerService;