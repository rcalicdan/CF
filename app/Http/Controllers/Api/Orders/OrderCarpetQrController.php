<?php

namespace App\Http\Controllers\Api\Orders;

use App\Actions\QrCode\CheckQrCodeExistsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderCarpetQr\AssignQrRequestForm;
use App\Http\Requests\QrCode\CheckQrCodeExistsRequest;
use App\Http\Resources\OrderCarpetResource;
use App\Models\OrderCarpet;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Order Carpet Qr assignment and handling",
 *     description="API Endpoints for Order Carpet QR operations"
 * )
 */
class OrderCarpetQrController extends Controller
{
    use ApiResponseTrait;

    /**
     * @OA\Post(
     *     path="/api/order-carpets/{orderCarpet}/assign-qr",
     *     summary="Assign QR code to a carpet",
     *     tags={"Order Carpet Qr assignment and handling"},
     *
     *     @OA\Parameter(
     *         name="orderCarpet",
     *         in="path",
     *         required=true,
     *         description="Order carpet ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"qr_code"},
     *             @OA\Property(property="qr_code", type="string", example="ORD-123-4567")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="QR code assigned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="QR code assigned successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="order_id", type="integer", example=1),
     *                 @OA\Property(property="qr_code", type="string", example="ORD-123-4567"),
     *                 @OA\Property(property="status", type="string", example="active"),
     *                 @OA\Property(property="remarks", type="string", example="Some remarks"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Carpet not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Carpet not found"),
     *             @OA\Property(property="error_code", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function assignQr(AssignQrRequestForm $request, OrderCarpet $orderCarpet): JsonResponse
    {
        $orderCarpet->update([
            'qr_code' => $request->qr_code,
        ]);

        return $this->successResponse([
            'status' => 'success',
            'message' => 'QR code assigned successfully',
            'data' => new OrderCarpetResource($orderCarpet),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/find-carpet-by-qr",
     *     summary="Find carpet by QR code",
     *     tags={"Order Carpet Qr assignment and handling"},
     *     description="Retrieves details of a carpet based on its QR code, including related order, photos, and complaint information.",
     *
     *     @OA\Parameter(
     *         name="qr_code",
     *         in="query",
     *         required=true,
     *         description="QR code of the carpet to search for",
     *         @OA\Schema(type="string", example="12345678")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Carpet found successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="qr_code", type="string", nullable=true, example="12345678"),
     *                 @OA\Property(property="height", type="string", nullable=true, example=null),
     *                 @OA\Property(property="width", type="string", nullable=true, example=null),
     *                 @OA\Property(property="total_area", type="string", nullable=true, example=null),
     *                 @OA\Property(property="measured_at", type="string", nullable=true, example=null),
     *                 @OA\Property(property="status", type="string", example="picked up"),
     *                 @OA\Property(property="remarks", type="string", nullable=true, example=null),
     *                 @OA\Property(
     *                     property="order_carpet_photos",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="order_carpet_id", type="integer", example=1),
     *                         @OA\Property(property="photo_url", type="string", example="/uploads/carpets/order1_photo1.jpg"),
     *                         @OA\Property(property="taken_by", type="string", example="Anthony DuBuque"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-15T20:26:09+00:00")
     *                     )
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-14T16:19:16+00:00"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-16T14:40:49+00:00"),
     *                 @OA\Property(
     *                     property="order",
     *                     type="object",
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
     *                         @OA\Property(property="city", type="string", example="Warsaw"),
     *                         @OA\Property(property="phone_number", type="string", example="682-244-9815"),
     *                         @OA\Property(property="remarks", type="string", example="Iure ipsum minima ut porro dolorem voluptatibus officia..."),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-14T16:16:30+00:00"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-14T16:16:30+00:00")
     *                     ),
     *                     @OA\Property(property="driver", type="null", nullable=true),
     *                     @OA\Property(property="schedule_date", type="string", format="date-time", example="1987-07-23T00:00:00+00:00"),
     *                     @OA\Property(property="status", type="string", example="pending"),
     *                     @OA\Property(property="is_complaint", type="boolean", example=false),
     *                     @OA\Property(property="total_amount", type="string", example="0.00"),
     *                     @OA\Property(
     *                         property="order_carpets",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="qr_code", type="string", nullable=true, example="12345678"),
     *                             @OA\Property(property="height", type="string", nullable=true, example=null),
     *                             @OA\Property(property="width", type="string", nullable=true, example=null),
     *                             @OA\Property(property="total_area", type="string", nullable=true, example=null),
     *                             @OA\Property(property="measured_at", type="string", nullable=true, example=null),
     *                             @OA\Property(property="status", type="string", example="picked up"),
     *                             @OA\Property(property="remarks", type="string", nullable=true, example=null),
     *                             @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-14T16:19:16+00:00"),
     *                             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-16T14:40:49+00:00"),
     *                             @OA\Property(
     *                                 property="complaint",
     *                                 type="object",
     *                                 nullable=true,
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="order_carpet_id", type="integer", example=1),
     *                                 @OA\Property(property="complaint_details", type="string", example="test"),
     *                                 @OA\Property(property="status", type="string", example="open"),
     *                                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-15T14:08:44+00:00"),
     *                                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-15T14:08:44+00:00")
     *                             )
     *                         )
     *                     ),
     *                     @OA\Property(property="order_service", type="array", @OA\Items()),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-14T16:16:30+00:00"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-16T14:40:49+00:00")
     *                 ),
     *                 @OA\Property(
     *                     property="complaint",
     *                     type="object",
     *                     nullable=true,
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="order_carpet_id", type="integer", example=1),
     *                     @OA\Property(property="complaint_details", type="string", example="test"),
     *                     @OA\Property(property="status", type="string", example="open"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-15T14:08:44+00:00"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-15T14:08:44+00:00")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Carpet not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Carpet not found"),
     *             @OA\Property(property="error_code", type="integer", example=404)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to retrieve carpet"),
     *             @OA\Property(property="error", type="string", example="Detailed error message"),
     *             @OA\Property(property="error_code", type="integer", example=500)
     *         )
     *     )
     * )
     */
    public function findByQr(Request $request): JsonResponse
    {
        try {
            $qrCode = $request->qr_code;
            $carpet = OrderCarpet::with([
                'order',
                'order.client',
                'order.driver',
                'order.orderCarpets.complaint',
                'order.orderServices',
                'orderCarpetPhotos.user',
                'complaint',
            ])->where('qr_code', $qrCode)->first();

            if (! $carpet) {
                return $this->errorResponse([
                    'status' => 'error',
                    'message' => 'Carpet not found',
                    'error_code' => 404,
                ], 404);
            }

            return $this->successResponse([
                'status' => 'success',
                'data' => new OrderCarpetResource($carpet),
            ], 200);
        } catch (\Exception $e) {
            return $this->errorResponse([
                'status' => 'error',
                'message' => 'Failed to retrieve carpet',
                'error' => $e->getMessage(),
                'error_code' => 500,
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/check-qr-exists",
     *     summary="Check if QR code reference exists",
     *     tags={"Order Carpet Qr assignment and handling"},
     *     description="Validates if a QR code Qr Code already exists in the system and returns carpet details if found",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="QR code reference to check",
     *         @OA\JsonContent(
     *             required={"qr_code"},
     *             @OA\Property(
     *                 property="qr_code",
     *                 type="string",
     *                 description="The QR code reference to check (case insensitive)",
     *                 example="ORD-123-4567",
     *                 minLength=3,
     *                 maxLength=50
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="QR code check result",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="QR code availability checked successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="exists", type="boolean", example=false, description="Whether the QR code exists"),
     *                 @OA\Property(property="qr_code", type="string", example="ORD-123-4567", description="The checked Qr Code"),
     *                 @OA\Property(
     *                     property="carpet",
     *                     type="object",
     *                     nullable=true,
     *                     description="Carpet details if QR code exists",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="order_id", type="integer", example=123),
     *                     @OA\Property(property="status", type="string", example="measured"),
     *                     @OA\Property(property="status_label", type="string", example="Measured"),
     *                     @OA\Property(property="height", type="number", format="float", example=2.5, nullable=true),
     *                     @OA\Property(property="width", type="number", format="float", example=3.0, nullable=true),
     *                     @OA\Property(property="total_area", type="number", format="float", example=7.5, nullable=true),
     *                     @OA\Property(property="measured_at", type="string", format="date-time", example="2025-08-31T10:30:00.000000Z", nullable=true),
     *                     @OA\Property(property="qr_code", type="string", example="ORD-123-4567"),
     *                     @OA\Property(property="qr_code_url", type="string", example="https://domain.com/storage/qr-codes/ORD-123-4567.png", nullable=true),
     *                     @OA\Property(property="qr_code", type="string", example="ORD-123-4567", nullable=true),
     *                     @OA\Property(property="services_count", type="integer", example=2),
     *                     @OA\Property(property="total_price", type="number", format="float", example=150.00),
     *                     @OA\Property(property="remarks", type="string", example="Special handling required", nullable=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-31T08:00:00.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-31T10:30:00.000000Z")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="error_code", type="integer", example=422),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="qr_code",
     *                     type="array",
     *                     @OA\Items(type="string", example="Qr Code is required.")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An error occurred while checking QR code existence"),
     *             @OA\Property(property="error_code", type="integer", example=500)
     *         )
     *     )
     * )
     */
    public function checkQrExists(CheckQrCodeExistsRequest $request, CheckQrCodeExistsAction $action): JsonResponse
    {
        try {
            $referenceCode = $request->validated()['qr_code'];
            
            $result = $action->execute($referenceCode);
            
            return $this->successResponse([
                'status' => 'success',
                'message' => 'QR code availability checked successfully',
                'data' => $result
            ], 200);
            
        } catch (\Exception $e) {
            return $this->errorResponse([
                'status' => 'error',
                'message' => 'An error occurred while checking QR code existence',
                'error_code' => 500,
            ], 500);
        }
    }
}