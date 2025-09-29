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

        console.log('🔍 Can optimize check:', {
            hasDriver,
            hasOrders,
            hasValidCoordinates,
            notLoading,
            result: hasDriver && hasOrders && hasValidCoordinates && notLoading
        });

        return hasDriver && hasOrders && hasValidCoordinates && notLoading;
    }

    async optimizeRoutes(skipChecks = false) {
        console.log('🚀 Starting route optimization with unlimited constraints:', {
            skipChecks: skipChecks,
            canOptimize: this.canOptimize(),
            ordersCount: this.routeComponent.orders.length,
            selectedDriver: this.routeComponent.selectedDriver?.id,
            selectedDate: this.routeComponent.selectedDate
        });

        this.debugOrders();

        if (!skipChecks) {
            if (this.routeComponent.orders.length === 0) {
                console.warn('❌ No orders available for optimization');
                alert(`Brak dostępnych zamówień dla ${this.routeComponent.formattedSelectedDate}`);
                return;
            }

            if (!this.canOptimize()) {
                console.warn('❌ Cannot optimize - validation failed');
                return;
            }

            this.routeComponent.loading = true;
            this.routeComponent.optimizationError = null;
        }

        try {
            const vroomResult = await this.callVroomAPI();

            this.debugVroomResult(vroomResult);

            const optimizationResult = this.processVroomResult(vroomResult);

            this.routeComponent.optimizationResult = optimizationResult;

            await this.saveOptimizationToServer();

            if (window.mapManager) {
                setTimeout(() => {
                    window.mapManager.visualizeOptimizedRoute();
                }, 100);
            }

            console.log('✅ Route optimization completed successfully');

        } catch (error) {
            console.error('❌ Route optimization failed:', {
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

    debugOrders() {
        console.log('🔍 DEBUGGING ORDERS DATA:');
        console.log('📊 Total orders:', this.routeComponent.orders.length);

        const depotCoords = [52.2297, 21.0122];

        this.routeComponent.orders.forEach((order, index) => {
            const hasCoords = order.coordinates && Array.isArray(order.coordinates) && order.coordinates.length === 2;
            const isValidCoords = hasCoords && !isNaN(order.coordinates[0]) && !isNaN(order.coordinates[1]);

            let distance = 'N/A';
            if (isValidCoords) {
                distance = this.calculateDistance(depotCoords, order.coordinates).toFixed(2) + ' km';
            }

            console.log(`📍 Order ${index + 1} (ID: ${order.id}):`, {
                client: order.client_name,
                address: order.address,
                priority: order.priority,
                coordinates: order.coordinates,
                hasValidCoords: isValidCoords,
                distanceFromDepot: distance,
                totalAmount: order.total_amount
            });
        });

        const validOrders = this.routeComponent.orders.filter(order =>
            order.coordinates &&
            Array.isArray(order.coordinates) &&
            order.coordinates.length === 2 &&
            !isNaN(order.coordinates[0]) &&
            !isNaN(order.coordinates[1])
        );

        console.log('✅ Valid orders for optimization:', validOrders.length);
        console.log('❌ Invalid orders:', this.routeComponent.orders.length - validOrders.length);
    }

    async callVroomAPI() {
        const vroomPayload = this.buildVroomPayload();

        console.log('🌐 VROOM API REQUEST DETAILS:');
        console.log('📡 Endpoint:', this.vroomEndpoint);
        console.log('📦 Payload size:', JSON.stringify(vroomPayload).length, 'bytes');
        console.log('🚛 Vehicle config:', vroomPayload.vehicles[0]);
        console.log('📋 Jobs count:', vroomPayload.jobs.length);
        console.log('⚙️ Options:', vroomPayload.options);

        vroomPayload.jobs.forEach((job, index) => {
            console.log(`📋 Job ${index + 1}:`, {
                id: job.id,
                location: job.location,
                service: job.service,
                amount: job.amount,
                priority: job.priority
            });
        });

        try {
            const startTime = Date.now();
            const response = await fetch(`${this.vroomEndpoint}/`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(vroomPayload)
            });

            const requestDuration = Date.now() - startTime;

            console.log('📡 VROOM API RESPONSE:');
            console.log('⏱️ Request duration:', requestDuration + 'ms');
            console.log('📊 Status:', response.status, response.statusText);
            console.log('✅ OK:', response.ok);

            if (!response.ok) {
                const errorText = await response.text();
                console.error('❌ VROOM API Error Response:', {
                    status: response.status,
                    statusText: response.statusText,
                    errorText: errorText
                });
                throw new Error(`Błąd VROOM API: ${response.status} ${response.statusText} - ${errorText}`);
            }

            const result = await response.json();
            return result;

        } catch (error) {
            console.error('❌ VROOM API Call Failed:', {
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

    debugVroomResult(vroomResult) {
        console.log('🔍 DEBUGGING VROOM RESULT:');
        console.log('📊 Full VROOM result:', vroomResult);

        // Check summary
        if (vroomResult.summary) {
            console.log('📈 Summary:', {
                cost: vroomResult.summary.cost,
                routes: vroomResult.summary.routes,
                unassigned: vroomResult.summary.unassigned,
                setup: vroomResult.summary.setup,
                service: vroomResult.summary.service,
                duration: vroomResult.summary.duration,
                waiting_time: vroomResult.summary.waiting_time,
                priority: vroomResult.summary.priority,
                delivery: vroomResult.summary.delivery,
                pickup: vroomResult.summary.pickup
            });
        }

        console.log('🛣️ Routes count:', vroomResult.routes?.length || 0);
        if (vroomResult.routes) {
            vroomResult.routes.forEach((route, routeIndex) => {
                console.log(`🛣️ Route ${routeIndex + 1}:`, {
                    vehicle: route.vehicle,
                    cost: route.cost,
                    setup: route.setup,
                    service: route.service,
                    duration: route.duration,
                    waiting_time: route.waiting_time,
                    priority: route.priority,
                    distance: route.distance,
                    steps: route.steps?.length || 0
                });

                if (route.steps) {
                    route.steps.forEach((step, stepIndex) => {
                        console.log(`  📍 Step ${stepIndex + 1}:`, {
                            type: step.type,
                            location: step.location,
                            job: step.job,
                            service: step.service,
                            duration: step.duration,
                            arrival: step.arrival,
                            distance: step.distance,
                            load: step.load
                        });
                    });
                }
            });
        }

        if (vroomResult.unassigned && vroomResult.unassigned.length > 0) {
            console.error('❌ UNASSIGNED JOBS FOUND:');
            vroomResult.unassigned.forEach((unassigned, index) => {
                console.error(`❌ Unassigned ${index + 1}:`, {
                    id: unassigned.id,
                    location: unassigned.location,
                    description: unassigned.description || 'No description provided'
                });
            });
        } else {
            console.log('✅ All jobs were successfully assigned to routes');
        }

        if (vroomResult.routes && vroomResult.routes[0] && vroomResult.routes[0].geometry) {
            console.log('✅ Route geometry available for visualization');
        } else {
            console.warn('⚠️ No route geometry available');
        }
    }

    buildVroomPayload() {
        console.log('🔧 Building VROOM payload with UNLIMITED constraints for pure route optimization...');

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

        console.log('🔧 UNLIMITED CONSTRAINTS PAYLOAD:');
        console.log('🏭 Depot coordinates:', depotCoords);
        console.log('🚛 Vehicle capacity:', 'UNLIMITED (99999)');
        console.log('🚛 Vehicle time window:', 'UNLIMITED (no constraints)');
        console.log('📋 Total orders:', this.routeComponent.orders.length);
        console.log('✅ Valid orders:', validOrders.length);
        console.log('❌ Invalid orders:', this.routeComponent.orders.length - validOrders.length);

        if (validOrders.length === 0) {
            console.error('❌ No valid orders for optimization');
            throw new Error('Żadne zamówienia nie mają prawidłowych współrzędnych do optymalizacji');
        }

        const jobs = validOrders.map((order) => {
            const job = {
                id: order.id,
                location: [order.coordinates[1], order.coordinates[0]],
                service: 600, 
                amount: [1], 
                priority: this.getPriorityValue(order.priority)
            };

            console.log(`📋 Job created for order ${order.id}:`, {
                id: job.id,
                location: job.location,
                client: order.client_name,
                address: order.address,
                serviceTime: '10 minutes',
                timeWindow: 'UNLIMITED'
            });

            return job;
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

        console.log('🎯 UNLIMITED CONSTRAINTS PAYLOAD SUMMARY:', {
            vehicles: payload.vehicles.length,
            jobs: payload.jobs.length,
            vehicleCapacity: 'UNLIMITED',
            vehicleTimeWindow: 'UNLIMITED',
            jobTimeWindows: 'UNLIMITED',
            optimizationLevel: 3
        });

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

            console.log('💾 Save Optimization Request:', {
                endpoint: `${this.serverEndpoint}/api/route-data/save-optimization`,
                data: optimizationData
            });

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
                console.error('❌ Save Optimization Error:', {
                    status: response.status,
                    statusText: response.statusText,
                    responseText: responseText
                });
                throw new Error(`Nie udało się zapisać optymalizacji: ${response.statusText}`);
            }

            console.log('✅ Save Optimization Success');

        } catch (error) {
            console.error('❌ Save Optimization Failed:', error);
        }
    }

    processVroomResult(vroomResult) {
        console.log('🔄 Processing VROOM result...');

        if (!vroomResult.routes || vroomResult.routes.length === 0) {
            console.error('❌ No routes in VROOM result');
            throw new Error('Brak tras zwróconych z VROOM API');
        }

        const route = vroomResult.routes[0];
        const steps = route.steps || [];

        console.log('🔄 VROOM Route Info:', {
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

        console.log('✅ Optimization Result:', {
            totalDistance: totalDistance,
            totalTime: totalTime,
            savings: savings,
            routeStepsCount: routeSteps.length
        });

        return result;
    }

    processRouteSteps(steps) {
        console.log('🔄 Processing route steps:', steps.length);

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
                }
            }
        });

        console.log('✅ Route steps processed:', routeSteps.length);
        return routeSteps;
    }

    updateOrderSequence(steps) {
        console.log('🔄 Updating order sequence...');

        const jobSequence = [];
        steps.forEach(step => {
            if (step.type === 'job') {
                jobSequence.push(step.job);
            }
        });

        console.log('📋 Job sequence:', jobSequence);

        const orderedOrders = [];
        const remainingOrders = [...this.routeComponent.orders];

        jobSequence.forEach(jobId => {
            const orderIndex = remainingOrders.findIndex(order => order.id === jobId);
            if (orderIndex !== -1) {
                orderedOrders.push(remainingOrders.splice(orderIndex, 1)[0]);
            }
        });

        orderedOrders.push(...remainingOrders);

        console.log('🔄 Orders reordered:', {
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
        console.error('❌ Handling optimization error:', error);

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