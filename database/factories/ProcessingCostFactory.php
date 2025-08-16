<?php

namespace Database\Factories;

use App\Enums\CostType;
use App\Models\ProcessingCost;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProcessingCostFactory extends Factory
{
    protected $model = ProcessingCost::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'type' => $this->faker->randomElement(array_column(CostType::cases(), 'value')),
            'amount' => $this->faker->randomFloat(2, 10, 500),
            'cost_date' => $this->faker->date(),
        ];
    }
}
