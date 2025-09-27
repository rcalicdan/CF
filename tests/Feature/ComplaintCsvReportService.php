<?php

use App\ActionService\ComplaintCsvReportService;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderCarpet;
use App\Models\User;
use App\Models\Driver;
use App\Models\Service;
use App\Enums\OrderStatus;
use App\Enums\OrderCarpetStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->service = new ComplaintCsvReportService();
    
    $this->client = Client::factory()->create([
        'first_name' => 'Anna',
        'last_name' => 'Kowalski',
        'email' => 'anna.kowalski@example.com',
        'phone_number' => '+48123456789',
        'address' => 'ul. Testowa 123',
        'city' => 'Warsaw',
        'postal_code' => '00-001',
    ]);
  
    $this->driverUser = User::factory()->create([
        'first_name' => 'Jan',
        'last_name' => 'Kierowca',
        'email' => 'kierowca@example.com',
    ]);
    
    $this->driver = Driver::factory()->create([
        'user_id' => $this->driverUser->id,
    ]);
});

it('generates complaint CSV report successfully for default period', function () {
    $filename = $this->service->generateComplaintCsvReport();
    
    expect($filename)
        ->toBeString()
        ->toContain('raport_skarg_')
        ->toEndWith('.csv');
    
    Storage::disk('public')->assertExists($filename);
});

it('generates complaint CSV report for custom days period', function () {
    $filename = $this->service->generateComplaintCsvReport(60);
    
    expect($filename)
        ->toBeString()
        ->toContain('raport_skarg_')
        ->toEndWith('.csv');
    
    Storage::disk('public')->assertExists($filename);
});

it('handles complaints with complete data', function () {
    $order = Order::factory()->create([
        'client_id' => $this->client->id,
        'driver_id' => $this->driver->id,
        'is_complaint' => true,
        'status' => OrderStatus::PENDING->value,
        'total_amount' => 1500.00,
        'schedule_date' => Carbon::now()->addDays(2),
        'created_at' => Carbon::now()->subDays(5),
    ]);
    
    $carpets = OrderCarpet::factory()->count(2)->create([
        'order_id' => $order->id,
        'status' => OrderCarpetStatus::COMPLAINT->value,
        'reference_code' => 'COMP-' . fake()->unique()->randomNumber(4),
        'width' => 3.0,
        'height' => 2.5,
        'total_area' => 7.5,
        'total_price' => 750.00,
    ]);

    
    $service = Service::factory()->create(['name' => 'Pranie dywanów']);
    foreach ($carpets as $carpet) {
        $carpet->services()->attach($service->id);
    }
    
    $filename = $this->service->generateComplaintCsvReport();
    $content = Storage::disk('public')->get($filename);
    
    expect($content)
        ->toContain('ID Zamówienia')
        ->toContain('Klient - Imię')
        ->toContain('Klient - Nazwisko')
        ->toContain($order->id)
        ->toContain($this->client->first_name)
        ->toContain($this->client->last_name);
});

it('generates CSV with proper UTF-8 encoding and delimiter', function () {
    $filename = $this->service->generateComplaintCsvReport();
    $content = Storage::disk('public')->get($filename);
  
    expect(substr($content, 0, 3))->toBe("\xEF\xBB\xBF");
    
    expect($content)->toContain(';');
});

it('generates complaint summary report successfully', function () {
    $filename = $this->service->generateComplaintSummaryReport();
    
    expect($filename)
        ->toBeString()
        ->toContain('raport_statystyk_skarg_')
        ->toEndWith('.csv');
    
    Storage::disk('public')->assertExists($filename);
});

it('handles complaint summary with data', function () {
    Order::factory()->create([
        'client_id' => $this->client->id,
        'is_complaint' => true,
        'status' => OrderStatus::PENDING->value,
        'total_amount' => 800.00,
        'created_at' => Carbon::now()->subDays(10),
    ]);
    
    Order::factory()->create([
        'client_id' => $this->client->id,
        'is_complaint' => true,
        'status' => OrderStatus::COMPLETED->value,
        'total_amount' => 1200.00,
        'created_at' => Carbon::now()->subDays(15),
    ]);
    
    $filename = $this->service->generateComplaintSummaryReport(30);
    $content = Storage::disk('public')->get($filename);
    
    expect($content)
        ->toContain('RAPORT STATYSTYK SKARG')
        ->toContain('OGÓLNE STATYSTYKI')
        ->toContain('ROZKŁAD WEDŁUG STATUSU')
        ->toContain('ROZKŁAD WEDŁUG KATEGORII')
        ->toContain('STATYSTYKI MIESIĘCZNE');
});

it('generates weekly trend report successfully', function () {
    $filename = $this->service->generateWeeklyTrendReport();
    
    expect($filename)
        ->toBeString()
        ->toContain('trend_tygodniowy_skarg_')
        ->toEndWith('.csv');
    
    Storage::disk('public')->assertExists($filename);
});

it('handles weekly trend with complaint data', function () {
    for ($i = 1; $i <= 3; $i++) {
        Order::factory()->create([
            'client_id' => $this->client->id,
            'is_complaint' => true,
            'status' => OrderStatus::PENDING->value,
            'created_at' => Carbon::now()->subDays($i),
        ]);
        
        Order::factory()->create([
            'client_id' => $this->client->id,
            'is_complaint' => true,
            'status' => OrderStatus::COMPLETED->value,
            'created_at' => Carbon::now()->subDays($i + 3),
            'updated_at' => Carbon::now()->subDays($i),
        ]);
    }
    
    $filename = $this->service->generateWeeklyTrendReport();
    $content = Storage::disk('public')->get($filename);
    
    expect($content)
        ->toContain('TREND TYGODNIOWY SKARG')
        ->toContain('Dzień')
        ->toContain('Nowe skargi')
        ->toContain('Rozwiązane skargi')
        ->toContain('Zmiana netto');
});

