<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;

use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function () {
    if (! Client::where('personal_access_client', 1)->exists()) {
        \Artisan::call('passport:client', [
            '--personal' => true,
            '--name' => 'Test Personal Access Client',
            '--no-interaction' => true,
        ]);
    }
});

it('logs out an authenticated user', function () {
    // Create a user and log them in.
    $user = User::factory()->create([
        'email' => 'logoutuser@example.com',
        'password' => bcrypt('password123'),
    ]);

    $loginPayload = [
        'email' => $user->email,
        'password' => 'password123',
    ];

    $loginResponse = postJson('/api/auth/login', $loginPayload);

    $loginResponse->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'message',
            'user' => [
                'id',
                'first_name',
                'last_name',
                'email',
                'role',
            ],
            'token',
        ]);

    $token = $loginResponse->json('token');

    // Log out the user.
    $logoutResponse = postJson('/api/auth/logout', [], [
        'Authorization' => "Bearer $token",
    ]);

    $logoutResponse->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'message' => 'Logout Succesfully',
        ]);
});

it('registers a new user', function () {
    $payload = [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'testuser@example.com',
        'password' => 'password123',
        'role' => 'admin', // valid roles: admin, driver, or employee
    ];

    $response = postJson('/api/auth/register', $payload);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'message',
            'user' => [
                'id',
                'first_name',
                'last_name',
                'email',
                'role',
            ],
            'token',
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'testuser@example.com',
    ]);
});

it('logs in an existing user', function () {
    // Create a user with known credentials.
    $user = User::factory()->create([
        'email' => 'loginuser@example.com',
        'password' => bcrypt('password123'),
    ]);

    $payload = [
        'email' => $user->email,
        'password' => 'password123',
    ];

    $response = postJson('/api/auth/login', $payload);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'message',
            'user' => [
                'id',
                'first_name',
                'last_name',
                'email',
                'role',
            ],
            'token',
        ]);
});

it('fails to log in with invalid credentials', function () {
    // Create a user.
    $user = User::factory()->create([
        'email' => 'invalidlogin@example.com',
        'password' => bcrypt('password123'),
    ]);

    $payload = [
        'email' => $user->email,
        'password' => 'wrongpassword',
    ];

    $response = postJson('/api/auth/login', $payload);

    $response->assertStatus(401)
        ->assertJson([
            'status' => 'error',
            'message' => 'Invalid Credentials',
        ]);
});
