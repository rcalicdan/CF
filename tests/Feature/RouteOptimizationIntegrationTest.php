<?php

use App\Models\User;
use App\Models\Driver;
use App\Models\Client;
use App\Models\Order;
use App\Models\RouteOptimization;
use App\ActionService\RouteDataService;
use Laravel\Passport\Passport;
use Carbon\Carbon;

beforeEach(function () {
    $this->routeService = app(RouteDataService::class);
    
    $this->adminUser = User::factory()->create(['role' => 'admin']);
    $this->driverUser = User::factory()->create(['role' => 'driver']);
    
    $this->driver = Driver::factory()->create([
        'user_id' => $this->driverUser->id,
        'license_number' => 'DL789012',
        'vehicle_details' => 'Mercedes Sprinter'
    ]);

    $this->clients = Client::factory()->count(5)->create([
        'latitude' => fn() => fake()->latitude(52.0, 53.0),
        'longitude' => fn() => fake()->longitude(20.5, 21.5)
    ]);
});

it('performs complete route optimization workflow', function () {
    Passport::actingAs($this->adminUser);

    $orders = collect();
    foreach ($this->clients as $client) {
        $orders->push(Order::factory()->create([
            'assigned_driver_id' => $this->driver->id,
            'client_id' => $client->id,
            'status' => 'pending',
            'schedule_date' => Carbon::today(),
            'total_amount' => fake()->randomFloat(2, 100, 1000)
        ]));
    }

    $response = $this->getJson('/api/route-data/orders', [
        'driver_id' => $this->driver->id,
        'date' => Carbon::today()->format('Y-m-d')
    ]);

    expect($response->json('success'))->toBeTrue()
        ->and($response->json('data'))->toHaveCount(5);

    $orderData = $response->json('data');
    $totalValue = collect($orderData)->sum('total_amount');

    $optimizationData = [
        'driver_id' => $this->driver->id,
        'optimization_date' => Carbon::today()->format('Y-m-d'),
        'optimization_result' => [
            'total_distance' => 125.5,
            'total_time' => 280,
            'savings' => 35.7,
            'total_value' => $totalValue,
            'route_steps' => collect($orderData)->map(fn($order, $index) => [
                'step' => $index + 1,
                'order_id' => $order['id'],
                'lat' => $order['coordinates'][0] ?? 0,
                'lng' => $order['coordinates'][1] ?? 0,
                'address' => $order['address']
            ])->toArray(),
            'geometry' => 'optimized_route_geometry_string',
            'optimization_timestamp' => now()->toISOString()
        ],
        'order_sequence' => collect($orderData)->pluck('id')->toArray(),
        'total_distance' => 125.5,
        'total_time' => 280,
        'estimated_fuel_cost' => 55.75,
        'carbon_footprint' => 28.3,
        'is_manual_edit' => false
    ];

    $saveResponse = $this->postJson('/api/route-data/save-optimization', $optimizationData);

    expect($saveResponse->json('success'))->toBeTrue()
        ->and($saveResponse->json('data.total_distance'))->toBe(125.5)
        ->and($saveResponse->json('data.driver_id'))->toBe($this->driver->id);

    $retrieveResponse = $this->getJson('/api/route-data/saved-optimization', [
        'driver_id' => $this->driver->id,
        'date' => Carbon::today()->format('Y-m-d')
    ]);

    expect($retrieveResponse->json('success'))->toBeTrue()
        ->and($retrieveResponse->json('data.total_distance'))->toBe(125.5)
        ->and($retrieveResponse->json('data.order_sequence'))->toHaveCount(5);

    $statsResponse = $this->getJson('/api/route-data/statistics', [
        'driver_id' => $this->driver->id,
        'date' => Carbon::today()->format('Y-m-d')
    ]);

    expect($statsResponse->json('success'))->toBeTrue()
        ->and($statsResponse->json('data.total_orders'))->toBe(5)
        ->and($statsResponse->json('data.orders_with_coordinates'))->toBe(5);
});

