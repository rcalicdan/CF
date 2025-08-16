<?php

use App\Jobs\SendSmsJob;
use App\Models\Client;
use App\Models\Order;
use App\Models\PriceList;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Passport\Passport;

uses(RefreshDatabase::class);

it('can create a new order', function () {
    Queue::fake();

    $user = User::factory()->create();
    Passport::actingAs($user);

    $client = Client::factory()->create();
    $priceList = PriceList::factory()->create();
    $service = Service::factory()->create();

    $orderData = [
        'client_id' => $client->id,
        'price_list_id' => $priceList->id,
        'services' => [
            [
                'service_id' => $service->id,
            ],
        ],
        'is_complaint' => false,
    ];

    $response = $this->postJson('/api/orders', $orderData);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'id',
                'client' => [
                    'id',
                    'first_name',
                    'last_name',
                ],
                'status',
                'total_amount',
                'is_complaint',
                'created_at',
                'updated_at',
            ],
            'summary' => [
                'order_id',
                'client_id',
                'client_name',
                'price_list_id',
                'price_list_name',
                'status',
                'total_amount',
            ],
        ]);

    expect(Order::count())->toBe(1);

    Queue::assertPushed(SendSmsJob::class);
});

it('requires client_id when creating order', function () {
    $user = User::factory()->create();
    Passport::actingAs($user);

    $priceList = PriceList::factory()->create();
    $service = Service::factory()->create();

    $orderData = [
        'price_list_id' => $priceList->id,
        'services' => [
            [
                'service_id' => $service->id,
            ],
        ],
    ];

    $response = $this->postJson('/api/orders', $orderData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['client_id']);
});

it('requires price_list_id when creating order', function () {
    $user = User::factory()->create();
    Passport::actingAs($user);

    $client = Client::factory()->create();
    $service = Service::factory()->create();

    $orderData = [
        'client_id' => $client->id,
    ];

    $response = $this->postJson('/api/orders', $orderData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['price_list_id']);
});

it('can update an order', function () {
    Queue::fake();

    $user = User::factory()->create();
    Passport::actingAs($user);

    $client = Client::factory()->create();
    $priceList = PriceList::factory()->create();
    $service = Service::factory()->create();

    $order = Order::create([
        'client_id' => $client->id,
        'price_list_id' => $priceList->id,
        'status' => 'pending',
        'total_amount' => 0,
        'is_complaint' => false,
        'user_id' => $user->id,
    ]);

    $order->orderServices()->create([
        'service_id' => $service->id,
        'total_price' => 100.00,
    ]);

    $updateData = [
        'client_id' => $client->id,
        'price_list_id' => $priceList->id,
        'status' => 'processing',
        'is_complaint' => true,
    ];

    $response = $this->putJson("/api/orders/{$order->id}", $updateData);

    $response->assertStatus(200);

    $updatedOrder = $order->fresh();
    expect($updatedOrder->status)->toBe('processing')
        ->and($updatedOrder->is_complaint)->toBeTrue()
        ->and($updatedOrder->orderServices)->toHaveCount(1)
        ->and($updatedOrder->orderServices->first()->service_id)->toBe($service->id);
});

it('validates required fields when updating order', function () {
    $user = User::factory()->create();
    Passport::actingAs($user);

    $order = Order::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->putJson("/api/orders/{$order->id}", []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'is_complaint',
        ]);
});

it('creates order with scheduled date', function () {
    Queue::fake();

    $user = User::factory()->create();
    Passport::actingAs($user);

    $client = Client::factory()->create();
    $priceList = PriceList::factory()->create();
    $service = Service::factory()->create();

    // Format the date consistently
    $scheduledDate = now()->addDays(2)->format('Y-m-d H:i:s');

    $orderData = [
        'client_id' => $client->id,
        'price_list_id' => $priceList->id,
        'schedule_date' => $scheduledDate,
    ];

    $response = $this->postJson('/api/orders', $orderData);

    $response->assertStatus(201);

    // Compare formatted dates from the saved order using Carbon
    expect(Order::first()->schedule_date->format('Y-m-d H:i:s'))->toBe($scheduledDate);

    Queue::assertPushed(SendSmsJob::class);
});
