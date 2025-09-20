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
        this.isMapReady = false;
        this.routePolyline = null;
    }

    decodePolyline(str, precision) {
        let index = 0,
            lat = 0,
            lng = 0,
            coordinates = [],
            shift = 0,
            result = 0,
            byte = null,
            latitude_change,
            longitude_change,
            factor = Math.pow(10, precision || 5);

        while (index < str.length) {
            byte = null;
            shift = 0;
            result = 0;
            do {
                byte = str.charCodeAt(index++) - 63;
                result |= (byte & 0x1f) << shift;
                shift += 5;
            } while (byte >= 0x20);

            latitude_change = ((result & 1) ? ~(result >> 1) : (result >> 1));
            lat += latitude_change;
            shift = 0;
            result = 0;
            do {
                byte = str.charCodeAt(index++) - 63;
                result |= (byte & 0x1f) << shift;te
                shift += 5;
            } while (byte >= 0x20);
            longitude_change = ((result & 1) ? ~(result >> 1) : (result >> 1));
            lng += longitude_change;
            coordinates.push([lat / factor, lng / factor]);
        }
        return coordinates;
    }


    init() {
        if (this.data.mapInitialized) return;
        try {
            this.data.map = L.map('map', { center: [52.0, 19.0], zoom: 6, zoomControl: true, attributionControl: true });
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap contributors' }).addTo(this.data.map);
            this.data.map.whenReady(() => {
                this.isMapReady = true;
                this.addDepotMarker();
                this.addOrderMarkers();
                this.data.mapInitialized = true;
            });
        } catch (error) {
            console.error('Map initialization failed:', error);
        }
    }

    safeMapOperation(operation) {
        if (!this.data.map || !this.isMapReady) {
            return false;
        }
        try {
            return operation();
        } catch (error) {
            console.error(`Error during map operation:`, error);
            return false;
        }
    }

    enableManualEdit() {
        return this.safeMapOperation(() => {
            this.editMode = true;
            this.data.markers.forEach((marker, index) => {
                if (marker && marker.dragging) {
                    marker.dragging.enable();
                    marker.off('dragend').on('dragend', (e) => {
                        const newLatLng = e.target.getLatLng();
                        if (this.data.orders[index]) {
                            this.data.orders[index].coordinates = [newLatLng.lat, newLatLng.lng];
                            this.data.refreshOptimizedRoute();
                        }
                    });
                }
            });
            this.data.map.off('click').on('click', this.onMapClick.bind(this));
            this.data.map.getContainer().style.cursor = 'crosshair';
        });
    }

    disableManualEdit() {
        return this.safeMapOperation(() => {
            this.editMode = false;
            this.data.markers.forEach(marker => {
                if (marker && marker.dragging) marker.dragging.disable();
            });
            this.data.map.off('click', this.onMapClick);
            this.data.map.getContainer().style.cursor = '';
        });
    }

    onMapClick(e) {
        if (this.editMode && e.latlng) {
            const { lat, lng } = e.latlng;
            if (isNaN(lat) || isNaN(lng)) return;
            const address = `Custom Location (${lat.toFixed(4)}, ${lng.toFixed(4)})`;
            if (confirm(`Add custom stop at ${address}?`)) {
                this.data.addCustomStop(lat, lng, address);
            }
        }
    }

    addDepotMarker() {
        this.safeMapOperation(() => {
            const depotIcon = L.divIcon({
                html: `<div style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 50%; background-color: #1e293b; font-size: 16px; color: white; border: 2px solid white; box-shadow: 0 1px 4px rgba(0,0,0,0.4);">
                           <i class="fas fa-warehouse"></i>
                       </div>`,
                className: '',
                iconSize: [36, 36],
                iconAnchor: [18, 18]
            });
            L.marker(this.depotCoordinates, { icon: depotIcon, zIndexOffset: 1000 }).addTo(this.data.map).bindPopup(`<strong>Main Depot</strong>`);
        });
    }

    addOrderMarkers() {
        this.safeMapOperation(() => {
            this.clearOrderMarkers();
            this.data.orders.forEach((order, index) => {
                if (!order.coordinates || !Array.isArray(order.coordinates) || order.coordinates.length !== 2) return;
                const orderIcon = L.divIcon({
                    html: `<div style="display: flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; background-color: ${this.priorityColors[order.priority] || '#6b7280'}; color: white; font-size: 14px; font-weight: bold; border: 2px solid white; box-shadow: 0 1px 3px rgba(0,0,0,0.3);">
                               ${index + 1}
                           </div>`,
                    className: '',
                    iconSize: [28, 28],
                    iconAnchor: [14, 14]
                });
                const marker = L.marker(order.coordinates, { icon: orderIcon, draggable: false })
                    .addTo(this.data.map)
                    .bindPopup(this.createOrderPopup(order, index + 1));
                this.data.markers.push(marker);
            });
            if (this.editMode) this.enableManualEdit();
        });
    }

    createOrderPopup(order, orderNumber) {
        return `<div class="p-1">
                    <div class="font-bold text-base">Order #${order.id} (Stop ${orderNumber})</div>
                    <div class="text-sm text-slate-600">${order.client_name}</div>
                    <div class="text-xs text-slate-500">${order.address}</div>
                </div>`;
    }

    clearOrderMarkers() {
        if (this.data.markers) {
            this.data.markers.forEach(marker => marker.remove());
        }
        this.data.markers = [];
    }

    clearRoute() {
        this.safeMapOperation(() => {
            if (this.routePolyline) {
                this.routePolyline.remove();
                this.routePolyline = null;
            }
        });
    }

    visualizeOptimizedRoute() {
        return this.safeMapOperation(() => {
            this.clearRoute();
            const { optimizationResult } = this.data;

            if (!optimizationResult || !optimizationResult.geometry) {
                return false;
            }

            const coordinates = this.decodePolyline(optimizationResult.geometry);
            this.routePolyline = L.polyline(coordinates, {
                color: '#38bdf8',
                weight: 5,
                opacity: 0.9
            }).addTo(this.data.map);

            this.fitMapToRoute();
            return true;
        });
    }

    fitMapToRoute() {
        this.safeMapOperation(() => {
            if (this.routePolyline) {
                this.data.map.fitBounds(this.routePolyline.getBounds(), { padding: [40, 40] });
            } else if (this.data.markers.length > 0) {
                const group = new L.featureGroup(this.data.markers);
                this.data.map.fitBounds(group.getBounds().pad(0.2));
            }
        });
    }

    refreshMarkers() {
        this.addOrderMarkers();
    }

    focusOnOrder(orderId) {
        this.safeMapOperation(() => {
            const orderIndex = this.data.orders.findIndex(o => o.id === orderId);
            if (orderIndex > -1 && this.data.markers[orderIndex]) {
                const marker = this.data.markers[orderIndex];
                this.data.map.setView(marker.getLatLng(), 15);
                marker.openPopup();
            }
        });
    }
}