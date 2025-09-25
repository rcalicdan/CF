<?php

use App\Models\User;
use App\Models\Driver;
use App\Models\Client;
use App\Models\Order;
use App\Models\RouteOptimization;
use Laravel\Passport\Passport;
use Carbon\Carbon;

beforeEach(function () {
    $this->adminUser = User::factory()->create([
        'role' => 'admin',
        'full_name' => 'Admin User'
    ]);

    $this->driverUser = User::factory()->create([
        'role' => 'driver',
        'full_name' => 'Driver User'
    ]);

    $this->driver = Driver::factory()->create([
        'user_id' => $this->driverUser->id,
        'license_number' => 'DL123456',
        'vehicle_details' => 'Ford Transit'
    ]);

    $this->client = Client::factory()->create([
        'latitude' => 52.2297,
        'longitude' => 21.0122
    ]);
});

it('gets all drivers successfully', function () {
    Passport::actingAs($this->adminUser);

    Order::factory()->create([
        'assigned_driver_id' => $this->driver->id,
        'client_id' => $this->client->id,
        'status' => 'completed'
    ]);

    $response = $this->getJson('/api/route-data/drivers');

    $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'full_name',
                        'license_number',
                        'vehicle_details',
                        'phone_number',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);

    expect($response->json('success'))->toBeTrue()
        ->and($response->json('data'))->toHaveCount(1);
});

it('gets orders for driver and date successfully', function () {
    Passport::actingAs($this->adminUser);

    $scheduleDate = Carbon::today();
    Order::factory()->create([
        'assigned_driver_id' => $this->driver->id,
        'client_id' => $this->client->id,
        'status' => 'completed',
        'schedule_date' => $scheduleDate,
        'total_amount' => 250.00
    ]);

    $response = $this->getJson('/api/route-data/orders', [
        'driver_id' => $this->driver->id,
        'date' => $scheduleDate->format('Y-m-d')
    ]);

    $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'driver_id',
                        'client_name',
                        'address',
                        'coordinates',
                        'total_amount',
                        'status',
                        'priority',
                        'has_coordinates',
                        'driver_name'
                    ]
                ],
                'meta' => [
                    'driver_id',
                    'date',
                    'total_orders',
                    'total_value'
                ]
            ]);

    expect($response->json('success'))->toBeTrue()
        ->and($response->json('meta.total_orders'))->toBe(1)
        ->and($response->json('meta.total_value'))->toBe(250.00);
});

it('gets all orders for date range successfully', function () {
    Passport::actingAs($this->adminUser);

    $startDate = Carbon::today();
    $endDate = Carbon::today()->addDays(7);

    Order::factory()->count(3)->create([
        'assigned_driver_id' => $this->driver->id,
        'client_id' => $this->client->id,
        'status' => 'completed',
        'schedule_date' => Carbon::today()->addDays(2)
    ]);

    $response = $this->getJson('/api/route-data/all-orders', [
        'start_date' => $startDate->format('Y-m-d'),
        'end_date' => $endDate->format('Y-m-d')
    ]);

    $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'driver_id',
                        'client_name',
                        'address',
                        'total_amount',
                        'status',
                        'priority'
                    ]
                ],
                'meta' => [
                    'total_orders',
                    'date_range'
                ]
            ]);

    expect($response->json('success'))->toBeTrue()
        ->and($response->json('meta.total_orders'))->toBe(3);
});

it('gets route statistics successfully', function () {
    Passport::actingAs($this->adminUser);

    Order::factory()->count(2)->create([
        'assigned_driver_id' => $this->driver->id,
        'client_id' => $this->client->id,
        'status' => 'completed',
        'schedule_date' => Carbon::today(),
        'total_amount' => 300.00
    ]);

    $response = $this->getJson('/api/route-data/statistics', [
        'driver_id' => $this->driver->id,
        'date' => Carbon::today()->format('Y-m-d')
    ]);

    $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_orders',
                    'total_value',
                    'orders_with_coordinates',
                    'orders_without_coordinates',
                    'status_breakdown',
                    'priority_breakdown',
                    'driver_breakdown',
                    'average_order_value',
                    'complaints_count'
                ]
            ]);

    expect($response->json('success'))->toBeTrue()
        ->and($response->json('data.total_orders'))->toBe(2)
        ->and($response->json('data.total_value'))->toBe(600.00);
});

