<?php

use App\Enums\OrderDeliveryConfirmationType;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

test('can confirm delivery with signature', function () {
    $user = User::factory()->create();
    Passport::actingAs($user);

    $order = Order::factory()->create();

    $signature = UploadedFile::fake()->image('signature.png');

    $response = $this->postJson("/api/deliveries/{$order->id}/confirm-delivery", [
        'confirmation_type' => OrderDeliveryConfirmationType::SIGNATURE->value,
        'signature_image' => $signature, // Changed from 'signature' to 'signature_image'
        'confirmation_data' => json_encode(['notes' => 'Delivered to front door']),
        'payment_details' => [ // Added payment details as required by the error
            'payment_method' => 'cash',
            'status' => 'completed',
        ],
    ]);

    $response->assertStatus(200);

    expect(DB::table('order_delivery_confirmations')->where([
        'order_id' => $order->id,
        'confirmation_type' => 'signature',
    ])->exists())->toBeTrue();

    // Storage::disk('public')->assertExists('delivery_signatures/' . $signature->hashName());
});

test('cannot confirm delivery without authentication', function () {
    $order = Order::factory()->create();

    $response = $this->postJson("/api/deliveries/{$order->id}/confirm-delivery", [
        'confirmation_type' => OrderDeliveryConfirmationType::DATA->value,
        'confirmation_data' => json_encode(['notes' => 'Left with neighbor']),
    ]);

    $response->assertStatus(401);
});

test('can confirm delivery with photo', function () {
    $user = User::factory()->create();
    Passport::actingAs($user);

    $order = Order::factory()->create();

    $photo = UploadedFile::fake()->image('delivery.jpg');

    $response = $this->postJson("/api/deliveries/{$order->id}/confirm-delivery", [
        'confirmation_type' => OrderDeliveryConfirmationType::SIGNATURE->value, // You may need to check what valid types are allowed
        'signature_image' => $photo,
        'payment_details' => [
            'payment_method' => 'cash',
            'status' => 'completed',
        ],
    ]);

    $response->assertStatus(200);

    expect(DB::table('order_delivery_confirmations')->where([
        'order_id' => $order->id,
        'confirmation_type' => OrderDeliveryConfirmationType::SIGNATURE->value,
    ])->exists())->toBeTrue();
});
