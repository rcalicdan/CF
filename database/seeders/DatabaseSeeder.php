<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderCarpet;
use App\Models\PriceList;
use App\Models\Service;
use App\Models\ServicePriceList;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(1)->admin()->create();
        User::factory(1)->employee()->create();
        User::factory(1)->driver()->create();
        Order::factory(1)->create();
        OrderCarpet::factory(5)->create();
        PriceList::factory(5)->create();
        Service::factory(5)->create();
        ServicePriceList::factory(5)->create();
    }
}
