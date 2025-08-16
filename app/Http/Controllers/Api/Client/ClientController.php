<?php

namespace App\Http\Controllers\Api\Client;

use App\ActionService\ClientService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientFormRequest;
use App\Http\Requests\Client\UpdateClientFormRequest;
use App\Http\Resources\ClientResource;
use App\Http\Resources\Collection\ClientCollection;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Tag(
 *     name="Clients",
 *     description="API Endpoints for client management"
 * )
 */
class ClientController extends Controller
{
    use ApiResponseTrait;

    private $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    /**
     * @OA\Get(
     *     path="/api/clients",
     *     tags={"Clients"},
     *     summary="Get all clients with optional filters",
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="first_name",
     *         in="query",
     *         description="Filter clients by first name (partial match)",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="last_name",
     *         in="query",
     *         description="Filter clients by last name (partial match)",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="phone_number",
     *         in="query",
     *         description="Filter clients by phone number (partial match)",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="street_name",
     *         in="query",
     *         description="Filter clients by street name (partial match)",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="street_number",
     *         in="query",
     *         description="Filter clients by street number (partial match)",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="city",
     *         in="query",
     *         description="Filter clients by city (partial match)",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="postal_code",
     *         in="query",
     *         description="Filter clients by postal code (partial match)",
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
     *                 @OA\Property(property="data", type="array",
     *
     *                     @OA\Items(
     *                         type="object",
     *
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="first_name", type="string", example="Sofia"),
     *                         @OA\Property(property="last_name", type="string", example="Casper"),
     *                         @OA\Property(property="street_name", type="string", example="Norma Radial"),
     *                         @OA\Property(property="street_number", type="string", example="9515"),
     *                         @OA\Property(property="postal_code", type="string", example="98315"),
     *                         @OA\Property(property="city", type="string", example="Botsfordfort"),
     *                         @OA\Property(property="phone_number", type="string", example="1-747-707-8924"),
     *                         @OA\Property(property="remarks", type="string", example="Molestias perferendis omnis quae deserunt est consequuntur sunt doloremque."),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-28T15:25:34.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-28T15:25:34.000000Z")
     *                     )
     *                 ),
     *                 @OA\Property(property="first_page_url", type="string", example="http://127.0.0.1:8000/api/clients?page=1"),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=1),
     *                 @OA\Property(property="last_page_url", type="string", example="http://127.0.0.1:8000/api/clients?page=1"),
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
     *                 @OA\Property(property="next_page_url", type="string", nullable=true),
     *                 @OA\Property(property="path", type="string", example="http://127.0.0.1:8000/api/clients"),
     *                 @OA\Property(property="per_page", type="integer", example=30),
     *                 @OA\Property(property="prev_page_url", type="string", nullable=true),
     *                 @OA\Property(property="to", type="integer", example=3),
     *                 @OA\Property(property="total", type="integer", example=3)
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
    public function index(): JsonResource|JsonResponse
    {
        $clients = $this->clientService->getAllClients();

        return $this->successResponse([
            'status' => 'success',
            'data' => new ClientCollection($clients),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/clients",
     *     tags={"Clients"},
     *     summary="Create a new client",
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"first_name", "last_name", "phone_number"},
     *
     *             @OA\Property(property="first_name", type="string", maxLength=255, description="The first name of the client (required)."),
     *             @OA\Property(property="last_name", type="string", maxLength=255, description="The last name of the client (required)."),
     *             @OA\Property(property="street_name", type="string", maxLength=255, nullable=true, description="The street name of the client (nullable)."),
     *             @OA\Property(property="street_number", type="string", maxLength=255, pattern="^[0-9]+$", nullable=true, description="The street number of the client (nullable, must be numeric)."),
     *             @OA\Property(property="postal_code", type="string", maxLength=255, pattern="^[0-9]+$", nullable=true, description="The postal code of the client (nullable, must be numeric)."),
     *             @OA\Property(property="city", type="string", maxLength=255, nullable=true, description="The city of the client (nullable)."),
     *             @OA\Property(property="phone_number", type="string", maxLength=255, pattern="^[0-9]+$", description="The phone number of the client (required, must be numeric)."),
     *             @OA\Property(property="remarks", type="string", maxLength=500, nullable=true, description="Additional remarks about the client (nullable).")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Client created successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Client Added!"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function store(StoreClientFormRequest $request): JsonResponse
    {
        $client = $this->clientService->createClient($request->validated());

        return $this->successResponse([
            'status' => 'success',
            'message' => 'Client Added!',
            'data' => new ClientResource($client),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/clients/{id}",
     *     tags={"Clients"},
     *     summary="Get specific client",
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string"),
     *         description="The unique identifier of the client (required).",
     *         example="1"
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="first_name", type="string", example="Madge"),
     *                 @OA\Property(property="last_name", type="string", example="Cassin"),
     *                 @OA\Property(property="street_name", type="string", nullable=true, example="Eduardo Run"),
     *                 @OA\Property(property="street_number", type="string", nullable=true, example="1609"),
     *                 @OA\Property(property="postal_code", type="string", nullable=true, example="37905-9294"),
     *                 @OA\Property(property="phone_number", type="string", example="682-244-9815"),
     *                 @OA\Property(property="city", type="string", nullable=true, example="Poznan"),
     *                 @OA\Property(property="remarks", type="string", nullable=true, example="Iure ipsum minima ut porro dolorem voluptatibus officia. Neque est aspernatur et quia. Dolorem molestiae officiis quia saepe. Rerum ea repellat consequatur quis autem odio dolorum."),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-14T16:16:30+00:00"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-20T05:31:18+00:00")
     *             )
     *         )
     *     )
     * )
     */
    public function show(string $id): JsonResource|JsonResponse
    {
        $client = $this->clientService->showSelectedClient($id);

        return $this->successResponse([
            'status' => 'success',
            'data' => new ClientResource($client),
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/clients/{id}",
     *     tags={"Clients"},
     *     summary="Update client",
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string"),
     *         description="The unique identifier of the client (required).",
     *         example="1"
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"first_name", "last_name", "phone_number"},
     *
     *             @OA\Property(
     *                 property="first_name",
     *                 type="string",
     *                 maxLength=255,
     *                 description="The first name of the client (required).",
     *                 example="John"
     *             ),
     *             @OA\Property(
     *                 property="last_name",
     *                 type="string",
     *                 maxLength=255,
     *                 description="The last name of the client (required).",
     *                 example="Doe"
     *             ),
     *             @OA\Property(
     *                 property="street_name",
     *                 type="string",
     *                 maxLength=255,
     *                 nullable=true,
     *                 description="The street name of the client (nullable).",
     *                 example="Baker Street"
     *             ),
     *             @OA\Property(
     *                 property="street_number",
     *                 type="string",
     *                 maxLength=255,
     *                 pattern="^[0-9]+$",
     *                 nullable=true,
     *                 description="The street number of the client (nullable, must be numeric).",
     *                 example="221"
     *             ),
     *             @OA\Property(
     *                 property="postal_code",
     *                 type="string",
     *                 maxLength=255,
     *                 pattern="^[0-9]+$",
     *                 nullable=true,
     *                 description="The postal code of the client (nullable, must be numeric).",
     *                 example="12345"
     *             ),
     *             @OA\Property(
     *                 property="city",
     *                 type="string",
     *                 maxLength=255,
     *                 nullable=true,
     *                 description="The city of the client (nullable).",
     *                 example="London"
     *             ),
     *             @OA\Property(
     *                 property="phone_number",
     *                 type="string",
     *                 maxLength=255,
     *                 pattern="^[0-9]+$",
     *                 description="The phone number of the client (required, must be numeric).",
     *                 example="1234567890"
     *             ),
     *             @OA\Property(
     *                 property="remarks",
     *                 type="string",
     *                 maxLength=500,
     *                 nullable=true,
     *                 description="Additional remarks about the client (nullable).",
     *                 example="Regular customer, prefers email contact."
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Client updated successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Client Updated!"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="first_name", type="string", example="Madge"),
     *                 @OA\Property(property="last_name", type="string", example="Cassin"),
     *                 @OA\Property(property="street_name", type="string", nullable=true, example="Eduardo Run"),
     *                 @OA\Property(property="street_number", type="string", nullable=true, example="1609"),
     *                 @OA\Property(property="postal_code", type="string", nullable=true, example="37905-9294"),
     *                 @OA\Property(property="phone_number", type="string", example="682-244-9815"),
     *                 @OA\Property(property="city", type="string", nullable=true, example="Poznan"),
     *                 @OA\Property(property="remarks", type="string", nullable=true, example="Iure ipsum minima ut porro dolorem voluptatibus officia. Neque est aspernatur et quia. Dolorem molestiae officiis quia saepe. Rerum ea repellat consequatur quis autem odio dolorum."),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-14T16:16:30+00:00"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-20T05:31:18+00:00")
     *             )
     *         )
     *     )
     * )
     */
    public function update(UpdateClientFormRequest $request, string $id): JsonResponse
    {
        $client = $this->clientService->updateClient($id, $request->validated());

        return $this->successResponse([
            'status' => 'success',
            'message' => 'Client Updated!',
            'data' => new ClientResource($client),
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/clients/{id}",
     *     tags={"Clients"},
     *     summary="Delete client",
     *     security={{"bearerAuth": {}}},
     *
     *    @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string"),
     *         description="The unique identifier of the client (required).",
     *         example="1"),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Client deleted successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Client Deleted!")
     *         )
     *     )
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        $this->clientService->deleteClient($id);

        return $this->successResponse([
            'status' => 'success',
            'message' => 'Client Deleted!',
        ]);
    }
}
