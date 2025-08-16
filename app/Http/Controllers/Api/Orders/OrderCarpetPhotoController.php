<?php

namespace App\Http\Controllers\Api\Orders;

use App\ActionService\PhotoUploadService;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderCarpetPhoto\StoreCarpetPhotoFormRequest;
use App\Http\Resources\OrderCarpetPhotoResource;
use App\Models\OrderCarpetPhoto;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class OrderCarpetPhotoController extends Controller
{
    use ApiResponseTrait;

    private $photoUploadService;

    public function __construct(PhotoUploadService $photoUploadService)
    {
        $this->photoUploadService = $photoUploadService;
    }

    /**
     * Upload a new carpet photo
     *
     * @OA\Post(
     *     path="/api/order-carpets/{order-carpet}/upload-photo",
     *     tags={"Order Carpet Photos"},
     *     summary="Upload a new carpet photo",
     *     description="Upload a photo for a specific order carpet",
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="order-carpet",
     *         in="path",
     *         required=true,
     *         description="Order carpet ID",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *                 type="object",
     *                 required={"photo"},
     *
     *                 @OA\Property(
     *                     property="photo",
     *                     type="string",
     *                     format="binary",
     *                     description="Photo file to upload"
     *                 ),
     *                 @OA\Property(
     *                     property="order_carpet_id",
     *                     type="integer",
     *                     example=1
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Photo uploaded successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Carpet photo uploaded successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="order_carpet_id", type="integer", example=1),
     *                 @OA\Property(property="photo_url", type="string", example="carpets/photo123.jpg"),
     *                 @OA\Property(property="taken_by", type="string", example="John Doe"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-21T12:00:00+00:00")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The given data was invalid."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="photo",
     *                     type="array",
     *
     *                     @OA\Items(type="string", example="The photo field is required.")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthenticated"
     *             )
     *         )
     *     )
     * )
     */
    public function store(StoreCarpetPhotoFormRequest $request): JsonResponse
    {
        $file = $request->file('photo');

        $filePath = $this->photoUploadService->upload($file, 'carpets');

        $carpetPhoto = OrderCarpetPhoto::create([
            'order_carpet_id' => $request->order_carpet_id,
            'user_id' => Auth::user()->id,
            'photo_path' => $filePath,
        ]);

        return $this->successResponse([
            'status' => 'success',
            'message' => 'Carpet photo uploaded successfully',
            'data' => new OrderCarpetPhotoResource($carpetPhoto),
        ]);
    }

    /**
     * Delete a carpet photo
     *
     * @OA\Delete(
     *     path="/api/carpet-photo/{carpet-photo}",
     *     tags={"Order Carpet Photos"},
     *     summary="Delete a carpet photo",
     *     description="Delete a specific carpet photo with example usage",
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="carpet-photo",
     *         in="path",
     *         required=true,
     *         description="Carpet photo ID",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Photo deleted successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *             example={
     *                 "status": "success",
     *                 "message": "Carpet photo deleted successfully!"
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Photo not found",
     *
     *         @OA\JsonContent(
     *             type="object",
     *             example={
     *                 "message": "Not Found"
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(
     *             type="object",
     *             example={
     *                 "message": "Unauthenticated"
     *             }
     *         )
     *     )
     * )
     */
    public function destroy(OrderCarpetPhoto $carpetPhoto): JsonResponse
    {
        $this->photoUploadService->deleteCarpetPhotoInStorage($carpetPhoto);

        $carpetPhoto->delete();

        return $this->successResponse([
            'status' => 'success',
            'message' => 'Carpet photo deleted successfully!',
        ]);
    }
}
