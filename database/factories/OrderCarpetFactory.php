<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderCarpet>
 */
class OrderCarpetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $height = $this->faker->randomFloat(2, 1, 10);
        $width = $this->faker->randomFloat(2, 1, 7);

        return [
            'order_id' => Order::factory()->create(),
            'qr_code' => null,
            'height' => $height,
            'width' => $width,
            'total_area' => $height * $width,
            'measured_at' => $this->faker->dateTimeThisYear(),
            'status' => 'picked up',
            'remarks' => null,
        ];
    }

    public function withQrCode()
    {
        return $this->state(function (array $attributes) {
            return [
                'qr_code' => $this->faker->uuid,
            ];
        });
    }
}
