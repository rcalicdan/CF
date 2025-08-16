<?php

namespace App\Http\Controllers\Api\CarpetService;

use App\ActionService\ServicePriceListService;
use App\Http\Controllers\Controller;
use App\Http\Requests\ServicePriceList\StoreServicePriceListFormRequest;
use App\Http\Requests\ServicePriceList\UpdateServicePriceListFormRequest;
use App\Http\Resources\Collection\ServicePriceListCollection;
use App\Http\Resources\ServicePriceListResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class ServicePriceListController extends Controller
{
    use ApiResponseTrait;

    private $servicePriceListService;

    public function __construct(ServicePriceListService $servicePriceListService)
    {
        $this->servicePriceListService = $servicePriceListService;
    }

    /**
     * @OA\Get(
     *     path="/api/service-price-lists",
     *     summary="Get all service price lists (paginated)",
     *     operationId="getAllServicePriceLists",
     *     tags={"ServicePriceList"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Filter by service price list ID",
     *         required=false,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="service_id",
     *         in="query",
     *         description="Filter by service ID",
     *         required=false,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="service_name",
     *         in="query",
     *         description="Filter by service name (partial match)",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="price_list_id",
     *         in="query",
     *         description="Filter by price list ID",
     *         required=false,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="price",
     *         in="query",
     *         description="Filter by price",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="price_list_name",
     *         in="query",
     *         description="Filter by price list name (partial match)",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="A paginated list of service price lists",
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
     *                         @OA\Property(
     *                             property="price_list",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=7),
     *                             @OA\Property(property="name", type="string", example="consequatur"),
     *                             @OA\Property(property="location_postal_code", type="string", example="20122-1770"),
     *                             @OA\Property(
     *                                 property="price_list_services",
     *                                 type="array",
     *
     *                                 @OA\Items(
     *                                     type="object",
     *
     *                                     @OA\Property(property="service_price_list_id", type="integer", example=1),
     *                                     @OA\Property(property="service_id", type="integer", example=6),
     *                                     @OA\Property(property="service_name", type="string", example="modi"),
     *                                     @OA\Property(property="service_base_price", type="string", example="77.09"),
     *                                     @OA\Property(property="price", type="string", example="289.52")
     *                                 )
     *                             ),
     *                             @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-24T14:33:55.000000Z"),
     *                             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-24T14:33:55.000000Z")
     *                         ),
     *                         @OA\Property(
     *                             property="service",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=6),
     *                             @OA\Property(property="name", type="string", example="modi"),
     *                             @OA\Property(property="base_price", type="string", example="77.09"),
     *                             @OA\Property(property="is_area_based", type="boolean", example=false)
     *                         ),
     *                         @OA\Property(property="price", type="string", example="289.52"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-24T14:33:55.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-24T14:33:55.000000Z")
     *                     )
     *                 ),
     *                 @OA\Property(property="first_page_url", type="string", example="http://127.0.0.1:8000/api/service-price-lists?page=1"),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=1),
     *                 @OA\Property(property="last_page_url", type="string", example="http://127.0.0.1:8000/api/service-price-lists?page=1"),
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
     *                 @OA\Property(property="path", type="string", example="http://127.0.0.1:8000/api/service-price-lists"),
     *                 @OA\Property(property="per_page", type="integer", example=20),
     *                 @OA\Property(property="prev_page_url", type="string", nullable=true, example=null),
     *                 @OA\Property(property="to", type="integer", example=9),
     *                 @OA\Property(property="total", type="integer", example=9)
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
        $priceListServices = $this->servicePriceListService->getAllServicePriceLists();

        return $this->successResponse([
            'status' => 'success',
            'data' => new ServicePriceListCollection($priceListServices),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/service-price-lists",
     *     summary="Create a new service price list",
     *     operationId="createServicePriceList",
     *     tags={"ServicePriceList"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             type="object",
     *             required={"price_list_id", "service_id", "price"},
     *
     *             @OA\Property(property="price_list_id", type="string", description="The ID of the price list", example="1"),
     *             @OA\Property(property="service_id", type="string", description="The ID of the service", example="2"),
     *             @OA\Property(property="price", type="number", format="float", description="The price of the service", example="99.99")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Service Price List created successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Service Price List Created!"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=10),
     *                 @OA\Property(
     *                     property="price_list",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=7),
     *                     @OA\Property(property="name", type="string", example="consequatur"),
     *                     @OA\Property(property="location_postal_code", type="string", example="20122-1770"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-24T14:33:55.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-24T14:33:55.000000Z")
     *                 ),
     *                 @OA\Property(
     *                     property="service",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=6),
     *                     @OA\Property(property="name", type="string", example="modi"),
     *                     @OA\Property(property="base_price", type="string", example="77.09"),
     *                     @OA\Property(property="is_area_based", type="boolean", example=false),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-24T14:33:55.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-24T14:33:55.000000Z")
     *                 ),
     *                 @OA\Property(property="price", type="string", example="99.99"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-24T14:33:55.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-24T14:33:55.000000Z")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=400, description="Validation error")
     * )
     */
    public function store(StoreServicePriceListFormRequest $request): JsonResponse
    {
        $priceListService = $this->servicePriceListService
            ->createPriceListService($request->validated());

        return $this->successResponse([
            'message' => 'Service Price List Created!',
            'status' => 'success',
            'data' => new ServicePriceListResource($priceListService),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/service-price-lists/{id}",
     *     summary="Get a specific service price list by ID",
     *     operationId="getServicePriceListById",
     *     tags={"ServicePriceList"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the service price list to retrieve",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="The details of the specified service price list",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(
     *                     property="price_list",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=7),
     *                     @OA\Property(property="name", type="string", example="consequatur"),
     *                     @OA\Property(property="location_postal_code", type="string", example="20122-1770"),
     *                     @OA\Property(
     *                         property="price_list_services",
     *                         type="array",
     *
     *                         @OA\Items(
     *                             type="object",
     *
     *                             @OA\Property(property="service_price_list_id", type="integer", example=1),
     *                             @OA\Property(property="service_id", type="integer", example=6),
     *                             @OA\Property(property="service_name", type="string", example="modi"),
     *                             @OA\Property(property="service_base_price", type="string", example="77.09"),
     *                             @OA\Property(property="price", type="string", example="289.52")
     *                         )
     *                     ),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-24T14:33:55.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-24T14:33:55.000000Z")
     *                 ),
     *                 @OA\Property(
     *                     property="service",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=6),
     *                     @OA\Property(property="name", type="string", example="modi"),
     *                     @OA\Property(property="base_price", type="string", example="77.09"),
     *                     @OA\Property(property="is_area_based", type="boolean", example=false)
     *                 ),
     *                 @OA\Property(property="price", type="string", example="289.52"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-24T14:33:55.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-24T14:33:55.000000Z")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Service Price List not found",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Service Price List not found")
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
    public function show(string $id): JsonResponse
    {
        $priceListService = $this->servicePriceListService->showSelectedPriceList($id);

        return $this->successResponse([
            'status' => 'success',
            'data' => new ServicePriceListResource($priceListService),
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/service-price-lists/{id}",
     *     summary="Update an existing service price list",
     *     operationId="updateServicePriceList",
     *     tags={"ServicePriceList"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the service price list to update",
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             type="object",
     *             required={"price_list_id", "service_id", "price"},
     *
     *             @OA\Property(property="price_list_id", type="string", description="The ID of the price list", example="1"),
     *             @OA\Property(property="service_id", type="string", description="The ID of the service", example="3"),
     *             @OA\Property(property="price", type="number", format="float", description="The updated price of the service", example="109.99")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Service Price List updated successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Service Price List Updated!"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=10),
     *                 @OA\Property(
     *                     property="price_list",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=7),
     *                     @OA\Property(property="name", type="string", example="consequatur"),
     *                     @OA\Property(property="location_postal_code", type="string", example="20122-1770"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-24T14:33:55.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-24T14:33:55.000000Z")
     *                 ),
     *                 @OA\Property(
     *                     property="service",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=6),
     *                     @OA\Property(property="name", type="string", example="modi"),
     *                     @OA\Property(property="base_price", type="string", example="77.09"),
     *                     @OA\Property(property="is_area_based", type="boolean", example=false),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-24T14:33:55.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-24T14:33:55.000000Z")
     *                 ),
     *                 @OA\Property(property="price", type="string", example="109.99"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-24T14:33:55.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-24T14:33:55.000000Z")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="Service Price List not found"),
     *     @OA\Response(response=400, description="Validation error")
     * )
     */
    public function update(UpdateServicePriceListFormRequest $request, string $id): JsonResponse
    {
        $service = $this->servicePriceListService->updatePriceListService($id, $request->validated());

        return $this->successResponse([
            'status' => 'success',
            'message' => 'Service Price List Updated!',
            'data' => new ServicePriceListResource($service),
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/service-price-lists/{id}",
     *     summary="Delete a service price list",
     *     operationId="deleteServicePriceList",
     *     tags={"ServicePriceList"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the service price list to delete",
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Service Price List deleted successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Service Price List Deleted!")
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="Service Price List not found")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        $this->servicePriceListService->deletePriceListService($id);

        return $this->successResponse([
            'status' => 'success',
            'message' => 'Service Price List Deleted!',
        ]);
    }
}
