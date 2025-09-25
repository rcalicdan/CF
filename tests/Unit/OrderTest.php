<?php

use App\Models\Order;
use App\Models\Client;
use App\Models\Driver;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    $this->user = User::factory()->create([
        'full_name' => 'Test Driver'
    ]);

    $this->driver = Driver::factory()->create([
        'user_id' => $this->user->id,
        'license_number' => 'DL123456'
    ]);

    $this->client = Client::factory()->create([
        'first_name' => 'Jane',
        'last_name' => 'Customer',
        'street_name' => 'Test Street',
        'street_number' => '456',
        'city' => 'Warsaw',
        'postal_code' => '00-002',
        'latitude' => 52.2297,
        'longitude' => 21.0122
    ]);

    $this->order = Order::factory()->create([
        'assigned_driver_id' => $this->driver->id,
        'client_id' => $this->client->id,
        'status' => 'pending',
        'total_amount' => 250.00,
        'schedule_date' => Carbon::today(),
        'is_complaint' => false
    ]);
});

it('gets client name correctly', function () {
    expect($this->order->client_name)->toBe('Jane Customer');
});

it('gets address correctly', function () {
    expect($this->order->address)->toBe('Test Street, 456, 00-002, Warsaw');
});

it('gets coordinates correctly', function () {
    $coordinates = $this->order->coordinates;
    
    expect($coordinates)->toBeArray()
        ->and($coordinates)->toBe([52.2297, 21.0122]);
});

it('detects when order has coordinates', function () {
    expect($this->order->hasCoordinates())->toBeTrue();
});

it('gets driver name correctly', function () {
    expect($this->order->driver_name)->toBe('Test Driver')
        ->and($this->order->driver_full_name)->toBe('Test Driver');
});

it('handles order without client gracefully', function () {
    $orderWithoutClient = Order::factory()->create([
        'client_id' => null,
        'assigned_driver_id' => $this->driver->id
    ]);

    expect($orderWithoutClient->client_name)->toBe('N/A')
        ->and($orderWithoutClient->address)->toBe('N/A')
        ->and($orderWithoutClient->coordinates)->toBeNull()
        ->and($orderWithoutClient->hasCoordinates())->toBeFalse();
});

it('handles order without driver gracefully', function () {
    $orderWithoutDriver = Order::factory()->create([
        'assigned_driver_id' => null,
        'client_id' => $this->client->id
    ]);

    expect($orderWithoutDriver->driver_name)->toBe('N/A')
        ->and($orderWithoutDriver->driver_full_name)->toBe('N/A');
});

it('filters orders with coordinates scope', function () {
    // Create order with client without coordinates
    $clientWithoutCoords = Client::factory()->create([
        'latitude' => null,
        'longitude' => null
    ]);

    Order::factory()->create([
        'client_id' => $clientWithoutCoords->id,
        'assigned_driver_id' => $this->driver->id
    ]);

    $ordersWithCoords = Order::withCoordinates()->get();
    $ordersWithoutCoords = Order::withoutCoordinates()->get();

    expect($ordersWithCoords)->toHaveCount(1)
        ->and($ordersWithoutCoords)->toHaveCount(1);
});