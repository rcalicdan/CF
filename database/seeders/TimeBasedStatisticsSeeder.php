<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderCarpet;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TimeBasedStatisticsSeeder extends Seeder
{
    /**
     * Run the database seeds with configurable time periods
     */
    public function run(): void
    {
        $this->command->info('Starting time-based statistics seeding...');

        // Seed for last 7 days
        $this->seedForPeriod(7, 'days', 20);
        
        // Seed for last 30 days
        $this->seedForPeriod(30, 'days', 50);
        
        // Seed for last 3 months
        $this->seedForPeriod(3, 'months', 100);

        $this->command->info('Successfully completed time-based statistics seeding!');
    }

    /**
     * Seed orders for a specific time period
     *
     * @param int $value Number of time units
     * @param string $unit Time unit (days, months)
     * @param int $totalOrders Number of orders to create
     */
    protected function seedForPeriod(int $value, string $unit, int $totalOrders): void
    {
        $periodName = $value . ' ' . $unit;
        $this->command->info("Seeding {$totalOrders} orders for last {$periodName}...");

        $startDate = $unit === 'months' 
            ? Carbon::now()->subMonths($value)
            : Carbon::now()->subDays($value);
        
        $endDate = Carbon::now();
        $totalDays = $startDate->diffInDays($endDate);

        for ($i = 0; $i < $totalOrders; $i++) {
            $orderDate = $startDate->copy()->addDays(rand(0, $totalDays));
            
            $client = Client::inRandomOrder()->first() ?? Client::factory()->create();
            
            $order = Order::factory()->create([
                'client_id' => $client->id,
                'schedule_date' => $orderDate,
                'status' => $this->getRandomStatus(),
                'total_amount' => rand(100, 500),
                'is_complaint' => rand(0, 10) === 0,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            $carpetsCount = rand(1, 3);
            for ($c = 0; $c < $carpetsCount; $c++) {
                OrderCarpet::factory()->create([
                    'order_id' => $order->id,
                    'status' => $this->getRandomCarpetStatus(),
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);
            }

            if (($i + 1) % 10 === 0) {
                $this->command->info("  Seeded " . ($i + 1) . "/{$totalOrders} orders for {$periodName}...");
            }
        }

        $this->command->info("✓ Completed seeding {$totalOrders} orders for last {$periodName}");
    }

    protected function getRandomStatus(): string
    {
        $statuses = ['pending', 'accepted', 'processing', 'completed', 'delivered', 'cancelled'];
        return $statuses[array_rand($statuses)];
    }

    protected function getRandomCarpetStatus(): string
    {
        $statuses = ['pending', 'picked up', 'at laundry', 'measured', 'completed', 'delivered'];
        return $statuses[array_rand($statuses)];
    }
}