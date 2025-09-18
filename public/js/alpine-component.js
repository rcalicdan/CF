function routeOptimizer() {
    const data = new RouteOptimizerData();

    return {
        dataInstance: new RouteOptimizerData(),
        routeDataService: null,

        loading: false,

        selectedDriver: null,
        showRouteSummary: false,
        manualEditMode: false,
        isDragging: false,
        draggedOrderIndex: null,

        get drivers() {
            return this.dataInstance.drivers || [];
        },

        get allOrders() {
            return this.dataInstance.allOrders || [];
        },

        get dataLoaded() {
            return this.dataInstance.dataLoaded;
        },

        get loadingError() {
            return this.dataInstance.loadingError;
        },

        get selectedDate() {
            return this.dataInstance.selectedDate;
        },

        set selectedDate(value) {
            this.dataInstance.selectedDate = value;
        },

        get orders() {
            return this.dataInstance.orders || [];
        },

        set orders(value) {
            this.dataInstance.orders = value;
        },

        get optimizationResult() {
            return this.dataInstance.optimizationResult;
        },

        set optimizationResult(value) {
            this.dataInstance.optimizationResult = value;
        },

        get optimizationError() {
            return this.dataInstance.optimizationError;
        },

        set optimizationError(value) {
            this.dataInstance.optimizationError = value;
        },

        async init() {
            window.routeOptimizerInstance = this;

            this.routeDataService = new RouteDataService();

            await this.loadData();

            this.$watch("selectedDate", async (newDate, oldDate) => {
                if (newDate !== oldDate) {
                    await this.updateOrders();
                    await this.loadSavedRoute();
                }
            });

            this.$watch("selectedDriver", async (newDriver, oldDriver) => {
                if (newDriver && newDriver.id !== oldDriver?.id) {
                    await this.updateOrders();
                    await this.loadSavedRoute();
                }
            });

            this.$watch("optimizationResult", (value) => {
                if (value) {
                    this.showRouteSummary = true;
                }
            });

            this.$nextTick(() => {
                setTimeout(() => {
                    const mapManager = new MapManager(this);
                    const optimizerService = new RouteOptimizerService(this);
                    window.mapManager = mapManager;
                    window.optimizerService = optimizerService;
                    window.routeData = this;
                    mapManager.init();
                }, 100);
            });
        },

        async loadData() {
            this.loading = true;
            this.dataInstance.loadingError = null;

            try {
                const drivers = await this.routeDataService.getDrivers();
                this.dataInstance.drivers = drivers || [];

                const startDate = new Date();
                startDate.setDate(startDate.getDate() - 7);
                const endDate = new Date();
                endDate.setDate(endDate.getDate() + 30);

                const allOrders = await this.routeDataService.getAllOrdersForDateRange(
                    startDate.toISOString().split('T')[0],
                    endDate.toISOString().split('T')[0]
                );
                this.dataInstance.allOrders = allOrders || [];

                this.dataInstance.dataLoaded = true;

                if (!this.selectedDriver && this.drivers.length > 0) {
                    this.selectedDriver = this.drivers[0];
                    this.$nextTick(() => {
                        this.updateOrders();
                    });
                }

            } catch (error) {
                this.dataInstance.loadingError = error.message;
                this.dataInstance.dataLoaded = false;
            } finally {
                this.loading = false;
            }
        },

        async loadSavedRoute() {
            if (!this.selectedDriver?.id || !this.selectedDate) {
                this.optimizationResult = null;
                return;
            }

            try {
                const savedRoute = await this.routeDataService.loadSavedRouteOptimization(
                    this.selectedDriver.id,
                    this.selectedDate
                );

                if (savedRoute) {
                    this.optimizationResult = savedRoute.optimization_result;

                    if (savedRoute.order_sequence && savedRoute.order_sequence.length > 0) {
                        this.applySavedOrderSequence(savedRoute.order_sequence);
                    }

                    if (this.optimizationResult) {
                        this.showRouteSummary = true;
                    }

                    if (window.mapManager) {
                        setTimeout(() => {
                            window.mapManager.refreshMarkers();
                            if (this.optimizationResult) {
                                window.mapManager.visualizeOptimizedRoute();
                            }
                        }, 500);
                    }

                    this.showNotification('Zapisana trasa została pomyślnie załadowana', 'success');
                } else {
                    this.optimizationResult = null;
                    this.showRouteSummary = false;
                }

            } catch (error) {
                this.optimizationResult = null;
                this.showRouteSummary = false;
            }
        },

        applySavedOrderSequence(savedSequence) {
            if (!savedSequence || savedSequence.length === 0) {
                return;
            }

            const orderMap = new Map(this.orders.map(order => [order.id, order]));
            const reorderedOrders = [];

            if (this.optimizationResult && this.optimizationResult.route_steps) {
                this.optimizationResult.route_steps.forEach(routeStep => {
                    let order = orderMap.get(routeStep.order_id);

                    if (!order && routeStep.client_name === "Custom Stop") {
                        order = {
                            id: routeStep.order_id,
                            client_name: "Własny przystanek",
                            address: routeStep.location,
                            coordinates: routeStep.coordinates,
                            total_amount: 0,
                            status: "custom",
                            priority: routeStep.priority || "medium",
                            delivery_date: this.selectedDate,
                            driver_id: this.selectedDriver.id,
                            isCustom: true
                        };
                    }

                    if (order) {
                        reorderedOrders.push(order);
                        orderMap.delete(routeStep.order_id);
                    }
                });
            }

            orderMap.forEach(order => {
                reorderedOrders.push(order);
            });

            this.orders = reorderedOrders;
        },

        showNotification(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 px-4 py-2 rounded shadow-lg text-white z-50 ${type === 'success' ? 'bg-green-500' :
                type === 'error' ? 'bg-red-500' :
                    type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'
                }`;
            toast.textContent = message;

            document.body.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);
        },

        getOrdersForDriverAndDate(driverId, date) {
            return this.dataInstance.getOrdersForDriverAndDate(driverId, date);
        },

        async updateOrders() {
            if (this.selectedDriver && this.selectedDate) {
                try {
                    const orders = this.getOrdersForDriverAndDate(
                        this.selectedDriver.id,
                        this.selectedDate
                    ) || [];

                    this.dataInstance.orders = orders;

                    this.$nextTick(() => {
                        if (window.mapManager) {
                            window.mapManager.refreshMarkers();
                        }
                        if (this.refreshOptimizedRoute) {
                            this.refreshOptimizedRoute();
                        }
                    });

                } catch (error) {
                    this.dataInstance.orders = [];
                }
            } else {
                this.dataInstance.orders = [];
            }
        },

        getTodayDate() {
            const today = new Date();
            return today.toISOString().split("T")[0];
        },

        get formattedSelectedDate() {
            const date = new Date(this.selectedDate + "T00:00:00");
            return date.toLocaleDateString("pl-PL", {
                weekday: "long",
                year: "numeric",
                month: "long",
                day: "numeric",
            });
        },

        get dateStatus() {
            const today = this.getTodayDate();
            if (this.selectedDate === today) return "today";
            if (this.selectedDate < today) return "past";
            return "future";
        },

        get totalOrderValue() {
            return this.orders.reduce(
                (sum, order) => sum + order.total_amount,
                0
            );
        },

        toggleManualEdit() {
            this.manualEditMode = !this.manualEditMode;
            if (window.mapManager) {
                if (this.manualEditMode) {
                    window.mapManager.enableManualEdit();
                } else {
                    window.mapManager.disableManualEdit();
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
            if (
                confirm(
                    "Czy na pewno chcesz usunąć ten przystanek z trasy?"
                )
            ) {
                this.orders.splice(index, 1);
                this.refreshOptimizedRoute();
            }
        },

        addCustomStop(lat, lng, address = "Własna lokalizacja") {
            if (
                isNaN(lat) ||
                isNaN(lng) ||
                lat < -90 ||
                lat > 90 ||
                lng < -180 ||
                lng > 180
            ) {
                alert("Nieprawidłowe współrzędne. Spróbuj ponownie.");
                return;
            }
            const customOrder = {
                id: Date.now(),
                client_name: "Własny przystanek",
                address: address,
                coordinates: [parseFloat(lat), parseFloat(lng)],
                total_amount: 0,
                status: "custom",
                priority: "medium",
                delivery_date: this.selectedDate,
                driver_id: this.selectedDriver.id,
                isCustom: true,
            };
            this.orders.push(customOrder);
            setTimeout(() => {
                this.refreshOptimizedRoute();
            }, 100);
        },

        onDragStart(index, event) {
            this.isDragging = true;
            this.draggedOrderIndex = index;
            event.dataTransfer.effectAllowed = "move";
            event.dataTransfer.setData("text/html", event.target.outerHTML);
            event.target.classList.add("dragging");
        },

        onDragOver(event) {
            if (this.isDragging) {
                event.preventDefault();
                event.dataTransfer.dropEffect = "move";
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
                document.querySelectorAll(".dragging").forEach((el) => {
                    el.classList.remove("dragging");
                });
                this.refreshOptimizedRoute();
            }
        },

        refreshOptimizedRoute() {
            this.optimizationResult = null;
            this.showRouteSummary = false;

            if (window.mapManager) {
                window.mapManager.refreshMarkers();
                window.mapManager.clearRoute();
            }
        },

        async saveManualChanges() {
            if (!confirm("Zapisać bieżącą konfigurację trasy?")) {
                return;
            }

            this.loading = true;

            try {
                const saveData = {
                    driver_id: this.selectedDriver.id,
                    optimization_date: this.selectedDate,
                    optimization_result: this.optimizationResult || {},
                    order_sequence: this.orders.map((order, index) => ({
                        order_id: order.id,
                        sequence: index + 1
                    })),
                    total_distance: this.optimizationResult?.total_distance || null,
                    total_time: this.optimizationResult?.total_time || null,
                    is_manual_edit: true,
                    manual_modifications: {
                        edit_mode_used: this.manualEditMode,
                        custom_stops_added: this.orders.filter(o => o.isCustom).length,
                        saved_at: new Date().toISOString()
                    }
                };

                await this.routeDataService.request(`${this.routeDataService.baseUrl}/save-optimization`, {
                    method: 'POST',
                    body: JSON.stringify(saveData)
                });

                this.showNotification('Trasa zapisana pomyślnie!', 'success');

                this.manualEditMode = false;
                if (window.mapManager) {
                    window.mapManager.disableManualEdit();
                }

            } catch (error) {
                this.showNotification('Nie udało się zapisać zmian w trasie', 'error');
            } finally {
                this.loading = false;
            }
        },

        resetToOptimized() {
            if (
                confirm(
                    "Zresetować do oryginalnej zoptymalizowanej trasy? Spowoduje to utratę wszystkich ręcznych zmian."
                )
            ) {
                this.updateOrders();
                this.manualEditMode = false;
            }
        },

        exportManualRoute() {
            if (this.orders.length === 0) {
                alert("Brak danych trasy do eksportu");
                return;
            }
            const exportData = {
                export_type: "manual_route",
                export_date: new Date().toISOString(),
                delivery_date: this.selectedDate,
                driver: this.selectedDriver,
                manual_modifications: {
                    edit_mode_used: this.manualEditMode,
                    custom_stops_added: this.orders.filter((o) => o.isCustom)
                        .length,
                },
                route_data: {
                    total_stops: this.orders.length,
                    custom_stops: this.orders.filter((o) => o.isCustom),
                    stop_sequence: this.orders.map((order, index) => ({
                        sequence: index + 1,
                        order_id: order.id,
                        client_name: order.client_name,
                        address: order.address,
                        coordinates: order.coordinates,
                        priority: order.priority,
                        value: order.total_amount,
                        is_custom: order.isCustom || false,
                    })),
                },
                statistics: {
                    total_value: this.totalOrderValue,
                    priority_breakdown: this.priorityBreakdown,
                },
            };
            const dataStr = JSON.stringify(exportData, null, 2);
            const dataBlob = new Blob([dataStr], { type: "application/json" });
            const url = URL.createObjectURL(dataBlob);
            const link = document.createElement("a");
            link.href = url;
            link.download = `trasa-ręczna-${this.selectedDate
                }-${Date.now()}.json`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        },

        async optimizeRoutes() {
            if (this.orders.length === 0) {
                alert(`Brak dostępnych zamówień dla ${this.formattedSelectedDate}`);
                return;
            }

            if (!window.optimizerService || !window.optimizerService.canOptimize()) {
                return;
            }

            this.loading = true;
            this.optimizationError = null;

            try {
                await window.optimizerService.optimizeRoutes(true);
                setTimeout(() => {
                    this.showRouteSummary = true;
                }, 100);
            } catch (error) {
                this.optimizationError = error.message;
            } finally {
                this.loading = false;
            }
        },

        onDateChange(event) {
            const newDate = event.target.value;
            if (newDate) {
                this.selectedDate = newDate;
            }
        },

        getMinDate() {
            return this.getTodayDate();
        },

        getMaxDate() {
            const maxDate = new Date();
            maxDate.setDate(maxDate.getDate() + 30);
            return maxDate.toISOString().split("T")[0];
        },

        getDateStatusClass() {
            const status = this.dateStatus;
            const classes = {
                today: "bg-green-100 text-green-800 border-green-200",
                past: "bg-gray-100 text-gray-500 border-gray-200",
                future: "bg-blue-100 text-blue-800 border-blue-200",
            };
            return classes[status] || classes.future;
        },

        getDateStatusText() {
            const status = this.dateStatus;
            const texts = {
                today: "Dzisiejsze dostawy",
                past: "Poprzednie dostawy",
                future: "Zaplanowane dostawy",
            };
            return texts[status] || "Dostawy";
        },

        get executiveSummary() {
            if (!this.optimizationResult) {
                return null;
            }
            const result = this.optimizationResult;
            const savings = result.savings || 0;
            const routeSteps = result.route_steps || [];
            return {
                totalStops: result.total_orders || 0,
                totalDistance: `${result.total_distance || 0} km`,
                totalTime: this.formatTime(result.total_time || 0),
                savings: `${savings} km`,
                startTime: "08:00",
                firstDelivery: "09:30",
                lastDelivery:
                    routeSteps.length > 0
                        ? routeSteps[routeSteps.length - 1]?.estimated_arrival
                        : "16:45",
                returnTime: this.calculateReturnTime(result.total_time || 0),
                deliveryDate: this.formattedSelectedDate,
            };
        },

        get priorityBreakdown() {
            const breakdown = {
                high: { count: 0, value: 0, colorClass: "bg-red-500" },
                medium: { count: 0, value: 0, colorClass: "bg-yellow-500" },
                low: { count: 0, value: 0, colorClass: "bg-green-500" },
            };
            this.orders.forEach((order) => {
                if (breakdown[order.priority]) {
                    breakdown[order.priority].count++;
                    breakdown[order.priority].value += order.total_amount;
                }
            });
            return Object.entries(breakdown).map(([level, data]) => ({
                level,
                ...data,
            }));
        },

        toggleSummary() {
            this.showRouteSummary = !this.showRouteSummary;
        },

        selectDriver(driver) {
            if (!driver || !driver.id) {
                return;
            }

            this.selectedDriver = driver;

            this.$nextTick(() => {
                this.updateOrders();
            });
        },

        focusOnOrder(orderId) {
            if (window.mapManager) {
                window.mapManager.focusOnOrder(orderId);
            }
        },

        resetOptimization() {
            this.optimizationResult = null;
            this.optimizationError = null;
            this.loading = false;
            this.showRouteSummary = false;
            this.manualEditMode = false;
            if (window.mapManager) {
                window.mapManager.clearRoute();
                window.mapManager.disableManualEdit();
            }
        },

        refreshMap() {
            if (window.mapManager && this.mapInitialized) {
                window.mapManager.refreshMarkers();
            }
        },

        exportSummary() {
            if (!this.optimizationResult) {
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
                        returnTime: summary.returnTime,
                    },
                },
                priority_breakdown: this.priorityBreakdown,
                route_details: this.optimizationResult.route_steps,
                orders: this.orders.map((order) => ({
                    id: order.id,
                    client: order.client_name,
                    address: order.address,
                    value: order.total_amount,
                    priority: order.priority,
                    delivery_date: order.delivery_date,
                    is_custom: order.isCustom || false,
                })),
            };
            const dataStr = JSON.stringify(exportData, null, 2);
            const dataBlob = new Blob([dataStr], { type: "application/json" });
            const url = URL.createObjectURL(dataBlob);
            const link = document.createElement("a");
            link.href = url;
            link.download = `podsumowanie-trasy-${this.selectedDate}.json`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        },

        calculateReturnTime(totalMinutes) {
            const startTime = new Date();
            startTime.setHours(8, 0, 0, 0);
            const returnTime = new Date(
                startTime.getTime() + totalMinutes * 60000
            );
            return returnTime.toLocaleTimeString("pl-PL", {
                hour: "2-digit",
                minute: "2-digit",
            });
        },

        calculateEstimatedArrival(arrivalSeconds) {
            const startTime = new Date();
            startTime.setHours(8, 0, 0, 0);
            const arrivalTime = new Date(
                startTime.getTime() + arrivalSeconds * 1000
            );
            return arrivalTime.toLocaleTimeString("pl-PL", {
                hour: "2-digit",
                minute: "2-digit",
            });
        },

        formatCurrency(amount) {
            return `zł${amount.toLocaleString("pl-PL")}`;
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
            // 
        },

        forceRefresh() {
            this.$nextTick(() => {
                // 
            });
        }
    };
}

window.routeOptimizer = routeOptimizer;