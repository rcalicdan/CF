class RouteDataService {
    constructor() {
        this.baseUrl = '/api/route-data';
        this.cache = new Map();
        this.cacheTimeout = 5 * 60 * 1000;
    }

    /**
     * Get authentication headers
     */
    getHeaders() {
        const token = localStorage.getItem('auth_token') ||
            document.querySelector('meta[name="token"]')?.content;

        return {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': token ? `Bearer ${token}` : '',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        };
    }

    /**
     * Generic API request method
     */
    async request(url, options = {}) {
        const config = {
            headers: this.getHeaders(),
            ...options
        };

        try {
            const response = await fetch(url, config);

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'API request failed');
            }

            return data;
        } catch (error) {
            console.error('API Request failed:', error);
            throw error;
        }
    }

    /**
     * Get cached data or fetch from API
     */
    async getCachedData(key, fetchFn) {
        const cached = this.cache.get(key);
        const now = Date.now();

        if (cached && (now - cached.timestamp) < this.cacheTimeout) {
            console.log(`Cache hit for key: ${key}`);
            return cached.data;
        }

        console.log(`Cache miss for key: ${key}, fetching from API...`);
        const data = await fetchFn();
        this.cache.set(key, {
            data,
            timestamp: now
        });

        return data;
    }

    /**
     * Fetch all drivers
     */
    async getDrivers() {
        return this.getCachedData('drivers', async () => {
            const response = await this.request(`${this.baseUrl}/drivers`);
            return response.data;
        });
    }

    /**
     * Fetch orders for specific driver and date
     */
    async getOrdersForDriverAndDate(driverId, date) {
        const cacheKey = `orders_${driverId}_${date}`;

        const data = await this.getCachedData(cacheKey, async () => {
            const url = `${this.baseUrl}/orders?driver_id=${driverId}&date=${date}`;
            const response = await this.request(url);
            return response.data;
        });

        return data;
    }

    /**
     * Fetch all orders for date range
     */
    async getAllOrdersForDateRange(startDate = null, endDate = null) {
        let url = `${this.baseUrl}/all-orders`;
        const params = new URLSearchParams();

        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);

        if (params.toString()) {
            url += `?${params.toString()}`;
        }

        const cacheKey = `all_orders_${params.toString()}`;

        return this.getCachedData(cacheKey, async () => {
            const response = await this.request(url);
            return response.data;
        });
    }

    /**
     * Get route statistics
     */
    async getRouteStatistics(driverId = null, date = null, startDate = null, endDate = null) {
        const params = new URLSearchParams();
        if (driverId) params.append('driver_id', driverId);
        if (date) params.append('date', date);
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);

        const url = `${this.baseUrl}/statistics?${params.toString()}`;
        const response = await this.request(url);
        return response.data;
    }

    /**
     * Trigger geocoding for clients without coordinates
     */
    async triggerGeocoding() {
        const response = await this.request(`${this.baseUrl}/geocode`, {
            method: 'POST'
        });
        return response.data;
    }

    /**
     * Clear cache entries matching pattern
     */
    clearCacheByPattern(pattern) {
        for (const key of this.cache.keys()) {
            if (key.includes(pattern)) {
                this.cache.delete(key);
                console.log(`Cleared cache for key: ${key}`);
            }
        }
    }

    /**
     * Clear specific cache entry
     */
    clearCache(key = null) {
        if (key) {
            this.cache.delete(key);
            console.log(`Cleared cache for key: ${key}`);
        } else {
            this.cache.clear();
            console.log('Cleared all cache');
        }
    }

    /**
     * Get cache info for debugging
     */
    getCacheInfo() {
        const cacheEntries = [];
        for (const [key, value] of this.cache.entries()) {
            cacheEntries.push({
                key,
                timestamp: new Date(value.timestamp).toISOString(),
                age: Date.now() - value.timestamp,
                dataSize: JSON.stringify(value.data).length
            });
        }
        return cacheEntries;
    }
}

window.RouteDataService = RouteDataService;