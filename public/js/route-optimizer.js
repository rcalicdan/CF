class RouteOptimizerData {
    constructor() {
        this.drivers = [
            {
                id: 1,
                user_id: 101,
                full_name: "Marek Kowalski",
                license_number: "WAW123456",
                vehicle_details: "Ford Transit - WX 1234A",
                phone_number: "+48 123 456 789"
            },
            {
                id: 2,
                user_id: 102,
                full_name: "Anna Nowak",
                license_number: "KRK789012",
                vehicle_details: "Mercedes Sprinter - KR 5678B",
                phone_number: "+48 987 654 321"
            },
            {
                id: 3,
                user_id: 103,
                full_name: "Piotr Wiśniewski",
                license_number: "GDA345678",
                vehicle_details: "Iveco Daily - GD 9012C",
                phone_number: "+48 555 444 333"
            }
        ];

        this.allOrders = {
            '2025-09-06': [
                {
                    id: 1001,
                    client_name: "Jan Kowalczyk",
                    address: "Warszawa, ul. Krakowskie Przedmieście 5",
                    coordinates: [52.2370, 21.0170],
                    total_amount: 340,
                    status: "pending",
                    priority: "high",
                    delivery_date: "2025-09-06"
                },
                {
                    id: 1002,
                    client_name: "Maria Szymańska",
                    address: "Warszawa, ul. Nowy Świat 15",
                    coordinates: [52.2297, 21.0122],
                    total_amount: 580,
                    status: "pending",
                    priority: "medium",
                    delivery_date: "2025-09-06"
                },
                {
                    id: 1003,
                    client_name: "Andrzej Duda",
                    address: "Warszawa, ul. Marszałkowska 100",
                    coordinates: [52.2319, 21.0067],
                    total_amount: 750,
                    status: "pending",
                    priority: "high",
                    delivery_date: "2025-09-06"
                },
                {
                    id: 1004,
                    client_name: "Katarzyna Lewandowska",
                    address: "Warszawa, ul. Aleje Jerozolimskie 65",
                    coordinates: [52.2244, 21.0067],
                    total_amount: 420,
                    status: "pending",
                    priority: "low",
                    delivery_date: "2025-09-06"
                }
            ],
            '2025-09-07': [
                {
                    id: 2001,
                    client_name: "Tomasz Zieliński",
                    address: "Warszawa, ul. Puławska 15",
                    coordinates: [52.2096, 21.0252],
                    total_amount: 680,
                    status: "pending",
                    priority: "medium",
                    delivery_date: "2025-09-07"
                },
                {
                    id: 2002,
                    client_name: "Barbara Kowalska",
                    address: "Warszawa, ul. Mokotowska 50",
                    coordinates: [52.2180, 21.0155],
                    total_amount: 920,
                    status: "pending",
                    priority: "high",
                    delivery_date: "2025-09-07"
                },
                {
                    id: 2003,
                    client_name: "Michał Nowak",
                    address: "Warszawa, ul. Złota 44",
                    coordinates: [52.2298, 21.0067],
                    total_amount: 365,
                    status: "pending",
                    priority: "low",
                    delivery_date: "2025-09-07"
                }
            ],
            '2025-09-08': [
                {
                    id: 3001,
                    client_name: "Agnieszka Wiśniewska",
                    address: "Warszawa, ul. Żurawia 20",
                    coordinates: [52.2340, 21.0089],
                    total_amount: 1200,
                    status: "pending",
                    priority: "high",
                    delivery_date: "2025-09-08"
                },
                {
                    id: 3002,
                    client_name: "Robert Krawczyk",
                    address: "Warszawa, ul. Bracka 25",
                    coordinates: [52.2287, 21.0089],
                    total_amount: 480,
                    status: "pending",
                    priority: "medium",
                    delivery_date: "2025-09-08"
                }
            ]
        };

        this.selectedDate = this.getTodayDate();
        this.orders = this.getOrdersForDate(this.selectedDate);

        this.selectedDriver = null;
        this.loading = false;
        this.optimizationResult = null;
        this.optimizationError = null;
        this.showRouteSummary = false;
        this.map = null;
        this.markers = [];
        this.routingControl = null;
        this.mapInitialized = false;
    }

    getTodayDate() {
        const today = new Date();
        return today.toISOString().split('T')[0];
    }

    getOrdersForDate(date) {
        return this.allOrders[date] || [];
    }

    setSelectedDate(date) {
        console.log('Setting selected date to:', date);
        this.selectedDate = date;
        this.orders = this.getOrdersForDate(date);

        // Reset optimization when date changes
        this.optimizationResult = null;
        this.optimizationError = null;
        this.showRouteSummary = false;

        // Refresh map markers if map is initialized
        if (this.mapInitialized && window.mapManager) {
            window.mapManager.refreshMarkers();
            window.mapManager.clearRoute();
        }

        console.log(`Loaded ${this.orders.length} orders for ${date}`);
    }

    get availableDates() {
        return Object.keys(this.allOrders).sort();
    }

    get totalOrders() {
        return this.orders.length;
    }

    get totalValue() {
        return this.orders.reduce((sum, order) => sum + order.total_amount, 0);
    }

    get pendingOrders() {
        return this.orders.filter(order => order.status === 'pending');
    }

    get highPriorityOrders() {
        return this.orders.filter(order => order.priority === 'high');
    }

    get mediumPriorityOrders() {
        return this.orders.filter(order => order.priority === 'medium');
    }

    get lowPriorityOrders() {
        return this.orders.filter(order => order.priority === 'low');
    }

    get formattedSelectedDate() {
        const date = new Date(this.selectedDate + 'T00:00:00');
        return date.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    get dateStatus() {
        const today = this.getTodayDate();
        if (this.selectedDate === today) return 'today';
        if (this.selectedDate < today) return 'past';
        return 'future';
    }
}