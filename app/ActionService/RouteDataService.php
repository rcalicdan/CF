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
     * Get all drivers with their user information who have completed or undelivered orders
     */
    public function getAllDrivers(): Collection
    {
        return Driver::with('user')
            ->whereHas('orders', function ($query) {
                $query->whereIn('status', ['completed', 'undelivered']);
            })
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
            // ->whereIn('status', ['completed', 'undelivered'])
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
            // ->whereIn('status', ['completed', 'undelivered'])
        ;

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

    /**
     * Get all route optimizations for a specific driver
     */
    public function getRouteOptimizationsForDriver(int $driverId, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = RouteOptimization::with('driver.user')
            ->where('driver_id', $driverId);

        if ($startDate) {
            $query->whereDate('optimization_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('optimization_date', '<=', $endDate);
        }

        return $query->orderBy('optimization_date', 'desc')
            ->get()
            ->map(function ($optimization) {
                $optimizationResult = $optimization->optimization_result ?? [];
                $orderSequence = $optimization->order_sequence ?? [];

                return [
                    'id' => $optimization->id,
                    'driver_id' => $optimization->driver_id,
                    'driver_name' => $optimization->driver->user->full_name ?? 'Unknown Driver',
                    'optimization_date' => $optimization->optimization_date->format('Y-m-d'),
                    'optimization_date_formatted' => $optimization->optimization_date->format('d M Y'),
                    'total_distance' => $optimization->total_distance,
                    'total_time' => $optimization->total_time,
                    'estimated_fuel_cost' => $optimization->estimated_fuel_cost,
                    'carbon_footprint' => $optimization->carbon_footprint,
                    'total_orders' => count($orderSequence),
                    'order_sequence' => $orderSequence,
                    'optimization_result' => $optimizationResult,
                    'is_manual_edit' => $optimization->is_manual_edit,
                    'manual_modifications' => $optimization->manual_modifications,
                    'savings' => $optimizationResult['savings'] ?? 0,
                    'total_value' => $optimizationResult['total_value'] ?? 0,
                    'route_steps' => $optimizationResult['route_steps'] ?? [],
                    'route_steps_count' => count($optimizationResult['route_steps'] ?? []),
                    'geometry' => $optimizationResult['geometry'] ?? null,
                    'optimization_timestamp' => $optimizationResult['optimization_timestamp'] ?? null,

                    'created_at' => $optimization->created_at->toISOString(),
                    'updated_at' => $optimization->updated_at->toISOString(),
                ];
            });
    }

    /**
     * Get route optimization statistics for a driver
     */
    public function getDriverRouteOptimizationStats(int $driverId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = RouteOptimization::where('driver_id', $driverId);

        if ($startDate) {
            $query->whereDate('optimization_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('optimization_date', '<=', $endDate);
        }

        $optimizations = $query->get();

        $totalDistance = $optimizations->sum('total_distance');
        $totalTime = $optimizations->sum('total_time');
        $totalFuelCost = $optimizations->sum('estimated_fuel_cost');
        $totalCarbonFootprint = $optimizations->sum('carbon_footprint');

        $totalSavings = 0;
        $totalValue = 0;
        $totalOrders = 0;
        $totalRouteSteps = 0;

        foreach ($optimizations as $optimization) {
            $optimizationResult = $optimization->optimization_result ?? [];
            $orderSequence = $optimization->order_sequence ?? [];

            $totalSavings += $optimizationResult['savings'] ?? 0;
            $totalValue += $optimizationResult['total_value'] ?? 0;
            $totalOrders += count($orderSequence);
            $totalRouteSteps += count($optimizationResult['route_steps'] ?? []);
        }

        $optimizationCount = $optimizations->count();

        return [
            'total_optimizations' => $optimizationCount,
            'total_distance' => round($totalDistance, 2),
            'total_time' => $totalTime,
            'total_fuel_cost' => round($totalFuelCost, 2),
            'total_carbon_footprint' => round($totalCarbonFootprint, 2),
            'total_savings' => round($totalSavings, 2),
            'total_value' => round($totalValue, 2),
            'total_orders_optimized' => $totalOrders,
            'total_route_steps' => $totalRouteSteps,
            'average_distance_per_route' => $optimizationCount > 0 ? round($totalDistance / $optimizationCount, 2) : 0,
            'average_time_per_route' => $optimizationCount > 0 ? round($totalTime / $optimizationCount, 2) : 0,
            'average_fuel_cost_per_route' => $optimizationCount > 0 ? round($totalFuelCost / $optimizationCount, 2) : 0,
            'average_savings_per_route' => $optimizationCount > 0 ? round($totalSavings / $optimizationCount, 2) : 0,
            'average_orders_per_route' => $optimizationCount > 0 ? round($totalOrders / $optimizationCount, 2) : 0,

            'manual_edits_count' => $optimizations->where('is_manual_edit', true)->count(),
            'optimizations_with_geometry' => $optimizations->filter(function ($opt) {
                return !empty($opt->optimization_result['geometry'] ?? null);
            })->count(),

            'date_range' => [
                'start' => $startDate,
                'end' => $endDate,
            ],

            'period_breakdown' => $this->getPeriodBreakdown($optimizations),
            'distance_breakdown' => $this->getDistanceBreakdown($optimizations)
        ];
    }

    /**
     * Get breakdown by time periods
     */
    private function getPeriodBreakdown(Collection $optimizations): array
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'today' => $optimizations->filter(fn($opt) => $opt->optimization_date->isToday())->count(),
            'this_week' => $optimizations->filter(fn($opt) => $opt->optimization_date->gte($thisWeek))->count(),
            'this_month' => $optimizations->filter(fn($opt) => $opt->optimization_date->gte($thisMonth))->count(),
            'last_30_days' => $optimizations->filter(fn($opt) => $opt->optimization_date->gte($today->copy()->subDays(30)))->count(),
        ];
    }

    /**
     * Get breakdown by distance ranges
     */
    private function getDistanceBreakdown(Collection $optimizations): array
    {
        return [
            'short_routes' => $optimizations->filter(fn($opt) => ($opt->total_distance ?? 0) < 50)->count(), // < 50km
            'medium_routes' => $optimizations->filter(fn($opt) => ($opt->total_distance ?? 0) >= 50 && ($opt->total_distance ?? 0) < 150)->count(), // 50-150km
            'long_routes' => $optimizations->filter(fn($opt) => ($opt->total_distance ?? 0) >= 150)->count(), // >= 150km
        ];
    }
}
