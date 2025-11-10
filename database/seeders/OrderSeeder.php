<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $client = \App\Models\Client::factory()->create();
        $priceList = \App\Models\PriceList::factory()->create();
        $user = \App\Models\User::factory()->create();

        $totalRecords = 100000;
        $batchSize = 5000;
        $batches = intdiv($totalRecords, $batchSize);

        for ($i = 0; $i < 10000; $i++) {
            $orders = Order::factory()->count($batchSize)->make([
                'client_id' => $client->id,
                'price_list_id' => $priceList->id,
                'user_id' => $user->id,
            ])->toArray();

            try {
                \DB::table('orders')->insert($orders);
                $this->command->info('Inserted batch '.($i + 1)." of {$batches}.");
            } catch (\Exception $e) {
                $this->command->error('Error on batch '.($i + 1).': '.$e->getMessage());
                break;
            }
        }
    }
}
