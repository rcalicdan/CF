<?php

namespace App\Http\Controllers\Api\Auth;

use App\ActionService\AuthService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginFormRequest;
use App\Http\Requests\Auth\RegisterFormRequest;
use App\Http\Resources\UserResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Info(
 *     title="Aladyn Backend API",
 *     version="1.0.0",
 *     description="API endpoints for the application"
 * )
 */
class AuthController extends Controller
{
    use ApiResponseTrait;

    private $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     tags={"Authentication"},
     *     summary="Register a new user",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"first_name", "last_name", "email", "password", "role"},
     *
     *             @OA\Property(property="first_name", type="string", example="Kyle"),
     *             @OA\Property(property="last_name", type="string", example="Lowry"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(
     *                 property="role",
     *                 type="string",
     *                 enum={"admin", "driver", "employee"},
     *                 example="admin",
     *                 description="User role - must be either 'admin', 'driver', or 'employee'"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User registered successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="User Registered Successfully!"),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="token", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function register(RegisterFormRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $user = $this->authService->registerUser($request->validated());
            $token = $this->authService->generateToken($user);
            DB::commit();

            return $this->successResponse([
                'status' => 'success',
                'message' => 'User Registered Succesfully!',
                'user' => new UserResource($user),
                'token' => $token,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
                'code' => 500,
            ]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     tags={"Authentication"},
     *     summary="Login user",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Login Successful!"),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="token", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid Credentials"),
     *             @OA\Property(property="code", type="integer", example=401)
     *         )
     *     )
     * )
     */
    public function login(LoginFormRequest $request): JsonResponse
    {
        $user = $this->authService->authenticateUser($request->validated());

        if (! $user) {
            return $this->errorResponse([
                'status' => 'error',
                'message' => 'Invalid Credentials',
                'code' => 401,
            ], 401);
        }

        $token = $this->authService->generateToken($user);

        return $this->successResponse([
            'status' => 'success',
            'message' => 'Login Successful!',
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     tags={"Authentication"},
     *     summary="Logout user",
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Logout Successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();

        return $this->successResponse([
            'status' => 'success',
            'message' => 'Logout Succesfully',
        ]);
    }
}
