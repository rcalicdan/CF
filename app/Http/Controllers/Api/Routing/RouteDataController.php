<?php

namespace App\Http\Controllers\Api\Routing;

use App\Http\Controllers\Controller;
use App\ActionService\RouteDataService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RouteDataController extends Controller
{
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

            return response()->json([
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
            return response()->json([
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

            return response()->json([
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
            return response()->json([
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

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);
        } catch (\Exception $e) {
            return response()->json([
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

            return response()->json([
                'success' => true,
                'message' => 'Geocoding process completed',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
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

            return response()->json([
                'success' => true,
                'message' => 'Route optimization saved successfully',
                'data' => $optimization
            ]);
        } catch (\Exception $e) {
            return response()->json([
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

            return response()->json([
                'success' => true,
                'data' => $optimization
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve saved optimization',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
