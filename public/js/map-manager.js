class MapManager {
    constructor(data) {
        this.data = data;
        this.priorityColors = {
            'high': '#ef4444',
            'medium': '#f59e0b',
            'low': '#10b981'
        };
        this.depotCoordinates = [52.2297, 21.0122];

        this.editMode = false;
        this.drawingMode = false;
        this.customPolyline = null;
        this.customRoutePoints = [];
        this.tempMarkers = [];
        this.drawingInstructions = null;
        this.isMapReady = false;
    }

    init() {
        if (this.data.mapInitialized) return;

        try {
            console.log('Initializing map...');

            this.data.map = L.map('map', {
                center: [52.0, 19.0],
                zoom: 6,
                zoomControl: true,
                attributionControl: true,
                preferCanvas: true,
                maxZoom: 18,
                minZoom: 5
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 18,
                tileSize: 256,
                zoomOffset: 0,
                crossOrigin: true,
                updateWhenIdle: true,
                updateWhenZooming: false,
                keepBuffer: 2
            }).addTo(this.data.map);

            this.data.map.whenReady(() => {
                console.log('Map ready, adding markers...');
                this.isMapReady = true;
                this.addDepotMarker();
                this.addOrderMarkers();
                this.data.mapInitialized = true;

                setTimeout(() => {
                    this.data.map.invalidateSize();
                }, 200);
            });

            setTimeout(() => {
                if (!this.isMapReady) {
                    console.log('Map ready timeout, forcing initialization...');
                    this.isMapReady = true;
                    this.addDepotMarker();
                    this.addOrderMarkers();
                    this.data.mapInitialized = true;
                }
            }, 3000);

        } catch (error) {
            console.error('Map initialization failed:', error);
        }
    }

    safeMapOperation(operation, operationName = 'map operation') {
        if (!this.data.map || !this.isMapReady) {
            console.warn(`Cannot perform ${operationName}: map not ready`);
            return false;
        }

        try {
            if (!this.data.map.getContainer()) {
                console.warn(`Cannot perform ${operationName}: map container not available`);
                return false;
            }

            return operation();
        } catch (error) {
            console.error(`Error during ${operationName}:`, error);
            return false;
        }
    }

    enableManualEdit() {
        return this.safeMapOperation(() => {
            this.editMode = true;
            console.log('Manual edit mode enabled');

            this.data.markers.forEach((marker, index) => {
                if (marker && marker.dragging) {
                    marker.dragging.enable();
                    marker.off('dragend');
                    marker.on('dragend', (e) => {
                        const newLatLng = e.target.getLatLng();
                        if (this.data.orders[index]) {
                            this.data.orders[index].coordinates = [newLatLng.lat, newLatLng.lng];
                            this.data.refreshOptimizedRoute();
                            console.log(`Order ${this.data.orders[index].id} moved to:`, [newLatLng.lat, newLatLng.lng]);
                        }
                    });
                }
            });


            this.data.map.off('click', this.onMapClick);
            this.data.map.on('click', this.onMapClick.bind(this));


            this.data.map.getContainer().style.cursor = 'crosshair';

            this.addEditModeIndicator();
            return true;
        }, 'enable manual edit');
    }

    disableManualEdit() {
        return this.safeMapOperation(() => {
            this.editMode = false;
            console.log('Manual edit mode disabled');

            this.data.markers.forEach(marker => {
                if (marker && marker.dragging) {
                    marker.dragging.disable();
                    marker.off('dragend');
                    marker.off('contextmenu');
                }
            });

            this.data.map.off('click', this.onMapClick);

            this.data.map.getContainer().style.cursor = '';

            this.removeEditModeIndicator();
            return true;
        }, 'disable manual edit');
    }

    addEditModeIndicator() {
        if (!this.editModeIndicator) {
            this.editModeIndicator = L.control({ position: 'topleft' });
            this.editModeIndicator.onAdd = () => {
                const div = L.DomUtil.create('div', 'edit-mode-indicator');
                div.innerHTML = `
                    <div style="background: #f97316; color: white; padding: 8px 12px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.2); font-size: 12px; font-weight: bold;">
                        <i class="fas fa-edit mr-1"></i>EDIT MODE ACTIVE
                    </div>
                `;
                return div;
            };
        }

        if (this.data.map && !this.data.map.hasControl && this.editModeIndicator) {
            try {
                this.editModeIndicator.addTo(this.data.map);
            } catch (error) {
                console.warn('Could not add edit mode indicator:', error);
            }
        }
    }

    removeEditModeIndicator() {
        if (this.editModeIndicator && this.data.map) {
            try {
                this.data.map.removeControl(this.editModeIndicator);
            } catch (error) {
                console.warn('Could not remove edit mode indicator:', error);
            }
        }
    }

    onMapClick(e) {
        if (this.editMode && !this.drawingMode && this.isMapReady) {
            if (this.clickTimeout) {
                clearTimeout(this.clickTimeout);
            }

            this.clickTimeout = setTimeout(() => {
                this.handleCustomStopClick(e);
            }, 300);
        }
    }

    handleCustomStopClick(e) {
        if (!e.latlng) return;

        const { lat, lng } = e.latlng;

        if (isNaN(lat) || isNaN(lng)) {
            console.warn('Invalid coordinates received:', { lat, lng });
            return;
        }

        const address = `Custom Location (${lat.toFixed(4)}, ${lng.toFixed(4)})`;

        if (confirm(`Add custom stop at ${address}?`)) {
            setTimeout(() => {
                this.data.addCustomStop(lat, lng, address);
            }, 100);
        }
    }

    enableRouteDrawing() {
        return this.safeMapOperation(() => {
            this.drawingMode = true;
            this.customRoutePoints = [];
            console.log('Route drawing mode enabled');

            // Clear existing route
            this.clearRoute();

            // Add click handler for route drawing
            this.data.map.off('click', this.onRouteDrawClick);
            this.data.map.on('click', this.onRouteDrawClick.bind(this));

            // Change cursor and add instructions
            this.data.map.getContainer().style.cursor = 'crosshair';

            // Show drawing instructions
            this.addDrawingInstructions();

            // Add right-click handler to finish drawing
            this.data.map.off('contextmenu', this.finishRouteDrawing);
            this.data.map.on('contextmenu', this.finishRouteDrawing.bind(this));

            return true;
        }, 'enable route drawing');
    }

    // Add drawing instructions safely
    addDrawingInstructions() {
        if (!this.drawingInstructions) {
            this.drawingInstructions = L.control({ position: 'topright' });
            this.drawingInstructions.onAdd = () => {
                const div = L.DomUtil.create('div', 'route-drawing-instructions');
                div.innerHTML = `
                    <div style="background: white; padding: 12px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); max-width: 200px;">
                        <div style="display: flex; align-items: center; margin-bottom: 8px;">
                            <i class="fas fa-pencil-alt" style="color: #8b5cf6; margin-right: 6px;"></i>
                            <strong style="color: #374151;">Route Drawing</strong>
                        </div>
                        <div style="font-size: 13px; color: #6b7280; line-height: 1.4;">
                            Click points to draw route<br>
                            Right-click to finish
                        </div>
                        <div style="margin-top: 8px; font-size: 11px; color: #9ca3af;">
                            Points: <span id="point-count">0</span>
                        </div>
                    </div>
                `;
                return div;
            };
        }

        if (this.data.map && this.drawingInstructions) {
            try {
                this.drawingInstructions.addTo(this.data.map);
            } catch (error) {
                console.warn('Could not add drawing instructions:', error);
            }
        }
    }

    onRouteDrawClick(e) {
        if (this.drawingMode && e.latlng && this.isMapReady) {

            if (this.drawClickTimeout) {
                clearTimeout(this.drawClickTimeout);
            }

            this.drawClickTimeout = setTimeout(() => {
                this.handleRouteDrawClick(e);
            }, 100);
        }
    }

    handleRouteDrawClick(e) {
        const { lat, lng } = e.latlng;

        if (isNaN(lat) || isNaN(lng)) {
            console.warn('Invalid coordinates for route drawing:', { lat, lng });
            return;
        }

        this.customRoutePoints.push([lat, lng]);

        const pointCounter = document.getElementById('point-count');
        if (pointCounter) {
            pointCounter.textContent = this.customRoutePoints.length;
        }

        this.safeMapOperation(() => {
            const tempMarker = L.circleMarker([lat, lng], {
                radius: 6,
                color: '#8b5cf6',
                fillColor: '#8b5cf6',
                fillOpacity: 0.8,
                weight: 2
            });

            if (tempMarker) {
                tempMarker.addTo(this.data.map);
                if (!this.tempMarkers) this.tempMarkers = [];
                this.tempMarkers.push(tempMarker);
            }

            if (this.customRoutePoints.length > 1) {
                this.updateCustomRouteLine();
            }

            return true;
        }, 'add route drawing marker');
    }

    updateCustomRouteLine() {
        this.safeMapOperation(() => {
            if (this.customPolyline && this.data.map.hasLayer(this.customPolyline)) {
                this.data.map.removeLayer(this.customPolyline);
            }

            this.customPolyline = L.polyline(this.customRoutePoints, {
                color: '#8b5cf6',
                weight: 4,
                opacity: 0.8,
                dashArray: '10, 5'
            });

            if (this.customPolyline) {
                this.customPolyline.addTo(this.data.map);
            }

            return true;
        }, 'update custom route line');
    }

    finishRouteDrawing(e) {
        if (this.drawingMode) {
            if (e.originalEvent) {
                e.originalEvent.preventDefault();
            }

            this.drawingMode = false;

            this.data.map.off('click', this.onRouteDrawClick);
            this.data.map.off('contextmenu', this.finishRouteDrawing);

            this.removeDrawingInstructions();

            if (this.data.map.getContainer()) {
                this.data.map.getContainer().style.cursor = '';
            }

            console.log('Custom route drawn with', this.customRoutePoints.length, 'points');

            this.data.customRoutePoints = this.customRoutePoints.slice();

            if (this.customRoutePoints.length > 0) {
                alert(`Custom route created with ${this.customRoutePoints.length} points!`);
            }
        }
    }

    removeDrawingInstructions() {
        if (this.drawingInstructions && this.data.map) {
            try {
                this.data.map.removeControl(this.drawingInstructions);
                this.drawingInstructions = null;
            } catch (error) {
                console.warn('Could not remove drawing instructions:', error);
            }
        }
    }

    disableRouteDrawing() {
        this.drawingMode = false;

        if (this.data.map) {
            this.data.map.off('click', this.onRouteDrawClick);
            this.data.map.off('contextmenu', this.finishRouteDrawing);
        }

        this.removeDrawingInstructions();

        if (this.data.map && this.data.map.getContainer()) {
            this.data.map.getContainer().style.cursor = '';
        }
    }

    clearCustomRoute() {
        this.safeMapOperation(() => {
            if (this.customPolyline && this.data.map.hasLayer(this.customPolyline)) {
                this.data.map.removeLayer(this.customPolyline);
            }
            this.customPolyline = null;

            if (this.tempMarkers) {
                this.tempMarkers.forEach(marker => {
                    if (marker && this.data.map.hasLayer(marker)) {
                        this.data.map.removeLayer(marker);
                    }
                });
                this.tempMarkers = [];
            }

            this.customRoutePoints = [];
            this.data.customRoutePoints = [];

            return true;
        }, 'clear custom route');
    }

    addDepotMarker() {
        return this.safeMapOperation(() => {
            const depotIcon = L.divIcon({
                className: 'depot-marker',
                html: '<i class="fas fa-warehouse"></i>',
                iconSize: [32, 32],
                iconAnchor: [16, 16]
            });

            const depotMarker = L.marker(this.depotCoordinates, { icon: depotIcon })
                .addTo(this.data.map)
                .bindPopup(`
                   <div class="p-3">
                       <strong class="text-lg">Main Depot</strong><br>
                       <div class="text-sm text-gray-600">Warsaw Distribution Center</div>
                       <div class="text-sm text-blue-600 mt-1">Starting Point for All Routes</div>
                   </div>
               `, {
                    maxWidth: 200,
                    className: 'custom-popup'
                });

            console.log('Depot marker added successfully');
            return depotMarker;
        }, 'add depot marker');
    }

    addOrderMarkers() {
        return this.safeMapOperation(() => {
            this.clearOrderMarkers();

            console.log(`Adding ${this.data.orders.length} order markers...`);

            this.data.orders.forEach((order, index) => {
                try {
                    if (!order.coordinates || !Array.isArray(order.coordinates) ||
                        order.coordinates.length !== 2 ||
                        isNaN(order.coordinates[0]) || isNaN(order.coordinates[1])) {
                        console.warn(`Invalid coordinates for order ${order.id}:`, order.coordinates);
                        return;
                    }

                    const orderIcon = L.divIcon({
                        className: 'custom-marker',
                        html: `<div class="custom-marker ${order.isCustom ? 'custom-stop' : ''}" 
                              style="background-color: ${this.priorityColors[order.priority] || '#6b7280'}">
                              ${index + 1}
                              </div>`,
                        iconSize: [24, 24],
                        iconAnchor: [12, 12]
                    });

                    const marker = L.marker([order.coordinates[0], order.coordinates[1]], {
                        icon: orderIcon,
                        title: `Order #${order.id} - ${order.client_name}`,
                        draggable: false
                    })
                        .addTo(this.data.map)
                        .bindPopup(this.createOrderPopup(order, index + 1), {
                            maxWidth: 250,
                            className: 'custom-popup'
                        });

                    if (this.editMode && marker.dragging) {
                        marker.dragging.enable();
                        marker.on('dragend', (e) => {
                            const newLatLng = e.target.getLatLng();
                            if (this.data.orders[index]) {
                                this.data.orders[index].coordinates = [newLatLng.lat, newLatLng.lng];
                                this.data.refreshOptimizedRoute();
                                console.log(`Order ${order.id} moved to:`, [newLatLng.lat, newLatLng.lng]);
                            }
                        });

                        marker.on('contextmenu', (e) => {
                            e.originalEvent.preventDefault();
                            this.showMarkerContextMenu(e, order, index);
                        });
                    }

                    this.data.markers.push(marker);
                } catch (error) {
                    console.error(`Failed to add marker for order ${order.id}:`, error);
                }
            });

            console.log(`Successfully added ${this.data.markers.length} markers`);
            return true;
        }, 'add order markers');
    }

    showMarkerContextMenu(e, order, index) {
        if (!this.data.map || !e.latlng) return;

        try {
            const contextMenu = L.popup({
                closeButton: false,
                autoClose: true,
                closeOnClick: true,
                className: 'context-menu-popup'
            })
                .setLatLng(e.latlng)
                .setContent(`
               <div class="marker-context-menu">
                   <button onclick="window.routeData.moveStopUp(${index})" class="context-btn" ${index === 0 ? 'disabled' : ''}>
                       <i class="fas fa-arrow-up"></i> Move Up
                   </button>
                   <button onclick="window.routeData.moveStopDown(${index})" class="context-btn" ${index === this.data.orders.length - 1 ? 'disabled' : ''}>
                       <i class="fas fa-arrow-down"></i> Move Down
                   </button>
                   <button onclick="window.routeData.focusOnOrder(${order.id})" class="context-btn">
                       <i class="fas fa-crosshairs"></i> Focus
                   </button>
                   ${order.isCustom ? `
                       <button onclick="window.routeData.removeStop(${index}); this.closest('.leaflet-popup').remove();" class="context-btn danger">
                           <i class="fas fa-trash"></i> Remove
                       </button>
                   ` : ''}
               </div>
           `)
                .openOn(this.data.map);
        } catch (error) {
            console.error('Failed to show context menu:', error);
        }
    }

    createOrderPopup(order, orderNumber) {
        const safeValue = (value, fallback = 'N/A') => value || fallback;

        return `
           <div class="p-2 min-w-[200px]">
               <div class="flex items-center justify-between mb-2">
                   <strong class="text-lg">Order #${safeValue(order.id)}</strong>
                   <div class="flex items-center gap-1">
                       <div class="w-6 h-6 rounded-full flex items-center justify-center text-white text-sm font-bold" 
                            style="background-color: ${this.priorityColors[order.priority] || '#6b7280'}">${orderNumber}</div>
                       ${order.isCustom ? '<div class="w-5 h-5 bg-purple-500 rounded-full flex items-center justify-center text-white text-xs"><i class="fas fa-star"></i></div>' : ''}
                   </div>
               </div>
               <div class="text-sm font-medium text-gray-800">${safeValue(order.client_name)}</div>
               <div class="text-sm text-gray-600 mb-2">${safeValue(order.address)}</div>
               <div class="flex items-center justify-between">
                   <div class="text-lg font-semibold text-green-600">zł${safeValue(order.total_amount, '0')}</div>
                   <div class="text-xs uppercase font-medium px-2 py-1 rounded" 
                        style="background-color: ${this.priorityColors[order.priority] || '#6b7280'}20; color: ${this.priorityColors[order.priority] || '#6b7280'}">${safeValue(order.priority, 'medium')} priority</div>
               </div>
               <div class="text-xs text-gray-500 mt-1">Status: ${safeValue(order.status, 'pending')}</div>
               ${order.isCustom ? '<div class="text-xs text-purple-600 mt-1 font-medium">Custom Stop</div>' : ''}
           </div>
       `;
    }

    clearOrderMarkers() {
        if (this.data.markers) {
            this.data.markers.forEach(marker => {
                try {
                    if (marker && this.data.map && this.data.map.hasLayer(marker)) {
                        this.data.map.removeLayer(marker);
                    }
                } catch (error) {
                    console.warn('Error removing marker:', error);
                }
            });
        }
        this.data.markers = [];
    }

    clearRoute() {
        this.safeMapOperation(() => {
            if (this.data.routingControl) {
                try {
                    this.data.map.removeControl(this.data.routingControl);
                } catch (error) {
                    console.warn('Error removing routing control:', error);
                }
                this.data.routingControl = null;
            }

            if (this.data.routeStepMarkers) {
                this.data.routeStepMarkers.forEach(marker => {
                    try {
                        if (marker && this.data.map.hasLayer(marker)) {
                            this.data.map.removeLayer(marker);
                        }
                    } catch (error) {
                        console.warn('Error removing route step marker:', error);
                    }
                });
                this.data.routeStepMarkers = [];
            }

            if (this.data.fallbackPolyline) {
                try {
                    if (this.data.map.hasLayer(this.data.fallbackPolyline)) {
                        this.data.map.removeLayer(this.data.fallbackPolyline);
                    }
                } catch (error) {
                    console.warn('Error removing fallback polyline:', error);
                }
                this.data.fallbackPolyline = null;
            }

            return true;
        }, 'clear route');
    }

    // Rest of the methods remain the same but wrapped with safeMapOperation where needed...
    visualizeOptimizedRoute() {
        if (!this.isMapReady) {
            console.warn('Map not ready for route visualization, retrying...');
            setTimeout(() => this.visualizeOptimizedRoute(), 1000);
            return;
        }

        return this.safeMapOperation(() => {
            if (!this.data.optimizationResult && this.data.orders.length === 0) {
                console.warn('No optimization result or orders available');
                return false;
            }

            console.log('Visualizing optimized route...');
            this.clearRoute();

            // Use custom route if available, otherwise use optimized route
            const routeCoordinates = this.data.customRoutePoints.length > 0
                ? this.data.customRoutePoints
                : this.buildOptimizedRouteCoordinates();

            // If we have custom route points, use them directly
            if (this.data.customRoutePoints.length > 0) {
                this.visualizeCustomRoute(this.data.customRoutePoints);
                return true;
            }

            // Otherwise use Leaflet Routing Machine
            this.data.routingControl = L.Routing.control({
                waypoints: routeCoordinates.map(coord => L.latLng(coord[0], coord[1])),
                routeWhileDragging: false,
                addWaypoints: false,
                show: false,
                createMarker: function () {
                    return null;
                },
                lineOptions: {
                    styles: [{
                        color: '#667eea',
                        opacity: 0.8,
                        weight: 5,
                        dashArray: '10, 5'
                    }]
                },
                router: L.Routing.osrmv1({
                    serviceUrl: 'https://router.project-osrm.org/route/v1',
                    profile: 'driving',
                    timeout: 30000
                }),

                formatter: new L.Routing.Formatter({
                    units: 'metric',
                    roundingSensitivity: 1,
                    language: 'en'
                })
            });

            this.data.routingControl.addTo(this.data.map);

            this.data.routingControl.on('routesfound', (e) => {
                console.log('Routes found:', e.routes);
                this.onRoutesFound(e.routes);
            });

            this.data.routingControl.on('routingerror', (e) => {
                console.error('Routing error:', e.error);
                this.onRoutingError(e.error);
            });

            setTimeout(() => {
                this.fitMapToRoute();
            }, 1000);

            console.log('Route visualization completed');
            return true;
        }, 'visualize optimized route');
    }

    visualizeCustomRoute(points) {
        return this.safeMapOperation(() => {
            console.log('Visualizing custom route with', points.length, 'points');

            const polyline = L.polyline(points, {
                color: '#8b5cf6',
                weight: 4,
                opacity: 0.8,
                dashArray: '10, 5'
            }).addTo(this.data.map);

            this.data.customPolyline = polyline;

            // Add numbered markers for route points
            points.forEach((point, index) => {
                if (index > 0 && index < points.length - 1) { // Skip start and end
                    const pointMarker = L.circleMarker(point, {
                        radius: 8,
                        color: '#8b5cf6',
                        fillColor: '#8b5cf6',
                        fillOpacity: 0.8,
                        weight: 2
                    }).addTo(this.data.map);

                    pointMarker.bindPopup(`Route Point ${index}`);

                    if (!this.tempMarkers) this.tempMarkers = [];
                    this.tempMarkers.push(pointMarker);
                }
            });

            this.data.map.fitBounds(polyline.getBounds().pad(0.1));
            return true;
        }, 'visualize custom route');
    }

    buildOptimizedRouteCoordinates() {
        const coordinates = [this.depotCoordinates];

        if (this.data.optimizationResult && this.data.optimizationResult.route_steps) {
            this.data.optimizationResult.route_steps.forEach(step => {
                if (step.coordinates && Array.isArray(step.coordinates) && step.coordinates.length === 2) {
                    coordinates.push(step.coordinates);
                }
            });
        } else {
            this.data.orders.forEach(order => {
                if (order.coordinates && Array.isArray(order.coordinates) && order.coordinates.length === 2) {
                    coordinates.push(order.coordinates);
                }
            });
        }

        coordinates.push(this.depotCoordinates);
        return coordinates;
    }

    onRoutesFound(routes) {
        if (routes && routes.length > 0) {
            const route = routes[0];
            console.log('Route details:', {
                distance: `${(route.summary.totalDistance / 1000).toFixed(1)} km`,
                time: `${Math.round(route.summary.totalTime / 60)} minutes`,
                waypoints: route.waypoints.length
            });

            if (this.data.optimizationResult) {
                this.data.optimizationResult.actual_route_distance = Math.round(route.summary.totalDistance / 1000);
                this.data.optimizationResult.actual_route_time = Math.round(route.summary.totalTime / 60);
            }

            this.addRouteStepMarkers(route);
        }
    }

    onRoutingError(error) {
        console.error('Leaflet Routing Machine error:', error);
        const coordinates = this.buildOptimizedRouteCoordinates();
        this.fallbackRouteVisualization(coordinates);
    }

    addRouteStepMarkers(route) {
        return this.safeMapOperation(() => {
            route.waypoints.forEach((waypoint, index) => {
                if (index === 0 || index === route.waypoints.length - 1) {
                    return;
                }

                const stepNumber = index;
                const stepIcon = L.divIcon({
                    className: 'route-step-marker',
                    html: `<div class="route-step-number" style="background-color: #667eea; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 12px; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">${stepNumber}</div>`,
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                });

                const stepMarker = L.marker([waypoint.latLng.lat, waypoint.latLng.lng], {
                    icon: stepIcon,
                    zIndexOffset: 1000
                }).addTo(this.data.map);

                if (!this.data.routeStepMarkers) {
                    this.data.routeStepMarkers = [];
                }
                this.data.routeStepMarkers.push(stepMarker);
            });

            return true;
        }, 'add route step markers');
    }

    fallbackRouteVisualization(coordinates) {
        return this.safeMapOperation(() => {
            console.log('Using fallback route visualization...');

            const polyline = L.polyline(coordinates, {
                color: '#ff6b6b',
                weight: 4,
                opacity: 0.7,
                dashArray: '5, 10'
            }).addTo(this.data.map);

            this.data.fallbackPolyline = polyline;
            this.data.map.fitBounds(polyline.getBounds().pad(0.1));

            return true;
        }, 'fallback route visualization');
    }

    fitMapToRoute() {
        return this.safeMapOperation(() => {
            try {
                if (this.data.routingControl && this.data.routingControl._routes && this.data.routingControl._routes.length > 0) {
                    const route = this.data.routingControl._routes[0];
                    if (route.bounds) {
                        this.data.map.fitBounds(route.bounds, { padding: [20, 20] });
                        return true;
                    }
                }

                if (this.data.customPolyline) {
                    this.data.map.fitBounds(this.data.customPolyline.getBounds().pad(0.1));
                    return true;
                }

                if (this.data.markers.length > 0) {
                    const group = new L.featureGroup([...this.data.markers]);
                    this.data.map.fitBounds(group.getBounds().pad(0.1));
                    return true;
                }
            } catch (error) {
                console.error('Failed to fit map to route:', error);
                return false;
            }

            return false;
        }, 'fit map to route');
    }

    refreshMarkers() {
        if (this.isMapReady && this.data.mapInitialized) {
            this.addOrderMarkers();
        } else {
            console.warn('Map not ready for marker refresh');
        }
    }

    focusOnOrder(orderId) {
        return this.safeMapOperation(() => {
            const order = this.data.orders.find(o => o.id === orderId);
            if (order && order.coordinates && Array.isArray(order.coordinates) && order.coordinates.length === 2) {
                this.data.map.setView(order.coordinates, 15);

                const markerIndex = this.data.orders.findIndex(o => o.id === orderId);
                if (markerIndex >= 0 && this.data.markers[markerIndex]) {
                    this.data.markers[markerIndex].openPopup();
                }
                return true;
            }
            return false;
        }, 'focus on order');
    }

    getMapBounds() {
        return this.safeMapOperation(() => {
            return this.data.map.getBounds();
        }, 'get map bounds') || null;
    }
}