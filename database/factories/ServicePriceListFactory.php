<?php

namespace Database\Factories;

use App\Models\PriceList;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ServicePriceList>
 */
class ServicePriceListFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'price_list_id' => PriceList::factory()->create(),
            'service_id' => Service::factory()->create(),
            'price' => fake()->randomFloat(2, 150, 300),
        ];
    }
}
