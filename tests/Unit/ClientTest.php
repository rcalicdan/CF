<?php

use App\Models\Client;

beforeEach(function () {
    $this->clientWithCoordinates = Client::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'street_name' => 'Main Street',
        'street_number' => '123',
        'postal_code' => '00-001',
        'city' => 'Warsaw',
        'latitude' => 52.2297,
        'longitude' => 21.0122
    ]);

    $this->clientWithoutCoordinates = Client::factory()->create([
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'latitude' => null,
        'longitude' => null
    ]);
});

it('generates full address correctly', function () {
    expect($this->clientWithCoordinates->full_address)
        ->toBe('Main Street, 123, 00-001, Warsaw');
});

it('generates full name correctly', function () {
    expect($this->clientWithCoordinates->full_name)
        ->toBe('John Doe');
});

it('returns coordinates in correct format', function () {
    $coordinates = $this->clientWithCoordinates->coordinates;
    
    expect($coordinates)->toBeArray()
        ->and($coordinates)->toBe([52.2297, 21.0122]);
});

it('returns vroom coordinates in correct format', function () {
    $vroomCoordinates = $this->clientWithCoordinates->vroom_coordinates;
    
    expect($vroomCoordinates)->toBeArray()
        ->and($vroomCoordinates)->toBe([21.0122, 52.2297]); // [lng, lat]
});

it('detects when client has coordinates', function () {
    expect($this->clientWithCoordinates->hasCoordinates())->toBeTrue()
        ->and($this->clientWithoutCoordinates->hasCoordinates())->toBeFalse();
});

it('filters clients with coordinates scope', function () {
    $clientsWithCoordinates = Client::withCoordinates()->get();
    $clientsWithoutCoordinates = Client::withoutCoordinates()->get();

    expect($clientsWithCoordinates)->toHaveCount(1)
        ->and($clientsWithoutCoordinates)->toHaveCount(1);
});

it('returns null coordinates when not set', function () {
    expect($this->clientWithoutCoordinates->coordinates)->toBeNull()
        ->and($this->clientWithoutCoordinates->vroom_coordinates)->toBeNull();
});

it('handles partial address information', function () {
    $partialClient = Client::factory()->create([
        'street_name' => 'Partial Street',
        'city' => 'Warsaw',
        'street_number' => null,
        'postal_code' => null
    ]);

    expect($partialClient->full_address)->toBe('Partial Street, Warsaw');
});