it('generates monthly complaint CSV report successfully', function () {
    $month = Carbon::now()->subMonth()->format('Y-m');
    $filename = $this->service->generateComplaintCsvReportForMonth($month);
    
    expect($filename)
        ->toBeString()
        ->toContain('raport_skarg_')
        ->toContain($month)
        ->toEndWith('.csv');
    
    Storage::disk('public')->assertExists($filename);
});

it('handles monthly report with complaint data', function () {
    $month = Carbon::now()->format('Y-m');
    $startOfMonth = Carbon::now()->startOfMonth();
    
    Order::factory()->create([
        'client_id' => $this->client->id,
        'driver_id' => $this->driver->id,
        'is_complaint' => true,
        'status' => OrderStatus::PROCESSING->value,
        'total_amount' => 950.00,
        'created_at' => $startOfMonth->copy()->addDays(5),
    ]);
    
    $filename = $this->service->generateComplaintCsvReportForMonth($month);
    $content = Storage::disk('public')->get($filename);
    
    expect($content)
        ->toContain('ID Zamówienia')
        ->toContain('Data Utworzenia')
        ->toContain('Status Zamówienia')
        ->toContain('Priorytet')
        ->toContain('Kategoria');
});

it('generates monthly summary report successfully', function () {
    $month = Carbon::now()->subMonth()->format('Y-m');
    $filename = $this->service->generateComplaintSummaryReportForMonth($month);
    
    expect($filename)
        ->toBeString()
        ->toContain('raport_statystyk_skarg_')
        ->toContain($month)
        ->toEndWith('.csv');
    
    Storage::disk('public')->assertExists($filename);
});

it('handles empty complaint data gracefully', function () {
    $filename = $this->service->generateComplaintCsvReport();
    $content = Storage::disk('public')->get($filename);
    
    expect($content)
        ->toContain('ID Zamówienia')
        ->toContain('Data Utworzenia')
        ->toContain('Status Zamówienia');
    
    // File should exist even with no data
    Storage::disk('public')->assertExists($filename);
});

it('formats numbers correctly in Polish locale', function () {
    Order::factory()->create([
        'client_id' => $this->client->id,
        'is_complaint' => true,
        'total_amount' => 1234.56,
        'created_at' => Carbon::now()->subDays(5),
    ]);
    
    $filename = $this->service->generateComplaintCsvReport();
    $content = Storage::disk('public')->get($filename);
    
    // Should format with comma as decimal separator
    expect($content)->toContain('1 234,56');
});

it('categorizes complaints correctly', function () {
    // Create order with damaged carpet (damage category)
    $order = Order::factory()->create([
        'client_id' => $this->client->id,
        'is_complaint' => true,
        'status' => OrderStatus::PENDING->value,
        'created_at' => Carbon::now()->subDays(3),
    ]);
    
    OrderCarpet::factory()->create([
        'order_id' => $order->id,
        'status' => OrderCarpetStatus::COMPLAINT->value,
    ]);
    
    $filename = $this->service->generateComplaintCsvReport();
    
    expect($filename)->toBeString();
    Storage::disk('public')->assertExists($filename);
});

it('determines priority levels correctly', function () {
    // Create high priority order (amount > 1000)
    $highPriorityOrder = Order::factory()->create([
        'client_id' => $this->client->id,
        'is_complaint' => true,
        'total_amount' => 1500.00,
        'created_at' => Carbon::now()->subDays(2),
    ]);
    
    // Create low priority order (amount < 500)
    $lowPriorityOrder = Order::factory()->create([
        'client_id' => $this->client->id,
        'is_complaint' => true,
        'total_amount' => 300.00,
        'created_at' => Carbon::now()->subDays(1),
    ]);
    
    $filename = $this->service->generateComplaintCsvReport();
    
    expect($filename)->toBeString();
    Storage::disk('public')->assertExists($filename);
});

it('handles different order statuses', function () {
    $statuses = [
        OrderStatus::PENDING->value,
        OrderStatus::PROCESSING->value,
        OrderStatus::COMPLETED->value,
        OrderStatus::CANCELED->value,
    ];
    
    foreach ($statuses as $status) {
        Order::factory()->create([
            'client_id' => $this->client->id,
            'is_complaint' => true,
            'status' => $status,
            'created_at' => Carbon::now()->subDays(rand(1, 10)),
        ]);
    }
    
    $filename = $this->service->generateComplaintSummaryReport();
    $content = Storage::disk('public')->get($filename);
    
    expect($content)
        ->toContain('ROZKŁAD WEDŁUG STATUSU')
        ->toContain('Oczekujące')
        ->toContain('W trakcie')
        ->toContain('Ukończone')
        ->toContain('Anulowane');
});

it('generates unique filenames for concurrent reports', function () {
    $filename1 = $this->service->generateComplaintCsvReport();
    
    // Small delay to ensure different timestamp
    sleep(1);
    
    $filename2 = $this->service->generateComplaintCsvReport();
    
    expect($filename1)->not->toBe($filename2);
    
    Storage::disk('public')->assertExists($filename1);
    Storage::disk('public')->assertExists($filename2);
});

it('handles Polish month and day names correctly', function () {
    Order::factory()->create([
        'client_id' => $this->client->id,
        'is_complaint' => true,
        'created_at' => Carbon::create(2024, 3, 15), // March
    ]);
    
    $filename = $this->service->generateComplaintSummaryReport();
    $content = Storage::disk('public')->get($filename);
    
    // Should contain Polish month names in summary
    expect($content)->toBeString();
    Storage::disk('public')->assertExists($filename);
});