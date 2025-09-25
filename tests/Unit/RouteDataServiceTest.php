<?php
// tests/Unit/Services/RouteDataServiceTest.php

use App\ActionService\RouteDataService;
use App\Models\Driver;
use App\Models\Order;
use App\Models\Client;
use App\Models\RouteOptimization;
use App\Models\User;
use Illuminate\Support\Collection;
use Carbon\Carbon;

beforeEach(function () {
    $this->service = new RouteDataService();
    
    $this->user = User::factory()->create([
        'full_name' => 'John Driver',
        'phone_number' => '123456789'
    ]);
    
    $this->driver = Driver::factory()->create([
        'user_id' => $this->user->id,
        'license_number' => 'DL123456',
        'vehicle_details' => 'Ford Transit Van',
        'phone_number' => '987654321'
    ]);

    $this->client = Client::factory()->create([
        'first_name' => 'Jane',
        'last_name' => 'Customer',
        'street_name' => 'Main Street',
        'street_number' => '123',
        'city' => 'Warsaw',
        'postal_code' => '00-001',
        'latitude' => 52.2297,
        'longitude' => 21.0122
    ]);
});

it('gets all drivers with orders', function () {
    // Create completed order for the driver
    Order::factory()->create([
        'assigned_driver_id' => $this->driver->id,
        'client_id' => $this->client->id,
        'status' => 'completed',
        'total_amount' => 150.00
    ]);

    $result = $this->service->getAllDrivers();

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(1);
    
    $driverData = $result->first();
    expect($driverData['id'])->toBe($this->driver->id)
        ->and($driverData['full_name'])->toBe('John Driver')
        ->and($driverData['license_number'])->toBe('DL123456')
        ->and($driverData['vehicle_details'])->toBe('Ford Transit Van')
        ->and($driverData['phone_number'])->toBe('987654321');
});

it('gets orders for driver and date', function () {
    $scheduleDate = Carbon::today();
    
    $order = Order::factory()->create([
        'assigned_driver_id' => $this->driver->id,
        'client_id' => $this->client->id,
        'status' => 'completed',
        'total_amount' => 200.00,
        'schedule_date' => $scheduleDate,
        'is_complaint' => false
    ]);

    $result = $this->service->getOrdersForDriverAndDate(
        $this->driver->id, 
        $scheduleDate->format('Y-m-d')
    );

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(1);
    
    $orderData = $result->first();
    expect($orderData['id'])->toBe($order->id)
        ->and($orderData['driver_id'])->toBe($this->driver->id)
        ->and($orderData['client_name'])->toBe('Jane Customer')
        ->and($orderData['address'])->toBe('Main Street, 123, 00-001, Warsaw')
        ->and($orderData['total_amount'])->toBe(200.00)
        ->and($orderData['has_coordinates'])->toBeTrue()
        ->and($orderData['priority'])->toBe('medium'); // >= 500 would be high
});

it('calculates order priority correctly', function () {
    // High priority: complaint
    Order::factory()->create([
        'assigned_driver_id' => $this->driver->id,
        'client_id' => $this->client->id,
        'status' => 'completed',
        'total_amount' => 100.00,
        'is_complaint' => true,
        'schedule_date' => Carbon::now()
    ]);

    // High priority: high amount
    Order::factory()->create([
        'assigned_driver_id' => $this->driver->id,
        'client_id' => $this->client->id,
        'status' => 'completed',
        'total_amount' => 1200.00,
        'is_complaint' => false,
        'schedule_date' => Carbon::now()
    ]);

    // Medium priority: medium amount
    Order::factory()->create([
        'assigned_driver_id' => $this->driver->id,
        'client_id' => $this->client->id,
        'status' => 'completed',
        'total_amount' => 750.00,
        'is_complaint' => false,
        'schedule_date' => Carbon::now()->addDays(5)
    ]);

    $result = $this->service->getOrdersForDriverAndDate(
        $this->driver->id, 
        Carbon::now()->format('Y-m-d')
    );

    // Find orders by amount to verify priority
    $complaintData = $result->first(fn($order) => $order['total_amount'] == 100.00);
    $highAmountData = $result->first(fn($order) => $order['total_amount'] == 1200.00);

    expect($complaintData['priority'])->toBe('high')
        ->and($highAmountData['priority'])->toBe('high');
});

it('saves route optimization successfully', function () {
    $optimizationData = [
        'driver_id' => $this->driver->id,
        'optimization_date' => Carbon::today()->format('Y-m-d'),
        'optimization_result' => [
            'total_distance' => 45.5,
            'total_time' => 120,
            'savings' => 15.2,
            'route_steps' => [
                ['lat' => 52.2297, 'lng' => 21.0122, 'address' => 'Main Street 123']
            ]
        ],
        'order_sequence' => [1, 2, 3],
        'total_distance' => 45.5,
        'total_time' => 120,
        'estimated_fuel_cost' => 25.50,
        'carbon_footprint' => 12.3,
        'is_manual_edit' => false
    ];

    $result = $this->service->saveRouteOptimization($optimizationData);

    expect($result)->toBeInstanceOf(RouteOptimization::class)
        ->and($result->driver_id)->toBe($this->driver->id)
        ->and($result->total_distance)->toBe(45.5)
        ->and($result->total_time)->toBe(120)
        ->and($result->estimated_fuel_cost)->toBe(25.50)
        ->and($result->is_manual_edit)->toBeFalse()
        ->and($result->order_sequence)->toHaveCount(3);
});

