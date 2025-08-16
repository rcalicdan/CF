<?php

namespace Tests\Feature;

use App\Jobs\SendSmsJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Laravel\Passport\Client;
use Tests\TestCase;

class SmsFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Client::where('personal_access_client', 1)->exists()) {
            \Artisan::call('passport:client', [
                '--personal' => true,
                '--name' => 'Test Personal Access Client',
                '--no-interaction' => true,
            ]);
        }
    }

    public function test_send_sms_message()
    {
        $user = User::factory()->create();
        $token = $user->createToken('Aladyn Personal Access Client')->accessToken;

        Bus::fake();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/send-sms', [
            'phone_number' => '48793676408',
            'message' => 'test message',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'SMS sent successfully',
            ]);

        Bus::assertDispatched(SendSmsJob::class, function ($job) {
            return $job->phone_number === '48793676408'
                && $job->message === 'test message';
        });
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
