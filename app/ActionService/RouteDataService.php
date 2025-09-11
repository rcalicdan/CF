<?php

namespace App\ActionService;

use App\Models\Driver;
use App\Models\Order;
use App\Models\Client;
use App\Models\RouteOptimization;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RouteDataService
{
    /**
     * Get all drivers with their user information
     */
    public function getAllDrivers(): Collection
    {
        return Driver::with('user')
            ->get()
            ->map(function ($driver) {
                return [
                    'id' => $driver->id,
                    'user_id' => $driver->user_id,
                    'full_name' => $driver->user->full_name ?? $driver->user->name,
                    'license_number' => $driver->license_number,
                    'vehicle_details' => $driver->vehicle_details,
                    'phone_number' => $driver->phone_number ?? $driver->user->phone_number,
                    'created_at' => $driver->created_at->toISOString(),
                    'updated_at' => $driver->updated_at->toISOString(),
                ];
            });
    }

    /**
     * Get orders for a specific driver and date
     */
    public function getOrdersForDriverAndDate(int $driverId, string $date): Collection
    {
        return Order::with(['client', 'driver.user'])
            ->where('assigned_driver_id', $driverId)
            ->whereDate('schedule_date', $date)
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->get()
            ->map(function ($order) {
                return $this->transformOrderForRouteData($order);
            });
    }

    /**
     * Get all orders within a date range
     */
    public function getAllOrdersForDateRange(?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = Order::with(['client', 'driver.user'])
            ->whereNotNull('assigned_driver_id')
            ->whereIn('status', ['pending', 'confirmed', 'in_progress']);

        if ($startDate) {
            $query->whereDate('schedule_date', '>=', $startDate);
        } else {
            $query->whereDate('schedule_date', '>=', Carbon::today());
        }

        if ($endDate) {
            $query->whereDate('schedule_date', '<=', $endDate);
        } else {
            $query->whereDate('schedule_date', '<=', Carbon::now()->addDays(30));
        }

        return $query->orderBy('schedule_date')
            ->orderBy('assigned_driver_id')
            ->get()
            ->map(function ($order) {
                return $this->transformOrderForRouteData($order);
            });
    }

    /**
     * Save route optimization result
     */
    public function saveRouteOptimization(array $data): RouteOptimization
    {
        return RouteOptimization::updateOrCreate(
            [
                'driver_id' => $data['driver_id'],
                'optimization_date' => $data['optimization_date']
            ],
            [
                'optimization_result' => $data['optimization_result'],
                'order_sequence' => $data['order_sequence'] ?? [],
                'total_distance' => $data['total_distance'] ?? null,
                'total_time' => $data['total_time'] ?? null,
                'estimated_fuel_cost' => $data['estimated_fuel_cost'] ?? null,
                'carbon_footprint' => $data['carbon_footprint'] ?? null,
                'is_manual_edit' => $data['is_manual_edit'] ?? false,
                'manual_modifications' => $data['manual_modifications'] ?? null
            ]
        );
    }

    /**
     * Get saved route optimization
     */
    public function getSavedRouteOptimization(int $driverId, string $date): ?RouteOptimization
    {
        return RouteOptimization::where('driver_id', $driverId)
            ->where('optimization_date', $date)
            ->first();
    }

    /**
     * Transform order model for VROOM optimization
     */
    private function transformOrderForVroomData(Order $order): array
    {
        $baseData = $this->transformOrderForRouteData($order);

        if ($order->client && $order->client->hasCoordinates()) {
            $baseData['coordinates'] = $order->client->vroom_coordinates; // [lng, lat]
        }

        return $baseData;
    }

    /**
     * Get orders formatted specifically for VROOM optimization
     */
    public function getVroomOptimizedOrdersForDriverAndDate(int $driverId, string $date): Collection
    {
        return Order::with(['client', 'driver.user'])
            ->where('assigned_driver_id', $driverId)
            ->whereDate('schedule_date', $date)
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->get()
            ->map(function ($order) {
                return $this->transformOrderForVroomData($order);
            });
    }

    /**
     * Transform order model for route data
     */
    private function transformOrderForRouteData(Order $order): array
    {
        $priority = $this->calculateOrderPriority($order);

        return [
            'id' => $order->id,
            'driver_id' => $order->assigned_driver_id,
            'client_name' => $order->client_name,
            'address' => $order->address,
            'coordinates' => $order->coordinates,
            'total_amount' => (float) $order->total_amount,
            'status' => $order->status,
            'status_label' => $order->status_label,
            'priority' => $priority,
            'delivery_date' => $order->schedule_date->format('Y-m-d'),
            'schedule_time' => $order->schedule_date->format('H:i'),
            'schedule_datetime' => $order->schedule_date->toISOString(),
            'client_phone' => $order->client->phone_number ?? null,
            'has_coordinates' => $order->hasCoordinates(),
            'driver_name' => $order->driver_name,
            'driver_full_name' => $order->driver_full_name,
            'is_complaint' => $order->is_complaint,
            'created_at' => $order->created_at->toISOString(),
            'updated_at' => $order->updated_at->toISOString(),
        ];
    }

    /**
     * Calculate order priority based on business rules
     */
    private function calculateOrderPriority(Order $order): string
    {
        $amount = $order->total_amount;
        $isComplaint = $order->is_complaint;
        $scheduleDate = $order->schedule_date;
        $now = Carbon::now();

        // High priority conditions
        if ($isComplaint) {
            return 'high';
        }

        if ($amount >= 1000) {
            return 'high';
        }

        if ($scheduleDate->diffInDays($now) <= 1) {
            return 'high';
        }

        // Medium priority conditions
        if ($amount >= 500) {
            return 'medium';
        }

        if ($scheduleDate->diffInDays($now) <= 3) {
            return 'medium';
        }

        // Default to low priority
        return 'low';
    }

    /**
     * Get route statistics
     */
    public function getRouteStatistics(?int $driverId = null, ?string $date = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = Order::with(['client', 'driver.user'])
            ->whereNotNull('assigned_driver_id');

        if ($driverId) {
            $query->where('assigned_driver_id', $driverId);
        }

        if ($date) {
            $query->whereDate('schedule_date', $date);
        } elseif ($startDate && $endDate) {
            $query->whereBetween('schedule_date', [$startDate, $endDate]);
        }

        $orders = $query->get();
        $ordersData = $orders->map(fn($order) => $this->transformOrderForRouteData($order));

        return [
            'total_orders' => $orders->count(),
            'total_value' => $orders->sum('total_amount'),
            'orders_with_coordinates' => $orders->filter(fn($order) => $order->hasCoordinates())->count(),
            'orders_without_coordinates' => $orders->filter(fn($order) => !$order->hasCoordinates())->count(),
            'status_breakdown' => $orders->groupBy('status')->map(fn($group) => [
                'count' => $group->count(),
                'total_value' => $group->sum('total_amount')
            ]),
            'priority_breakdown' => $ordersData->groupBy('priority')->map(fn($group) => [
                'count' => $group->count(),
                'total_value' => $group->sum('total_amount')
            ]),
            'driver_breakdown' => $orders->groupBy('assigned_driver_id')->map(fn($group) => [
                'count' => $group->count(),
                'total_value' => $group->sum('total_amount'),
                'driver_name' => $group->first()->driver_name ?? 'Unknown'
            ]),
            'average_order_value' => $orders->count() > 0 ? round($orders->avg('total_amount'), 2) : 0,
            'complaints_count' => $orders->where('is_complaint', true)->count(),
            'date_range' => [
                'start' => $startDate,
                'end' => $endDate,
                'specific_date' => $date
            ]
        ];
    }

    /**
     * Trigger geocoding for clients without coordinates
     */
    public function geocodeMissingCoordinates(int $limit = 20): array
    {
        $clientsWithoutCoordinates = Client::withoutCoordinates()->limit($limit)->get();

        $geocoded = 0;
        $failed = 0;
        $errors = [];

        foreach ($clientsWithoutCoordinates as $client) {
            try {
                if ($client->geocodeAddress()) {
                    $client->save();
                    $geocoded++;

                    Log::info('Successfully geocoded client', [
                        'client_id' => $client->id,
                        'address' => $client->full_address
                    ]);
                } else {
                    $failed++;
                    $errors[] = "Failed to geocode client {$client->id}: {$client->full_address}";
                }

                // Rate limiting - sleep for 1 second between requests to respect Nominatim limits
                sleep(1);
            } catch (\Exception $e) {
                $failed++;
                $errorMsg = "Geocoding failed for client {$client->id}: {$e->getMessage()}";
                $errors[] = $errorMsg;

                Log::error('Geocoding failed for client', [
                    'client_id' => $client->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return [
            'processed' => $clientsWithoutCoordinates->count(),
            'geocoded' => $geocoded,
            'failed' => $failed,
            'errors' => $errors,
            'remaining_without_coordinates' => Client::withoutCoordinates()->count()
        ];
    }

    /**
     * Get summary data for dashboard
     */
    public function getDashboardSummary(): array
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'today' => [
                'orders_count' => Order::whereDate('schedule_date', $today)->count(),
                'orders_value' => Order::whereDate('schedule_date', $today)->sum('total_amount'),
                'drivers_active' => Order::whereDate('schedule_date', $today)
                    ->distinct('assigned_driver_id')
                    ->whereNotNull('assigned_driver_id')
                    ->count()
            ],
            'this_week' => [
                'orders_count' => Order::where('schedule_date', '>=', $thisWeek)->count(),
                'orders_value' => Order::where('schedule_date', '>=', $thisWeek)->sum('total_amount'),
            ],
            'this_month' => [
                'orders_count' => Order::where('schedule_date', '>=', $thisMonth)->count(),
                'orders_value' => Order::where('schedule_date', '>=', $thisMonth)->sum('total_amount'),
            ],
            'clients_without_coordinates' => Client::withoutCoordinates()->count(),
            'total_drivers' => Driver::count(),
            'pending_orders' => Order::where('status', 'pending')->count()
        ];
    }
}
