<?php

namespace App\Http\Controllers\Api\User;

use App\ActionService\UserService;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserFormRequest;
use App\Http\Requests\User\UpdateUserFormRequest;
use App\Http\Resources\Collection\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    use ApiResponseTrait;

    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @OA\Get(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="Get a list of users with optional filters",
     *     description="Retrieve all users in the system with pagination and optional filters.",
     *
     *     @OA\Parameter(
     *         name="first_name",
     *         in="query",
     *         description="Filter users by first name (partial match)",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="last_name",
     *         in="query",
     *         description="Filter users by last name (partial match)",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="Filter users by email (partial match)",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="Filter users by role (exact match)",
     *         required=false,
     *
     *         @OA\Schema(type="string", enum={"admin", "driver", "user"})
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of users",
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
     *                         @OA\Property(property="first_name", type="string", example="Jennings"),
     *                         @OA\Property(property="last_name", type="string", example="Muller"),
     *                         @OA\Property(property="email", type="string", example="rasheed57@example.org"),
     *                         @OA\Property(property="email_verified_at", type="string", format="date-time", example="2025-01-28T15:14:23.000000Z"),
     *                         @OA\Property(property="profile_path", type="string", nullable=true, example=null),
     *                         @OA\Property(property="role", type="string", example="admin"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-28T15:14:23.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-28T15:14:23.000000Z"),
     *                         @OA\Property(
     *                             property="driver",
     *                             type="object",
     *                             nullable=true,
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="user_id", type="integer", example=3),
     *                             @OA\Property(property="license_number", type="string", nullable=true, example=null),
     *                             @OA\Property(property="vehicle_details", type="string", nullable=true, example=null),
     *                             @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-28T15:14:24.000000Z"),
     *                             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-28T15:14:24.000000Z")
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="first_page_url", type="string", example="http://127.0.0.1:8000/api/users?page=1"),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=1),
     *                 @OA\Property(property="last_page_url", type="string", example="http://127.0.0.1:8000/api/users?page=1"),
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
     *                 @OA\Property(property="next_page_url", type="string", nullable=true, example=null),
     *                 @OA\Property(property="path", type="string", example="http://127.0.0.1:8000/api/users"),
     *                 @OA\Property(property="per_page", type="integer", example=30),
     *                 @OA\Property(property="prev_page_url", type="string", nullable=true, example=null),
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
    public function index(): JsonResponse
    {
        $users = $this->userService->getAllUsers();

        return $this->successResponse([
            'status' => 'success',
            'data' => new UserCollection($users),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="Create a new user",
     *     description="Add a new user to the system",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="first_name", type="string", example="Jane"),
     *             @OA\Property(property="last_name", type="string", example="Smith"),
     *             @OA\Property(property="email", type="string", example="jane.smith@example.com"),
     *             @OA\Property(property="role", type="string", example="user"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="New User Created"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="first_name", type="string", example="Jane"),
     *                 @OA\Property(property="last_name", type="string", example="Smith"),
     *                 @OA\Property(property="email", type="string", example="jane.smith@example.com"),
     *                 @OA\Property(property="role", type="string", example="user"),
     *                 @OA\Property(property="profile_path", type="string", example="/images/jane.jpg")
     *             )
     *         )
     *     )
     * )
     */
    public function store(StoreUserFormRequest $request): JsonResponse
    {
        $user = $this->userService->storeNewUser($request->validated());

        return $this->successResponse([
            'status' => 'success',
            'message' => 'New User Created',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/users/{user}",
     *     tags={"Users"},
     *     summary="Get a user's information",
     *     description="Retrieve details about a specific user",
     *
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User details retrieved successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="first_name", type="string", example="John"),
     *                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                 @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                 @OA\Property(property="role", type="string", example="admin"),
     *                 @OA\Property(property="profile_path", type="string", example="/images/john.jpg")
     *             )
     *         )
     *     )
     * )
     */
    public function show(User $user): JsonResponse
    {
        $user->userService->getUserInformation($user);

        return $this->successResponse([
            'status' => 'success',
            'data' => $user,
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/users/{user}",
     *     tags={"Users"},
     *     summary="Update a user's information",
     *     description="Update details of a specific user",
     *
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="first_name", type="string", example="Jane"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="email", type="string", example="jane.doe@example.com"),
     *             @OA\Property(property="role", type="string", example="user"),
     *             @OA\Property(property="password", type="string", example="newpassword123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="User updated successfully"),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="first_name", type="string", example="Jane"),
     *                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                 @OA\Property(property="email", type="string", example="jane.doe@example.com"),
     *                 @OA\Property(property="role", type="string", example="user"),
     *                 @OA\Property(property="profile_path", type="string", example="/images/jane.jpg")
     *             )
     *         )
     *     )
     * )
     */
    public function update(UpdateUserFormRequest $request, User $user): JsonResponse
    {
        $user->userService->updateUser($user, $request->validated());

        return $this->successResponse([
            'status' => 'success',
            'message' => 'User updated successfully',
            'user' => new UserResource($user),
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{user}",
     *     tags={"Users"},
     *     summary="Delete a user",
     *     description="Remove a user from the system",
     *
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="User deleted successfully")
     *         )
     *     )
     * )
     */
    public function destroy(User $user)
    {
        $user->userService->deleteUser($user);

        return $this->successResponse([
            'status' => 'success',
            'message' => 'User deleted successfully',
        ]);
    }
}