it('gets route statistics accurately', function () {
    // Create multiple orders with different statuses and amounts
    Order::factory()->create([
        'assigned_driver_id' => $this->driver->id,
        'client_id' => $this->client->id,
        'status' => 'completed',
        'total_amount' => 300.00,
        'schedule_date' => Carbon::today()
    ]);

    Order::factory()->create([
        'assigned_driver_id' => $this->driver->id,
        'client_id' => $this->client->id,
        'status' => 'undelivered',
        'total_amount' => 150.00,
        'schedule_date' => Carbon::today(),
        'is_complaint' => true
    ]);

    $stats = $this->service->getRouteStatistics(
        $this->driver->id, 
        Carbon::today()->format('Y-m-d')
    );

    expect($stats)->toBeArray()
        ->and($stats['total_orders'])->toBe(2)
        ->and($stats['total_value'])->toBe(450.00)
        ->and($stats['orders_with_coordinates'])->toBe(2)
        ->and($stats['orders_without_coordinates'])->toBe(0)
        ->and($stats['average_order_value'])->toBe(225.00)
        ->and($stats['complaints_count'])->toBe(1)
        ->and($stats['status_breakdown'])->toHaveKey('completed')
        ->and($stats['status_breakdown'])->toHaveKey('undelivered')
        ->and($stats['status_breakdown']['completed']['count'])->toBe(1)
        ->and($stats['status_breakdown']['completed']['total_value'])->toBe(300.00);
});

it('gets dashboard summary correctly', function () {
    // Create orders for different time periods
    Order::factory()->create([
        'assigned_driver_id' => $this->driver->id,
        'client_id' => $this->client->id,
        'status' => 'pending',
        'total_amount' => 100.00,
        'schedule_date' => Carbon::today()
    ]);

    Order::factory()->create([
        'assigned_driver_id' => $this->driver->id,
        'client_id' => $this->client->id,
        'status' => 'completed',
        'total_amount' => 250.00,
        'schedule_date' => Carbon::now()->startOfWeek()
    ]);

    $summary = $this->service->getDashboardSummary();

    expect($summary)->toBeArray()
        ->and($summary)->toHaveKey('today')
        ->and($summary)->toHaveKey('this_week')
        ->and($summary)->toHaveKey('this_month')
        ->and($summary['today']['orders_count'])->toBe(1)
        ->and($summary['today']['orders_value'])->toBe(100.00)
        ->and($summary['today']['drivers_active'])->toBe(1)
        ->and($summary['total_drivers'])->toBe(1)
        ->and($summary['pending_orders'])->toBe(1);
});

it('gets driver route optimization stats correctly', function () {
    // Create route optimizations
    RouteOptimization::factory()->create([
        'driver_id' => $this->driver->id,
        'optimization_date' => Carbon::today(),
        'total_distance' => 50.0,
        'total_time' => 120,
        'estimated_fuel_cost' => 30.00,
        'carbon_footprint' => 15.5,
        'optimization_result' => [
            'savings' => 10.5,
            'total_value' => 500.00,
            'route_steps' => [['step' => 1], ['step' => 2]]
        ],
        'order_sequence' => [1, 2, 3]
    ]);

    RouteOptimization::factory()->create([
        'driver_id' => $this->driver->id,
        'optimization_date' => Carbon::yesterday(),
        'total_distance' => 30.0,
        'total_time' => 90,
        'estimated_fuel_cost' => 20.00,
        'carbon_footprint' => 10.2,
        'optimization_result' => [
            'savings' => 8.0,
            'total_value' => 350.00,
            'route_steps' => [['step' => 1]]
        ],
        'order_sequence' => [4, 5],
        'is_manual_edit' => true
    ]);

    $stats = $this->service->getDriverRouteOptimizationStats($this->driver->id);

    expect($stats)->toBeArray()
        ->and($stats['total_optimizations'])->toBe(2)
        ->and($stats['total_distance'])->toBe(80.0)
        ->and($stats['total_time'])->toBe(210)
        ->and($stats['total_fuel_cost'])->toBe(50.00)
        ->and($stats['total_carbon_footprint'])->toBe(25.7)
        ->and($stats['total_savings'])->toBe(18.5)
        ->and($stats['total_value'])->toBe(850.00)
        ->and($stats['total_orders_optimized'])->toBe(5)
        ->and($stats['total_route_steps'])->toBe(3)
        ->and($stats['manual_edits_count'])->toBe(1)
        ->and($stats['average_distance_per_route'])->toBe(40.0)
        ->and($stats['average_time_per_route'])->toBe(105.0);
});

it('handles geocoding missing coordinates successfully', function () {
    Client::factory()->count(3)->create([
        'latitude' => null,
        'longitude' => null,
        'street_name' => 'Test Street',
        'city' => 'Warsaw'
    ]);

    $result = $this->service->geocodeMissingCoordinates(3);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('processed')
        ->and($result)->toHaveKey('geocoded')
        ->and($result)->toHaveKey('failed')
        ->and($result)->toHaveKey('errors')
        ->and($result)->toHaveKey('remaining_without_coordinates')
        ->and($result['processed'])->toBeGreaterThanOrEqual(0);
});