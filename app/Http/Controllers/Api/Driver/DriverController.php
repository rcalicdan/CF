<?php

namespace App\Http\Controllers\Api\Driver;

use App\ActionService\DriverService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\UpdateDriverFormRequest;
use App\Http\Resources\Collection\DriverCollection;
use App\Http\Resources\DriverResource;
use App\Models\Driver;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class DriverController extends Controller
{
    use ApiResponseTrait;

    private $driverService;

    public function __construct(DriverService $driverService)
    {
        $this->driverService = $driverService;
    }

    /**
     * @OA\Get(
     *     path="/api/drivers",
     *     summary="List all drivers with optional filters",
     *     tags={"Drivers"},
     *     description="Retrieve all drivers in the system with pagination and optional filters.",
     *
     *     @OA\Parameter(
     *         name="license_number",
     *         in="query",
     *         description="Filter drivers by license number (partial match)",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="vehicle_details",
     *         in="query",
     *         description="Filter drivers by vehicle details (partial match)",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="first_name",
     *         in="query",
     *         description="Filter drivers by first name (partial match)",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="last_name",
     *         in="query",
     *         description="Filter drivers by last name (partial match)",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="Filter drivers by email (partial match)",
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
     *                 type="array",
     *
     *                 @OA\Items(
     *                     type="object",
     *
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="license_number", type="string", nullable=true, example="DL123456789"),
     *                     @OA\Property(property="vehicle_details", type="string", nullable=true, example="Toyota Corolla, White, 2020"),
     *                     @OA\Property(property="user", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="first_name", type="string", example="John"),
     *                         @OA\Property(property="last_name", type="string", example="Doe"),
     *                         @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                         @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, example="2025-01-28T15:14:23.000000Z"),
     *                         @OA\Property(property="profile_path", type="string", nullable=true, example=null),
     *                         @OA\Property(property="role", type="string", example="driver"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-28T15:14:23.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-28T15:14:23.000000Z")
     *                     )
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
    public function index(): JsonResponse
    {
        $drivers = $this->driverService->getAllDrivers();

        return $this->successResponse([
            'status' => 'success',
            'data' => new DriverCollection($drivers),
        ]);
    }

    /**
     * Get specific driver information
     *
     *
     * @OA\Get(
     *     path="/api/drivers/{driver}",
     *     summary="Get specific driver information",
     *     tags={"Drivers"},
     *     description="Retrieves detailed information about a specific driver, including their associated user details.",
     *
     *     @OA\Parameter(
     *         name="driver",
     *         in="path",
     *         required=true,
     *         description="Driver ID",
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
     *                 @OA\Property(property="license_number", type="string", nullable=true, example="ABC12345"),
     *                 @OA\Property(property="vehicle_details", type="string", nullable=true, example="Toyota Corolla 2020"),
     *                 @OA\Property(property="phone_number", type="string", nullable=true, example="123-456-7890"),
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="first_name", type="string", example="John"),
     *                     @OA\Property(property="last_name", type="string", example="Doe"),
     *                     @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, example="2025-02-14T16:16:29+00:00"),
     *                     @OA\Property(property="profile_path", type="string", nullable=true, example=null),
     *                     @OA\Property(property="role", type="string", example="driver"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-14T16:16:30+00:00"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-14T16:16:30+00:00")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Driver not found",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Driver not found"),
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
     *             @OA\Property(property="message", type="string", example="Failed to retrieve driver"),
     *             @OA\Property(property="error", type="string", example="Detailed error message"),
     *             @OA\Property(property="error_code", type="integer", example=500)
     *         )
     *     )
     * )
     */
    public function show(Driver $driver): JsonResponse
    {
        $driver = $this->driverService->getDriverInformation($driver);

        return $this->successResponse([
            'status' => 'success',
            'data' => new DriverResource($driver),
        ]);
    }

    /**
     * Update driver information
     *
     *
     * @OA\Put(
     *     path="/api/drivers/{driver}",
     *     summary="Update driver information",
     *     tags={"Drivers"},
     *     description="Updates the details of an existing driver, including license number and vehicle details.",
     *
     *     @OA\Parameter(
     *         name="driver",
     *         in="path",
     *         required=true,
     *         description="Driver ID",
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
     *             @OA\Property(property="license_number", type="string", nullable=true, description="Driver's license number", example="ABC12345"),
     *             @OA\Property(property="vehicle_details", type="string", nullable=true, description="Vehicle details", example="Toyota Corolla 2020")
     *         )
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
     *             @OA\Property(property="message", type="string", example="Driver information updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="license_number", type="string", nullable=true, example="ABC12345"),
     *                 @OA\Property(property="vehicle_details", type="string", nullable=true, example="Toyota Corolla 2020"),
     *                 @OA\Property(property="phone_number", type="string", nullable=true, example="123-456-7890"),
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="first_name", type="string", example="John"),
     *                     @OA\Property(property="last_name", type="string", example="Doe"),
     *                     @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, example="2025-02-14T16:16:29+00:00"),
     *                     @OA\Property(property="profile_path", type="string", nullable=true, example=null),
     *                     @OA\Property(property="role", type="string", example="driver"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-14T16:16:30+00:00"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-14T16:16:30+00:00")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Driver not found",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Driver not found"),
     *             @OA\Property(property="error_code", type="integer", example=404)
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
     *                 @OA\Property(property="license_number", type="array", @OA\Items(type="string", example="The license number must be a valid format.")),
     *                 @OA\Property(property="vehicle_details", type="array", @OA\Items(type="string", example="The vehicle details field is required."))
     *             )
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
     *             @OA\Property(property="message", type="string", example="Failed to update driver"),
     *             @OA\Property(property="error", type="string", example="Detailed error message"),
     *             @OA\Property(property="error_code", type="integer", example=500)
     *         )
     *     )
     * )
     */
    public function update(UpdateDriverFormRequest $request, Driver $driver): JsonResponse
    {
        $driver = $this->driverService->updateDriverInformation($driver, $request->validated());

        return $this->successResponse([
            'status' => 'success',
            'message' => 'Driver information updated successfully',
            'data' => new DriverResource($driver),
        ]);
    }
}
