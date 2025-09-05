<?php

namespace Database\Seeders;

use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use App\Models\OrderCarpet;
use Illuminate\Database\Seeder;

class ComplaintSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $totalComplaints = 30;
        $this->command->info("Creating {$totalComplaints} complaints...");

        $orderCarpets = $this->ensureOrderCarpetsExist();

        $this->createOpenComplaints(intval($totalComplaints * 0.2), $orderCarpets);
        $this->createInProgressComplaints(intval($totalComplaints * 0.25), $orderCarpets);
        $this->createResolvedComplaints(intval($totalComplaints * 0.35), $orderCarpets);
        $this->createRejectedComplaints(intval($totalComplaints * 0.1), $orderCarpets);
        $this->createClosedComplaints(intval($totalComplaints * 0.1), $orderCarpets);

        $this->command->info("Successfully created complaints!");
        $this->showDetailedDistribution();
    }

    private function ensureOrderCarpetsExist(): array
    {
        $existingIds = OrderCarpet::pluck('id')->toArray();
        
        if (count($existingIds) < 50) {
            $this->command->info('Creating additional OrderCarpets for complaints...');
            OrderCarpet::factory()->count(50 - count($existingIds))->create();
            $existingIds = OrderCarpet::pluck('id')->toArray();
        }

        return $existingIds;
    }

    private function createOpenComplaints(int $count, array $orderCarpetIds): void
    {
        Complaint::factory()
            ->count($count)
            ->open()
            ->withRealisticDates()
            ->create()
            ->each(function ($complaint) use ($orderCarpetIds) {
                $complaint->update([
                    'order_carpet_id' => fake()->randomElement($orderCarpetIds)
                ]);
            });
    }

    private function createInProgressComplaints(int $count, array $orderCarpetIds): void
    {
        Complaint::factory()
            ->count($count)
            ->inProgress()
            ->withRealisticDates()
            ->create()
            ->each(function ($complaint) use ($orderCarpetIds) {
                $complaint->update([
                    'order_carpet_id' => fake()->randomElement($orderCarpetIds)
                ]);
            });
    }

    private function createResolvedComplaints(int $count, array $orderCarpetIds): void
    {
        Complaint::factory()
            ->count($count)
            ->resolved()
            ->withRealisticDates()
            ->create()
            ->each(function ($complaint) use ($orderCarpetIds) {
                $complaint->update([
                    'order_carpet_id' => fake()->randomElement($orderCarpetIds)
                ]);
            });
    }

    private function createRejectedComplaints(int $count, array $orderCarpetIds): void
    {
        Complaint::factory()
            ->count($count)
            ->rejected()
            ->withRealisticDates()
            ->create()
            ->each(function ($complaint) use ($orderCarpetIds) {
                $complaint->update([
                    'order_carpet_id' => fake()->randomElement($orderCarpetIds)
                ]);
            });
    }

    private function createClosedComplaints(int $count, array $orderCarpetIds): void
    {
        Complaint::factory()
            ->count($count)
            ->closed()
            ->withRealisticDates()
            ->create()
            ->each(function ($complaint) use ($orderCarpetIds) {
                $complaint->update([
                    'order_carpet_id' => fake()->randomElement($orderCarpetIds)
                ]);
            });
    }

    private function showDetailedDistribution(): void
    {
        $total = Complaint::count();
        $distribution = Complaint::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $this->command->info("Total complaints created: {$total}");
        $this->command->info('Status Distribution:');
        
        foreach (ComplaintStatus::cases() as $status) {
            $count = $distribution[$status->value] ?? 0;
            $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
            $this->command->info("  {$status->value}: {$count} ({$percentage}%)");
        }
    }
}