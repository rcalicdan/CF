<?php

namespace App\Http\Controllers\Api\Orders;

use App\ActionService\OrderCarpetService;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderCarpet\StoreOrderCarpetFormRequest;
use App\Http\Requests\OrderCarpet\UpdateOrderCarpetFormRequest;
use App\Http\Resources\Collection\OrderCarpetCollection;
use App\Http\Resources\OrderCarpetResource;
use App\Models\OrderCarpet;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class OrderCarpetController extends Controller
{
    use ApiResponseTrait;

    private $orderCarpetService;

    public function __construct(OrderCarpetService $orderCarpetService)
    {
        $this->orderCarpetService = $orderCarpetService;
    }

    /**
     * @OA\Get(
     *     path="/api/order-carpets",
     *     tags={"Order Carpets"},
     *     summary="Get list of all order carpets",
     *     description="Fetches a paginated list of all order carpets along with related order details. Filters can be applied to narrow down the results.",
     *
     *     @OA\Parameter(
     *         name="qr_code",
     *         in="query",
     *         description="Filter by QR code. Partial matches are supported.",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter order carpets by status (exact match)",
     *         required=false,
     *
     *         @OA\Schema(
     *             type="string",
     *             enum={"pending", "picked up", "at laundry", "measured", "completed", "waiting", "delivered", "not delivered", "returned", "complaint", "under review"}
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="order_id",
     *         in="query",
     *         description="Filter by the associated order ID.",
     *         required=false,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="width",
     *         in="query",
     *         description="Filter by carpet width.",
     *         required=false,
     *
     *         @OA\Schema(type="number", format="float")
     *     ),
     *
     *     @OA\Parameter(
     *         name="height",
     *         in="query",
     *         description="Filter by carpet height.",
     *         required=false,
     *
     *         @OA\Schema(type="number", format="float")
     *     ),
     *
     *     @OA\Parameter(
     *         name="total_area",
     *         in="query",
     *         description="Filter by carpet total area.",
     *         required=false,
     *
     *         @OA\Schema(type="number", format="float")
     *     ),
     *
     *     @OA\Parameter(
     *         name="measured_at",
     *         in="query",
     *         description="Filter by the measured date/time of the carpet. Format: YYYY-MM-DD HH:MM:SS.",
     *         required=false,
     *
     *         @OA\Schema(type="string", format="date-time")
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
     *                         @OA\Property(property="id", type="integer", example=4),
     *                         @OA\Property(property="qr_code", type="string", nullable=true, example=null),
     *                         @OA\Property(property="height", type="string", nullable=true, example="2.00"),
     *                         @OA\Property(property="width", type="string", nullable=true, example="2.00"),
     *                         @OA\Property(property="total_area", type="integer", nullable=true, example=4),
     *                         @OA\Property(property="measured_at", type="string", format="date-time", nullable=true, example="2025-03-10T15:06:20+00:00"),
     *                         @OA\Property(property="status", type="string", example="pending"),
     *                         @OA\Property(property="remarks", type="string", nullable=true, example=null),
     *                         @OA\Property(
     *                             property="order_carpet_photos",
     *                             type="array",
     *
     *                             @OA\Items(
     *                                 type="object",
     *
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="order_carpet_id", type="integer", example=1),
     *                                 @OA\Property(property="photo_url", type="string", example="/uploads/carpets/order1_photo1.jpg"),
     *                                 @OA\Property(
     *                                     property="taken_by",
     *                                     type="string",
     *                                     example="Anthony DuBuque"
     *                                 ),
     *                                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-15T20:26:09+00:00")
     *                             )
     *                         ),
     *                         @OA\Property(
     *                             property="services",
     *                             type="array",
     *
     *                             @OA\Items(
     *                                 type="object",
     *
     *                                 @OA\Property(property="service_id", type="integer", example=1),
     *                                 @OA\Property(property="service_name", type="string", example="voluptatem"),
     *                                 @OA\Property(property="service_base_price", type="string", example="29.15"),
     *                                 @OA\Property(property="service_price_list_price", type="string", nullable=true, example=null),
     *                                 @OA\Property(property="total_price", type="string", example="116.60")
     *                             )
     *                         ),
     *                         @OA\Property(property="complaint", type="object", nullable=true),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-10T15:05:29+00:00"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-10T15:06:20+00:00")
     *                     )
     *                 ),
     *                 @OA\Property(property="first_page_url", type="string", example="http://127.0.0.1:8000/api/orders?page=1"),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=1),
     *                 @OA\Property(property="last_page_url", type="string", example="http://127.0.0.1:8000/api/orders?page=1"),
     *                 @OA\Property(
     *                     property="links",
     *                     type="array",
     *
     *                     @OA\Items(
     *                         type="object",
     *
     *                         @OA\Property(property="url", type="string", nullable=true, example=null),
     *                         @OA\Property(property="label", type="string", example="« Previous"),
     *                         @OA\Property(property="active", type="boolean", example=false)
     *                     )
     *                 ),
     *                 @OA\Property(property="next_page_url", type="string", nullable=true, example=null),
     *                 @OA\Property(property="path", type="string", example="http://127.0.0.1:8000/api/orders"),
     *                 @OA\Property(property="per_page", type="integer", example=30),
     *                 @OA\Property(property="prev_page_url", type="string", nullable=true, example=null),
     *                 @OA\Property(property="to", type="integer", example=3),
     *                 @OA\Property(property="total", type="integer", example=3)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Error fetching order carpets",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Error fetching order carpets"),
     *             @OA\Property(property="error_code", type="string", example="500"),
     *             @OA\Property(property="error", type="string", example="Detailed error message")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', OrderCarpet::class);
        $orderCarpets = $this->orderCarpetService->getAllOrderCarpets();

        return $this->successResponse([
            'status' => 'success',
            'data' => new OrderCarpetCollection($orderCarpets),
        ]);
    }

    /**
     * Store a new order carpet
     *
     * @OA\Post(
     *     path="/api/order-carpets",
     *     tags={"Order Carpets"},
     *     summary="Create a new order carpet",
     *     description="Creates a new order carpet and associates it with an existing order.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             type="object",
     *             required={"order_id", "services"},
     *
     *             @OA\Property(property="order_id", type="string", example="1"),
     *             @OA\Property(property="qr_code", type="string", nullable=true, example="QR123456"),
     *             @OA\Property(property="status", type="string", example="pending"),
     *             @OA\Property(property="remarks", type="string", nullable=true, example="Handle with care", description="Maximum 1000 characters"),
     *             @OA\Property(
     *                 property="services",
     *                 type="array",
     *
     *                 @OA\Items(type="integer", example=1),
     *                 description="Array of service IDs"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Order carpet created successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Order carpet added successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=7),
     *                 @OA\Property(property="qr_code", type="string", nullable=true, example=null),
     *                 @OA\Property(property="height", type="number", format="float", nullable=true, example=null),
     *                 @OA\Property(property="width", type="number", format="float", nullable=true, example=null),
     *                 @OA\Property(property="total_area", type="number", format="float", nullable=true, example=null),
     *                 @OA\Property(property="measured_at", type="string", format="date-time", nullable=true, example=null),
     *                 @OA\Property(property="status", type="string", example="pending"),
     *                 @OA\Property(property="remarks", type="string", nullable=true, example=null),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-11T14:57:03+00:00"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-11T14:57:03+00:00"),
     *                 @OA\Property(
     *                     property="services",
     *                     type="array",
     *
     *                     @OA\Items(
     *                         type="object",
     *
     *                         @OA\Property(property="service_id", type="integer", example=1),
     *                         @OA\Property(property="service_name", type="string", example="voluptatem"),
     *                         @OA\Property(property="service_base_price", type="string", example="29.15"),
     *                         @OA\Property(property="service_price_list_price", type="string", nullable=true, example=null),
     *                         @OA\Property(property="total_price", type="string", example="0.00")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="order",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(
     *                         property="client",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=6),
     *                         @OA\Property(property="first_name", type="string", example="Bettye"),
     *                         @OA\Property(property="last_name", type="string", example="Schulist"),
     *                         @OA\Property(property="street_name", type="string", example="Amparo Isle"),
     *                         @OA\Property(property="street_number", type="string", example="3946"),
     *                         @OA\Property(property="postal_code", type="string", example="19410-9689"),
     *                         @OA\Property(property="phone_number", type="string", example="+1-605-536-7506"),
     *                         @OA\Property(property="city", type="string", example="Grimesstad"),
     *                         @OA\Property(property="remarks", type="string", example="Nesciunt quia temporibus ut. Dolores adipisci ducimus molestiae ut distinctio. Unde necessitatibus aut id quis ut."),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-09T15:26:40+00:00"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-09T15:26:40+00:00")
     *                     ),
     *                     @OA\Property(property="driver", type="null", nullable=true),
     *                     @OA\Property(property="schedule_date", type="string", format="date-time", nullable=true, example="1998-07-08T00:00:00+00:00"),
     *                     @OA\Property(
     *                         property="price_list",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="placeat"),
     *                         @OA\Property(property="location_postal_code", type="string", example="72681-9691"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-09T15:26:40.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-09T15:26:40.000000Z")
     *                     ),
     *                     @OA\Property(property="status", type="string", example="pending"),
     *                     @OA\Property(property="is_complaint", type="boolean", example=false),
     *                     @OA\Property(property="total_amount", type="string", example="29.15"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-09T15:26:40+00:00"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-09T15:49:17+00:00")
     *                 ),
     *                 @OA\Property(property="complaint", type="null", nullable=true)
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
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="order_id", type="array", @OA\Items(type="string", example="The order id field is required.")),
     *                 @OA\Property(property="services", type="array", @OA\Items(type="string", example="The services field is required."))
     *             )
     *         )
     *     )
     * )
     */
    public function store(StoreOrderCarpetFormRequest $request): JsonResponse
    {
        $orderCarpet = $this->orderCarpetService->storeOrderCarpet($request->validated());

        return $this->successResponse([
            'status' => 'success',
            'message' => 'Order carpet added successfully',
            'data' => new OrderCarpetResource($orderCarpet),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/order-carpets/{id}",
     *     tags={"Order Carpets"},
     *     summary="Get order carpet by ID",
     *     description="Retrieves the details of a specific order carpet by its ID, including related order information.",
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order carpet ID",
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
     *                 @OA\Property(
     *                     property="client",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=6),
     *                     @OA\Property(property="first_name", type="string", example="Bettye"),
     *                     @OA\Property(property="last_name", type="string", example="Schulist"),
     *                     @OA\Property(property="street_name", type="string", example="Amparo Isle"),
     *                     @OA\Property(property="street_number", type="string", example="3946"),
     *                     @OA\Property(property="postal_code", type="string", example="19410-9689"),
     *                     @OA\Property(property="city", type="string", example="Grimesstad"),
     *                     @OA\Property(property="phone_number", type="string", example="+1-605-536-7506"),
     *                     @OA\Property(property="remarks", type="string", example="Nesciunt quia temporibus ut. Dolores adipisci ducimus molestiae ut distinctio. Unde necessitatibus aut id quis ut."),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-09T15:26:40+00:00"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-09T15:26:40+00:00")
     *                 ),
     *                 @OA\Property(property="driver", type="null", nullable=true),
     *                 @OA\Property(property="schedule_date", type="string", format="date-time", example="1998-07-08T00:00:00+00:00"),
     *                 @OA\Property(
     *                     property="price_list",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="placeat"),
     *                     @OA\Property(property="location_postal_code", type="string", example="72681-9691"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-09T15:26:40.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-09T15:26:40.000000Z")
     *                 ),
     *                 @OA\Property(property="status", type="string", example="pending"),
     *                 @OA\Property(property="is_complaint", type="boolean", example=false),
     *                 @OA\Property(property="total_amount", type="string", example="29.15"),
     *                 @OA\Property(
     *                     property="order_carpets",
     *                     type="array",
     *
     *                     @OA\Items(
     *                         type="object",
     *
     *                         @OA\Property(property="id", type="integer", example=3),
     *                         @OA\Property(property="qr_code", type="string", nullable=true, example=null),
     *                         @OA\Property(property="height", type="string", nullable=true, example=null),
     *                         @OA\Property(property="width", type="string", nullable=true, example=null),
     *                         @OA\Property(property="total_area", type="string", nullable=true, example=null),
     *                         @OA\Property(property="measured_at", type="string", nullable=true, example=null),
     *                         @OA\Property(property="status", type="string", example="pending"),
     *                         @OA\Property(property="remarks", type="string", nullable=true, example=null),
     *                         @OA\Property(
     *                             property="order_carpet_photos",
     *                             type="array",
     *
     *                             @OA\Items(type="object")
     *                         ),
     *
     *                         @OA\Property(
     *                             property="services",
     *                             type="array",
     *
     *                             @OA\Items(
     *                                 type="object",
     *
     *                                 @OA\Property(property="service_id", type="integer", example=1),
     *                                 @OA\Property(property="service_name", type="string", example="voluptatem"),
     *                                 @OA\Property(property="service_base_price", type="string", example="29.15"),
     *                                 @OA\Property(property="service_price_list_price", type="string", nullable=true, example=null),
     *                                 @OA\Property(property="total_price", type="string", example="29.15")
     *                             )
     *                         ),
     *                         @OA\Property(property="complaint", type="object", nullable=true),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-09T15:49:17+00:00"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-09T15:49:17+00:00")
     *                     )
     *                 ),
     *                 @OA\Property(property="order_delivery_confirmation", type="null", nullable=true),
     *                 @OA\Property(property="order_payment", type="null", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-09T15:26:40+00:00"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-09T15:49:17+00:00")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Order carpet not found",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="message", type="string", example="Order carpet not found")
     *         )
     *     )
     * )
     */
    public function show(OrderCarpet $orderCarpet): JsonResponse
    {
        $this->authorize('view', $orderCarpet);
        $orderCarpet = $orderCarpet::with([
            'order',
            'complaint',
            'order.driver.user',
            'order.client',
            'order.priceList',
            'order.orderServices',
            'orderCarpetPhotos.user',
            'services',
        ])->find($orderCarpet->id);

        return $this->successResponse([
            'status' => 'success',
            'data' => new OrderCarpetResource($orderCarpet),
        ]);
    }

    /**
     * Update order carpet
     *
     * @OA\Put(
     *     path="/api/order-carpets/{id}",
     *     tags={"Order Carpets"},
     *     summary="Update an existing order carpet",
     *     description="Updates the details of an existing order carpet by its ID.",
     *
     *     @OA\Parameter(
     *         name="id",
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
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="order_id", type="string", example="1"),
     *             @OA\Property(property="qr_code", type="string", nullable=true, example="QR123456"),
     *             @OA\Property(property="status", type="string", example="picked up"),
     *             @OA\Property(property="remarks", type="string", nullable=true, example="Updated remarks", description="Maximum 1000 characters"),
     *             @OA\Property(
     *                 property="services",
     *                 type="array",
     *
     *                 @OA\Items(type="integer", example=1),
     *                 description="Array of service IDs"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Order carpet updated successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Order Carpet Updated!"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="qr_code", type="string", nullable=true, example="12345678"),
     *                 @OA\Property(property="height", type="number", format="float", nullable=true, example=null),
     *                 @OA\Property(property="width", type="number", format="float", nullable=true, example=null),
     *                 @OA\Property(property="total_area", type="number", format="float", nullable=true, example=null),
     *                 @OA\Property(property="measured_at", type="string", format="date-time", nullable=true, example=null),
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
     *                         @OA\Property(
     *                             property="taken_by",
     *                             type="string",
     *                             example="Anthony DuBuque"
     *                         ),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-15T20:26:09+00:00")
     *                     )
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-14T16:19:16+00:00"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-16T14:40:49+00:00"),
     *                 @OA\Property(
     *                     property="services",
     *                     type="array",
     *
     *                     @OA\Items(
     *                         type="object",
     *
     *                         @OA\Property(property="service_id", type="integer", example=1),
     *                         @OA\Property(property="service_name", type="string", example="voluptatem"),
     *                         @OA\Property(property="service_base_price", type="string", example="29.15"),
     *                         @OA\Property(property="service_price_list_price", type="string", nullable=true, example=null),
     *                         @OA\Property(property="total_price", type="string", example="0.00")
     *                     )
     *                 ),
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
     *                     @OA\Property(property="schedule_date", type="string", format="date-time", nullable=true, example="1987-07-23T00:00:00+00:00"),
     *                     @OA\Property(
     *                         property="price_list",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="perspiciatis"),
     *                         @OA\Property(property="location_postal_code", type="string", example="07486"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-09T15:26:40.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-09T15:26:40.000000Z")
     *                     ),
     *                     @OA\Property(property="status", type="string", example="pending"),
     *                     @OA\Property(property="is_complaint", type="boolean", example=false),
     *                     @OA\Property(property="total_amount", type="string", example="29.15"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-14T16:16:30+00:00"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-16T14:40:49+00:00")
     *                 ),
     *                 @OA\Property(property="complaint", type="null", nullable=true)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Order carpet not found",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="message", type="string", example="Order carpet not found")
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
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="status", type="array", @OA\Items(type="string", example="The status field is required.")),
     *                 @OA\Property(property="services", type="array", @OA\Items(type="string", example="The services field is required."))
     *             )
     *         )
     *     )
     * )
     */
    public function update(UpdateOrderCarpetFormRequest $request, OrderCarpet $orderCarpet): JsonResponse
    {
        $orderCarpet = $this->orderCarpetService->updateOrderCarpet($orderCarpet, $request->validated());

        return $this->successResponse(data: [
            'status' => 'success',
            'message' => 'Order Carpet Updated!',
            'data' => new OrderCarpetResource($orderCarpet),
        ]);
    }

    /**
     * Delete order carpet
     *
     * @OA\Delete(
     *     path="/api/order-carpets/{id}",
     *     tags={"Order Carpets"},
     *     summary="Delete an order carpet",
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order carpet ID",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Order carpet deleted successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Order Carpet Deleted!")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Order carpet not found"
     *     )
     * )
     */
    public function destroy(OrderCarpet $orderCarpet): JsonResponse
    {
        $this->orderCarpetService->deleteCarpet($orderCarpet);

        return $this->successResponse([
            'status' => 'success',
            'message' => 'Order Carpet Deleted!',
        ]);
    }
}
