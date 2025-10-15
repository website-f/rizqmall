<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SubscriptionService
{
    private $baseUrl;
    private $apiKey;
    private $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.subscription.base_url');
        $this->apiKey = config('services.subscription.api_key');
        $this->timeout = config('services.subscription.timeout', 10);
    }

    /**
     * Fetch user from subscription system and sync to local database
     */
    public function syncUser($subscriptionUserId)
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get("{$this->baseUrl}/api/users/{$subscriptionUserId}");

            if (!$response->successful()) {
                Log::error('Failed to fetch user from subscription system', [
                    'user_id' => $subscriptionUserId,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                return null;
            }

            $userData = $response->json('data');
            return $this->updateOrCreateLocalUser($userData);

        } catch (\Exception $e) {
            Log::error('Error syncing user from subscription system', [
                'user_id' => $subscriptionUserId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Update or create local user from subscription data
     * THIS IS WHERE THE USER GETS SAVED TO RIZQMALL DATABASE
     */
    private function updateOrCreateLocalUser($userData)
    {
        // Log the sync operation
        Log::info('Syncing user to RizqMall database', [
            'subscription_user_id' => $userData['id'],
            'email' => $userData['email'],
        ]);

        // Create or update user in RizqMall database
        $user = User::updateOrCreate(
            ['subscription_user_id' => $userData['id']], // Find by subscription_user_id
            [
                'name' => $userData['name'],
                'email' => $userData['email'],
                'phone' => $userData['phone'] ?? null,
                'avatar' => $userData['avatar'] ?? null,
                'user_type' => $userData['account_type'] === 'vendor' ? 'vendor' : 'customer',
                'email_verified' => $userData['email_verified_at'] !== null,
                'email_verified_at' => $userData['email_verified_at'],
                'subscription_status' => $userData['subscription']['status'] ?? null,
                'subscription_expires_at' => $userData['subscription']['expires_at'] ?? null,
                'is_active' => true,
            ]
        );

        Log::info('User synced successfully', [
            'local_user_id' => $user->id,
            'subscription_user_id' => $userData['id'],
        ]);

        return $user;
    }

    /**
     * Verify subscription status
     */
    public function verifySubscription($subscriptionUserId)
    {
        $cacheKey = "subscription_status_{$subscriptionUserId}";
        
        return Cache::remember($cacheKey, 300, function () use ($subscriptionUserId) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Accept' => 'application/json',
                    ])
                    ->get("{$this->baseUrl}/api/subscriptions/verify/{$subscriptionUserId}");

                if ($response->successful()) {
                    return $response->json('data');
                }

                return null;
            } catch (\Exception $e) {
                Log::error('Error verifying subscription', [
                    'user_id' => $subscriptionUserId,
                    'error' => $e->getMessage(),
                ]);
                return null;
            }
        });
    }

    /**
     * Validate access token from subscription system
     */
    public function validateToken($token)
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ])
                ->post("{$this->baseUrl}/api/auth/validate");

            return $response->successful() ? $response->json('data') : null;

        } catch (\Exception $e) {
            Log::error('Error validating token', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get user profile from subscription system
     */
    public function getUserProfile($subscriptionUserId)
    {
        $cacheKey = "user_profile_{$subscriptionUserId}";
        
        return Cache::remember($cacheKey, 600, function () use ($subscriptionUserId) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Accept' => 'application/json',
                    ])
                    ->get("{$this->baseUrl}/api/users/{$subscriptionUserId}/profile");

                return $response->successful() ? $response->json('data') : null;

            } catch (\Exception $e) {
                Log::error('Error fetching user profile', [
                    'user_id' => $subscriptionUserId,
                    'error' => $e->getMessage(),
                ]);
                return null;
            }
        });
    }

    /**
     * Notify subscription system about events
     */
    public function notifyEvent($event, $data)
    {
        try {
            Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->post("{$this->baseUrl}/api/webhooks/rizqmall", [
                    'event' => $event,
                    'data' => $data,
                    'timestamp' => now()->toIso8601String(),
                ]);

        } catch (\Exception $e) {
            Log::warning('Failed to notify subscription system', [
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Clear user cache
     */
    public function clearUserCache($subscriptionUserId)
    {
        Cache::forget("subscription_status_{$subscriptionUserId}");
        Cache::forget("user_profile_{$subscriptionUserId}");
    }
}