<?php

namespace App\Http\Controllers\Api\QrCode;

use App\ActionService\QrValidationService;
use App\Enums\OrderCarpetStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\QrCode\QrCodeFormRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(name="QR Code Validation", description="Endpoints for validating QR codes")
 */
class ValidateQrController extends Controller
{
    use ApiResponseTrait;

    private QrValidationService $qrValidationService;

    public function __construct(QrValidationService $qrValidationService)
    {
        $this->qrValidationService = $qrValidationService;
    }

    /**
     * @OA\Post(
     *     path="/api/validate-qr/measure-validation",
     *     tags={"QR Code Validation"},
     *     summary="Validate QR Code and mark status as 'measured'",
     *     description="This endpoint validates a QR code and updates the status of the associated order carpet to 'measured'.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="qr_code", type="string", description="The QR code to validate")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="QR code validated and status updated to 'measured'.",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Qr code validated successfully and status updated to measured")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="QR code not found.",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Qr code not found"),
     *             @OA\Property(property="error_code", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function measureQrValidation(QrCodeFormRequest $request)
    {
        try {
            $this->qrValidationService->validateAndUpdateStatus(
                $request->input('qr_code'),
                OrderCarpetStatus::MEASURED
            );

            return $this->successResponse([
                'status' => 'success',
                'message' => 'Qr code validated successfully and status updated to measured',
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse([
                'status' => 'error',
                'message' => 'Qr code not found',
                'error_code' => 404,
            ], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/validate-qr/package-completed-validation",
     *     tags={"QR Code Validation"},
     *     summary="Validate QR Code and mark status as 'completed'",
     *     description="This endpoint validates a QR code and updates the status of the associated order carpet to 'completed'.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="qr_code", type="string", description="The QR code to validate")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="QR code validated and status updated to 'completed'.",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Qr code validated successfully and status updated to completed!")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="QR code not found.",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Qr code not found"),
     *             @OA\Property(property="error_code", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function packageCompleteQrValidation(QrCodeFormRequest $request)
    {
        try {
            $message = 'Your order has been completed and is ready for delivery!';

            $this->qrValidationService->validateAndUpdateStatus(
                $request->input('qr_code'),
                OrderCarpetStatus::COMPLETED,
                $message
            );

            return $this->successResponse([
                'status' => 'success',
                'message' => 'Qr code validated successfully and status updated to completed!',
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse([
                'status' => 'error',
                'message' => 'Qr code not found',
                'error_code' => 404,
            ], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/validate-qr//delivery-validation",
     *     tags={"QR Code Validation"},
     *     summary="Validate QR Code and mark status as 'delivered'",
     *     description="This endpoint validates a QR code and updates the status of the associated order carpet to 'completed'.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="qr_code", type="string", description="The QR code to validate")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="QR code validated and status updated to 'completed'.",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Qr code validated successfully and status updated to delivered!")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="QR code not found.",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Qr code not found"),
     *             @OA\Property(property="error_code", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function deliveryQrValidation(QrCodeFormRequest $request): JsonResponse
    {
        try {
            $this->qrValidationService->validateAndUpdateStatus(
                $request->input('qr_code'),
                OrderCarpetStatus::DELIVERED
            );

            return $this->successResponse([
                'status' => 'success',
                'message' => 'Qr code validated successfully and status updated to delivered!',
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse([
                'status' => 'error',
                'message' => 'Qr code not found',
                'error_code' => 404,
            ], 404);
        }
    }

    public function pickedUpValidation(QrCodeFormRequest $request): JsonResponse
    {
        try {
            $this->qrValidationService->validateAndUpdateStatus(
                $request->input('qr_code'),
                OrderCarpetStatus::PICKED_UP,
            );

            return $this->successResponse([
                'status' => 'success',
                'message' => 'Qr code validated successfully and status updated to delivered!',
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse([
                'status' => 'error',
                'message' => 'Qr code not found',
                'error_code' => 404,
            ], 404);
        }
    }

    public function validateCompletedLaundryCarpets(QrCodeFormRequest $request): JsonResponse
    {
        if (! $this->qrValidationService->validateIfLaundryWasCleaned($request->validated())) {
            return $this->successResponse([
                'status' => 'success',
                'message' => 'Laundry was not cleaned in the laundry',
            ], 200);
        } else {
            return $this->successResponse([
                'status' => 'success',
                'message' => 'Laundry was cleaned in the laundry',
            ]);
        }
    }
}
