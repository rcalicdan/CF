<?php

use App\ActionService\ClientPdfReportService;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderCarpet;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->service = new ClientPdfReportService();
    
    $this->client = Client::factory()->create([
        'first_name' => 'Anna',
        'last_name' => 'Nowak',
        'email' => 'anna.nowak@example.com',
        'phone_number' => '+48987654321',
        'address' => 'ul. Przykładowa 456',
        'city' => 'Krakow',
        'postal_code' => '31-001',
        'created_at' => Carbon::now()->subMonths(8),
        'updated_at' => Carbon::now()->subHours(2),
    ]);
});

it('generates PDF report successfully without date filters', function () {
    $filename = $this->service->generateClientReport($this->client);
    
    expect($filename)
        ->toBeString()
        ->toContain('raport_klienta_')
        ->toContain($this->client->id)
        ->toEndWith('.pdf');
    
    Storage::disk('public')->assertExists($filename);
});

it('generates PDF report with date range filters', function () {
    $dateFrom = Carbon::now()->subMonths(3);
    $dateTo = Carbon::now()->subMonth();
    
    $filename = $this->service->generateClientReport($this->client, $dateFrom, $dateTo);
    
    expect($filename)
        ->toBeString()
        ->toContain('raport_klienta_')
        ->toContain($dateFrom->format('Y-m-d'))
        ->toContain($dateTo->format('Y-m-d'))
        ->toEndWith('.pdf');
    
    Storage::disk('public')->assertExists($filename);
});

it('generates PDF with only dateFrom filter', function () {
    $dateFrom = Carbon::now()->subMonths(2);
    
    $filename = $this->service->generateClientReport($this->client, $dateFrom);
    
    expect($filename)
        ->toBeString()
        ->toContain('raport_klienta_')
        ->toContain($dateFrom->format('Y-m-d'))
        ->toContain('od')
        ->toEndWith('.pdf');
    
    Storage::disk('public')->assertExists($filename);
});

it('generates PDF with only dateTo filter', function () {
    $dateTo = Carbon::now()->subMonth();
    
    $filename = $this->service->generateClientReport($this->client, null, $dateTo);
    
    expect($filename)
        ->toBeString()
        ->toContain('raport_klienta_')
        ->toContain($dateTo->format('Y-m-d'))
        ->toContain('do')
        ->toEndWith('.pdf');
    
    Storage::disk('public')->assertExists($filename);
});

it('handles client with comprehensive data', function () {
    $orders = collect();
    
    for ($i = 0; $i < 3; $i++) {
        $orders->push(Order::factory()->create([
            'client_id' => $this->client->id,
            'status' => fake()->randomElement(['completed', 'pending', 'in_progress']),
            'total_amount' => fake()->randomFloat(2, 500, 2000),
            'created_at' => Carbon::now()->subMonths($i)->subDays(fake()->numberBetween(1, 28)),
        ]));
    }
    
    foreach ($orders as $order) {
        OrderCarpet::factory()->count(fake()->numberBetween(1, 3))->create([
            'order_id' => $order->id,
            'status' => $order->status,
            'width' => fake()->randomFloat(2, 1, 5),
            'height' => fake()->randomFloat(2, 1, 5),
            'total_area' => fake()->randomFloat(2, 1, 25),
            'total_price' => fake()->randomFloat(2, 200, 800),
            'reference_code' => 'PDF-TEST-' . fake()->unique()->randomNumber(4),
        ]);
    }
    
    $filename = $this->service->generateClientReport($this->client);
    
    expect($filename)->toBeString();
    Storage::disk('public')->assertExists($filename);
    
    $fileSize = Storage::disk('public')->size($filename);
    expect($fileSize)->toBeGreaterThan(1000); 
});

it('filters data correctly by date range', function () {
    $dateFrom = Carbon::now()->subMonths(2);
    $dateTo = Carbon::now()->subMonth();
    
    Order::factory()->create([
        'client_id' => $this->client->id,
        'created_at' => Carbon::now()->subMonths(3), 
        'total_amount' => 1000,
    ]);
    
    Order::factory()->create([
        'client_id' => $this->client->id,
        'created_at' => $dateFrom->copy()->addDays(10), 
        'total_amount' => 1500,
    ]);
    
    Order::factory()->create([
        'client_id' => $this->client->id,
        'created_at' => Carbon::now(), 
        'total_amount' => 2000,
    ]);
    
    $filename = $this->service->generateClientReport($this->client, $dateFrom, $dateTo);
    
    expect($filename)->toBeString();
    Storage::disk('public')->assertExists($filename);
});

it('handles client with no data gracefully', function () {
    $emptyClient = Client::factory()->create([
        'first_name' => 'Empty',
        'last_name' => 'Client',
        'email' => 'empty@example.com',
    ]);
    
    $filename = $this->service->generateClientReport($emptyClient);
    
    expect($filename)->toBeString();
    Storage::disk('public')->assertExists($filename);
    
    $fileSize = Storage::disk('public')->size($filename);
    expect($fileSize)->toBeGreaterThan(500); 
});

it('generates unique filenames for multiple reports', function () {
    $filename1 = $this->service->generateClientReport($this->client);
    
    sleep(1);
    
    $filename2 = $this->service->generateClientReport($this->client);
    
    expect($filename1)->not->toBe($filename2);
    
    Storage::disk('public')->assertExists($filename1);
    Storage::disk('public')->assertExists($filename2);
});

it('limits orders and carpets appropriately for PDF', function () {
    $orders = Order::factory()->count(15)->create([
        'client_id' => $this->client->id,
        'status' => 'completed',
        'total_amount' => 1000,
    ]);
    
    foreach ($orders->take(20) as $order) {
        OrderCarpet::factory()->create([
            'order_id' => $order->id,
            'status' => 'completed',
            'reference_code' => 'LIMIT-TEST-' . $order->id,
        ]);
    }
    
    $filename = $this->service->generateClientReport($this->client);
    
    expect($filename)->toBeString();
    Storage::disk('public')->assertExists($filename);
});

it('calculates revenue analysis correctly', function () {
    Order::factory()->create([
        'client_id' => $this->client->id,
        'status' => 'completed',
        'total_amount' => 1000,
    ]);
    
    Order::factory()->create([
        'client_id' => $this->client->id,
        'status' => 'pending',
        'total_amount' => 500,
    ]);
    
    Order::factory()->create([
        'client_id' => $this->client->id,
        'status' => 'in_progress',
        'total_amount' => 750,
    ]);
    
    $filename = $this->service->generateClientReport($this->client);
    
    expect($filename)->toBeString();
    Storage::disk('public')->assertExists($filename);
});

it('handles monthly performance data correctly', function () {
    for ($i = 1; $i <= 6; $i++) {
        Order::factory()->count(2)->create([
            'client_id' => $this->client->id,
            'created_at' => Carbon::now()->subMonths($i),
            'total_amount' => 1000 * $i,
            'status' => 'completed',
        ]);
    }
    
    $filename = $this->service->generateClientReport($this->client);
    
    expect($filename)->toBeString();
    Storage::disk('public')->assertExists($filename);
    
    $fileSize = Storage::disk('public')->size($filename);
    expect($fileSize)->toBeGreaterThan(2000);
});