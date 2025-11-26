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