it('handles driver accessing their own optimizations', function () {
    Passport::actingAs($this->driverUser);

    $optimizations = RouteOptimization::factory()->count(3)->create([
        'driver_id' => $this->driver->id,
        'optimization_date' => fn() => Carbon::today()->subDays(fake()->numberBetween(1, 10)),
        'optimization_result' => [
            'savings' => fake()->randomFloat(2, 10, 50),
            'total_value' => fake()->randomFloat(2, 500, 2000),
            'route_steps' => fake()->randomElements([
                ['step' => 1, 'address' => 'Address 1'],
                ['step' => 2, 'address' => 'Address 2']
            ], fake()->numberBetween(2, 5))
        ]
    ]);

    $response = $this->getJson('/api/route-data/driver-optimizations');

    expect($response->json('success'))->toBeTrue()
        ->and($response->json('meta.total_optimizations'))->toBe(3)
        ->and($response->json('data.statistics.total_optimizations'))->toBe(3)
        ->and($response->json('data.driver_info.id'))->toBe($this->driver->id);

    $firstOptimization = $optimizations->first();
    $detailsResponse = $this->getJson("/api/route-data/driver-optimizations/{$firstOptimization->id}");

    expect($detailsResponse->json('success'))->toBeTrue()
        ->and($detailsResponse->json('data.optimization.id'))->toBe($firstOptimization->id)
        ->and($detailsResponse->json('data.driver_info.id'))->toBe($this->driver->id);
});

it('processes multiple drivers optimization data correctly', function () {
    Passport::actingAs($this->adminUser);

    $secondDriverUser = User::factory()->create(['role' => 'driver']);
    $secondDriver = Driver::factory()->create(['user_id' => $secondDriverUser->id]);

    Order::factory()->count(3)->create([
        'assigned_driver_id' => $this->driver->id,
        'client_id' => $this->clients->random()->id,
        'status' => 'completed',
        'schedule_date' => Carbon::today(),
        'total_amount' => 200.00
    ]);

    Order::factory()->count(2)->create([
        'assigned_driver_id' => $secondDriver->id,
        'client_id' => $this->clients->random()->id,
        'status' => 'completed',
        'schedule_date' => Carbon::today(),
        'total_amount' => 300.00
    ]);

    $driversResponse = $this->getJson('/api/route-data/drivers');
    
    expect($driversResponse->json('success'))->toBeTrue()
        ->and($driversResponse->json('data'))->toHaveCount(2);

    $allStatsResponse = $this->getJson('/api/route-data/statistics', [
        'date' => Carbon::today()->format('Y-m-d')
    ]);

    expect($allStatsResponse->json('success'))->toBeTrue()
        ->and($allStatsResponse->json('data.total_orders'))->toBe(5)
        ->and($allStatsResponse->json('data.total_value'))->toBe(1200.00)
        ->and($allStatsResponse->json('data.driver_breakdown'))->toHaveCount(2);

    $driver1Stats = $this->getJson('/api/route-data/statistics', [
        'driver_id' => $this->driver->id,
        'date' => Carbon::today()->format('Y-m-d')
    ]);

    $driver2Stats = $this->getJson('/api/route-data/statistics', [
        'driver_id' => $secondDriver->id,
        'date' => Carbon::today()->format('Y-m-d')
    ]);

    expect($driver1Stats->json('data.total_orders'))->toBe(3)
        ->and($driver1Stats->json('data.total_value'))->toBe(600.00)
        ->and($driver2Stats->json('data.total_orders'))->toBe(2)
        ->and($driver2Stats->json('data.total_value'))->toBe(600.00);
});

it('handles date range filtering correctly', function () {
    Passport::actingAs($this->adminUser);

    $today = Carbon::today();
    $tomorrow = Carbon::tomorrow();
    $nextWeek = Carbon::today()->addWeek();

    Order::factory()->create([
        'assigned_driver_id' => $this->driver->id,
        'client_id' => $this->clients->first()->id,
        'status' => 'completed',
        'schedule_date' => $today,
        'total_amount' => 100.00
    ]);

    Order::factory()->create([
        'assigned_driver_id' => $this->driver->id,
        'client_id' => $this->clients->last()->id,
        'status' => 'completed',
        'schedule_date' => $tomorrow,
        'total_amount' => 200.00
    ]);

    Order::factory()->create([
        'assigned_driver_id' => $this->driver->id,
        'client_id' => $this->clients->random()->id,
        'status' => 'completed',
        'schedule_date' => $nextWeek,
        'total_amount' => 300.00
    ]);

    $rangeResponse = $this->getJson('/api/route-data/all-orders', [
        'start_date' => $today->format('Y-m-d'),
        'end_date' => $tomorrow->format('Y-m-d')
    ]);

    expect($rangeResponse->json('success'))->toBeTrue()
        ->and($rangeResponse->json('data'))->toHaveCount(2);

    $singleDateResponse = $this->getJson('/api/route-data/statistics', [
        'date' => $today->format('Y-m-d')
    ]);

    expect($singleDateResponse->json('success'))->toBeTrue()
        ->and($singleDateResponse->json('data.total_orders'))->toBe(1)
        ->and($singleDateResponse->json('data.total_value'))->toBe(100.00);
});