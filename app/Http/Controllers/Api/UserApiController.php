<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserApiController extends Controller
{
    /**
     * Create user in RizqMall when they register in Sandbox
     * This ensures unified account management across both platforms
     */
    public function createFromSandbox(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'phone' => 'nullable|string|max:20',
                'country' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:100',
                'city' => 'nullable|string|max:100',
                'sandbox_user_id' => 'required|integer', // ID from Sandbox database
            ]);

            Log::info('Creating RizqMall user from Sandbox registration', [
                'email' => $request->email,
                'sandbox_user_id' => $request->sandbox_user_id,
            ]);

            // Check if user already exists
            $existingUser = User::where('email', $request->email)->first();
            if ($existingUser) {
                // Update subscription_user_id if not set
                if (!$existingUser->subscription_user_id) {
                    $existingUser->subscription_user_id = $request->sandbox_user_id;
                    $existingUser->save();
                }

                Log::warning('User already exists in RizqMall', [
                    'email' => $request->email,
                    'user_id' => $existingUser->id,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'User already exists',
                    'user' => [
                        'id' => $existingUser->id,
                        'email' => $existingUser->email,
                        'name' => $existingUser->name,
                    ],
                ], 200);
            }

            // Create user in RizqMall
            $user = User::create([
                'subscription_user_id' => $request->sandbox_user_id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'country' => $request->country,
                'state' => $request->state,
                'city' => $request->city,
                'password' => null, // No password initially - will use SSO
                'user_type' => 'customer', // Default to customer
                'auth_type' => 'sso', // SSO authentication
                'is_active' => true,
                'email_verified' => false,
                'subscription_status' => 'none',
            ]);

            Log::info('RizqMall user created successfully from Sandbox', [
                'rizqmall_user_id' => $user->id,
                'sandbox_user_id' => $request->sandbox_user_id,
                'email' => $user->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully in RizqMall',
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                    'user_type' => $user->user_type,
                ],
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed for Sandbox user creation in RizqMall', [
                'errors' => $e->errors(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Failed to create RizqMall user from Sandbox', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create user in RizqMall',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Find user by email (for linking existing accounts)
     */
    public function findByEmail(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            Log::info('RizqMall user found by email', [
                'email' => $request->email,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                    'subscription_user_id' => $user->subscription_user_id,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to find RizqMall user by email', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error finding user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Link RizqMall user to Sandbox user
     */
    public function linkToSandbox(Request $request)
    {
        try {
            $request->validate([
                'rizqmall_user_id' => 'required|integer|exists:users,id',
                'sandbox_user_id' => 'required|integer',
            ]);

            $user = User::find($request->rizqmall_user_id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            // Link to Sandbox
            $user->subscription_user_id = $request->sandbox_user_id;
            $user->auth_type = 'sso'; // Update to SSO since they're linked
            $user->save();

            Log::info('RizqMall user linked to Sandbox', [
                'rizqmall_user_id' => $user->id,
                'sandbox_user_id' => $request->sandbox_user_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User linked successfully',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to link RizqMall user to Sandbox', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error linking user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
