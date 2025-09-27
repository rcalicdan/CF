<?php

use App\ActionService\ComplaintPdfReportService;
use App\Models\Order;
use App\Models\Client;
use App\Models\Driver;
use App\Models\User;
use App\Models\OrderCarpet;
use App\Models\Service;
use App\Enums\OrderStatus;
use App\Enums\OrderCarpetStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

describe('ComplaintPdfReportService', function () {
    beforeEach(function () {
        Storage::fake('public');

        Carbon::setTestNow('2024-03-15 10:30:00');

        $this->service = new ComplaintPdfReportService();
    });

    afterEach(function () {
        Carbon::setTestNow(); 
    });

    it('can generate complaint report for default 30 days period', function () {
        createTestComplaintData();

        $filename = $this->service->generateComplaintReport();

        expect($filename)
            ->toBeString()
            ->toMatch('/^raport_statystyk_skarg_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}\.pdf$/')
            ->and(Storage::disk('public')->exists($filename))
            ->toBeTrue();

        $fileContent = Storage::disk('public')->get($filename);
        expect($fileContent)
            ->not->toBeEmpty()
            ->and(strlen($fileContent))
            ->toBeGreaterThan(1000); 
    });

    it('can generate complaint report for custom days period', function () {
        $this->createTestComplaintDataWithDifferentDates();

        $filename = $this->service->generateComplaintReport(7);

     
        expect($filename)
            ->toBeString()
            ->and(Storage::disk('public')->exists($filename))
            ->toBeTrue();

        $fileContent = Storage::disk('public')->get($filename);
        expect($fileContent)
            ->toStartWith('%PDF')
            ->and(strlen($fileContent))
            ->toBeGreaterThan(500);
    });

    it('can generate monthly complaint report', function () {
       createTestComplaintDataForMonth('2024-03');

        $filename = $this->service->generateComplaintReportForMonth('2024-03');

      
        expect($filename)
            ->toBeString()
            ->toMatch('/^raport_statystyk_skarg_2024-03_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}\.pdf$/')
            ->and(Storage::disk('public')->exists($filename))
            ->toBeTrue();

        $fileContent = Storage::disk('public')->get($filename);
        expect($fileContent)
            ->not->toBeEmpty()
            ->and(strlen($fileContent))
            ->toBeGreaterThan(1000);
    });

    it('handles empty complaint data gracefully', function () {
        $filename = $this->service->generateComplaintReport(30);

        expect($filename)
            ->toBeString()
            ->and(Storage::disk('public')->exists($filename))
            ->toBeTrue();

        $fileContent = Storage::disk('public')->get($filename);
        expect($fileContent)
            ->not->toBeEmpty()
            ->and(strlen($fileContent))
            ->toBeGreaterThan(500); 
    });

    it('generates report with all complaint statuses represented', function () {
        createComplaintsWithAllStatuses();

        $filename = $this->service->generateComplaintReport(30);

        expect($filename)
            ->toBeString()
            ->and(Storage::disk('public')->exists($filename))
            ->toBeTrue();
    });

    it('generates report with various order values', function () {
        createComplaintsWithVariousOrderValues();

        $filename = $this->service->generateComplaintReport(30);

        expect($filename)
            ->toBeString()
            ->and(Storage::disk('public')->exists($filename))
            ->toBeTrue();
    });

    it('handles large datasets efficiently', function () {
        createLargeComplaintDataset();

        $startTime = microtime(true);
        $filename = $this->service->generateComplaintReport(60);
        $endTime = microtime(true);

        expect($endTime - $startTime)
            ->toBeLessThan(10.0) 
            ->and($filename)
            ->toBeString()
            ->and(Storage::disk('public')->exists($filename))
            ->toBeTrue();
    });

    it('generates consistent filenames with timestamp', function () {
        Carbon::setTestNow('2024-03-15 14:25:30');

        $filename1 = $this->service->generateComplaintReport(30);

        sleep(1);
        $filename2 = $this->service->generateComplaintReport(30);

        expect($filename1)
            ->not->toBe($filename2)
            ->and($filename1)
            ->toContain('2024-03-15_14-25')
            ->and(Storage::disk('public')->exists($filename1))
            ->toBeTrue()
            ->and(Storage::disk('public')->exists($filename2))
            ->toBeTrue();
    });

    it('handles edge cases in date ranges', function () {
        createEdgeCaseComplaintData();

        $filename1 = $this->service->generateComplaintReport(1);
        $filename2 = $this->service->generateComplaintReport(365);

        expect($filename1)
            ->toBeString()
            ->and($filename2)
            ->toBeString()
            ->and(Storage::disk('public')->exists($filename1))
            ->toBeTrue()
            ->and(Storage::disk('public')->exists($filename2))
            ->toBeTrue();
    });

    it('generates monthly report for different months', function () {
        createMultiMonthComplaintData();

        $january = $this->service->generateComplaintReportForMonth('2024-01');
        $february = $this->service->generateComplaintReportForMonth('2024-02');
        $march = $this->service->generateComplaintReportForMonth('2024-03');

        expect($january)
            ->toBeString()
            ->toContain('2024-01')
            ->and($february)
            ->toBeString()
            ->toContain('2024-02')
            ->and($march)
            ->toBeString()
            ->toContain('2024-03')
            ->and(Storage::disk('public')->exists($january))
            ->toBeTrue()
            ->and(Storage::disk('public')->exists($february))
            ->toBeTrue()
            ->and(Storage::disk('public')->exists($march))
            ->toBeTrue();
    });

    it('handles special characters in client and driver names', function () {
       createComplaintsWithSpecialCharacters();

        $filename = $this->service->generateComplaintReport(30);

        expect($filename)
            ->toBeString()
            ->and(Storage::disk('public')->exists($filename))
            ->toBeTrue();
    });


    function createTestComplaintData(): void
    {
        $drivers = User::factory(3)->create();
        $clients = Client::factory(5)->create();
        $services = Service::factory(4)->create([
            'name' => fn() => fake()->randomElement(['Pranie dywanów', 'Czyszczenie tapicerki', 'Impregnacja', 'Naprawa'])
        ]);
        $driverModels = [];
        foreach ($drivers as $user) {
            $driverModels[] = Driver::factory()->create(['user_id' => $user->id]);
        }

        foreach (range(1, 15) as $i) {
            $order = Order::factory()->create([
                'is_complaint' => true,
                'status' => fake()->randomElement([
                    OrderStatus::PENDING->value,
                    OrderStatus::PROCESSING->value,
                    OrderStatus::COMPLETED->value,
                    OrderStatus::CANCELED->value
                ]),
                'client_id' => $clients->random()->id,
                'driver_id' => $driverModels[array_rand($driverModels)]->id,
                'total_amount' => fake()->randomFloat(2, 100, 2000),
                'created_at' => Carbon::now()->subDays(rand(1, 29)),
                'updated_at' => Carbon::now()->subDays(rand(0, 5)),
                'schedule_date' => Carbon::now()->subDays(rand(0, 10))
            ]);

            foreach (range(1, rand(1, 5)) as $j) {
                $carpet = OrderCarpet::factory()->create([
                    'order_id' => $order->id,
                    'status' => fake()->randomElement([
                        OrderCarpetStatus::COMPLAINT->value,
                        OrderCarpetStatus::PENDING->value
                    ])
                ]);

                $carpet->services()->attach($services->random(rand(1, 2))->pluck('id'));
            }
        }
    }

    function createTestComplaintDataWithDifferentDates(): void
    {
        $clients = Client::factory(3)->create();
        $drivers = User::factory(2)->create();
        $driverModels = [];
        foreach ($drivers as $user) {
            $driverModels[] = Driver::factory()->create(['user_id' => $user->id]);
        }
        
        foreach (range(1, 7) as $i) {
            Order::factory()->create([
                'is_complaint' => true,
                'status' => OrderStatus::PENDING->value,
                'client_id' => $clients->random()->id,
                'driver_id' => $driverModels[array_rand($driverModels)]->id,
                'total_amount' => fake()->randomFloat(2, 200, 800),
                'created_at' => Carbon::now()->subDays($i),
            ]);
        }

        foreach (range(8, 15) as $i) {
            Order::factory()->create([
                'is_complaint' => true,
                'status' => OrderStatus::COMPLETED->value,
                'client_id' => $clients->random()->id,
                'driver_id' => $driverModels[array_rand($driverModels)]->id,
                'total_amount' => fake()->randomFloat(2, 300, 1200),
                'created_at' => Carbon::now()->subDays($i),
            ]);
        }
    }

    function createTestComplaintDataForMonth(string $month): void
    {
        $date = Carbon::createFromFormat('Y-m', $month);
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $clients = Client::factory(3)->create();
        $drivers = User::factory(2)->create();
        $driverModels = [];
        foreach ($drivers as $user) {
            $driverModels[] = Driver::factory()->create(['user_id' => $user->id]);
        }

        foreach (range(1, 10) as $i) {
            $randomDate = fake()->dateTimeBetween($startOfMonth, $endOfMonth);

            Order::factory()->create([
                'is_complaint' => true,
                'status' => fake()->randomElement([
                    OrderStatus::PENDING->value,
                    OrderStatus::PROCESSING->value,
                    OrderStatus::COMPLETED->value
                ]),
                'client_id' => $clients->random()->id,
                'driver_id' => $driverModels[array_rand($driverModels)]->id,
                'total_amount' => fake()->randomFloat(2, 150, 1500),
                'created_at' => $randomDate,
            ]);
        }
    }

    function createComplaintsWithAllStatuses(): void
    {
        $client = Client::factory()->create();
        $driver = Driver::factory()->create(['user_id' => User::factory()->create()->id]);

        $statuses = [
            OrderStatus::PENDING->value,
            OrderStatus::PROCESSING->value,
            OrderStatus::COMPLETED->value,
            OrderStatus::CANCELED->value
        ];

        foreach ($statuses as $status) {
            Order::factory()->create([
                'is_complaint' => true,
                'status' => $status,
                'client_id' => $client->id,
                'driver_id' => $driver->id,
                'total_amount' => fake()->randomFloat(2, 200, 1000),
                'created_at' => Carbon::now()->subDays(rand(1, 20)),
            ]);
        }
    }

    function createComplaintsWithVariousOrderValues(): void
    {
        $client = Client::factory()->create();
        $driver = Driver::factory()->create(['user_id' => User::factory()->create()->id]);

        $orderValues = [50, 250, 450, 750, 1200, 1800, 2500]; 

        foreach ($orderValues as $value) {
            Order::factory()->create([
                'is_complaint' => true,
                'status' => OrderStatus::PENDING->value,
                'client_id' => $client->id,
                'driver_id' => $driver->id,
                'total_amount' => $value,
                'created_at' => Carbon::now()->subDays(rand(1, 25)),
            ]);
        }
    }

    function createLargeComplaintDataset(): void
    {
        $clients = Client::factory(10)->create();
        $drivers = User::factory(5)->create();
        $driverModels = [];
        foreach ($drivers as $user) {
            $driverModels[] = Driver::factory()->create(['user_id' => $user->id]);
        }

        foreach (range(1, 100) as $i) {
            Order::factory()->create([
                'is_complaint' => true,
                'status' => fake()->randomElement([
                    OrderStatus::PENDING->value,
                    OrderStatus::PROCESSING->value,
                    OrderStatus::COMPLETED->value,
                    OrderStatus::CANCELED->value
                ]),
                'client_id' => $clients->random()->id,
                'driver_id' => $driverModels[array_rand($driverModels)]->id,
                'total_amount' => fake()->randomFloat(2, 100, 3000),
                'created_at' => Carbon::now()->subDays(rand(1, 60)),
            ]);
        }
    }

    function createEdgeCaseComplaintData(): void
    {
        $client = Client::factory()->create();
        $driver = Driver::factory()->create(['user_id' => User::factory()->create()->id]);

        Order::factory()->create([
            'is_complaint' => true,
            'status' => OrderStatus::PENDING->value,
            'client_id' => $client->id,
            'driver_id' => $driver->id,
            'total_amount' => 500,
            'created_at' => Carbon::now()->subDay()->startOfDay(),
        ]);

        Order::factory()->create([
            'is_complaint' => true,
            'status' => OrderStatus::COMPLETED->value,
            'client_id' => $client->id,
            'driver_id' => $driver->id,
            'total_amount' => 750,
            'created_at' => Carbon::now()->startOfDay(),
        ]);
    }

    function createMultiMonthComplaintData(): void
    {
        $client = Client::factory()->create();
        $driver = Driver::factory()->create(['user_id' => User::factory()->create()->id]);

        $months = ['2024-01', '2024-02', '2024-03'];

        foreach ($months as $month) {
            $date = Carbon::createFromFormat('Y-m', $month);

            foreach (range(1, 5) as $i) {
                Order::factory()->create([
                    'is_complaint' => true,
                    'status' => fake()->randomElement([
                        OrderStatus::PENDING->value,
                        OrderStatus::COMPLETED->value
                    ]),
                    'client_id' => $client->id,
                    'driver_id' => $driver->id,
                    'total_amount' => fake()->randomFloat(2, 200, 1000),
                    'created_at' => $date->copy()->addDays(rand(1, 28)),
                ]);
            }
        }
    }

    function createComplaintsWithSpecialCharacters(): void
    {
        $client = Client::factory()->create([
            'first_name' => 'Paweł',
            'last_name' => 'Żółć'
        ]);

        $user = User::factory()->create([
            'first_name' => 'Michał',
            'last_name' => 'Śłąski'
        ]);
        $driver = Driver::factory()->create(['user_id' => $user->id]);

        Order::factory()->create([
            'is_complaint' => true,
            'status' => OrderStatus::PENDING->value,
            'client_id' => $client->id,
            'driver_id' => $driver->id,
            'total_amount' => 600,
            'created_at' => Carbon::now()->subDays(5),
        ]);
    }
});

