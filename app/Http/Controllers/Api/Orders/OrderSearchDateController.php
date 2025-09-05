<?php

namespace App\Http\Controllers\Api\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\SearchOrdersByDateRequest;
use App\Http\Resources\Collection\OrderCollection;
use App\ActionService\OrderSearchService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class OrderSearchDateController extends Controller
{
    use ApiResponseTrait;

    protected OrderSearchService $orderSearchService;

    public function __construct(OrderSearchService $orderSearchService)
    {
        $this->orderSearchService = $orderSearchService;
    }

    public function getOrdersByDate(SearchOrdersByDateRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $perPage = $validated['per_page'] ?? 15;

            $orders = $this->orderSearchService->getOrdersByScheduleDate(
                $validated['schedule_date'],
                $perPage
            );

            return $this->successResponse([
                'message' => 'Orders retrieved successfully',
                'data' => new OrderCollection($orders),
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse([
                'message' => 'Invalid date format provided',
                'error' => 'Please provide a valid date in any common format (e.g., 2024-12-25, 25/12/2024, 12/25/2024, etc.)'
            ], 422);
        } catch (\Exception $e) {
            return $this->errorResponse([
                'message' => 'Failed to retrieve orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
