<?php

namespace App\Http\Controllers\Api\Orders;

use App\ActionService\OrderService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderFormRequest;
use App\Http\Requests\Order\UpdateOrderFormRequest;
use App\Http\Resources\Collection\OrderCollection;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class OrderController extends Controller
{
    use ApiResponseTrait;

    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * @OA\Get(
     *     path="/api/orders",
     *     operationId="getOrders",
     *     tags={"Orders"},
     *     summary="Retrieve the paginated list of orders",
     *     description="Fetches a paginated list of all orders along with related client, driver, services, and order carpets. Filters can be applied to narrow down the results.",
     *
     *     @OA\Parameter(
     *         name="order_id",
     *         in="query",
     *         description="Filter by order ID",
     *         required=false,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="client_first_name",
     *         in="query",
     *         description="Filter by client's first name",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="client_last_name",
     *         in="query",
     *         description="Filter by client's last name",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="price_list_name",
     *         in="query",
     *         description="Filter by price list name",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="service_name",
     *         in="query",
     *         description="Filter by service name",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="order_carpet_status",
     *         in="query",
     *         description="Filter by order carpet status",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="schedule_date",
     *         in="query",
     *         description="Filter by schedule date",
     *         required=false,
     *
     *         @OA\Schema(type="string", format="date-time")
     *     ),
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter orders by status (exact match)",
     *         required=false,
     *
     *         @OA\Schema(type="string", enum={"pending", "accepted", "processing", "completed", "undelivered", "cancelled"})
     *     ),
     *
     *     @OA\Parameter(
     *         name="created_at",
     *         in="query",
     *         description="Filter by creation date",
     *         required=false,
     *
     *         @OA\Schema(type="string", format="date")
     *     ),
     *
     *     @OA\Parameter(
     *         name="driver_name",
     *         in="query",
     *         description="Filter by driver name",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="driver_id",
     *         in="query",
     *         description="Filter by driver ID",
     *         required=false,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="A paginated list of orders",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *
     *                     @OA\Items(
     *
     *                         @OA\Property(property="id", type="integer", example=3),
     *                         @OA\Property(property="client", type="object",
     *                             @OA\Property(property="id", type="integer", example=6),
     *                             @OA\Property(property="first_name", type="string", example="Bettye"),
     *                             @OA\Property(property="last_name", type="string", example="Schulist"),
     *                             @OA\Property(property="street_name", type="string", example="Amparo Isle"),
     *                             @OA\Property(property="street_number", type="string", example="3946"),
     *                             @OA\Property(property="postal_code", type="string", example="19410-9689"),
     *                             @OA\Property(property="phone_number", type="string", example="+1-605-536-7506"),
     *                             @OA\Property(property="city", type="string", example="Grimesstad"),
     *                             @OA\Property(property="remarks", type="string", example="Nesciunt quia temporibus ut. Dolores adipisci ducimus molestiae ut distinctio. Unde necessitatibus aut id quis ut."),
     *                             @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-09T15:26:40+00:00"),
     *                             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-09T15:26:40+00:00")
     *                         ),
     *                         @OA\Property(property="driver", type="object", nullable=true,
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="first_name", type="string", example="Laurel"),
     *                             @OA\Property(property="last_name", type="string", example="Emmerich"),
     *                             @OA\Property(property="license_number", type="string", nullable=true),
     *                             @OA\Property(property="vehicle_details", type="string", nullable=true),
     *                             @OA\Property(property="phone_number", type="string", nullable=true)
     *                         ),
     *                         @OA\Property(property="schedule_date", type="string", format="date-time", nullable=true, example="1998-07-08T00:00:00+00:00"),
     *                         @OA\Property(property="price_list", type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="placeat"),
     *                             @OA\Property(property="location_postal_code", type="string", example="72681-9691"),
     *                             @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-09T15:26:40.000000Z"),
     *                             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-09T15:26:40.000000Z")
     *                         ),
     *                         @OA\Property(property="status", type="string", example="pending"),
     *                         @OA\Property(property="is_complaint", type="boolean", example=false),
     *                         @OA\Property(property="total_amount", type="string", example="226.21"),
     *                         @OA\Property(property="order_carpets", type="array",
     *
     *                             @OA\Items(
     *
     *                                 @OA\Property(property="id", type="integer", example=8),
     *                                 @OA\Property(property="qr_code", type="string", nullable=true),
     *                                 @OA\Property(property="height", type="string", nullable=true),
     *                                 @OA\Property(property="width", type="string", nullable=true),
     *                                 @OA\Property(property="total_area", type="string", nullable=true),
     *                                 @OA\Property(property="measured_at", type="string", format="date-time", nullable=true),
     *                                 @OA\Property(property="status", type="string", example="picked up"),
     *                                 @OA\Property(property="remarks", type="string", nullable=true),
     *                                 @OA\Property(property="order_carpet_photos", type="array", @OA\Items()),
     *                                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-11T15:19:31+00:00"),
     *                                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-11T15:19:50+00:00"),
     *                                 @OA\Property(property="services", type="array",
     *
     *                                     @OA\Items(
     *
     *                                         @OA\Property(property="service_id", type="integer", example=1),
     *                                         @OA\Property(property="service_name", type="string", example="voluptatem"),
     *                                         @OA\Property(property="service_base_price", type="string", example="29.15"),
     *                                         @OA\Property(property="service_price_list_price", type="string", nullable=true),
     *                                         @OA\Property(property="total_price", type="string", example="0.00")
     *                                     )
     *                                 ),
     *                                 @OA\Property(property="complaint", type="object", nullable=true)
     *                             )
     *                         ),
     *                         @OA\Property(property="order_service", type="array",
     *
     *                             @OA\Items(
     *
     *                                 @OA\Property(property="id", type="integer", example=3),
     *                                 @OA\Property(property="order_id", type="integer", example=1),
     *                                 @OA\Property(property="service_id", type="integer", example=2),
     *                                 @OA\Property(property="service_name", type="string", example="ipsum"),
     *                                 @OA\Property(property="service_base_price", type="string", example="12.39"),
     *                                 @OA\Property(property="is_service_by_area", type="boolean", example=true),
     *                                 @OA\Property(property="total_price", type="string", example="12.39")
     *                             )
     *                         ),
     *                         @OA\Property(property="order_delivery_confirmation", type="object", nullable=true,
     *                             @OA\Property(property="id", type="integer", example=3),
     *                             @OA\Property(property="order_id", type="integer", example=1),
     *                             @OA\Property(property="confirmation_type", type="string", example="data"),
     *                             @OA\Property(property="confirmation_data", type="string", example="1234567890"),
     *                             @OA\Property(property="signature_url", type="string", nullable=true),
     *                             @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-24T15:23:35+00:00")
     *                         ),
     *                         @OA\Property(property="order_payment", type="object", nullable=true,
     *                             @OA\Property(property="id", type="integer", example=3),
     *                             @OA\Property(property="order_id", type="integer", example=1),
     *                             @OA\Property(property="amount_paid", type="string", example="226.21"),
     *                             @OA\Property(property="payment_method", type="string", example="cash"),
     *                             @OA\Property(property="status", type="string", example="completed"),
     *                             @OA\Property(property="paid_at", type="string", format="date-time", example="2025-02-24T15:23:35+00:00")
     *                         ),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-09T15:26:40+00:00"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-11T15:48:35+00:00")
     *                     )
     *                 ),
     *                 @OA\Property(property="first_page_url", type="string", example="http://127.0.0.1:8000/api/orders?page=1"),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=1),
     *                 @OA\Property(property="last_page_url", type="string", example="http://127.0.0.1:8000/api/orders?page=1"),
     *                 @OA\Property(property="links", type="array",
     *
     *                     @OA\Items(
     *
     *                         @OA\Property(property="url", type="string", nullable=true),
     *                         @OA\Property(property="label", type="string", example="&laquo; Previous"),
     *                         @OA\Property(property="active", type="boolean", example=false)
     *                     )
     *                 ),
     *                 @OA\Property(property="next_page_url", type="string", nullable=true),
     *                 @OA\Property(property="path", type="string", example="http://127.0.0.1:8000/api/orders"),
     *                 @OA\Property(property="per_page", type="integer", example=30),
     *                 @OA\Property(property="prev_page_url", type="string", nullable=true),
     *                 @OA\Property(property="to", type="integer", example=3),
     *                 @OA\Property(property="total", type="integer", example=3)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Error fetching orders",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Error fetching orders"),
     *             @OA\Property(property="error_code", type="string", example="500"),
     *             @OA\Property(property="error", type="string", example="Detailed error message")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Order::class);
        $orders = $this->orderService->getOrders();

        return $this->successResponse([
            'status' => 'success',
            'data' => new OrderCollection($orders),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/orders",
     *     tags={"Orders"},
     *     summary="Create a new order",
     *     description="Creates a new order with the specified client, driver, schedule date, price list, status, and services.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="client_id", type="integer", example=1),
     *             @OA\Property(property="assigned_driver_id", type="integer", nullable=true, example=null),
     *             @OA\Property(property="schedule_date", type="string", format="date", nullable=true, example=null),
     *             @OA\Property(property="price_list_id", type="integer", example=1),
     *             @OA\Property(property="status", type="string", enum={"pending", "accepted", "in_progress", "completed", "cancelled"}, nullable=true, example="pending"),
     *             @OA\Property(property="is_complaint", type="boolean", example=false),
     *             @OA\Property(
     *                 property="services",
     *                 type="array",
     *
     *                 @OA\Items(
     *
     *                     @OA\Property(property="service_id", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Order created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=6),
     *                 @OA\Property(
     *                     property="client",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="first_name", type="string", example="Juston"),
     *                     @OA\Property(property="last_name", type="string", example="Glover"),
     *                     @OA\Property(property="street_name", type="string", example="Hermann Camp"),
     *                     @OA\Property(property="street_number", type="string", example="6647"),
     *                     @OA\Property(property="postal_code", type="string", example="26-217"),
     *                     @OA\Property(property="city", type="string", example="Geoffreymouth"),
     *                     @OA\Property(property="phone_number", type="string", example="+1-680-832-8511"),
     *                     @OA\Property(property="remarks", type="string", example="Velit et esse provident sed. Ut molestias magni occaecati molestiae dignissimos voluptatem sint. Beatae est fugiat consequatur totam facere deleniti dolor."),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-09T15:25:13+00:00"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-09T15:25:13+00:00")
     *                 ),
     *                 @OA\Property(property="driver", type="object", nullable=true),
     *                 @OA\Property(property="schedule_date", type="string", format="date", nullable=true, example=null),
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
     *                 @OA\Property(property="total_amount", type="integer", example=0),
     *                 @OA\Property(
     *                     property="order_carpets",
     *                     type="array",
     *
     *                     @OA\Items(type="object")
     *                 ),
     *
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-11T16:05:32+00:00"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-11T16:05:32+00:00")
     *             ),
     *             @OA\Property(
     *                 property="summary",
     *                 type="object",
     *                 @OA\Property(property="order_id", type="integer", example=6),
     *                 @OA\Property(property="client_id", type="integer", example=1),
     *                 @OA\Property(property="client_name", type="string", example="Juston Glover"),
     *                 @OA\Property(property="assigned_driver_id", type="integer", nullable=true, example=null),
     *                 @OA\Property(property="driver_name", type="string", nullable=true, example=null),
     *                 @OA\Property(property="price_list_id", type="integer", example=1),
     *                 @OA\Property(property="price_list_name", type="string", example="placeat"),
     *                 @OA\Property(property="status", type="string", example="pending"),
     *                 @OA\Property(property="total_amount", type="integer", example=0)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Error creating order",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Error creating order"),
     *             @OA\Property(property="error_code", type="string", example="500"),
     *             @OA\Property(property="error", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function store(StoreOrderFormRequest $request): JsonResponse
    {
        try {
            $result = $this->orderService->createOrder($request->validated());

            return $this->successResponse([
                'status' => 'success',
                'message' => 'Order created successfully',
                'data' => new OrderResource($result['order']),
                'summary' => $result['summary'],
            ], 201);
        } catch (\Exception $e) {
            return $this->errorResponse([
                'status' => 'error',
                'message' => 'Error creating order',
                'error_code' => '500',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/orders/{id}",
     *     operationId="getOrderById",
     *     tags={"Orders"},
     *     summary="Retrieve a specific order by ID",
     *     description="Returns the order details for the specified order ID, including related client, driver, services, price list, carpets, delivery confirmation, and payment information.",
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation: Order found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="client", type="object",
     *                     @OA\Property(property="id", type="integer", example=6),
     *                     @OA\Property(property="first_name", type="string", example="Bettye"),
     *                     @OA\Property(property="last_name", type="string", example="Schulist"),
     *                     @OA\Property(property="street_name", type="string", example="Amparo Isle"),
     *                     @OA\Property(property="street_number", type="string", example="3946"),
     *                     @OA\Property(property="postal_code", type="string", example="19410-9689"),
     *                     @OA\Property(property="phone_number", type="string", example="+1-605-536-7506"),
     *                     @OA\Property(property="city", type="string", example="Grimesstad"),
     *                     @OA\Property(property="remarks", type="string", example="Nesciunt quia temporibus ut. Dolores adipisci ducimus molestiae ut distinctio. Unde necessitatibus aut id quis ut."),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-09T15:26:40+00:00"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-09T15:26:40+00:00")
     *                 ),
     *                 @OA\Property(property="driver", type="object", nullable=true, example=null),
     *                 @OA\Property(property="schedule_date", type="string", format="date-time", nullable=true, example="1998-07-08T00:00:00+00:00"),
     *                 @OA\Property(property="price_list", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="placeat"),
     *                     @OA\Property(property="location_postal_code", type="string", example="72681-9691"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-09T15:26:40.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-09T15:26:40.000000Z")
     *                 ),
     *                 @OA\Property(property="status", type="string", example="pending"),
     *                 @OA\Property(property="is_complaint", type="boolean", example=false),
     *                 @OA\Property(property="total_amount", type="string", example="102.25"),
     *                 @OA\Property(property="order_carpets", type="array",
     *
     *                     @OA\Items(
     *
     *                         @OA\Property(property="id", type="integer", example=3),
     *                         @OA\Property(property="qr_code", type="string", nullable=true, example=null),
     *                         @OA\Property(property="height", type="string", nullable=true, example=null),
     *                         @OA\Property(property="width", type="string", nullable=true, example=null),
     *                         @OA\Property(property="total_area", type="string", nullable=true, example=null),
     *                         @OA\Property(property="measured_at", type="string", format="date-time", nullable=true, example=null),
     *                         @OA\Property(property="status", type="string", example="pending"),
     *                         @OA\Property(property="remarks", type="string", nullable=true, example=null),
     *                         @OA\Property(property="order_carpet_photos", type="array", @OA\Items(), example="[]"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-09T15:49:17+00:00"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-09T15:49:17+00:00"),
     *                         @OA\Property(property="services", type="array",
     *
     *                             @OA\Items(
     *
     *                                 @OA\Property(property="service_id", type="integer", example=1),
     *                                 @OA\Property(property="service_name", type="string", example="voluptatem"),
     *                                 @OA\Property(property="service_base_price", type="string", example="29.15"),
     *                                 @OA\Property(property="service_price_list_price", type="string", nullable=true, example=null),
     *                                 @OA\Property(property="total_price", type="string", example="29.15")
     *                             )
     *                         ),
     *                         @OA\Property(property="complaint", type="object", nullable=true, example=null)
     *                     )
     *                 ),
     *                 @OA\Property(property="order_delivery_confirmation", type="object", nullable=true, example=null),
     *                 @OA\Property(property="order_payment", type="object", nullable=true, example=null),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-09T15:26:40+00:00"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-11T15:55:29+00:00")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="No query results for model [Order] 10")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Error fetching order",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Error fetching order"),
     *             @OA\Property(property="error_code", type="string", example="500"),
     *             @OA\Property(property="error", type="string", example="Detailed error message")
     *         )
     *     )
     * )
     */
    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);
        $results = $this->orderService->showOrder($order);

        return $this->successResponse([
            'status' => 'success',
            'data' => new OrderResource($results['order']),
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/orders/{order}",
     *     operationId="updateOrder",
     *     tags={"Orders"},
     *     summary="Update an existing order",
     *     description="Updates the details of an existing order based on the provided request data.",
     *
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         description="ID of the order to update",
     *
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Order update data",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="client_id", type="integer", example=6),
     *             @OA\Property(property="assigned_driver_id", type="integer", nullable=true, example=null),
     *             @OA\Property(property="schedule_date", type="string", format="date", nullable=true, example="2025-03-15"),
     *             @OA\Property(property="price_list_id", type="integer", example=1),
     *             @OA\Property(property="status", type="string", enum={"pending", "accepted", "in_progress", "completed", "cancelled"}, example="pending"),
     *             @OA\Property(property="is_complaint", type="boolean", example=false)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Order updated successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Order updated successfully"),
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
     *                 @OA\Property(property="driver", type="object", nullable=true, example=null),
     *                 @OA\Property(property="schedule_date", type="string", format="date-time", nullable=true, example="1998-07-08T00:00:00+00:00"),
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
     *                 @OA\Property(property="total_amount", type="string", example="102.25"),
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
     *                         @OA\Property(property="measured_at", type="string", format="date-time", nullable=true, example=null),
     *                         @OA\Property(property="status", type="string", example="pending"),
     *                         @OA\Property(property="remarks", type="string", nullable=true, example=null),
     *                         @OA\Property(property="order_carpet_photos", type="array", @OA\Items(), example="[]"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-09T15:49:17+00:00"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-09T15:49:17+00:00"),
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
     *                         @OA\Property(property="complaint", type="object", nullable=true, example=null)
     *                     )
     *                 ),
     *                 @OA\Property(property="order_delivery_confirmation", type="object", nullable=true, example=null),
     *                 @OA\Property(property="order_payment", type="object", nullable=true, example=null),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-09T15:26:40+00:00"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-11T15:55:29+00:00")
     *             ),
     *             @OA\Property(
     *                 property="summary",
     *                 type="object",
     *                 @OA\Property(property="order_id", type="integer", example=1),
     *                 @OA\Property(property="client_id", type="integer", example=6),
     *                 @OA\Property(property="client_name", type="string", example="Bettye Schulist"),
     *                 @OA\Property(property="assigned_driver_id", type="integer", nullable=true, example=null),
     *                 @OA\Property(property="driver_name", type="string", nullable=true, example=null),
     *                 @OA\Property(property="price_list_id", type="integer", example=1),
     *                 @OA\Property(property="price_list_name", type="string", example="placeat"),
     *                 @OA\Property(property="status", type="string", example="pending"),
     *                 @OA\Property(property="total_amount", type="string", example="102.25")
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
     *                 @OA\Property(
     *                     property="client_id",
     *                     type="array",
     *
     *                     @OA\Items(type="string", example="The selected client id is invalid.")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Error updating order",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Error updating order"),
     *             @OA\Property(property="error_code", type="string", example="500"),
     *             @OA\Property(property="error", type="string", example="Detailed error message")
     *         )
     *     )
     * )
     */
    public function update(UpdateOrderFormRequest $request, Order $order): JsonResponse
    {
        try {
            $updatedOrder = $this->orderService->updateOrder($order, $request->validated());

            return $this->successResponse([
                'status' => 'success',
                'message' => 'Order updated successfully',
                'data' => new OrderResource($updatedOrder['order']),
                'summary' => $updatedOrder['summary'],
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse([
                'status' => 'error',
                'message' => 'Error updating order',
                'error_code' => '500',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
