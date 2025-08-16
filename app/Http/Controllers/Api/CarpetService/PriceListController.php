<?php

namespace App\Http\Controllers\Api\CarpetService;

use App\ActionService\PriceListService;
use App\Http\Controllers\Controller;
use App\Http\Requests\PriceList\StorePriceListFormRequest;
use App\Http\Requests\PriceList\UpdatePriceListFormRequest;
use App\Http\Resources\Collection\PriceListCollection;
use App\Http\Resources\PriceListResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * @OA\Tag(
 *     name="PriceList",
 *     description="Operations related to Price Lists"
 * )
 */
class PriceListController extends Controller
{
    use ApiResponseTrait;

    private $priceListService;

    public function __construct(PriceListService $priceListService)
    {
        $this->priceListService = $priceListService;
    }

    /**
     * @OA\Get(
     *     path="/api/price-lists",
     *     summary="List all price lists with optional filters",
     *     tags={"PriceList"},
     *     description="Retrieve all price lists in the system with pagination and optional filters.",
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Filter price lists by ID",
     *         required=false,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Filter price lists by name (partial match)",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="location_postal_code",
     *         in="query",
     *         description="Filter price lists by location postal code (partial match)",
     *         required=false,
     *
     *         @OA\Schema(type="string")
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
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *
     *                     @OA\Items(
     *                         type="object",
     *
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="aut"),
     *                         @OA\Property(property="location_postal_code", type="string", example="26267"),
     *                         @OA\Property(
     *                             property="price_list_services",
     *                             type="array",
     *
     *                             @OA\Items(
     *                                 type="object",
     *
     *                                 @OA\Property(property="service_price_list_id", type="integer", example=6),
     *                                 @OA\Property(property="service_id", type="integer", example=1),
     *                                 @OA\Property(property="service_name", type="string", example="laboriosam"),
     *                                 @OA\Property(property="service_base_price", type="string", example="19.77"),
     *                                 @OA\Property(property="price", type="string", example="200.00")
     *                             )
     *                         ),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-24T14:33:55.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-24T14:33:55.000000Z")
     *                     )
     *                 ),
     *                 @OA\Property(property="first_page_url", type="string", example="http://127.0.0.1:8000/api/price-lists?page=1"),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=1),
     *                 @OA\Property(property="last_page_url", type="string", example="http://127.0.0.1:8000/api/price-lists?page=1"),
     *                 @OA\Property(
     *                     property="links",
     *                     type="array",
     *
     *                     @OA\Items(
     *                         type="object",
     *
     *                         @OA\Property(property="url", type="string", nullable=true, example=null),
     *                         @OA\Property(property="label", type="string", example="&laquo; Previous"),
     *                         @OA\Property(property="active", type="boolean", example=false)
     *                     )
     *                 ),
     *                 @OA\Property(property="next_page_url", type="string", nullable=true, example=null),
     *                 @OA\Property(property="path", type="string", example="http://127.0.0.1:8000/api/price-lists"),
     *                 @OA\Property(property="per_page", type="integer", example=30),
     *                 @OA\Property(property="prev_page_url", type="string", nullable=true, example=null),
     *                 @OA\Property(property="to", type="integer", example=11),
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
        $priceLists = $this->priceListService->getAllServicePriceLists();

        return $this->successResponse([
            'status' => 'success',
            'data' => new PriceListCollection($priceLists),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/price-lists",
     *     summary="Create a new price list",
     *     tags={"PriceList"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name"},
     *
     *             @OA\Property(property="name", type="string", example="test"),
     *             @OA\Property(property="location_postal_code", type="string", example="6700")
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
     *             @OA\Property(property="message", type="string", example="Price List Created!"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=12),
     *                 @OA\Property(property="name", type="string", example="test"),
     *                 @OA\Property(property="location_postal_code", type="string", example="6700"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-02T15:48:53.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-02T15:48:53.000000Z")
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
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
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
    public function store(StorePriceListFormRequest $request): JsonResponse
    {
        $priceList = $this->priceListService->createPriceList($request->validated());

        return $this->successResponse([
            'status' => 'success',
            'message' => 'Price List Created!',
            'data' => new PriceListResource($priceList),
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/price-lists/{id}",
     *     summary="Get a specific price list",
     *     tags={"PriceList"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the price list to retrieve",
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
     *                 @OA\Property(property="name", type="string", example="aut"),
     *                 @OA\Property(property="location_postal_code", type="string", example="26267"),
     *                 @OA\Property(
     *                     property="price_list_services",
     *                     type="array",
     *
     *                     @OA\Items(
     *                         type="object",
     *
     *                         @OA\Property(property="service_price_list_id", type="integer", example=6),
     *                         @OA\Property(property="service_id", type="integer", example=1),
     *                         @OA\Property(property="service_name", type="string", example="laboriosam"),
     *                         @OA\Property(property="service_base_price", type="string", example="19.77"),
     *                         @OA\Property(property="price", type="string", example="200.00")
     *                     )
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-24T14:33:55.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-24T14:33:55.000000Z")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Price list not found",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Price list not found")
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
    public function show(string $id): JsonResource|JsonResponse
    {
        $priceList = $this->priceListService->showSelectedPriceList($id);

        return $this->successResponse([
            'status' => 'success',
            'data' => new PriceListResource($priceList),
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/price-lists/{id}",
     *     summary="Update a price list",
     *     tags={"PriceList"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the price list to update",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="name", type="string", example="Updated Price List"),
     *             @OA\Property(property="location_postal_code", type="string", example="9500")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Updated successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Price List Updated!"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=12),
     *                 @OA\Property(property="name", type="string", example="Updated Price List"),
     *                 @OA\Property(property="location_postal_code", type="string", example="9500"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-02T15:48:53.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-02T15:52:10.000000Z")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Price list not found",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Price list not found")
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
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
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
    public function update(UpdatePriceListFormRequest $request, string $id): JsonResponse
    {
        $priceList = $this->priceListService->updatePriceList($id, $request->validated());

        return $this->successResponse([
            'status' => 'success',
            'message' => 'Price List Updated!',
            'data' => new PriceListResource($priceList),
        ], 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/price-lists/{id}",
     *     summary="Delete a price list",
     *     tags={"PriceList"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string")
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
     *             @OA\Property(property="message", type="string", example="Price List Deleted!")
     *         )
     *     )
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        $this->priceListService->deletePriceList($id);

        return $this->successResponse([
            'status' => 'success',
            'message' => 'Price List Deleted!',
        ]);
    }
}
