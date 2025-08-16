<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;

uses(RefreshDatabase::class);

it('can update user profile information', function () {
    $user = User::factory()->create();

    // Authenticate with Passport
    Passport::actingAs($user);

    $fakeFirstName = fake()->firstName;
    $fakeLastName = fake()->lastName;
    $fakeEmail = fake()->safeEmail();

    $updateData = [
        'first_name' => $fakeFirstName,
        'last_name' => $fakeLastName,
        'email' => $fakeEmail,
    ];

    $response = $this->postJson('/api/auth/user/profile/update', $updateData);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'message' => 'User Information Updated',
            'data' => [
                'first_name' => $fakeFirstName,
                'last_name' => $fakeLastName,
                'email' => $fakeEmail,
            ],
        ]);

    $user->refresh();
    expect($user->first_name)->toBe($fakeFirstName)
        ->and($user->last_name)->toBe($fakeLastName)
        ->and($user->email)->toBe($fakeEmail);
});

it('can update user profile with profile picture', function () {
    $user = User::factory()->create();

    // Authenticate with Passport
    Passport::actingAs($user);

    Storage::fake('local');
    $file = UploadedFile::fake()->image('profile.jpg');

    $updateData = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => $user->email,
        'profile_picture' => $file,
    ];

    $response = $this->postJson('/api/auth/user/profile/update', $updateData);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'message' => 'User Information Updated',
        ]);

    $user->refresh();
    expect($user->first_name)->toBe('Jane')
        ->and($user->last_name)->toBe('Doe');

    Storage::disk('public')->assertExists('profile_pictures/'.$file->hashName());
});
