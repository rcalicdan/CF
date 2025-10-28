<?php

namespace Database\Factories;

use App\Models\RouteOptimization;
use App\Models\Driver;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class RouteOptimizationFactory extends Factory
{
    protected $model = RouteOptimization::class;

    public function definition(): array
    {
        return [
            'driver_id' => Driver::factory(),
            'optimization_date' => Carbon::today(),
            'optimization_result' => [
                'total_distance' => $this->faker->randomFloat(2, 10, 200),
                'total_time' => $this->faker->numberBetween(60, 480),
                'savings' => $this->faker->randomFloat(2, 5, 50),
                'total_value' => $this->faker->randomFloat(2, 200, 2000),
                'route_steps' => [
                    ['step' => 1, 'address' => $this->faker->address],
                    ['step' => 2, 'address' => $this->faker->address]
                ],
                'geometry' => 'mock_geometry_' . $this->faker->uuid,
                'optimization_timestamp' => now()->toISOString()
            ],
            'order_sequence' => $this->faker->randomElements([1, 2, 3, 4, 5], $this->faker->numberBetween(2, 5)),
            'total_distance' => $this->faker->randomFloat(2, 10, 200),
            'total_time' => $this->faker->numberBetween(60, 480),
            'estimated_fuel_cost' => $this->faker->randomFloat(2, 15, 100),
            'carbon_footprint' => $this->faker->randomFloat(2, 5, 50),
            'is_manual_edit' => $this->faker->boolean(20), 
            'manual_modifications' => null
        ];
    }

    public function manualEdit(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_manual_edit' => true,
            'manual_modifications' => [
                'modified_at' => now()->toISOString(),
                'changes' => ['reordered stops', 'added break time']
            ]
        ]);
    }
}