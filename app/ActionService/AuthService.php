<?php

namespace App\ActionService;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Class AuthService
 *
 * Handles user authentication and registration operations
 */
class AuthService
{
    /**
     * Register a new user in the system
     *
     * @param  array  $validatedData  Validated user registration data (name, email, password)
     * @return User The newly created user instance
     */
    public function registerUser(array $validatedData): User
    {
        return User::create($validatedData);
    }

    /**
     * Generate a personal access token for the user
     *
     * @param  User  $user  The user instance to generate token for
     * @return string The generated access token
     */
    public function generateToken(User $user): string
    {
        return $user->createToken('Personal Access Token')->accessToken;
    }

    /**
     * Authenticate a user based on their credentials
     *
     * @param  array  $userCredentials  User login credentials (email, password)
     * @return User|null The authenticated user or null if credentials are invalid
     */
    public function authenticateUser(array $userCredentials): ?User
    {
        if (Auth::attempt($userCredentials)) {
            $user = Auth::user(); // Get the authenticated user

            return $user; // Return the user object
        }

        return null; // Return null if authentication fails
    }

    /**
     * Get authenticated user information with relationships
     *
     * @return User The authenticated user with driver relationship
     */
    public function getAuthUserInformation(): User
    {
        $user = User::with('driver')->findOrFail(Auth::user()->id);

        return $user;
    }

    /**
     * Update authenticated user information
     *
     * @param  array  $data  Data to update
     * @return User The updated user instance
     */
    public function updateAuthUserInformation(array $data): User
    {
        $user = User::findOrFail(Auth::user()->id);
        $user->update($data);

        return $user;
    }

    /**
     * Logout user and revoke all tokens
     *
     * @return bool Success status
     */
    public function logoutUser(): bool
    {
        try {
            $user = Auth::user();

            if ($user && $user->tokens()) {
                $user->tokens()->delete();
            }

            Auth::logout();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if user exists by email
     *
     * @param  string  $email  Email to check
     * @return bool Whether user exists
     */
    public function userExists(string $email): bool
    {
        return User::where('email', $email)->exists();
    }

    /**
     * Find user by email
     *
     * @param  string  $email  Email to search for
     * @return User|null User instance or null if not found
     */
    public function findUserByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
}
