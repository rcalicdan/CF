<?php

use App\Enums\OrderPaymentMethods;
use App\Enums\OrderPaymentStatus;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;

use function Pest\Laravel\postJson;

uses(RefreshDatabase::class)->group('order-payments')->beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'admin']);

    $this->order = Order::factory()->create([
        'total_amount' => 150.00,
        'status' => 'pending',
    ]);
});

test('can create order payment', function () {
    Passport::actingAs($this->user);
    $paymentData = [
        'confirmation_type' => 'data',
        'confirmation_data' => '12345',
        'payment_details' => [
            'payment_method' => OrderPaymentMethods::CASH->value,
            'status' => OrderPaymentStatus::COMPLETED->value,
        ],
    ];

    $response = postJson("/api/deliveries/{$this->order->id}/confirm-delivery", $paymentData);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'message' => 'Delivery confirmed successfully',
        ]);

    expect(OrderPayment::count())->toBe(1);

    $payment = OrderPayment::first();
    expect($payment)
        ->amount_paid->toEqual($this->order->total_amount)
        ->payment_method->toBe(OrderPaymentMethods::CASH->value)
        ->status->toBe(OrderPaymentStatus::COMPLETED->value)
        ->order_id->toBe($this->order->id)
        ->paid_at->not->toBeNull();
});

test('cannot create payment with invalid payment method', function () {
    Passport::actingAs($this->user);
    $paymentData = [
        'confirmation_type' => 'data',
        'confirmation_data' => '12345',
        'payment_details' => [
            'payment_method' => 'invalid_method',
            'status' => OrderPaymentStatus::COMPLETED->value,
        ],
    ];

    $response = postJson("/api/deliveries/{$this->order->id}/confirm-delivery", $paymentData);

    $response->assertStatus(422);
    expect(OrderPayment::count())->toBe(0);
});

test('cannot create payment with invalid payment status', function () {
    Passport::actingAs($this->user);
    $paymentData = [
        'confirmation_type' => 'data',
        'confirmation_data' => '12345',
        'payment_details' => [
            'payment_method' => OrderPaymentMethods::CASH->value,
            'status' => 'invalid_status',
        ],
    ];

    $response = postJson("/api/deliveries/{$this->order->id}/confirm-delivery", $paymentData);

    $response->assertStatus(422);
    expect(OrderPayment::count())->toBe(0);
});

test('order status is updated after successful payment', function () {
    Passport::actingAs($this->user);
    $paymentData = [
        'confirmation_type' => 'data',
        'confirmation_data' => '12345',
        'payment_details' => [
            'payment_method' => OrderPaymentMethods::CASH->value,
            'status' => OrderPaymentStatus::COMPLETED->value,
        ],
    ];

    postJson("/api/deliveries/{$this->order->id}/confirm-delivery", $paymentData);

    $this->order->refresh();
    expect($this->order->status)->toBe('completed');
});

test('payment requires authentication', function () {
    $paymentData = [
        'confirmation_type' => 'data',
        'confirmation_data' => '12345',
        'payment_details' => [
            'payment_method' => OrderPaymentMethods::CASH->value,
            'status' => OrderPaymentStatus::COMPLETED->value,
        ],
    ];

    $response = postJson("/api/deliveries/{$this->order->id}/confirm-delivery", $paymentData);

    $response->assertStatus(401);
    expect(OrderPayment::count())->toBe(0);
});