describe('ComplaintPdfReportService PDF Content Validation', function () {
    beforeEach(function () {
        Storage::fake('public');
        Carbon::setTestNow('2024-03-15 10:30:00');
        $this->service = new ComplaintPdfReportService();
    });

    it('generates PDF with correct mime type signature', function () {
        $this->createBasicComplaintData();

        $filename = $this->service->generateComplaintReport(30);

        $fileContent = Storage::disk('public')->get($filename);
        expect($fileContent)
            ->toStartWith('%PDF-')
            ->and($fileContent)
            ->toContain('%%EOF'); 
    });

    it('creates files with unique names for concurrent requests', function () {
     createBasicComplaintData();

        $filenames = [];
        for ($i = 0; $i < 5; $i++) {
            $filenames[] = $this->service->generateComplaintReport(30);
            usleep(100000); 
        }


        $uniqueFilenames = array_unique($filenames);
        expect(count($uniqueFilenames))
            ->toBe(5)
            ->and($filenames)
            ->each->toBeString();

        foreach ($filenames as $filename) {
            expect(Storage::disk('public')->exists($filename))->toBeTrue();
        }
    });

    function createBasicComplaintData(): void
    {
        $client = Client::factory()->create();
        $driver = Driver::factory()->create(['user_id' => User::factory()->create()->id]);

        Order::factory()->create([
            'is_complaint' => true,
            'status' => OrderStatus::PENDING->value,
            'client_id' => $client->id,
            'driver_id' => $driver->id,
            'total_amount' => 500,
            'created_at' => Carbon::now()->subDays(5),
        ]);
    }
});