it('saves route optimization successfully', function () {
    Passport::actingAs($this->adminUser);

    $optimizationData = [
        'driver_id' => $this->driver->id,
        'optimization_date' => Carbon::today()->format('Y-m-d'),
        'optimization_result' => [
            'total_distance' => 45.5,
            'total_time' => 120,
            'savings' => 15.2,
            'route_steps' => [
                ['lat' => 52.2297, 'lng' => 21.0122, 'address' => 'Main Street 123']
            ],
            'geometry' => 'mock_geometry_string'
        ],
        'order_sequence' => [1, 2, 3],
        'total_distance' => 45.5,
        'total_time' => 120,
        'estimated_fuel_cost' => 25.50,
        'carbon_footprint' => 12.3,
        'is_manual_edit' => false
    ];

    $response = $this->postJson('/api/route-data/save-optimization', $optimizationData);

    $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'driver_id',
                    'optimization_date',
                    'total_distance',
                    'total_time',
                    'estimated_fuel_cost',
                    'carbon_footprint'
                ]
            ]);

    expect($response->json('success'))->toBeTrue()
        ->and($response->json('data.driver_id'))->toBe($this->driver->id)
        ->and($response->json('data.total_distance'))->toBe(45.5);
});

it('gets saved route optimization successfully', function () {
    Passport::actingAs($this->adminUser);

    $optimization = RouteOptimization::factory()->create([
        'driver_id' => $this->driver->id,
        'optimization_date' => Carbon::today(),
        'total_distance' => 50.5,
        'total_time' => 90
    ]);

    $response = $this->getJson('/api/route-data/saved-optimization', [
        'driver_id' => $this->driver->id,
        'date' => Carbon::today()->format('Y-m-d')
    ]);

    $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data'
            ]);

    expect($response->json('success'))->toBeTrue()
        ->and($response->json('data.id'))->toBe($optimization->id);
});

it('triggers geocoding successfully', function () {
    Passport::actingAs($this->adminUser);

    // Create clients without coordinates
    Client::factory()->count(2)->create([
        'latitude' => null,
        'longitude' => null,
        'street_name' => 'Test Street',
        'city' => 'Warsaw'
    ]);

    $response = $this->postJson('/api/route-data/geocode');

    $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'processed',
                    'geocoded',
                    'failed',
                    'errors',
                    'remaining_without_coordinates'
                ]
            ]);

    expect($response->json('success'))->toBeTrue()
        ->and($response->json('data.processed'))->toBeGreaterThanOrEqual(0);
});

it('driver gets their route optimizations successfully', function () {
    Passport::actingAs($this->driverUser);

    RouteOptimization::factory()->count(2)->create([
        'driver_id' => $this->driver->id,
        'optimization_date' => Carbon::today(),
        'total_distance' => 45.0,
        'optimization_result' => [
            'savings' => 12.5,
            'total_value' => 400.00,
            'route_steps' => []
        ]
    ]);

    $response = $this->getJson('/api/route-data/driver-optimizations');

    $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'optimizations' => [
                        '*' => [
                            'id',
                            'driver_id',
                            'driver_name',
                            'optimization_date',
                            'total_distance',
                            'total_time',
                            'total_orders'
                        ]
                    ],
                    'statistics',
                    'driver_info'
                ],
                'meta' => [
                    'total_optimizations',
                    'date_range'
                ]
            ]);

    expect($response->json('success'))->toBeTrue()
        ->and($response->json('meta.total_optimizations'))->toBe(2)
        ->and($response->json('data.driver_info.id'))->toBe($this->driver->id);
});

