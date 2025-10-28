<?php

namespace Database\Seeders;

use App\Enums\ComplaintStatus;
use App\Models\OrderCarpet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Schema;

class ComplaintDataSeeder extends Seeder
{
    /**
     * Number of complaints to create
     */
    protected $totalComplaints = 50;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting complaint seeding...');

        // Check if complaints table exists
        if (!Schema::hasTable('complaints')) {
            $this->command->warn('Complaints table does not exist. Skipping...');
            return;
        }

        // Get available columns in complaints table
        $columns = Schema::getColumnListing('complaints');
        $this->command->info('Available columns: ' . implode(', ', $columns));

        // Get 50 random order carpet IDs
        $orderCarpetIds = OrderCarpet::whereHas('order')
            ->inRandomOrder()
            ->limit($this->totalComplaints)
            ->pluck('id')
            ->toArray();

        if (empty($orderCarpetIds)) {
            $this->command->warn('No order carpets found. Please seed orders and order carpets first.');
            return;
        }

        $complaints = [];

        foreach ($orderCarpetIds as $index => $orderCarpetId) {
            $reportedAt = now()->subDays(rand(1, 90));
            $isResolved = rand(0, 1);

            // Build complaint data based on available columns
            $complaint = [
                'order_carpet_id' => $orderCarpetId,
                'created_at' => $reportedAt,
                'updated_at' => now(),
            ];

            // Add complaint_details (required column)
            if (in_array('complaint_details', $columns)) {
                $complaintTexts = [
                    'The carpet still has visible stains after cleaning.',
                    'The delivery was delayed by several days without notification.',
                    'The cleaning service damaged the carpet edges.',
                    'Wrong type of cleaning service was performed.',
                    'The quality of cleaning did not meet expectations.',
                    'Strong chemical smell remains after cleaning.',
                    'Colors appear faded after the cleaning process.',
                ];
                $complaint['complaint_details'] = $complaintTexts[array_rand($complaintTexts)];
            }

            // Add description if it exists (different from complaint_details)
            if (in_array('description', $columns)) {
                $complaint['description'] = 'Customer complaint regarding service issue #' . ($index + 1);
            }

            if (in_array('complaint_type', $columns)) {
                $complaintTypes = ['stain_not_removed', 'damage', 'late_delivery', 'wrong_service', 'poor_quality', 'other'];
                $complaint['complaint_type'] = $complaintTypes[array_rand($complaintTypes)];
            }

            if (in_array('status', $columns)) {
                // Use the actual enum values from ComplaintStatus
                $statuses = array_column(ComplaintStatus::cases(), 'value');
                $complaint['status'] = $statuses[array_rand($statuses)];
            }

            if (in_array('priority', $columns)) {
                $priorities = ['low', 'medium', 'high', 'urgent'];
                $complaint['priority'] = $priorities[array_rand($priorities)];
            }

            if (in_array('reported_at', $columns)) {
                $complaint['reported_at'] = $reportedAt;
            }

            if (in_array('resolved_at', $columns)) {
                $complaint['resolved_at'] = $isResolved ? $reportedAt->copy()->addDays(rand(1, 30)) : null;
            }

            if (in_array('resolution_notes', $columns)) {
                $complaint['resolution_notes'] = $isResolved ? 'Issue resolved successfully for complaint #' . ($index + 1) : null;
            }

            if (in_array('remarks', $columns)) {
                $complaint['remarks'] = 'Additional remarks for complaint #' . ($index + 1);
            }

            $complaints[] = $complaint;
        }

        // Single batch insert
        DB::table('complaints')->insert($complaints);

        // Update related orders to mark as complaint
        $orderIds = OrderCarpet::whereIn('id', $orderCarpetIds)
            ->pluck('order_id')
            ->unique()
            ->toArray();

        DB::table('orders')
            ->whereIn('id', $orderIds)
            ->update(['is_complaint' => true, 'updated_at' => now()]);

        $this->command->info('Successfully seeded ' . count($complaints) . ' complaints!');
    }
}