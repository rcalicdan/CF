<?php

namespace App\Http\Controllers\Api\Orders;

use App\ActionService\OrderDeliveryService;
use App\ActionService\PhotoUploadService;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderDeliveryConfirmation\DeliveryConfirmationFormRequest;
use App\Models\Order;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class OrderDeliveryController extends Controller
{
    use ApiResponseTrait;

    private $orderDeliveryService;

    private $photoUploadService;

    public function __construct(OrderDeliveryService $orderDeliveryService, PhotoUploadService $photoUploadService)
    {
        $this->orderDeliveryService = $orderDeliveryService;
        $this->photoUploadService = $photoUploadService;
    }

    /**
     * @OA\Post(
     *     path="/api/deliveries/{order}/confirm-delivery",
     *     summary="Confirm delivery of an order",
     *     description="Confirms the delivery of an order with either signature or data confirmation and processes the payment",
     *     tags={"Delivery"},
     *
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             oneOf={
     *
     *                 @OA\Schema(
     *                     type="object",
     *                     required={"confirmation_type", "signature_url", "payment_details"},
     *                     properties={
     *
     *                         @OA\Property(property="confirmation_type", type="string", enum={"signature"}),
     *                         @OA\Property(property="signature_url", type="string", format="uri"),
     *                         @OA\Property(
     *                             property="payment_details",
     *                             type="object",
     *                             required={"payment_method", "status"},
     *                             properties={
     *                                 @OA\Property(property="payment_method", type="string", enum={"cash", "card"}),
     *                                 @OA\Property(property="status", type="string", enum={"completed", "pending", "failed"})
     *                             }
     *                         )
     *                     }
     *                 ),
     *
     *                 @OA\Schema(
     *                     type="object",
     *                     required={"confirmation_type", "confirmation_data", "payment_details"},
     *                     properties={
     *
     *                         @OA\Property(property="confirmation_type", type="string", enum={"data"}),
     *                         @OA\Property(property="confirmation_data", type="string"),
     *                         @OA\Property(
     *                             property="payment_details",
     *                             type="object",
     *                             required={"payment_method", "status"},
     *                             properties={
     *                                 @OA\Property(property="payment_method", type="string", enum={"cash", "card"}),
     *                                 @OA\Property(property="status", type="string", enum={"completed", "pending", "failed"})
     *                             }
     *                         )
     *                     }
     *                 )
     *             },
     *
     *             @OA\Examples(
     *                 example="signature_confirmation",
     *                 summary="Signature confirmation example",
     *                 value={
     *                     "confirmation_type": "signature",
     *                     "signature_image": "file_upload",
     *                     "payment_details": {
     *                         "payment_method": "cash",
     *                         "status": "completed"
     *                     }
     *                 }
     *             ),
     *             @OA\Examples(
     *                 example="data_confirmation",
     *                 summary="Data confirmation example",
     *                 value={
     *                     "confirmation_type": "data",
     *                     "confirmation_data": "12345678",
     *                     "payment_details": {
     *                         "payment_method": "cash",
     *                         "status": "completed"
     *                     }
     *                 }
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Delivery confirmed successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Delivery confirmed successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="delivery_confirmation", type="object"),
     *                 @OA\Property(property="payment", type="object"),
     *                 @OA\Property(property="order", type="object")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function confirmDelivery(DeliveryConfirmationFormRequest $request, Order $order): JsonResponse
    {
        $validatedData = $request->validated();

        if ($request->hasFile('signature_image')) {
            $this->photoUploadService->handleConfirmationSignatureDataImageUpload(
                $validatedData,
                $request->file('signature_image')
            );
        }

        $orderDeliveryConfirmation = $this->orderDeliveryService
            ->confirmDelivery($order, $validatedData);

        return $this->successResponse([
            'status' => 'success',
            'message' => 'Delivery confirmed successfully',
            'data' => $orderDeliveryConfirmation,
        ]);
    }

    /**
     * @OA\POST(
     *     path="/api/deliveries/{order}/mark-undelivered",
     *     summary="Mark order as undelivered",
     *     description="Updates the order status to undelivered",
     *     tags={"Delivery"},
     *
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Order marked as undelivered successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Order marked as undelivered"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 properties={
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="client_id", type="integer", example=1),
     *                     @OA\Property(property="assigned_driver_id", type="integer", example=1),
     *                     @OA\Property(property="schedule_date", type="string", format="date", example="2025-01-31"),
     *                     @OA\Property(property="status", type="string", example="undelivered"),
     *                     @OA\Property(property="total_amount", type="number", format="decimal", example=150.00),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-31 12:00:00"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-31 12:00:00")
     *                 }
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function markAsUndelivered(Order $order): JsonResponse
    {
        $order->update(['status' => OrderStatus::UNDELIVERED->value]);

        return $this->successResponse([
            'status' => 'success',
            'message' => 'Order marked as undelivered',
            'data' => $order,
        ]);
    }
}
