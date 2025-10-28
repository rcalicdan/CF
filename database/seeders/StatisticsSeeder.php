<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderCarpet;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class StatisticsSeeder extends Seeder
{
    /**
     * Run the database seeds - Lightweight version (max 50 orders)
     */
    public function run(): void
    {
        $this->command->info('Starting lightweight statistics seeding...');

        $totalOrders = 50;
        $startDate = Carbon::now()->subMonths(3);
        
        for ($i = 0; $i < $totalOrders; $i++) {
            $orderDate = $startDate->copy()->addDays(rand(0, 90));
            
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

            $carpetsCount = rand(1, 2);
            for ($c = 0; $c < $carpetsCount; $c++) {
                OrderCarpet::factory()->create([
                    'order_id' => $order->id,
                    'status' => $this->getRandomCarpetStatus(),
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);
            }

            if (($i + 1) % 10 === 0) {
                $this->command->info("Seeded " . ($i + 1) . " orders...");
            }
        }

        $this->command->info("Successfully seeded {$totalOrders} orders with date distribution!");
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