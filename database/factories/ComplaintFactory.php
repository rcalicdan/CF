<?php

namespace Database\Factories;

use App\Enums\ComplaintStatus;
use App\Models\OrderCarpet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Complaint>
 */
class ComplaintFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $complaintTypes = [
            'Carpet was damaged during the cleaning process',
            'Stains were not completely removed as promised',
            'Carpet returned with new stains that were not there before',
            'Service took much longer than the estimated time',
            'Carpet has a strong chemical smell after cleaning',
            'Colors have faded significantly after the cleaning',
            'Carpet was not properly dried and smells musty',
            'My carpet went missing after pickup',
            'Wrong carpet was delivered to my address',
            'Carpet has shrunk considerably after cleaning',
            'The quality of service was below expectations',
            'Staff members were unprofessional during service',
            'Delivery was delayed beyond the promised date',
            'Carpet has new tears that appeared after cleaning',
            'I was charged more than the initial quote',
            'Carpet texture feels different after cleaning',
            'Pet odors were not eliminated as guaranteed',
            'Carpet backing was damaged during the process',
            'Service technician was late without notification',
            'Cleaning solution left visible residue on carpet',
        ];

        return [
            'order_carpet_id' => OrderCarpet::factory(),
            'complaint_details' => fake()->randomElement($complaintTypes),
            'status' => $this->getWeightedRandomStatus(),
        ];
    }

    /**
     * Get a weighted random status for more realistic distribution.
     */
    private function getWeightedRandomStatus(): string
    {
        $statuses = [
            ComplaintStatus::OPEN->value => 20,
            ComplaintStatus::IN_PROGRESS->value => 25,
            ComplaintStatus::RESOLVED->value => 35,
            ComplaintStatus::REJECTED->value => 10,
            ComplaintStatus::CLOSED->value => 10,
        ];

        $random = fake()->numberBetween(1, 100);
        $cumulative = 0;

        foreach ($statuses as $status => $weight) {
            $cumulative += $weight;
            if ($random <= $cumulative) {
                return $status;
            }
        }

        return ComplaintStatus::OPEN->value;
    }

    /**
     * Create complaint with open status.
     */
    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ComplaintStatus::OPEN->value,
        ]);
    }

    /**
     * Create complaint with in progress status.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ComplaintStatus::IN_PROGRESS->value,
        ]);
    }

    /**
     * Create complaint with resolved status.
     */
    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ComplaintStatus::RESOLVED->value,
        ]);
    }

    /**
     * Create complaint with rejected status.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ComplaintStatus::REJECTED->value,
        ]);
    }

    /**
     * Create complaint with closed status.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ComplaintStatus::CLOSED->value,
        ]);
    }

    /**
     * Create complaint with realistic timestamps.
     */
    public function withRealisticDates(): static
    {
        $createdAt = fake()->dateTimeBetween('-90 days', '-1 day');
        $updatedAt = fake()->dateTimeBetween($createdAt, 'now');

        return $this->state(fn (array $attributes) => [
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ]);
    }

    /**
     * Create complaint for existing order carpet.
     */
    public function forOrderCarpet($orderCarpetId): static
    {
        return $this->state(fn (array $attributes) => [
            'order_carpet_id' => $orderCarpetId,
        ]);
    }
}