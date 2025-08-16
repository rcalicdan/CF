<?php

namespace App\Http\Controllers\Api\Auth;

use App\ActionService\AuthService;
use App\ActionService\PhotoUploadService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateAuthUserFormRequest;
use App\Http\Resources\UserResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class AuthUserController extends Controller
{
    use ApiResponseTrait;

    private $authService;

    private $photoUploadService;

    public function __construct(AuthService $authService, PhotoUploadService $photoUploadService)
    {
        $this->authService = $authService;
        $this->photoUploadService = $photoUploadService;
    }

    /**
     * @OA\Get(
     *     path="/api/auth/user/profile",
     *     tags={"Auth Profile"},
     *     summary="Get authenticated user's profile",
     *     description="Retrieve the profile information of the currently authenticated user",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Authenticated user profile retrieved successfully",
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
     *                 @OA\Property(property="role", type="string", example="user"),
     *                 @OA\Property(property="profile_path", type="string", example="/images/john.jpg")
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $user = $this->authService->getAuthUserInformation();

        return $this->successResponse([
            'status' => 'success',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/user/profile/update",
     *     tags={"Auth Profile"},
     *     summary="Update authenticated user's profile",
     *     description="Update the profile information of the currently authenticated user",
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
     *             @OA\Property(property="password", type="string", example="newpassword123"),
     *             @OA\Property(
     *                 property="profile_picture",
     *                 type="string",
     *                 format="binary",
     *                 description="Optional profile picture upload (image file, max 5MB)"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User profile updated successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="User Information Updated"),
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
    public function update(UpdateAuthUserFormRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        if ($request->hasFile('profile_picture')) {
            $this->photoUploadService->handleProfilePictureUpdate(
                $validatedData,
                $request->file('profile_picture')
            );
        }

        $user = $this->authService->updateAuthUserInformation($validatedData);

        return $this->successResponse([
            'status' => 'success',
            'message' => 'User Information Updated',
            'data' => new UserResource($user),
        ]);
    }
}
