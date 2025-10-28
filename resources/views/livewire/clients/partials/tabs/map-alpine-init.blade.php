@script
<script>
    Alpine.data('clientLocationMap', () => ({
        map: null,
        marker: null,
        loading: true,
        hasCoordinates: @json($client->hasCoordinates()),
        coordinates: @json($client->hasCoordinates() ? $client->coordinates : null),
        address: @json($client->full_address),
        clientName: @json($client->full_name),
        phone: @json($client->phone_number),

        init() {
            this.$nextTick(() => {
                this.initMap();
            });

            this.$wire.on('addressGeocoded', () => {
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            });
        },

        initMap() {
            try {
                const defaultCenter = [52.237049, 21.017532];
                const mapCenter = this.hasCoordinates ? this.coordinates : defaultCenter;
                const zoomLevel = this.hasCoordinates ? 16 : 6;

                this.map = L.map(this.$refs.mapContainer, {
                    zoomControl: false
                }).setView(mapCenter, zoomLevel);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                    maxZoom: 19
                }).addTo(this.map);

                L.control.zoom({
                    position: 'bottomright'
                }).addTo(this.map);

                if (this.hasCoordinates) {
                    this.addLocationMarker();
                } else {
                    this.highlightPoland();
                }

                this.loading = false;

            } catch (error) {
                console.error('Error initializing map:', error);
                this.loading = false;
            }
        },

        addLocationMarker() {
            if (!this.coordinates || !this.map) return;

            const createLocationIcon = () => {
                return L.divIcon({
                    className: 'location-marker-container',
                    html: `
                    <div class="location-marker-wrapper">
                        <div class="location-pulse"></div>
                        <div class="location-marker">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                `,
                    iconSize: [40, 40],
                    iconAnchor: [20, 40]
                });
            };

            this.marker = L.marker(this.coordinates, {
                icon: createLocationIcon(),
                zIndexOffset: 1000
            }).addTo(this.map);

            const tooltipContent = `
                <div class="address-tooltip-content">
                    <div class="tooltip-header">
                        <span>${this.clientName}</span>
                    </div>
                    <div class="tooltip-address">${this.address}</div>
                    <div class="tooltip-contact">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        ${this.phone}
                    </div>
                </div>
            `;

            this.marker.bindTooltip(tooltipContent, {
                direction: 'top',
                offset: [0, -40],
                sticky: true,
                className: 'custom-address-tooltip'
            });

            L.circle(this.coordinates, {
                color: '#3b82f6',
                fillColor: '#3b82f6',
                fillOpacity: 0.1,
                radius: 200,
                weight: 2
            }).addTo(this.map);

            this.marker.on('click', () => {
                this.showDetailedPopup();
            });
        },

        showDetailedPopup() {
            if (!this.marker) return;

            const popupContent = `
            <div class="location-popup">
                <div class="popup-header">
                    <div class="client-info">
                        <h3>${this.clientName}</h3>
                        <div class="client-meta">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-4 8V9"></path>
                            </svg>
                            Client Location
                        </div>
                    </div>
                </div>
                <div class="popup-body">
                    <div class="address-section">
                        <div class="section-label">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            </svg>
                            Address
                        </div>
                        <div class="section-content">${this.address}</div>
                    </div>
                    <div class="contact-section">
                        <div class="section-label">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            Phone
                        </div>
                        <div class="section-content">
                            <a href="tel:${this.phone}" class="phone-link">${this.phone}</a>
                        </div>
                    </div>
                </div>
                <div class="popup-actions">
                    <a href="https://www.google.com/maps/dir/?api=1&destination=${this.coordinates[0]},${this.coordinates[1]}"
                       target="_blank" class="directions-btn">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 9m0 8V9m0 0V7"></path>
                        </svg>
                        Get Directions
                    </a>
                </div>
            </div>
        `;

            this.marker.bindPopup(popupContent, {
                maxWidth: 350,
                className: 'custom-location-popup'
            }).openPopup();
        },

        highlightPoland() {
            const polandBounds = [
                [49.0, 14.0],
                [49.0, 24.5],
                [54.8, 24.5],
                [54.8, 14.0],
                [49.0, 14.0]
            ];

            L.polygon(polandBounds, {
                color: '#3b82f6',
                weight: 2,
                opacity: 0.8,
                fillColor: '#3b82f6',
                fillOpacity: 0.1,
                dashArray: '5, 5'
            }).addTo(this.map);

            L.marker([52.237049, 19.5], {
                icon: L.divIcon({
                    className: 'poland-label',
                    html: `
                    <div class="poland-label-content">
                        <div class="poland-title">Polska</div>
                        <div class="poland-subtitle">Client Search Area</div>
                    </div>
                `,
                    iconSize: [180, 60],
                    iconAnchor: [90, 30]
                })
            }).addTo(this.map);
        },

        centerMap() {
            if (this.map && this.hasCoordinates && this.coordinates) {
                this.map.setView(this.coordinates, 16);
                if (this.marker) {
                    setTimeout(() => {
                        this.marker.openPopup();
                    }, 500);
                }
            }
        },

        toggleFullscreen() {
            const mapElement = this.$refs.mapContainer;

            if (!document.fullscreenElement) {
                mapElement.requestFullscreen().then(() => {
                    mapElement.style.height = '100vh';
                    setTimeout(() => {
                        if (this.map) {
                            this.map.invalidateSize();
                        }
                    }, 100);
                });
            } else {
                document.exitFullscreen().then(() => {
                    mapElement.style.height = '24rem';
                    setTimeout(() => {
                        if (this.map) {
                            this.map.invalidateSize();
                        }
                    }, 100);
                });
            }
        }
    }));
</script>
@endscript