<?php

use App\Models\OrderCarpet;
use App\Models\OrderCarpetPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;

uses(RefreshDatabase::class);

it('can upload a carpet photo', function () {
    $user = User::factory()->create();

    $order_carpet = OrderCarpet::factory()->create();

    Passport::actingAs($user);

    Storage::fake('local');
    $file = UploadedFile::fake()->image('carpet.jpg');

    $data = [
        'order_carpet_id' => $order_carpet->id,
        'user_id' => $user->id,
        'photo' => $file,
    ];

    $response = $this->postJson("/api/order-carpets/{$order_carpet->id}/upload-photo", $data);

    // dump($response->getContent());

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'message' => 'Carpet photo uploaded successfully',
            'data' => [
                'order_carpet_id' => $order_carpet->id,
                'photo_url' => 'carpets/'.$file->hashName(),
                'taken_by' => $user->full_name,
                'created_at' => $order_carpet->created_at->toIso8601String(),
            ],
        ]);

    Storage::disk('public')->assertExists('carpets/'.$file->hashName());
});

it('can delete a carpet photo', function () {
    $user = User::factory()->create();
    $order_carpet = OrderCarpet::factory()->create();

    Passport::actingAs($user);

    Storage::fake('local');
    $file = UploadedFile::fake()->image('carpet.jpg');

    // First, upload a photo
    $uploadData = [
        'order_carpet_id' => $order_carpet->id,
        'user_id' => $user->id,
        'photo' => $file,
    ];

    $uploadResponse = $this->postJson("/api/order-carpets/{$order_carpet->id}/upload-photo", $uploadData);
    $uploadResponse->assertStatus(200);

    // Get the created photo
    $carpetPhoto = OrderCarpetPhoto::first();

    // Delete the photo
    $deleteResponse = $this->deleteJson("/api/carpet-photo/{$carpetPhoto->id}");

    $deleteResponse->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'message' => 'Carpet photo deleted successfully!',
        ]);

    // Assert photo is deleted from database
    $this->assertDatabaseMissing('order_carpet_photos', ['id' => $carpetPhoto->id]);

    // Assert file is removed from storage
    Storage::disk('local')->assertMissing('carpets/'.$file->hashName());
});
