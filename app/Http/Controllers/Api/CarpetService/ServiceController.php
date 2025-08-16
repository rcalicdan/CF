<?php

namespace App\Http\Controllers\Api\CarpetService;

use App\ActionService\CarpetService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Service\StoreServiceFormRequest;
use App\Http\Requests\Service\UpdateServiceFormRequest;
use App\Http\Resources\Collection\ServiceResourceCollection;
use App\Http\Resources\ServiceResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * @OA\Tag(
 *     name="Services",
 *     description="Operations related to Carpet Services"
 * )
 */
class ServiceController extends Controller
{
    use ApiResponseTrait;

    private $carpetService;

    public function __construct(CarpetService $carpetService)
    {
        $this->carpetService = $carpetService;
    }

    /**
     * @OA\Get(
     *     path="/api/services",
     *     summary="List all carpet services with optional filters",
     *     tags={"Services"},
     *     description="Retrieve all carpet services in the system with pagination and optional filters.",
     *
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Filter services by name (partial match)",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="base_price_min",
     *         in="query",
     *         description="Filter services by minimum base price (inclusive)",
     *         required=false,
     *
     *         @OA\Schema(type="number", format="float")
     *     ),
     *
     *     @OA\Parameter(
     *         name="base_price_max",
     *         in="query",
     *         description="Filter services by maximum base price (inclusive)",
     *         required=false,
     *
     *         @OA\Schema(type="number", format="float")
     *     ),
     *
     *     @OA\Parameter(
     *         name="is_area_based",
     *         in="query",
     *         description="Filter services by whether they are area-based (true/false)",
     *         required=false,
     *
     *         @OA\Schema(type="boolean")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *
     *                     @OA\Items(
     *                         type="object",
     *
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="voluptate"),
     *                         @OA\Property(property="base_price", type="string", example="61.93"),
     *                         @OA\Property(property="is_area_based", type="boolean", example=false),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-28T15:14:24.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-28T15:14:24.000000Z")
     *                     )
     *                 ),
     *                 @OA\Property(property="first_page_url", type="string", example="http://127.0.0.1:8000/api/services?page=1"),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=2),
     *                 @OA\Property(property="last_page_url", type="string", example="http://127.0.0.1:8000/api/services?page=2"),
     *                 @OA\Property(property="links", type="array",
     *
     *                     @OA\Items(
     *                         type="object",
     *
     *                         @OA\Property(property="url", type="string", nullable=true),
     *                         @OA\Property(property="label", type="string", example="1"),
     *                         @OA\Property(property="active", type="boolean", example=true)
     *                     )
     *                 ),
     *                 @OA\Property(property="next_page_url", type="string", example="http://127.0.0.1:8000/api/services?page=2"),
     *                 @OA\Property(property="path", type="string", example="http://127.0.0.1:8000/api/services"),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="prev_page_url", type="string", nullable=true),
     *                 @OA\Property(property="to", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=11)
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
    public function index(): ResourceCollection|JsonResponse
    {
        $services = $this->carpetService->getAllCarpetServices();

        return $this->successResponse([
            'status' => 'success',
            'data' => new ServiceResourceCollection($services),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/services",
     *     summary="Create a new carpet service",
     *     tags={"Services"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name", "base_price", "unit"},
     *
     *             @OA\Property(property="name", type="string", example="Deep Cleaning"),
     *             @OA\Property(property="base_price", type="number", format="float", example=99.99),
     *             @OA\Property(property="unit", type="string", example="per square meter")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Created successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Carpet Service Created!"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Deep Cleaning"),
     *                 @OA\Property(property="base_price", type="number", format="float", example=99.99),
     *                 @OA\Property(property="unit", type="string", example="per square meter"),
     *                 @OA\Property(property="created_at", type="string", format="datetime", example="2024-01-20T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime", example="2024-01-20T12:00:00Z")
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
     *             @OA\Property(property="message", type="string", example="The name field is required."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *
     *                     @OA\Items(type="string", example="The name field is required.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function store(StoreServiceFormRequest $request): JsonResponse
    {
        $service = $this->carpetService->createCarpetService($request->validated());

        return $this->successResponse([
            'status' => 'success',
            'message' => 'Carpet Service Created!',
            'data' => new ServiceResource($service),
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/services/{id}",
     *     summary="Get a specific carpet service",
     *     tags={"Services"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Service ID",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="voluptate"),
     *                 @OA\Property(property="base_price", type="string", example="61.93"),
     *                 @OA\Property(property="is_area_based", type="boolean", example=false),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-28T15:14:24.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-28T15:14:24.000000Z")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Service not found",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="message", type="string", example="Service not found")
     *         )
     *     )
     * )
     */
    public function show(int $id): JsonResource|JsonResponse
    {
        $service = $this->carpetService->showSelectedCarpetService($id);

        return $this->successResponse([
            'status' => 'success',
            'data' => new ServiceResource($service),
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/services/{id}",
     *     summary="Update a carpet service",
     *     tags={"Services"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Service ID",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name", "base_price", "unit"},
     *
     *             @OA\Property(property="name", type="string", example="Updated Deep Cleaning"),
     *             @OA\Property(property="base_price", type="number", format="float", example=149.99),
     *             @OA\Property(property="unit", type="string", example="per square meter")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Updated successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Carpet Service Updated!"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Updated Deep Cleaning"),
     *                 @OA\Property(property="base_price", type="number", format="float", example=149.99),
     *                 @OA\Property(property="unit", type="string", example="per square meter"),
     *                 @OA\Property(property="created_at", type="string", format="datetime", example="2024-01-20T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime", example="2024-01-20T12:00:00Z")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Service not found",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="message", type="string", example="Service not found")
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
     *             @OA\Property(property="message", type="string", example="The name has already been taken."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *
     *                     @OA\Items(type="string", example="The name has already been taken.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function update(UpdateServiceFormRequest $request, string $id): JsonResponse
    {
        $service = $this->carpetService->updateCarpetService($id, $request->validated());

        return $this->successResponse([
            'status' => 'success',
            'message' => 'Carpet Service Updated!',
            'data' => new ServiceResource($service),
        ], 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/services/{id}",
     *     summary="Delete a carpet service",
     *     tags={"Services"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Service ID",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Deleted successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Carpet Service Deleted!")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Service not found",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="message", type="string", example="Service not found")
     *         )
     *     )
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        $this->carpetService->deleteCarpetService($id);

        return $this->successResponse([
            'status' => 'success',
            'message' => 'Carpet Service Deleted!',
        ]);
    }
}
