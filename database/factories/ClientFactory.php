<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName,
            'street_name' => fake()->streetName,
            'street_number' => fake()->buildingNumber,
            'postal_code' => fake()->postcode,
            'city' => fake()->city,
            'phone_number' => fake()->phoneNumber,
            'remarks' => fake()->text,
        ];
    }
}
