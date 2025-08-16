<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Client;
use App\Models\PriceList;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory()->create(),
            'assigned_driver_id' => null,
            'schedule_date' => fake()->date(),
            'price_list_id' => PriceList::factory()->create(),
            'status' => OrderStatus::PENDING->value,
            'total_amount' => fake()->randomFloat(2, 0, 1000),
            'user_id' => User::factory()->create(),
        ];
    }
}
