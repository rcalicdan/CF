<?php

namespace App\Http\Controllers\Api\Routing;

use App\Http\Controllers\Controller;
use App\ActionService\RouteDataService;
use App\Models\RouteOptimization;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RouteDataController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private RouteDataService $routeDataService
    ) {}

    /**
     * Get all drivers with their basic info
     */
    public function getDrivers(): JsonResponse
    {
        try {
            $drivers = $this->routeDataService->getAllDrivers();

            return response()->json([
                'success' => true,
                'data' => $drivers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch drivers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get orders for a specific driver and date
     */
    public function getOrdersForDriverAndDate(Request $request): JsonResponse
    {
        $request->validate([
            'driver_id' => 'required|integer|exists:drivers,id',
            'date' => 'required|date'
        ]);

        try {
            $orders = $this->routeDataService->getOrdersForDriverAndDate(
                $request->driver_id,
                $request->date
            );

            return $this->successResponse([
                'success' => true,
                'data' => $orders,
                'meta' => [
                    'driver_id' => $request->driver_id,
                    'date' => $request->date,
                    'total_orders' => count($orders),
                    'total_value' => collect($orders)->sum('total_amount')
                ]
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse([
                'success' => false,
                'message' => 'Failed to fetch orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all orders for the route optimizer (for initial load)
     */
    public function getAllOrdersForDateRange(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        try {
            $orders = $this->routeDataService->getAllOrdersForDateRange(
                $request->start_date,
                $request->end_date
            );

            return $this->successResponse([
                'success' => true,
                'data' => $orders,
                'meta' => [
                    'total_orders' => count($orders),
                    'date_range' => [
                        'start' => $request->start_date ?? 'all',
                        'end' => $request->end_date ?? 'all'
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse([
                'success' => false,
                'message' => 'Failed to fetch orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get route statistics
     */
    public function getRouteStatistics(Request $request): JsonResponse
    {
        $request->validate([
            'driver_id' => 'nullable|integer|exists:drivers,id',
            'date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date'
        ]);

        try {
            $statistics = $this->routeDataService->getRouteStatistics(
                $request->driver_id,
                $request->date,
                $request->start_date,
                $request->end_date
            );

            return $this->successResponse([
                'success' => true,
                'data' => $statistics
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse([
                'success' => false,
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Trigger geocoding for clients without coordinates
     */
    public function triggerGeocoding(): JsonResponse
    {
        try {
            $result = $this->routeDataService->geocodeMissingCoordinates();

            return $this->successResponse([
                'success' => true,
                'message' => 'Geocoding process completed',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse([
                'success' => false,
                'message' => 'Geocoding process failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save route optimization result
     */
    public function saveRouteOptimization(Request $request): JsonResponse
    {
        $request->validate([
            'driver_id' => 'required|integer|exists:drivers,id',
            'optimization_date' => 'required|date',
            'optimization_result' => 'required|array',
            'order_sequence' => 'nullable|array',
            'total_distance' => 'nullable|numeric',
            'total_time' => 'nullable|integer',
            'estimated_fuel_cost' => 'nullable|numeric',
            'carbon_footprint' => 'nullable|numeric',
            'is_manual_edit' => 'boolean',
            'manual_modifications' => 'nullable|array'
        ]);

        try {
            $optimization = $this->routeDataService->saveRouteOptimization($request->all());

            return $this->successResponse([
                'success' => true,
                'message' => 'Route optimization saved successfully',
                'data' => $optimization
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse([
                'success' => false,
                'message' => 'Failed to save route optimization',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get saved route optimization
     */
    public function getSavedRouteOptimization(Request $request): JsonResponse
    {
        $request->validate([
            'driver_id' => 'required|integer|exists:drivers,id',
            'date' => 'required|date'
        ]);

        try {
            $optimization = $this->routeDataService->getSavedRouteOptimization(
                $request->driver_id,
                $request->date
            );

            return $this->successResponse([
                'success' => true,
                'data' => $optimization
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse([
                'success' => false,
                'message' => 'Failed to retrieve saved optimization',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all route optimizations for the authenticated driver
     */
    public function getMyRouteOptimizations(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        try {
            $user = $request->user();

            if (!$user->isDriver()) {
                return $this->errorResponse([
                    'success' => false,
                    'message' => 'Access denied. Only drivers can access their route optimizations.'
                ], 403);
            }

            $driver = $user->driver;
            if (!$driver) {
                return $this->errorResponse([
                    'success' => false,
                    'message' => 'Driver profile not found.'
                ], 404);
            }

            $optimizations = $this->routeDataService->getRouteOptimizationsForDriver(
                $driver->id,
                $request->start_date,
                $request->end_date
            );

            $stats = $this->routeDataService->getDriverRouteOptimizationStats(
                $driver->id,
                $request->start_date,
                $request->end_date
            );

            return $this->successResponse([
                'success' => true,
                'data' => [
                    'optimizations' => $optimizations,
                    'statistics' => $stats,
                    'driver_info' => [
                        'id' => $driver->id,
                        'name' => $user->full_name,
                        'license_number' => $driver->license_number,
                        'vehicle_details' => $driver->vehicle_details
                    ]
                ],
                'meta' => [
                    'total_optimizations' => count($optimizations),
                    'date_range' => [
                        'start' => $request->start_date ?? 'all time',
                        'end' => $request->end_date ?? 'all time'
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse([
                'success' => false,
                'message' => 'Failed to fetch route optimizations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific route optimization details for the authenticated driver
     */
    public function getMyRouteOptimizationDetails(Request $request, int $optimizationId): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user->isDriver()) {
                return $this->errorResponse([
                    'success' => false,
                    'message' => 'Access denied. Only drivers can access their route optimizations.'
                ], 403);
            }

            $driver = $user->driver;
            if (!$driver) {
                return $this->errorResponse([
                    'success' => false,
                    'message' => 'Driver profile not found.'
                ], 404);
            }

            $optimization = RouteOptimization::with('driver.user')
                ->where('id', $optimizationId)
                ->where('driver_id', $driver->id)
                ->first();

            if (!$optimization) {
                return $this->errorResponse([
                    'success' => false,
                    'message' => 'Route optimization not found or access denied.'
                ], 404);
            }

            $orders = $this->routeDataService->getOrdersForDriverAndDate(
                $driver->id,
                $optimization->optimization_date->format('Y-m-d')
            );

            $optimizationResult = $optimization->optimization_result ?? [];
            $orderSequence = $optimization->order_sequence ?? [];

            return $this->successResponse([
                'success' => true,
                'data' => [
                    'optimization' => [
                        'id' => $optimization->id,
                        'driver_id' => $optimization->driver_id,
                        'driver_name' => $optimization->driver->user->full_name,
                        'optimization_date' => $optimization->optimization_date->toISOString(),
                        'optimization_date_formatted' => $optimization->optimization_date->toISOString(),
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
                        'geometry' => $optimizationResult['geometry'] ?? null,
                        'optimization_timestamp' => $optimizationResult['optimization_timestamp'] ?? null,
                        'vroom_raw' => $optimizationResult['vroom_raw'] ?? null,

                        'created_at' => $optimization->created_at->toISOString(),
                        'updated_at' => $optimization->updated_at->toISOString(),
                    ],
                    'orders' => $orders,
                    'driver_info' => [
                        'id' => $driver->id,
                        'name' => $user->full_name,
                        'license_number' => $driver->license_number,
                        'vehicle_details' => $driver->vehicle_details
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse([
                'success' => false,
                'message' => 'Failed to fetch route optimization details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
