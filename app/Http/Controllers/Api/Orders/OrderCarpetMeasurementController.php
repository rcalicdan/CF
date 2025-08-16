<?php

namespace App\Http\Controllers\Api\Orders;

use App\ActionService\OrderCarpetMeasurementService;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderCarpet\UpdateMeasureCarpetFormRequest;
use App\Http\Resources\OrderCarpetResource;
use App\Models\OrderCarpet;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Order Carpet Measurement",
 *     description="API Endpoints for managing carpet measurements"
 * )
 */
class OrderCarpetMeasurementController extends Controller
{
    use ApiResponseTrait;

    private $carpetMeasurementService;

    public function __construct(OrderCarpetMeasurementService $carpetMeasurementService)
    {
        $this->carpetMeasurementService = $carpetMeasurementService;
    }

    /**
     * @OA\Post(
     *     path="/api/order-carpets/{orderCarpet}/measure-carpet",
     *     summary="Update carpet measurement",
     *     tags={"Order Carpet Measurement"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="orderCarpet",
     *         in="path",
     *         required=true,
     *         description="ID of the order carpet",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"height", "width"},
     *
     *             @OA\Property(property="height", type="number", format="float", example=4.45),
     *             @OA\Property(property="width", type="number", format="float", example=6.87)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Carpet measurement updated successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Carpet measurement updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="qr_code", type="string", example="12345678"),
     *                 @OA\Property(property="height", type="string", example="4.45"),
     *                 @OA\Property(property="width", type="string", example="6.87"),
     *                 @OA\Property(property="total_area", type="number", format="float", example=30.5715),
     *                 @OA\Property(property="measured_at", type="string", format="date-time", example="2025-02-16T14:56:11+00:00"),
     *                 @OA\Property(property="status", type="string", example="picked up"),
     *                 @OA\Property(property="remarks", type="string", nullable=true, example=null),
     *                 @OA\Property(
     *                     property="order_carpet_photos",
     *                     type="array",
     *
     *                     @OA\Items(
     *                         type="object",
     *
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="order_carpet_id", type="integer", example=1),
     *                         @OA\Property(property="photo_url", type="string", example="/uploads/carpets/order1_photo1.jpg"),
     *                         @OA\Property(property="taken_by", type="string", example="Anthony DuBuque"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-15T20:26:09+00:00")
     *                     )
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-14T16:19:16+00:00"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-16T14:56:11+00:00"),
     *                 @OA\Property(property="order", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(
     *                         property="client",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="first_name", type="string", example="Madge"),
     *                         @OA\Property(property="last_name", type="string", example="Cassin"),
     *                         @OA\Property(property="street_name", type="string", example="Eduardo Run"),
     *                         @OA\Property(property="street_number", type="string", example="1609"),
     *                         @OA\Property(property="postal_code", type="string", example="37905-9294"),
     *                         @OA\Property(property="phone_number", type="string", example="682-244-9815"),
     *                         @OA\Property(property="remarks", type="string", example="Iure ipsum minima ut porro dolorem voluptatibus officia..."),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-14T16:16:30+00:00"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-14T16:16:30+00:00")
     *                     ),
     *                     @OA\Property(property="driver", type="null", nullable=true),
     *                     @OA\Property(property="schedule_date", type="string", format="date-time", example="1987-07-23T00:00:00+00:00"),
     *                     @OA\Property(
     *                         property="price_list",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="perspiciatis"),
     *                         @OA\Property(property="location_postal_code", type="string", example="07486")
     *                     ),
     *                     @OA\Property(property="status", type="string", example="pending"),
     *                     @OA\Property(property="is_complaint", type="boolean", example=false),
     *                     @OA\Property(property="total_amount", type="string", example="0.00"),
     *                     @OA\Property(property="order_service", type="array", @OA\Items()),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-14T16:16:30+00:00"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-16T14:56:11+00:00")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object",
     *
     *                 @OA\AdditionalProperties(
     *                     type="array",
     *
     *                     @OA\Items(type="string", example="The height field is required.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function measureCarpet(UpdateMeasureCarpetFormRequest $request, OrderCarpet $orderCarpet): JsonResponse
    {
        $orderCarpet = $this->carpetMeasurementService
            ->storeMeasurement($orderCarpet, $request->validated());

        return $this->successResponse([
            'status' => 'success',
            'message' => 'Carpet measurement updated successfully',
            'data' => new OrderCarpetResource($orderCarpet),
        ]);
    }
}
