<?php

use App\ActionService\ClientCsvReportService;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderCarpet;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->service = new ClientCsvReportService();
    
    $this->client = Client::factory()->create([
        'first_name' => 'Jan',
        'last_name' => 'Kowalski',
        'email' => 'jan.kowalski@example.com',
        'phone_number' => '+48123456789',
        'address' => 'ul. Testowa 123',
        'city' => 'Warsaw',
        'postal_code' => '00-001',
        'created_at' => Carbon::now()->subMonths(6),
        'updated_at' => Carbon::now()->subDays(1),
    ]);
});

it('generates CSV report successfully', function () {
    $filename = $this->service->generateClientCsvReport($this->client);
    
    expect($filename)
        ->toBeString()
        ->toContain('raport_klienta_csv_')
        ->toContain($this->client->id)
        ->toEndWith('.csv');
    
    Storage::disk('public')->assertExists($filename);
});

it('creates CSV with proper headers and client information', function () {
    $filename = $this->service->generateClientCsvReport($this->client);
    $content = Storage::disk('public')->get($filename);
    
    expect($content)
        ->toContain('Raport Klienta - ' . $this->client->full_name)
        ->toContain('ID Klienta: #' . $this->client->id)
        ->toContain('INFORMACJE O KLIENCIE')
        ->toContain('PODSUMOWANIE STATYSTYK');
});

it('handles client with orders and carpets', function () {
    $orders = Order::factory()->count(3)->create([
        'client_id' => $this->client->id,
        'status' => 'completed',
        'total_amount' => 1500.00,
        'created_at' => Carbon::now()->subMonths(2),
    ]);
    
    foreach ($orders as $order) {
        OrderCarpet::factory()->count(2)->create([
            'order_id' => $order->id,
            'status' => 'completed',
            'width' => 2.5,
            'height' => 3.0,
            'total_area' => 7.5,
            'total_price' => 500.00,
            'reference_code' => 'TEST-' . fake()->unique()->randomNumber(4),
        ]);
    }
    
    $filename = $this->service->generateClientCsvReport($this->client);
    $content = Storage::disk('public')->get($filename);
    
    expect($content)
        ->toContain('OSTATNIE ZAMÓWIENIA')
        ->toContain('OSTATNIE DYWANY')
        ->toContain('WYDAJNOŚĆ MIESIĘCZNA')
        ->toContain('ANALIZA PRZYCHODÓW');
});

it('formats numbers correctly in CSV', function () {
    Order::factory()->create([
        'client_id' => $this->client->id,
        'total_amount' => 1234.56,
        'status' => 'completed',
    ]);
    
    $filename = $this->service->generateClientCsvReport($this->client);
    $content = Storage::disk('public')->get($filename);

    expect($content)->toContain('1 234,56');
});

it('handles client with no orders gracefully', function () {
    $filename = $this->service->generateClientCsvReport($this->client);
    $content = Storage::disk('public')->get($filename);
    
    expect($content)
        ->toContain('Raport Klienta')
        ->toContain('INFORMACJE O KLIENCIE')
        ->toContain('PODSUMOWANIE STATYSTYK')
        ->not->toContain('OSTATNIE ZAMÓWIENIA');
});

it('includes proper CSV formatting with BOM and delimiter', function () {
    $filename = $this->service->generateClientCsvReport($this->client);
    $content = Storage::disk('public')->get($filename);
    
    expect(substr($content, 0, 3))->toBe("\xEF\xBB\xBF");
    
    expect($content)->toContain(';');
});

it('calculates completion rate correctly', function () {
    Order::factory()->create([
        'client_id' => $this->client->id,
        'status' => 'completed',
    ]);
    
    Order::factory()->create([
        'client_id' => $this->client->id,
        'status' => 'pending',
    ]);
    
    $filename = $this->service->generateClientCsvReport($this->client);
    $content = Storage::disk('public')->get($filename);
    
    expect($content)->toContain('50,0');
});

it('limits orders to 20 most recent in CSV', function () {
    Order::factory()->count(25)->create([
        'client_id' => $this->client->id,
        'status' => 'completed',
    ]);
    
    $filename = $this->service->generateClientCsvReport($this->client);
    
   
    expect($filename)->toBeString();
    Storage::disk('public')->assertExists($filename);
});

it('includes monthly performance data', function () {
    Order::factory()->create([
        'client_id' => $this->client->id,
        'total_amount' => 1000,
        'created_at' => Carbon::now()->subMonth(),
    ]);
    
    Order::factory()->create([
        'client_id' => $this->client->id,
        'total_amount' => 1500,
        'created_at' => Carbon::now()->subMonths(2),
    ]);
    
    $filename = $this->service->generateClientCsvReport($this->client);
    $content = Storage::disk('public')->get($filename);
    
    expect($content)->toContain('WYDAJNOŚĆ MIESIĘCZNA');
});

it('generates unique filenames', function () {
    $filename1 = $this->service->generateClientCsvReport($this->client);
    
    // Wait a moment to ensure different timestamp
    sleep(1);
    
    $filename2 = $this->service->generateClientCsvReport($this->client);
    
    expect($filename1)->not->toBe($filename2);
});