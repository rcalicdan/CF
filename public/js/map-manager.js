class MapManager {
    constructor(data) {
        this.data = data;
        this.priorityColors = {
            'high': '#ef4444',
            'medium': '#f59e0b',
            'low': '#10b981'
        };
        this.depotCoordinates = [52.2297, 21.0122]; // Warsaw

        // Add new properties for manual editing
        this.editMode = false;
        this.drawingMode = false;
        this.customPolyline = null;
        this.customRoutePoints = [];
        this.tempMarkers = [];
        this.drawingInstructions = null;
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
                this.addDepotMarker();
                this.addOrderMarkers();
                this.data.mapInitialized = true;

                setTimeout(() => {
                    this.data.map.invalidateSize();
                }, 200);
            });

        } catch (error) {
            console.error('Map initialization failed:', error);
        }
    }

    // Enable manual editing mode
    enableManualEdit() {
        this.editMode = true;
        console.log('Manual edit mode enabled');

        // Make markers draggable
        this.data.markers.forEach((marker, index) => {
            marker.dragging.enable();
            marker.on('dragend', (e) => {
                const newLatLng = e.target.getLatLng();
                this.data.orders[index].coordinates = [newLatLng.lat, newLatLng.lng];
                this.data.refreshOptimizedRoute();
                console.log(`Order ${this.data.orders[index].id} moved to:`, [newLatLng.lat, newLatLng.lng]);
            });
        });

        // Add click handler for adding custom stops
        this.data.map.on('click', this.onMapClick.bind(this));

        // Change cursor
        this.data.map.getContainer().style.cursor = 'crosshair';

        // Add edit mode indicator
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
        this.editModeIndicator.addTo(this.data.map);
    }

    // Disable manual editing mode
    disableManualEdit() {
        this.editMode = false;
        console.log('Manual edit mode disabled');

        // Disable marker dragging
        this.data.markers.forEach(marker => {
            marker.dragging.disable();
            marker.off('dragend');
            marker.off('contextmenu');
        });

        // Remove click handler
        this.data.map.off('click', this.onMapClick);

        // Reset cursor
        this.data.map.getContainer().style.cursor = '';

        // Remove edit mode indicator
        if (this.editModeIndicator) {
            this.data.map.removeControl(this.editModeIndicator);
        }
    }

    // Handle map clicks for adding custom stops
    onMapClick(e) {
        if (this.editMode && !this.drawingMode) {
            const { lat, lng } = e.latlng;

            // Use reverse geocoding to get address (simplified example)
            const address = `Custom Location (${lat.toFixed(4)}, ${lng.toFixed(4)})`;

            if (confirm(`Add custom stop at ${address}?`)) {
                this.data.addCustomStop(lat, lng, address);
            }
        }
    }

    // Enable route drawing mode
    enableRouteDrawing() {
        this.drawingMode = true;
        this.customRoutePoints = [];
        console.log('Route drawing mode enabled');

        // Clear existing route
        this.clearRoute();

        // Add click handler for route drawing
        this.data.map.on('click', this.onRouteDrawClick.bind(this));

        // Change cursor and add instructions
        this.data.map.getContainer().style.cursor = 'crosshair';

        // Show drawing instructions
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
        this.drawingInstructions.addTo(this.data.map);

        // Add right-click handler to finish drawing
        this.data.map.on('contextmenu', this.finishRouteDrawing.bind(this));
    }

    // Handle clicks during route drawing
    onRouteDrawClick(e) {
        if (this.drawingMode) {
            const { lat, lng } = e.latlng;
            this.customRoutePoints.push([lat, lng]);

            // Update point counter
            const pointCounter = document.getElementById('point-count');
            if (pointCounter) {
                pointCounter.textContent = this.customRoutePoints.length;
            }

            // Add temporary marker
            const tempMarker = L.circleMarker([lat, lng], {
                radius: 6,
                color: '#8b5cf6',
                fillColor: '#8b5cf6',
                fillOpacity: 0.8,
                weight: 2
            }).addTo(this.data.map);

            if (!this.tempMarkers) this.tempMarkers = [];
            this.tempMarkers.push(tempMarker);

            // Draw line if we have more than one point
            if (this.customRoutePoints.length > 1) {
                if (this.customPolyline) {
                    this.data.map.removeLayer(this.customPolyline);
                }

                this.customPolyline = L.polyline(this.customRoutePoints, {
                    color: '#8b5cf6',
                    weight: 4,
                    opacity: 0.8,
                    dashArray: '10, 5'
                }).addTo(this.data.map);
            }
        }
    }

    // Finish route drawing
    finishRouteDrawing(e) {
        if (this.drawingMode) {
            e.originalEvent.preventDefault();
            this.drawingMode = false;

            // Remove event handlers
            this.data.map.off('click', this.onRouteDrawClick);
            this.data.map.off('contextmenu', this.finishRouteDrawing);

            // Remove instructions
            if (this.drawingInstructions) {
                this.data.map.removeControl(this.drawingInstructions);
                this.drawingInstructions = null;
            }

            // Reset cursor
            this.data.map.getContainer().style.cursor = '';

            console.log('Custom route drawn with', this.customRoutePoints.length, 'points');

            // Save custom route points to data
            this.data.customRoutePoints = this.customRoutePoints.slice();

            alert(`Custom route created with ${this.customRoutePoints.length} points!`);
        }
    }

    // Disable route drawing mode
    disableRouteDrawing() {
        this.drawingMode = false;

        // Remove event handlers
        this.data.map.off('click', this.onRouteDrawClick);
        this.data.map.off('contextmenu', this.finishRouteDrawing);

        // Remove instructions
        if (this.drawingInstructions) {
            this.data.map.removeControl(this.drawingInstructions);
            this.drawingInstructions = null;
        }

        // Reset cursor
        this.data.map.getContainer().style.cursor = '';
    }

    // Clear custom route
    clearCustomRoute() {
        if (this.customPolyline) {
            this.data.map.removeLayer(this.customPolyline);
            this.customPolyline = null;
        }

        if (this.tempMarkers) {
            this.tempMarkers.forEach(marker => {
                if (this.data.map.hasLayer(marker)) {
                    this.data.map.removeLayer(marker);
                }
            });
            this.tempMarkers = [];
        }

        this.customRoutePoints = [];
        this.data.customRoutePoints = [];
    }

    addDepotMarker() {
        try {
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
        } catch (error) {
            console.error('Failed to add depot marker:', error);
        }
    }

    // Enhanced marker creation with edit capabilities
    addOrderMarkers() {
        try {
            this.clearOrderMarkers();

            console.log(`Adding ${this.data.orders.length} order markers...`);

            this.data.orders.forEach((order, index) => {
                const orderIcon = L.divIcon({
                    className: 'custom-marker',
                    html: `<div class="custom-marker ${order.isCustom ? 'custom-stop' : ''}" 
                          style="background-color: ${this.priorityColors[order.priority]}">
                          ${index + 1}
                          </div>`,
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                });

                const marker = L.marker([order.coordinates[0], order.coordinates[1]], {
                    icon: orderIcon,
                    title: `Order #${order.id} - ${order.client_name}`,
                    draggable: this.editMode
                })
                    .addTo(this.data.map)
                    .bindPopup(this.createOrderPopup(order, index + 1), {
                        maxWidth: 250,
                        className: 'custom-popup'
                    });

                // Add context menu for edit mode
                if (this.editMode) {
                    marker.on('contextmenu', (e) => {
                        e.originalEvent.preventDefault();
                        this.showMarkerContextMenu(e, order, index);
                    });

                    // Enable dragging if in edit mode
                    marker.dragging.enable();
                    marker.on('dragend', (e) => {
                        const newLatLng = e.target.getLatLng();
                        this.data.orders[index].coordinates = [newLatLng.lat, newLatLng.lng];
                        this.data.refreshOptimizedRoute();
                        console.log(`Order ${order.id} moved to:`, [newLatLng.lat, newLatLng.lng]);
                    });
                }

                this.data.markers.push(marker);
            });

            console.log(`Successfully added ${this.data.markers.length} markers`);
        } catch (error) {
            console.error('Failed to add order markers:', error);
        }
    }

    // Show context menu for markers
    showMarkerContextMenu(e, order, index) {
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
    }

    createOrderPopup(order, orderNumber) {
        return `
           <div class="p-2 min-w-[200px]">
               <div class="flex items-center justify-between mb-2">
                   <strong class="text-lg">Order #${order.id}</strong>
                   <div class="flex items-center gap-1">
                       <div class="w-6 h-6 rounded-full flex items-center justify-center text-white text-sm font-bold" 
                            style="background-color: ${this.priorityColors[order.priority]}">${orderNumber}</div>
                       ${order.isCustom ? '<div class="w-5 h-5 bg-purple-500 rounded-full flex items-center justify-center text-white text-xs"><i class="fas fa-star"></i></div>' : ''}
                   </div>
               </div>
               <div class="text-sm font-medium text-gray-800">${order.client_name}</div>
               <div class="text-sm text-gray-600 mb-2">${order.address}</div>
               <div class="flex items-center justify-between">
                   <div class="text-lg font-semibold text-green-600">zł${order.total_amount}</div>
                   <div class="text-xs uppercase font-medium px-2 py-1 rounded" 
                        style="background-color: ${this.priorityColors[order.priority]}20; color: ${this.priorityColors[order.priority]}">${order.priority} priority</div>
               </div>
               <div class="text-xs text-gray-500 mt-1">Status: ${order.status}</div>
               ${order.isCustom ? '<div class="text-xs text-purple-600 mt-1 font-medium">Custom Stop</div>' : ''}
           </div>
       `;
    }

    clearOrderMarkers() {
        this.data.markers.forEach(marker => {
            if (this.data.map.hasLayer(marker)) {
                this.data.map.removeLayer(marker);
            }
        });
        this.data.markers = [];
    }

    clearRoute() {
        if (this.data.routingControl) {
            this.data.map.removeControl(this.data.routingControl);
            this.data.routingControl = null;
        }

        if (this.data.routeStepMarkers) {
            this.data.routeStepMarkers.forEach(marker => {
                if (this.data.map.hasLayer(marker)) {
                    this.data.map.removeLayer(marker);
                }
            });
            this.data.routeStepMarkers = [];
        }

        if (this.data.fallbackPolyline) {
            if (this.data.map.hasLayer(this.data.fallbackPolyline)) {
                this.data.map.removeLayer(this.data.fallbackPolyline);
            }
            this.data.fallbackPolyline = null;
        }
    }

    visualizeOptimizedRoute() {
        if (!this.data.map || !this.data.mapInitialized) {
            console.warn('Map not initialized yet');
            return;
        }

        if (!this.data.optimizationResult && this.data.orders.length === 0) {
            console.warn('No optimization result or orders available');
            return;
        }

        console.log('Visualizing optimized route...');
        this.clearRoute();

        // Use custom route if available, otherwise use optimized route
        const routeCoordinates = this.data.customRoutePoints.length > 0
            ? this.data.customRoutePoints
            : this.buildOptimizedRouteCoordinates();

        try {
            // If we have custom route points, use them directly
            if (this.data.customRoutePoints.length > 0) {
                this.visualizeCustomRoute(this.data.customRoutePoints);
                return;
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

            // Fit map to route after a delay
            setTimeout(() => {
                this.fitMapToRoute();
            }, 1000);

            console.log('Route visualization completed');

        } catch (error) {
            console.error('Route visualization failed:', error);
            this.fallbackRouteVisualization(routeCoordinates);
        }
    }

    // Visualize custom drawn route
    visualizeCustomRoute(points) {
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
    }

    buildOptimizedRouteCoordinates() {
        const coordinates = [this.depotCoordinates];

        if (this.data.optimizationResult && this.data.optimizationResult.route_steps) {
            this.data.optimizationResult.route_steps.forEach(step => {
                if (step.coordinates) {
                    coordinates.push(step.coordinates);
                }
            });
        } else {
            this.data.orders.forEach(order => {
                coordinates.push(order.coordinates);
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
    }

    fallbackRouteVisualization(coordinates) {
        console.log('Using fallback route visualization...');

        const polyline = L.polyline(coordinates, {
            color: '#ff6b6b',
            weight: 4,
            opacity: 0.7,
            dashArray: '5, 10'
        }).addTo(this.data.map);

        this.data.fallbackPolyline = polyline;
        this.data.map.fitBounds(polyline.getBounds().pad(0.1));
    }

    fitMapToRoute() {
        try {
            if (this.data.routingControl && this.data.routingControl._routes && this.data.routingControl._routes.length > 0) {
                const route = this.data.routingControl._routes[0];
                if (route.bounds) {
                    this.data.map.fitBounds(route.bounds, { padding: [20, 20] });
                    return;
                }
            }

            if (this.data.customPolyline) {
                this.data.map.fitBounds(this.data.customPolyline.getBounds().pad(0.1));
                return;
            }

            if (this.data.markers.length > 0) {
                const group = new L.featureGroup([...this.data.markers]);
                this.data.map.fitBounds(group.getBounds().pad(0.1));
            }
        } catch (error) {
            console.error('Failed to fit map to route:', error);
        }
    }

    refreshMarkers() {
        if (this.data.mapInitialized) {
            this.addOrderMarkers();
        }
    }

    focusOnOrder(orderId) {
        const order = this.data.orders.find(o => o.id === orderId);
        if (order && this.data.map) {
            this.data.map.setView(order.coordinates, 15);

            const markerIndex = this.data.orders.findIndex(o => o.id === orderId);
            if (markerIndex >= 0 && this.data.markers[markerIndex]) {
                this.data.markers[markerIndex].openPopup();
            }
        }
    }

    getMapBounds() {
        return this.data.map ? this.data.map.getBounds() : null;
    }
}