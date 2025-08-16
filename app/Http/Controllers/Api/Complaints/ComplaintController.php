<?php

namespace App\Http\Controllers\Api\Complaints;

use App\ActionService\ComplaintService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Complaints\StoreComplaintFormRequest;
use App\Http\Requests\Complaints\UpdateComplaintFormRequest;
use App\Http\Resources\Collection\ComplaintCollection;
use App\Http\Resources\ComplaintResource;
use App\Models\Complaint;
use App\Models\OrderCarpet;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Complaints",
 *     description="Operations related to complaints"
 * )
 */
class ComplaintController extends Controller
{
    use ApiResponseTrait;

    private $complaintService;

    public function __construct(ComplaintService $complaintService)
    {
        $this->complaintService = $complaintService;
    }

    /**
     * @OA\Get(
     *     path="/api/complaints",
     *     operationId="getComplaints",
     *     tags={"Complaints"},
     *     summary="Get a paginated list of complaints with optional filters",
     *     description="Retrieves a paginated list of all complaints with optional filtering.",
     *
     *     @OA\Parameter(
     *         name="order_carpet_id",
     *         in="query",
     *         description="Filter complaints by order carpet ID (exact match)",
     *         required=false,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter complaints by status (exact match)",
     *         required=false,
     *
     *         @OA\Schema(type="string", enum={"open", "in progress", "rejected", "resolved", "pending"})
     *     ),
     *
     *     @OA\Parameter(
     *         name="complaint_details",
     *         in="query",
     *         description="Filter complaints by complaint details (partial match)",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *
     *                     @OA\Items(
     *                         type="object",
     *
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="order_carpet_id", type="integer", example=1),
     *                         @OA\Property(property="complaint_details", type="string", example="test"),
     *                         @OA\Property(property="status", type="string", example="open"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-15T14:08:44+00:00"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-15T14:08:44+00:00"),
     *                         @OA\Property(
     *                             property="order_carpet",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="qr_code", type="string", nullable=true, example="12345678"),
     *                             @OA\Property(property="height", type="string", example="4.45"),
     *                             @OA\Property(property="width", type="string", example="6.87"),
     *                             @OA\Property(property="total_area", type="number", format="float", example=30.5715),
     *                             @OA\Property(property="measured_at", type="string", format="date-time", example="2025-02-16T14:57:39+00:00"),
     *                             @OA\Property(property="status", type="string", example="picked up"),
     *                             @OA\Property(property="remarks", type="string", nullable=true, example=null),
     *                             @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-14T16:19:16+00:00"),
     *                             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-16T14:57:39+00:00"),
     *                             @OA\Property(
     *                                 property="order",
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(
     *                                     property="client",
     *                                     type="object",
     *                                     @OA\Property(property="id", type="integer", example=1),
     *                                     @OA\Property(property="first_name", type="string", example="Madge"),
     *                                     @OA\Property(property="last_name", type="string", example="Cassin"),
     *                                     @OA\Property(property="street_name", type="string", example="Eduardo Run"),
     *                                     @OA\Property(property="street_number", type="string", example="1609"),
     *                                     @OA\Property(property="postal_code", type="string", example="37905-9294"),
     *                                     @OA\Property(property="city", type="string", example="Warsaw"),
     *                                     @OA\Property(property="phone_number", type="string", example="682-244-9815"),
     *                                     @OA\Property(property="remarks", type="string", example="Iure ipsum minima ut porro dolorem voluptatibus officia. Neque est aspernatur et quia. Dolorem molestiae officiis quia saepe. Rerum ea repellat consequatur quis autem odio dolorum."),
     *                                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-14T16:16:30+00:00"),
     *                                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-14T16:16:30+00:00")
     *                                 ),
     *                                 @OA\Property(property="driver", type="null", nullable=true),
     *                                 @OA\Property(property="schedule_date", type="string", format="date", example="1987-07-23"),
     *                                 @OA\Property(property="status", type="string", example="pending"),
     *                                 @OA\Property(property="is_complaint", type="boolean", example=false),
     *                                 @OA\Property(property="total_amount", type="string", example="0.00"),
     *                                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-14T16:16:30+00:00"),
     *                                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-16T14:57:39+00:00")
     *                             )
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="first_page_url", type="string", example="http://127.0.0.1:8000/api/complaints?page=1"),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=1),
     *                 @OA\Property(property="last_page_url", type="string", example="http://127.0.0.1:8000/api/complaints?page=1"),
     *                 @OA\Property(
     *                     property="links",
     *                     type="array",
     *
     *                     @OA\Items(
     *                         type="object",
     *
     *                         @OA\Property(property="url", type="string", nullable=true),
     *                         @OA\Property(property="label", type="string", example="1"),
     *                         @OA\Property(property="active", type="boolean", example=true)
     *                     )
     *                 ),
     *                 @OA\Property(property="next_page_url", type="string", nullable=true),
     *                 @OA\Property(property="path", type="string", example="http://127.0.0.1:8000/api/complaints"),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="prev_page_url", type="string", nullable=true),
     *                 @OA\Property(property="to", type="integer", example=1),
     *                 @OA\Property(property="total", type="integer", example=1)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $complaints = $this->complaintService->getAllComplaints();

        return $this->successResponse([
            'status' => 'success',
            'data' => new ComplaintCollection($complaints),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/complaints/{complaint}",
     *     operationId="getComplaintById",
     *     tags={"Complaints"},
     *     summary="Get a specific complaint",
     *     description="Retrieves a specific complaint by its ID, including related order carpet and order details.",
     *
     *     @OA\Parameter(
     *         name="complaint",
     *         in="path",
     *         required=true,
     *         description="ID of the complaint",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="order_carpet_id", type="integer", example=1),
     *                 @OA\Property(property="complaint_details", type="string", example="so ugly!!"),
     *                 @OA\Property(property="status", type="string", example="in progress"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-02T15:25:05+00:00"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-02T15:34:44+00:00"),
     *                 @OA\Property(
     *                     property="order_carpet",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="qr_code", type="string", nullable=true, example="QR123456"),
     *                     @OA\Property(property="height", type="string", example="Not yet measured"),
     *                     @OA\Property(property="width", type="string", example="Not yet measured"),
     *                     @OA\Property(property="total_area", type="string", example="Not yet measured"),
     *                     @OA\Property(property="measured_at", type="string", nullable=true, example=null),
     *                     @OA\Property(property="status", type="string", example="under review"),
     *                     @OA\Property(property="remarks", type="string", nullable=true, example=null),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-01T16:29:57+00:00"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-02T15:34:44+00:00"),
     *                     @OA\Property(
     *                         property="order",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=2),
     *                         @OA\Property(
     *                             property="client",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="first_name", type="string", example="Taurean"),
     *                             @OA\Property(property="last_name", type="string", example="Deckow"),
     *                             @OA\Property(property="street_name", type="string", example="Main Street"),
     *                             @OA\Property(property="street_number", type="string", example="123"),
     *                             @OA\Property(property="postal_code", type="string", example="12345"),
     *                             @OA\Property(property="city", type="string", example="Warsaw"),
     *                             @OA\Property(property="phone_number", type="string", example="555-1234"),
     *                             @OA\Property(property="remarks", type="string", example="Client remarks here..."),
     *                             @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-01T16:29:57+00:00"),
     *                             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-02T15:34:44+00:00")
     *                         ),
     *                         @OA\Property(property="driver", type="null", nullable=true),
     *                         @OA\Property(property="schedule_date", type="string", format="date", example="2007-06-21"),
     *                         @OA\Property(property="status", type="string", example="pending"),
     *                         @OA\Property(property="is_complaint", type="boolean", example=true),
     *                         @OA\Property(property="total_amount", type="string", example="0.00"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-01T16:29:57+00:00"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-02T15:34:44+00:00")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Complaint not found",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Complaint not found"),
     *             @OA\Property(property="error_code", type="integer", example=404)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to retrieve complaint"),
     *             @OA\Property(property="error", type="string", example="Detailed error message"),
     *             @OA\Property(property="error_code", type="integer", example=500)
     *         )
     *     )
     * )
     */
    public function show(Complaint $complaint): JsonResponse
    {
        $complaint = $this->complaintService->showComplaint($complaint);

        return $this->successResponse([
            'status' => 'success',
            'data' => new ComplaintResource($complaint),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/complaints/{orderCarpet}/store-complaint",
     *     operationId="storeComplaint",
     *     tags={"Complaints"},
     *     summary="Store a new complaint",
     *     description="Stores a new complaint for a specific order carpet.",
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
     *
     *             @OA\Property(property="complaint_details", type="string", example="so ugly!!", description="Details of the complaint")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Complaint successfully submitted",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Complaint has been successfully submitted")
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
     *             @OA\Property(property="message", type="string", example="Validation error message"),
     *             @OA\Property(property="error_code", type="integer", example=422)
     *         )
     *     )
     * )
     */
    public function store(OrderCarpet $orderCarpet, StoreComplaintFormRequest $request): JsonResponse
    {
        try {
            $this->complaintService->storeComplaintThroughCarpets($orderCarpet, $request->validated());

            return $this->successResponse([
                'status' => 'success',
                'message' => 'Complaint has been successfully submitted',
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
                'error_code' => 422,
            ], 422);
        }
    }

    /**
     * @OA\Put(
     *     path="/complaints/{complaint}",
     *     operationId="updateComplaint",
     *     tags={"Complaints"},
     *     summary="Update a specific complaint",
     *     description="Updates a specific complaint by its ID.",
     *
     *     @OA\Parameter(
     *         name="complaint",
     *         in="path",
     *         required=true,
     *         description="ID of the complaint",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="complaint_details", type="string", example="so ugly!!"),
     *             @OA\Property(property="status", type="string", example="in progress")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Complaint successfully updated",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Complaint has been successfully updated")
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
     *             @OA\Property(property="message", type="string", example="Validation error message"),
     *             @OA\Property(property="error_code", type="integer", example=422)
     *         )
     *     )
     * )
     */
    public function update(UpdateComplaintFormRequest $request, Complaint $complaint): JsonResponse
    {
        try {
            $this->complaintService->updateComplaint($complaint, $request->validated());

            return $this->successResponse([
                'status' => 'success',
                'message' => 'Complaint has been successfully updated',
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
                'error_code' => 422,
            ], 422);
        }
    }

    /**
     * @OA\Delete(
     *     path="/complaints/{complaint}",
     *     operationId="deleteComplaint",
     *     tags={"Complaints"},
     *     summary="Delete a specific complaint",
     *     description="Deletes a specific complaint by its ID.",
     *
     *     @OA\Parameter(
     *         name="complaint",
     *         in="path",
     *         required=true,
     *         description="ID of the complaint",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Complaint successfully deleted",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Complaint has been successfully deleted")
     *         )
     *     )
     * )
     */
    public function destroy(Complaint $complaint): JsonResponse
    {
        $complaint->delete();

        return $this->successResponse([
            'status' => 'success',
            'message' => 'Complaint has been successfully deleted',
        ]);
    }
}
