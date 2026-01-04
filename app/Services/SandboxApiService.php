<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SandboxApiService
{
    protected $baseUrl;
    protected $apiKey;
    protected $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.sandbox.url');
        $this->apiKey = config('services.sandbox.api_key');
        $this->timeout = config('services.sandbox.timeout', 30);
    }

    /**
     * Validate SSO token with Sandbox
     */
    public function validateSsoToken(string $token)
    {
        try {
            Log::info('Validating SSO token', [
                'sandbox_url' => $this->baseUrl,
                'api_key_set' => !empty($this->apiKey),
                'token_length' => strlen($token),
            ]);

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->post($this->baseUrl . '/api/rizqmall/validate-token', [
                    'token' => $token,
                ]);

            Log::info('Token validation response', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body' => $response->body(),
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['valid'] ?? false) {
                    Log::info('Token validated successfully', [
                        'user_id' => $data['user']['id'] ?? null,
                    ]);
                    return $data['user'];
                }
            }

            Log::warning('SSO token validation failed', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('SSO token validation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Fetch user data from Sandbox
     */
    public function fetchUserData(int $subscriptionUserId)
    {
        $cacheKey = "sandbox_user_{$subscriptionUserId}";

        // Try cache first (5 minutes)
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return $cached;
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl . "/api/rizqmall/user/{$subscriptionUserId}");

            if ($response->successful()) {
                $data = $response->json();

                if ($data['success'] ?? false) {
                    // Cache for 5 minutes
                    Cache::put($cacheKey, $data['user'], now()->addMinutes(5));
                    return $data['user'];
                }
            }

            Log::warning('Failed to fetch user data from Sandbox', [
                'user_id' => $subscriptionUserId,
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error fetching user data from Sandbox', [
                'user_id' => $subscriptionUserId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Verify subscription status with Sandbox
     */
    public function verifySubscription(int $subscriptionUserId)
    {
        $cacheKey = "sandbox_subscription_{$subscriptionUserId}";

        // Try cache first (2 minutes for subscription status)
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return $cached;
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl . "/api/rizqmall/subscription/{$subscriptionUserId}/verify");

            if ($response->successful()) {
                $data = $response->json();

                if ($data['success'] ?? false) {
                    $result = [
                        'has_active' => $data['has_active_subscription'] ?? false,
                        'subscription' => $data['subscription'] ?? null,
                    ];

                    // Cache for 2 minutes
                    Cache::put($cacheKey, $result, now()->addMinutes(2));
                    return $result;
                }
            }

            Log::warning('Failed to verify subscription with Sandbox', [
                'user_id' => $subscriptionUserId,
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error verifying subscription with Sandbox', [
                'user_id' => $subscriptionUserId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Notify Sandbox of user logout
     */
    public function notifyLogout(int $subscriptionUserId)
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->post($this->baseUrl . '/api/rizqmall/logout-webhook', [
                    'user_id' => $subscriptionUserId,
                ]);

            if ($response->successful()) {
                Log::info('Logout notification sent to Sandbox', [
                    'user_id' => $subscriptionUserId,
                ]);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Error notifying Sandbox of logout', [
                'user_id' => $subscriptionUserId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Clear cached user data
     */
    public function clearUserCache(int $subscriptionUserId)
    {
        Cache::forget("sandbox_user_{$subscriptionUserId}");
        Cache::forget("sandbox_subscription_{$subscriptionUserId}");
    }

    /**
     * Find user in Sandbox by email
     */
    public function findUserByEmail(string $email)
    {
        try {
            Log::info('Looking up Sandbox user by email', [
                'email' => $email,
            ]);

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl . '/api/rizqmall/user-by-email', [
                    'email' => $email,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['success'] ?? false) {
                    Log::info('Found Sandbox user by email', [
                        'email' => $email,
                        'sandbox_user_id' => $data['user']['id'] ?? null,
                    ]);

                    return $data['user'];
                }
            }

            Log::info('No Sandbox user found with email', [
                'email' => $email,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error finding Sandbox user by email', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Create user in Sandbox when they register in RizqMall
     */
    public function createUserInSandbox(array $userData)
    {
        try {
            Log::info('Creating user in Sandbox via API', [
                'email' => $userData['email'] ?? null,
            ]);

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->post($this->baseUrl . '/api/rizqmall/create-user', $userData);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['success'] ?? false) {
                    Log::info('User created successfully in Sandbox', [
                        'sandbox_user_id' => $data['user']['id'] ?? null,
                        'email' => $userData['email'] ?? null,
                    ]);

                    return $data['user'];
                }
            }

            Log::error('Failed to create user in Sandbox', [
                'status' => $response->status(),
                'response' => $response->json(),
                'email' => $userData['email'] ?? null,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error creating user in Sandbox', [
                'error' => $e->getMessage(),
                'email' => $userData['email'] ?? null,
            ]);
            return null;
        }
    }

    /**
     * Get full user data from Sandbox
     */
    public function getUserData(int $subscriptionUserId)
    {
        try {
            Log::info('Fetching user data from Sandbox', [
                'subscription_user_id' => $subscriptionUserId,
            ]);

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl . "/api/rizqmall/user/{$subscriptionUserId}");

            if ($response->successful()) {
                $data = $response->json();

                if ($data['success'] ?? false) {
                    Log::info('Successfully fetched user data from Sandbox', [
                        'subscription_user_id' => $subscriptionUserId,
                        'has_rizqmall_subscription' => $data['user']['has_rizqmall_subscription'] ?? false,
                    ]);

                    return $data['user'];
                }
            }

            Log::warning('Failed to fetch user data from Sandbox', [
                'subscription_user_id' => $subscriptionUserId,
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error fetching user data from Sandbox', [
                'subscription_user_id' => $subscriptionUserId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get store quota for user
     */
    public function getStoreQuota(int $subscriptionUserId)
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl . "/api/rizqmall/store-quota/{$subscriptionUserId}");

            if ($response->successful()) {
                $data = $response->json();
                return $data['quota'] ?? 0;
            }

            return 0;
        } catch (\Exception $e) {
            Log::error('Error fetching store quota from Sandbox', [
                'user_id' => $subscriptionUserId,
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }
}