it('driver gets specific route optimization details successfully', function () {
    Passport::actingAs($this->driverUser);

    $optimization = RouteOptimization::factory()->create([
        'driver_id' => $this->driver->id,
        'optimization_date' => Carbon::today(),
        'total_distance' => 55.0,
        'total_time' => 150,
        'optimization_result' => [
            'savings' => 18.5,
            'total_value' => 650.00,
            'route_steps' => [
                ['lat' => 52.2297, 'lng' => 21.0122, 'address' => 'Step 1'],
                ['lat' => 52.2400, 'lng' => 21.0200, 'address' => 'Step 2']
            ],
            'geometry' => 'mock_geometry_data'
        ],
        'order_sequence' => [1, 2, 3, 4]
    ]);

    // Create matching orders for the same date
    Order::factory()->count(2)->create([
        'assigned_driver_id' => $this->driver->id,
        'client_id' => $this->client->id,
        'status' => 'completed',
        'schedule_date' => Carbon::today()
    ]);

    $response = $this->getJson("/api/route-data/driver-optimizations/{$optimization->id}");

    $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'optimization' => [
                        'id',
                        'driver_id',
                        'driver_name',
                        'optimization_date',
                        'total_distance',
                        'total_time',
                        'estimated_fuel_cost',
                        'carbon_footprint',
                        'total_orders',
                        'order_sequence',
                        'optimization_result',
                        'savings',
                        'total_value',
                        'route_steps',
                        'geometry'
                    ],
                    'orders',
                    'driver_info'
                ]
            ]);

    expect($response->json('success'))->toBeTrue()
        ->and($response->json('data.optimization.id'))->toBe($optimization->id)
        ->and($response->json('data.optimization.total_distance'))->toBe(55.0)
        ->and($response->json('data.optimization.total_orders'))->toBe(4)
        ->and($response->json('data.optimization.savings'))->toBe(18.5)
        ->and($response->json('data.driver_info.id'))->toBe($this->driver->id);
});

it('denies access to non-driver users for driver routes', function () {
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/route-data/driver-optimizations');

    $response->assertStatus(403)
            ->assertJsonStructure([
                'success',
                'message'
            ]);

    expect($response->json('success'))->toBeFalse()
        ->and($response->json('message'))->toContain('Access denied');
});

it('validates required parameters for orders endpoint', function () {
    Passport::actingAs($this->adminUser);

    $response = $this->getJson('/api/route-data/orders');

    $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors'
            ]);

    expect($response->json('errors'))->toHaveKey('driver_id')
        ->and($response->json('errors'))->toHaveKey('date');
});

it('validates date format for optimization saving', function () {
    Passport::actingAs($this->adminUser);

    $invalidData = [
        'driver_id' => $this->driver->id,
        'optimization_date' => 'invalid-date',
        'optimization_result' => ['test' => 'data']
    ];

    $response = $this->postJson('/api/route-data/save-optimization', $invalidData);

    $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors'
            ]);

    expect($response->json('errors'))->toHaveKey('optimization_date');
});

it('returns 404 for non-existent optimization details', function () {
    Passport::actingAs($this->driverUser);

    $response = $this->getJson('/api/route-data/driver-optimizations/99999');

    $response->assertStatus(404)
            ->assertJsonStructure([
                'success',
                'message'
            ]);

    expect($response->json('success'))->toBeFalse()
        ->and($response->json('message'))->toContain('not found');
});

it('handles empty results gracefully', function () {
    Passport::actingAs($this->adminUser);

    $emptyDriver = Driver::factory()->create(['user_id' => User::factory()->create()->id]);

    $response = $this->getJson('/api/route-data/orders', [
        'driver_id' => $emptyDriver->id,
        'date' => Carbon::today()->format('Y-m-d')
    ]);

    $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'meta'
            ]);

    expect($response->json('success'))->toBeTrue()
        ->and($response->json('data'))->toBeArray()
        ->and($response->json('data'))->toHaveCount(0)
        ->and($response->json('meta.total_orders'))->toBe(0)
        ->and($response->json('meta.total_value'))->toBe(0);
});