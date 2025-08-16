<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderCarpetSeeder extends Seeder
{
    /**
     * Number of records to insert per batch.
     *
     * @var int
     */
    protected $batchSize = 100;

    /**
     * Run the database seeds.
     *
     * Note: Adjust the data as needed according to your schema.
     *
     * @return void
     */
    public function run()
    {
        $orderCarpets = [];

        for ($i = 1; $i <= 1000; $i++) {
            $orderCarpets[] = [
                'order_id' => rand(1, 100),
                'carpet_id' => rand(1, 50),
                'quantity' => rand(1, 10),
                'price' => rand(10, 100),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($orderCarpets) === $this->batchSize) {
                DB::table('order_carpets')->insert($orderCarpets);
                $orderCarpets = [];
            }
        }

        if (! empty($orderCarpets)) {
            DB::table('order_carpets')->insert($orderCarpets);
        }
    }
}
